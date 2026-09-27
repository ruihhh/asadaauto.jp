<?php

namespace Tests\Unit;

use App\Support\BusinessHours;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessHoursTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function tokyo(string $datetime): CarbonImmutable
    {
        return CarbonImmutable::parse($datetime, 'Asia/Tokyo');
    }

    public function test_thursday_is_closed(): void
    {
        $this->assertTrue(BusinessHours::isClosed($this->tokyo('2026-10-01')));
        $this->assertSame('weekday', BusinessHours::closedReason($this->tokyo('2026-10-01')));
    }

    public function test_third_sunday_is_closed(): void
    {
        $this->assertTrue(BusinessHours::isClosed($this->tokyo('2026-10-18')));
        $this->assertSame('nth_weekday', BusinessHours::closedReason($this->tokyo('2026-10-18')));
    }

    public function test_other_sundays_are_open(): void
    {
        $this->assertFalse(BusinessHours::isClosed($this->tokyo('2026-10-25'))); // 第4日曜
        $this->assertFalse(BusinessHours::isClosed($this->tokyo('2026-10-11'))); // 第2日曜
        $this->assertFalse(BusinessHours::isClosed($this->tokyo('2026-09-27'))); // 第4日曜
    }

    public function test_before_opening(): void
    {
        $status = BusinessHours::status($this->tokyo('2026-10-19 10:00'));

        $this->assertSame('before', $status['state']);
        $this->assertSame('本日 11:00から営業', $status['label']);
        $this->assertSame('開店前', $status['short']);
        $this->assertTrue($status['next']->eq($this->tokyo('2026-10-19 11:00')));
    }

    public function test_after_closing(): void
    {
        $status = BusinessHours::status($this->tokyo('2026-10-19 21:30'));

        $this->assertSame('after', $status['state']);
        $this->assertSame('本日の営業は終了しました（次の営業 10月20日（火）11:00〜）', $status['label']);
        $this->assertSame('10月20日（火）11:00〜', $status['next_label']);
    }

    public function test_open(): void
    {
        $status = BusinessHours::status($this->tokyo('2026-09-27 12:00'));

        $this->assertSame('open', $status['state']);
        $this->assertSame('本日営業中（21:00まで）', $status['label']);
        $this->assertSame('ok', $status['tone']);
        $this->assertNull($status['next']);
    }

    public function test_opening_and_closing_boundaries(): void
    {
        $this->assertSame('before', BusinessHours::status($this->tokyo('2026-10-19 10:59'))['state']);
        $this->assertSame('open', BusinessHours::status($this->tokyo('2026-10-19 11:00'))['state']);
        $this->assertSame('open', BusinessHours::status($this->tokyo('2026-10-19 20:59'))['state']);
        $this->assertSame('after', BusinessHours::status($this->tokyo('2026-10-19 21:00'))['state']);
    }

    public function test_closed_day_status(): void
    {
        $thursday = BusinessHours::status($this->tokyo('2026-10-01 12:00'));
        $this->assertSame('closed', $thursday['state']);
        $this->assertSame('本日は定休日です（次の営業 10月2日（金）11:00〜）', $thursday['label']);
        $this->assertSame('caution', $thursday['tone']);

        $thirdSunday = BusinessHours::status($this->tokyo('2026-10-18 09:00'));
        $this->assertSame('closed', $thirdSunday['state']);
        $this->assertSame('本日は定休日です（次の営業 10月19日（月）11:00〜）', $thirdSunday['label']);
    }

    public function test_next_open_skips_thursday_and_third_sunday(): void
    {
        // 水曜の閉店後 → 木曜（定休）を飛ばして金曜
        $this->assertTrue(BusinessHours::nextOpen($this->tokyo('2026-09-30 21:30'))->eq($this->tokyo('2026-10-02 11:00')));

        // 土曜の閉店後 → 第3日曜（定休）を飛ばして月曜
        $this->assertTrue(BusinessHours::nextOpen($this->tokyo('2026-10-17 21:30'))->eq($this->tokyo('2026-10-19 11:00')));

        // 第4日曜は営業日なので飛ばさない
        $this->assertTrue(BusinessHours::nextOpen($this->tokyo('2026-10-24 21:30'))->eq($this->tokyo('2026-10-25 11:00')));
    }

    public function test_judges_in_tokyo_time_regardless_of_input_timezone(): void
    {
        // UTC 03:00 = 東京 12:00（日曜・営業中）
        $this->assertSame('open', BusinessHours::status(CarbonImmutable::parse('2026-09-27 03:00', 'UTC'))['state']);

        // UTC 水曜 15:30 = 東京 木曜 0:30（定休日）
        $this->assertSame('closed', BusinessHours::status(CarbonImmutable::parse('2026-09-30 15:30', 'UTC'))['state']);

        // 引数なしは現在時刻（テスト用に固定）で判定する
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-27 12:30', 'UTC')); // 東京 21:30
        $this->assertSame('after', BusinessHours::status()['state']);
    }

    public function test_temporary_holidays_from_config(): void
    {
        config(['shop.holidays' => ['2026-10-05' => '店内改装のため']]);

        $this->assertTrue(BusinessHours::isClosed($this->tokyo('2026-10-05')));

        $status = BusinessHours::status($this->tokyo('2026-10-05 12:00'));
        $this->assertSame('closed', $status['state']);
        $this->assertSame('holiday', $status['reason']);
        $this->assertSame('店内改装のため', $status['holiday_note']);
        $this->assertSame('本日は臨時休業です（次の営業 10月6日（火）11:00〜）', $status['label']);

        // 前日の閉店後は臨時休業日を飛ばす
        $this->assertTrue(BusinessHours::nextOpen($this->tokyo('2026-10-04 22:00'))->eq($this->tokyo('2026-10-06 11:00')));

        $holidays = BusinessHours::upcomingHolidays($this->tokyo('2026-09-27'));
        $this->assertCount(1, $holidays);
        $this->assertSame('10月5日（月）', $holidays[0]['label']);
        $this->assertSame('店内改装のため', $holidays[0]['reason']);
        $this->assertSame([], BusinessHours::upcomingHolidays($this->tokyo('2026-10-06')));
    }

    public function test_week_table(): void
    {
        $week = BusinessHours::week($this->tokyo('2026-09-27 12:00'));

        $this->assertCount(7, $week);
        $this->assertSame(['月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'], array_column($week, 'label'));

        $thursday = $week[3];
        $this->assertTrue($thursday['closed']);
        $this->assertSame('定休日', $thursday['text']);
        $this->assertNull($thursday['hours']);

        $sunday = $week[6];
        $this->assertFalse($sunday['closed']);
        $this->assertSame('第3日曜は定休', $sunday['note']);
        $this->assertSame('11:00〜21:00（第3日曜は定休）', $sunday['text']);
        $this->assertTrue($sunday['today']);
        $this->assertFalse($sunday['today_closed']);

        $this->assertSame('11:00〜21:00', $week[0]['text']);
        $this->assertSame([false, false, false, false, false, false, true], array_column($week, 'today'));

        // 第3日曜の当日は、今日の行が休み扱いになる
        $this->assertTrue(BusinessHours::week($this->tokyo('2026-10-18 12:00'))[6]['today_closed']);
    }

    public function test_upcoming_third_sundays(): void
    {
        $dates = array_map(
            fn (CarbonImmutable $date): string => $date->toDateString(),
            BusinessHours::upcomingNthClosed(3, $this->tokyo('2026-09-27 12:00')),
        );

        $this->assertSame(['2026-10-18', '2026-11-15', '2026-12-20'], $dates);

        // 当日が第3日曜なら当日を含む
        $this->assertSame('2026-10-18', BusinessHours::upcomingNthClosed(1, $this->tokyo('2026-10-18 20:00'))[0]->toDateString());
    }

    public function test_labels(): void
    {
        $this->assertSame('10月18日（日）', BusinessHours::dateLabel($this->tokyo('2026-10-18')));
        $this->assertSame('11:00〜21:00', BusinessHours::hoursLabel());
        $this->assertSame('11:00〜21:00（木曜・第3日曜定休）', BusinessHours::summary());
        // 画面用：「（木曜・第3日曜定休）」をひとかたまりにする（語の途中で改行しない）
        $this->assertSame('11:00〜21:00<span class="u-nowrap">（木曜・第3日曜定休）</span>', BusinessHours::summaryHtml()->toHtml());

        config(['shop.closed_label' => '']);
        $this->assertSame('11:00〜21:00', BusinessHours::summaryHtml()->toHtml());
    }

    public function test_schema_opening_hours(): void
    {
        $schema = BusinessHours::schemaOpeningHours(3, $this->tokyo('2026-09-27'));

        $this->assertSame(
            ['Monday', 'Tuesday', 'Wednesday', 'Friday', 'Saturday', 'Sunday'],
            $schema['openingHoursSpecification'][0]['dayOfWeek'],
        );
        $this->assertSame(['2026-10-18', '2026-11-15', '2026-12-20'], array_column($schema['specialOpeningHoursSpecification'], 'validFrom'));
        $this->assertSame('00:00', $schema['specialOpeningHoursSpecification'][0]['opens']);
        $this->assertSame('00:00', $schema['specialOpeningHoursSpecification'][0]['closes']);
    }
}
