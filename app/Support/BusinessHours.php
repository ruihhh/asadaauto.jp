<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\HtmlString;

/**
 * 営業状況の判定。
 *
 * アプリは UTC で動いているため（APP_TIMEZONE は変えない）、判定は常に Asia/Tokyo で行う。
 * 営業時間・定休日（毎週の曜日・第n曜日・臨時休業）は config/shop.php から読む。
 */
class BusinessHours
{
    public const TIMEZONE = 'Asia/Tokyo';

    private const WEEKDAY_SHORT = ['日', '月', '火', '水', '木', '金', '土'];

    private const SCHEMA_DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * その日（東京の暦日）が休みか。
     */
    public static function isClosed(CarbonInterface $date): bool
    {
        return self::closedReason($date) !== null;
    }

    /**
     * 休みの理由。営業日なら null。
     *
     * @return 'holiday'|'weekday'|'nth_weekday'|null holiday=臨時休業, weekday=毎週の定休日, nth_weekday=第n曜日の定休日
     */
    public static function closedReason(CarbonInterface $date): ?string
    {
        $day = self::local($date);

        if (array_key_exists($day->format('Y-m-d'), self::holidays())) {
            return 'holiday';
        }

        if (in_array($day->dayOfWeek, self::closedWeekdays(), true)) {
            return 'weekday';
        }

        foreach (self::closedNthWeekdays() as [$nth, $weekday]) {
            if ($day->dayOfWeek === $weekday && self::nthOfMonth($day) === $nth) {
                return 'nth_weekday';
            }
        }

        return null;
    }

    /**
     * 本日の営業状況。
     *
     * @return array{
     *     state: 'open'|'before'|'after'|'closed',
     *     label: string,
     *     short: string,
     *     tone: 'ok'|'neutral'|'caution',
     *     reason: 'holiday'|'weekday'|'nth_weekday'|null,
     *     holiday_note: ?string,
     *     open: string,
     *     close: string,
     *     next: ?CarbonImmutable,
     *     next_label: ?string,
     * }
     */
    public static function status(?CarbonInterface $now = null): array
    {
        $now = $now !== null ? self::local($now) : self::now();
        [$open, $close] = self::openingTimes($now);
        $reason = self::closedReason($now);

        $state = match (true) {
            $reason !== null => 'closed',
            $now->lt($open) => 'before',
            $now->lt($close) => 'open',
            default => 'after',
        };

        $hours = self::hoursConfig();
        $openText = $hours['open'];
        $closeText = $hours['close'];
        $next = $state === 'open' ? null : self::nextOpen($now);
        $nextLabel = $next !== null ? self::dateLabel($next).$openText.'〜' : null;

        [$label, $short, $tone] = match ($state) {
            'open' => ["本日営業中（{$closeText}まで）", '営業中', 'ok'],
            'before' => ["本日 {$openText}から営業", '開店前', 'neutral'],
            'after' => ["本日の営業は終了しました（次の営業 {$nextLabel}）", '営業終了', 'neutral'],
            'closed' => $reason === 'holiday'
                ? ["本日は臨時休業です（次の営業 {$nextLabel}）", '臨時休業', 'caution']
                : ["本日は定休日です（次の営業 {$nextLabel}）", '定休日', 'caution'],
        };

        return [
            'state' => $state,
            'label' => $label,
            'short' => $short,
            'tone' => $tone,
            'reason' => $reason,
            'holiday_note' => $reason === 'holiday' ? self::holidays()[$now->format('Y-m-d')] : null,
            'open' => $openText,
            'close' => $closeText,
            'next' => $next,
            'next_label' => $nextLabel,
        ];
    }

    /**
     * $now より後で、いちばん近い開店の日時（Asia/Tokyo）。
     */
    public static function nextOpen(?CarbonInterface $now = null): CarbonImmutable
    {
        $now = $now !== null ? self::local($now) : self::now();
        $day = $now->startOfDay();

        // 臨時休業が続いても1年先までは探す
        for ($i = 0; $i <= 366; $i++) {
            $candidate = $day->addDays($i);
            if (self::isClosed($candidate)) {
                continue;
            }

            [$open] = self::openingTimes($candidate);
            if ($open->gt($now)) {
                return $open;
            }
        }

        return self::openingTimes($day->addDays(367))[0];
    }

    /**
     * 営業時間表（月曜〜日曜の7行）。
     *
     * @return list<array{
     *     weekday: int,
     *     label: string,
     *     short: string,
     *     hours: ?string,
     *     text: string,
     *     closed: bool,
     *     note: ?string,
     *     today: bool,
     *     today_closed: bool,
     * }>
     */
    public static function week(?CarbonInterface $now = null): array
    {
        $now = $now !== null ? self::local($now) : self::now();
        $hours = self::hoursLabel();
        $rows = [];

        foreach ([1, 2, 3, 4, 5, 6, 0] as $weekday) {
            $closed = in_array($weekday, self::closedWeekdays(), true);

            $notes = [];
            foreach (self::closedNthWeekdays() as [$nth, $nthWeekday]) {
                if ($nthWeekday === $weekday && ! $closed) {
                    $notes[] = '第'.$nth.self::WEEKDAY_SHORT[$weekday].'曜は定休';
                }
            }
            $note = $notes !== [] ? implode('・', $notes) : null;

            $isToday = $now->dayOfWeek === $weekday;

            $rows[] = [
                'weekday' => $weekday,
                'label' => self::WEEKDAY_SHORT[$weekday].'曜日',
                'short' => self::WEEKDAY_SHORT[$weekday],
                'hours' => $closed ? null : $hours,
                'text' => $closed ? '定休日' : $hours.($note !== null ? "（{$note}）" : ''),
                'closed' => $closed,
                'note' => $note,
                'today' => $isToday,
                'today_closed' => $isToday && self::isClosed($now),
            ];
        }

        return $rows;
    }

    /**
     * これから来る第n曜日の定休日（今日を含む）。
     *
     * @return list<CarbonImmutable> 東京の 0:00
     */
    public static function upcomingNthClosed(int $count = 3, ?CarbonInterface $from = null): array
    {
        $rules = self::closedNthWeekdays();
        if ($rules === [] || $count < 1) {
            return [];
        }

        $today = ($from !== null ? self::local($from) : self::now())->startOfDay();
        $dates = [];

        for ($m = 0; $m < 36 && count($dates) < $count; $m++) {
            $month = $today->startOfMonth()->addMonthsNoOverflow($m);
            $found = [];

            foreach ($rules as [$nth, $weekday]) {
                $offset = ($weekday - $month->dayOfWeek + 7) % 7;
                $date = $month->addDays($offset + ($nth - 1) * 7);
                if ($date->month === $month->month && $date->gte($today)) {
                    $found[] = $date;
                }
            }

            usort($found, fn (CarbonImmutable $a, CarbonImmutable $b): int => $a <=> $b);
            array_push($dates, ...$found);
        }

        return array_slice($dates, 0, $count);
    }

    /**
     * config('shop.holidays') に登録した臨時休業のうち、今日以降のもの（日付順）。
     *
     * @return list<array{date: CarbonImmutable, label: string, reason: string}>
     */
    public static function upcomingHolidays(?CarbonInterface $from = null): array
    {
        $today = ($from !== null ? self::local($from) : self::now())->format('Y-m-d');
        $items = [];

        foreach (self::holidays() as $ymd => $reason) {
            if ($ymd < $today) {
                continue;
            }

            $date = CarbonImmutable::createFromFormat('!Y-m-d', (string) $ymd, self::TIMEZONE);
            if ($date === false) {
                continue;
            }
            $items[] = ['date' => $date, 'label' => self::dateLabel($date), 'reason' => (string) $reason];
        }

        usort($items, fn (array $a, array $b): int => $a['date'] <=> $b['date']);

        return $items;
    }

    /**
     * 「10月18日（日）」の形。
     */
    public static function dateLabel(CarbonInterface $date): string
    {
        $day = self::local($date);

        return $day->format('n月j日').'（'.self::WEEKDAY_SHORT[$day->dayOfWeek].'）';
    }

    /**
     * 「11:00〜21:00」の形。
     */
    public static function hoursLabel(): string
    {
        $hours = self::hoursConfig();

        return $hours['open'].'〜'.$hours['close'];
    }

    /**
     * 「11:00〜21:00（木曜・第3日曜定休）」の形。
     */
    public static function summary(): string
    {
        $closed = (string) config('shop.closed_label', '');

        return self::hoursLabel().($closed !== '' ? "（{$closed}定休）" : '');
    }

    /**
     * summary() の画面表示用。「（木曜・第3日曜定休）」をひとかたまり（u-nowrap）にして、
     * 狭い画面で「定／休）」のように語の途中で改行されないようにする。
     */
    public static function summaryHtml(): HtmlString
    {
        $closed = (string) config('shop.closed_label', '');

        return new HtmlString(e(self::hoursLabel()).($closed !== '' ? '<span class="u-nowrap">（'.e($closed).'定休）</span>' : ''));
    }

    /**
     * AutoDealer の JSON-LD 用。定休日（第n曜日・臨時休業）は specialOpeningHoursSpecification で終日休み（00:00〜00:00）にする。
     *
     * @return array{openingHoursSpecification: list<array<string, mixed>>, specialOpeningHoursSpecification: list<array<string, string>>}
     */
    public static function schemaOpeningHours(int $nthCount = 3, ?CarbonInterface $from = null): array
    {
        $hours = self::hoursConfig();
        $openDays = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $weekday) {
            if (! in_array($weekday, self::closedWeekdays(), true)) {
                $openDays[] = self::SCHEMA_DAYS[$weekday];
            }
        }

        $closedDates = array_map(
            fn (CarbonImmutable $date): string => $date->toDateString(),
            self::upcomingNthClosed($nthCount, $from),
        );
        foreach (self::upcomingHolidays($from) as $holiday) {
            $closedDates[] = $holiday['date']->toDateString();
        }
        $closedDates = array_values(array_unique($closedDates));
        sort($closedDates);

        return [
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $openDays,
                'opens' => $hours['open'],
                'closes' => $hours['close'],
            ]],
            'specialOpeningHoursSpecification' => array_map(fn (string $ymd): array => [
                '@type' => 'OpeningHoursSpecification',
                'validFrom' => $ymd,
                'validThrough' => $ymd,
                'opens' => '00:00',
                'closes' => '00:00',
            ], $closedDates),
        ];
    }

    private static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE);
    }

    private static function local(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::instance($date)->setTimezone(self::TIMEZONE);
    }

    /**
     * その日の開店・閉店の日時。
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function openingTimes(CarbonImmutable $day): array
    {
        $hours = self::hoursConfig();
        [$openH, $openM] = array_map('intval', explode(':', $hours['open']));
        [$closeH, $closeM] = array_map('intval', explode(':', $hours['close']));

        return [
            $day->setTime($openH, $openM),
            $day->setTime($closeH, $closeM),
        ];
    }

    /**
     * @return array{open: string, close: string}
     */
    private static function hoursConfig(): array
    {
        $hours = (array) config('shop.hours', []);

        return [
            'open' => (string) ($hours['open'] ?? '11:00'),
            'close' => (string) ($hours['close'] ?? '21:00'),
        ];
    }

    /**
     * @return list<int>
     */
    private static function closedWeekdays(): array
    {
        return array_map('intval', (array) config('shop.closed_weekdays', []));
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    private static function closedNthWeekdays(): array
    {
        return array_map(
            fn ($rule): array => [(int) $rule[0], (int) $rule[1]],
            (array) config('shop.closed_nth_weekdays', []),
        );
    }

    /**
     * @return array<string, string>
     */
    private static function holidays(): array
    {
        return (array) config('shop.holidays', []);
    }

    private static function nthOfMonth(CarbonImmutable $day): int
    {
        return intdiv($day->day - 1, 7) + 1;
    }
}
