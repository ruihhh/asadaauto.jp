<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 店舗案内・アクセス（/store）の画面。
 * 共通部品（営業時間表・よくある質問など）の中身は SiteComponentsTest が確かめる。ここではページの構成・文言・構造化データを確かめる。
 *
 * 案B（2026-09-27 オーナー決定）で、見出し帯（x-site.page-header）の代わりに、看板入りの店舗外観の写真ヒーロー（c-hero）に
 * 斜め帯の h1「店舗案内・アクセス」を置く形にした。リード文はヒーローの下の「お店の基本情報」のパネルに置く。
 */
class StorePageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        \Carbon\Carbon::setTestNow();

        parent::tearDown();
    }

    private function freezeTokyo(string $datetime): void
    {
        $now = CarbonImmutable::parse($datetime, 'Asia/Tokyo')->setTimezone('UTC');
        CarbonImmutable::setTestNow($now);
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::instance($now));
    }

    public function test_store_page_is_built_from_new_parts(): void
    {
        config(['shop.line_url' => null]);

        $response = $this->get(route('store'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'h1 はページに1つだけ');
        $this->assertSame(1, substr_count($html, '<main'), '<main> はレイアウトの1つだけ');
        $this->assertStringNotContainsString(' style="', $html);

        // h1 は写真ヒーローの斜め帯（黒「店舗案内・」＋赤「アクセス」）。読み上げると「店舗案内・アクセス」
        $this->assertMatchesRegularExpression(
            '#<h1 class="c-hero__catch" id="store-title">\s*<span class="c-slant c-slant--black">店舗案内・</span>\s*<span class="c-slant c-slant--red">アクセス</span>\s*</h1>#u',
            $html
        );
        preg_match('#<h1[^>]*>(.*?)</h1>#su', $html, $h1);
        $this->assertSame('店舗案内・アクセス', preg_replace('/\s+/u', '', strip_tags($h1[1])));

        $response
            ->assertSee('<title>店舗案内・アクセス | '.config('shop.name').'</title>', false)
            ->assertSee('<nav class="c-breadcrumb" aria-label="現在地">', false)
            ->assertSee('<span class="c-breadcrumb__current" aria-current="page">店舗案内・アクセス</span>', false)
            ->assertSee('class="c-hero p-store-top"', false)
            ->assertSee('class="c-hero__pill"', false)
            ->assertSee('兵庫県尼崎市下坂部の中古車販売店です。お車でも、電車・バスでもお越しいただけます。')
            ->assertSee('class="p-store', false)
            ->assertSee('id="map"', false)
            ->assertSee('id="hours"', false)
            ->assertSee('看板が目印です')
            ->assertSee('images/store-hero-bg.png.webp', false)
            ->assertSee('地図アプリで道順を見る')
            ->assertSee('href="'.e(config('shop.directions_url')).'"', false)
            ->assertSee('aria-label="電話をかける '.config('shop.tel').'"', false)
            ->assertSee('href="'.config('shop.tel_href').'"', false)
            ->assertSee(config('shop.parking'))
            ->assertSee('JR尼崎駅')
            ->assertSee('阪神尼崎駅')
            ->assertSee('駅までの送迎')
            ->assertSee('会社概要')
            ->assertSee('朝田 繕行')
            ->assertSee(route('home').'#faq', false)
            ->assertSee(route('home').'#promise', false)
            ->assertSee('ご相談・ご来店はこちら')
            ->assertDontSee('LINEで相談');

        // 事業内容（看板の6業務）は、業務名の途中で改行しない形で会社概要に出る
        $this->assertStringContainsString(implode('・', config('shop.business_lines')), strip_tags($html));
        $this->assertStringContainsString('<span class="u-nowrap">一般整備・</span>', $html);
    }

    public function test_basic_info_panel_has_two_step_buttons_and_page_links(): void
    {
        config(['shop.line_url' => null]);

        $html = $this->get(route('store'))->assertOk()->getContent();
        $deck = substr($html, strpos($html, 'class="p-store-deck"'), strpos($html, 'id="map"') - strpos($html, 'class="p-store-deck"'));

        // 大きな電話番号と、2段ボタン［電話する］［地図アプリで道順を見る］（道順は新しいタブ）
        $this->assertStringContainsString('<a class="p-store-facts__tel" href="'.config('shop.tel_href').'" aria-label="電話をかける '.config('shop.tel').'">'.config('shop.tel').'</a>', $deck);
        $this->assertMatchesRegularExpression('#<a class="c-btn2 c-btn2--red c-btn2--xl c-btn2--block" aria-label="電話をかける '.preg_quote(config('shop.tel'), '#').'" href="'.preg_quote(config('shop.tel_href'), '#').'">#', $deck);
        $this->assertStringContainsString('<span class="c-btn2__big"><svg', $deck);
        $this->assertStringContainsString('電話する', $deck);
        $this->assertMatchesRegularExpression('#<a class="c-btn2 c-btn2--black c-btn2--xl c-btn2--block" target="_blank" rel="noopener" aria-label="地図アプリで道順を見る（新しいタブで開きます）" href="'.preg_quote(e(config('shop.directions_url')), '#').'">#', $deck);

        // 営業時間・定休日（橙）・駐車場・本日の営業状況
        $this->assertStringContainsString('class="c-open-status c-open-status--badge', $deck);
        $this->assertStringContainsString('<dd class="p-store-facts__value p-store-facts__value--closed">'.config('shop.closed_label').'</dd>', $deck);

        // 無料駐車場・駅までの送迎の黄の丸バッジ（数字や最上級の表現は入れない）
        $this->assertStringContainsString('<span class="c-round-badge__num">駐車場</span>', $deck);
        $this->assertStringContainsString('<span class="c-round-badge__num">送迎</span>', $deck);

        // ページ内のリンク（地図・営業時間・当店でできること・よくある質問）
        foreach (['#map', '#hours', '#service', '#faq'] as $anchor) {
            $this->assertStringContainsString('href="'.$anchor.'"', $deck);
        }
        foreach (['id="service"', 'id="faq"', 'id="company"'] as $id) {
            $this->assertStringContainsString($id, $html);
        }
    }

    public function test_removed_claims_and_dummy_content_do_not_appear(): void
    {
        $html = $this->get(route('store'))->assertOk()->getContent();

        foreach ([
            '100台', '麻田', '山田 花子', '佐藤 健一', 'STORE INFO', '他社より', '事故歴', 'ございます',
            '最長84回', '3ヶ月', '5,000km', '後から追加費用', '第三者', '全国陸送', 'メールでお問い合わせ',
            'JR東西線', 'store-card', 'hours-week', 'btn-primary', 'No.1', '地域最大', '多数',
        ] as $text) {
            $this->assertStringNotContainsString($text, $html, "「{$text}」が残っている");
        }

        // 旧ヒーロー（store-hero）のクラスが残っていない（写真のファイル名 store-hero-bg.png は使う）
        $this->assertDoesNotMatchRegularExpression('/class="[^"]*store-hero/', $html);

        // 絵文字を使わない
        $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $html);
    }

    public function test_business_hours_use_shop_timezone_and_show_third_sunday_closure(): void
    {
        // 2026-10-18 は第3日曜（定休日）。UTC ではまだ土曜の 2026-10-17 15:30 = 日本時間 10-18 00:30
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-17 15:30:00', 'UTC'));
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-10-17 15:30:00', 'UTC'));

        $html = $this->get(route('store'))
            ->assertOk()
            ->assertSee('class="c-hours"', false)
            ->assertSee('本日は定休日です')
            ->assertSee('（第3日曜は定休）')
            ->assertSee('<span class="c-hours__closed">定休日</span>', false)
            ->getContent();

        // 営業日カレンダーでも、東京の今日（10月18日・第3日曜）が「今日」かつ定休日
        $this->assertMatchesRegularExpression('#<td class="p-store-cal__cell is-closed is-today" aria-current="date">\s*<span class="p-store-cal__num">18</span>#', $html);
        // 次の第3日曜は、今日の次の 11月15日
        $this->assertStringContainsString('<b class="u-num">11</b>月<b class="u-num">15</b>日', $html);
    }

    public function test_hours_board_and_calendar_show_closed_days_from_config(): void
    {
        // 2026-10-01（木）＝定休日の朝（日本時間）
        $this->freezeTokyo('2026-10-01 10:00:00');

        $html = $this->get(route('store'))->assertOk()->getContent();
        $hours = substr($html, strpos($html, 'id="hours"'), strpos($html, 'id="service"') - strpos($html, 'id="hours"'));

        // 黒い案内板：営業時間の大きな数字・定休日の決まり・次の第3日曜
        $this->assertStringContainsString('<span class="u-num">'.config('shop.hours.open').'</span><span class="p-store-board__tilde">〜</span><span class="u-num">'.config('shop.hours.close').'</span>', $hours);
        $this->assertStringContainsString('<span class="p-store-board__cycle">毎週</span>木曜日', $hours);
        $this->assertStringContainsString('<span class="p-store-board__cycle">毎月</span>第3日曜日', $hours);
        $this->assertStringContainsString('次の第3日曜（定休日）', $hours);
        $this->assertStringContainsString('<b class="u-num">10</b>月<b class="u-num">18</b>日', $hours);
        $this->assertStringContainsString('そのあとは 11月15日（日）・12月20日（日）', $hours);

        // 営業日カレンダー：今月（10月）・来月（11月）のタブ。定休日は木曜（10月は5日・11月は4日）＋第3日曜（10/18・11/15）
        $this->assertStringContainsString('今月（10月）', $hours);
        $this->assertStringContainsString('来月（11月）', $hours);
        $this->assertStringContainsString('2026年10月の営業日カレンダー', $hours);
        $this->assertSame(11, preg_match_all('#<td class="p-store-cal__cell is-closed#', $hours));
        $this->assertSame(11, substr_count($hours, '定休</span>'));
        // 今日（10月1日・木曜）は定休日かつ今日
        $this->assertMatchesRegularExpression('#<td class="p-store-cal__cell is-closed is-today" aria-current="date">\s*<span class="p-store-cal__num">1</span>#', $hours);
        // 臨時休業がない間はお知らせを出さない
        $this->assertStringNotContainsString('臨時休業のお知らせ', $hours);
    }

    public function test_temporary_holidays_appear_in_notice_and_calendar(): void
    {
        $this->freezeTokyo('2026-10-01 10:00:00');
        config(['shop.holidays' => ['2026-10-05' => '店内改装']]);

        $html = $this->get(route('store'))->assertOk()->getContent();
        $hours = substr($html, strpos($html, 'id="hours"'), strpos($html, 'id="service"') - strpos($html, 'id="hours"'));

        $this->assertStringContainsString('臨時休業のお知らせ', $hours);
        $this->assertStringContainsString('10月5日（月）（店内改装）は休業します。', $hours);
        $this->assertMatchesRegularExpression('#<span class="p-store-cal__num">5</span>\s*<span class="p-store-cal__mark"><svg[^>]*>.*?</svg>\s*休業</span>#s', $hours);
    }

    public function test_service_tiles_follow_business_lines_and_count_public_cars(): void
    {
        $html = $this->get(route('store'))->assertOk()->getContent();
        $service = substr($html, strpos($html, 'id="service"'), strpos($html, 'id="company"') - strpos($html, 'id="service"'));

        // 看板の6業務が、アイコンのタイル（リンク）で出る
        $this->assertSame(count(config('shop.business_lines')), substr_count($service, '<a class="p-store-service"'));
        foreach (config('shop.business_lines') as $line) {
            $this->assertStringContainsString('<span class="p-store-service__name">'.$line.'</span>', $service);
        }
        $this->assertStringContainsString('<a class="p-store-service" href="'.route('cars.index').'">', $service);
        $this->assertStringContainsString('<a class="p-store-service" href="'.route('buy.index').'">', $service);
        // 公開中の車がないときは台数を出さない
        $this->assertStringNotContainsString('台を掲載中', $service);

        // 台数は公開中の在庫だけの実数（売約済・未公開は数えない）
        Car::factory()->count(2)->create(['status' => 'available', 'published_at' => now()->subDay()]);
        Car::factory()->create(['status' => 'sold', 'published_at' => now()->subDay()]);
        Car::factory()->create(['status' => 'available', 'published_at' => now()->addDays(3)]);

        $html = $this->get(route('store'))->assertOk()->getContent();
        $this->assertStringContainsString('いま<b class="u-num">2</b>台を掲載中', $html);
    }

    public function test_structured_data_has_breadcrumb_and_faq_and_no_duplicate_dealer(): void
    {
        $html = $this->get(route('store'))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $types = array_map(fn (string $json) => json_decode($json, true)['@type'] ?? null, $matches[1]);

        $this->assertSame(1, count(array_keys($types, 'AutoDealer', true)), 'AutoDealer はレイアウトの1つだけ');
        $this->assertContains('BreadcrumbList', $types);
        $this->assertContains('FAQPage', $types);

        $breadcrumb = json_decode($matches[1][array_search('BreadcrumbList', $types, true)], true);
        $this->assertSame('店舗案内・アクセス', $breadcrumb['itemListElement'][1]['name']);
        $this->assertSame(route('store'), $breadcrumb['itemListElement'][1]['item']);

        // FAQPage の質問と答えは、画面に出ている文面と同じ
        $faq = json_decode($matches[1][array_search('FAQPage', $types, true)], true);
        $this->assertCount(4, $faq['mainEntity']);
        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString(e($question['name']), $html);
            $this->assertStringContainsString(e($question['acceptedAnswer']['text']), $html);
        }
    }

    public function test_optional_rows_and_buttons_follow_config(): void
    {
        config([
            'shop.line_url' => null,
            'shop.kobutsu.number' => null,
            'shop.operator.company' => null,
        ]);
        $this->get(route('store'))
            ->assertOk()
            ->assertDontSee('古物商許可')
            ->assertDontSee('運営会社')
            ->assertDontSee('LINEで相談');

        config([
            'shop.line_url' => 'https://lin.ee/example',
            'shop.kobutsu.number' => '123456789012',
            'shop.kobutsu.holder' => '朝田 繕行',
            'shop.operator.company' => '株式会社サンプル',
        ]);
        $response = $this->get(route('store'))
            ->assertOk()
            ->assertSee('<th scope="row" class="c-table__th">古物商許可</th>', false)
            ->assertSee('兵庫県公安委員会 第123456789012号（朝田 繕行）')
            ->assertSee('<th scope="row" class="c-table__th">運営会社</th>', false)
            ->assertSee('株式会社サンプル');

        // お店の基本情報のボタン列に LINE が加わる（ヘッダー・メニュー・ご相談帯の LINE とは別に）
        $html = $response->getContent();
        $info = substr($html, strpos($html, 'class="p-store-info"'), strpos($html, 'id="map"') - strpos($html, 'class="p-store-info"'));
        $this->assertStringContainsString('LINEで相談', $info);
        $this->assertStringContainsString('href="https://lin.ee/example" target="_blank" rel="noopener"', $info);
    }

    public function test_parking_badge_only_when_parking_is_free(): void
    {
        config(['shop.parking' => '駐車場はお問い合わせください']);

        $html = $this->get(route('store'))->assertOk()->getContent();
        $this->assertStringNotContainsString('<span class="c-round-badge__num">駐車場</span>', $html);
        $this->assertStringContainsString('駐車場はお問い合わせください', $html);
    }

    public function test_map_uses_configured_embed_url_with_title_and_address(): void
    {
        config(['shop.map_embed_url' => null]);
        $this->get(route('store'))
            ->assertOk()
            ->assertSee('title="'.config('shop.name').'の地図（Googleマップ）"', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('https://maps.google.com/maps?q='.rawurlencode(config('shop.address')), false)
            ->assertSee('〒'.config('shop.postal'));

        config(['shop.map_embed_url' => 'https://www.google.com/maps/embed?pb=example']);
        $this->get(route('store'))
            ->assertOk()
            ->assertSee('src="https://www.google.com/maps/embed?pb=example"', false);
    }
}
