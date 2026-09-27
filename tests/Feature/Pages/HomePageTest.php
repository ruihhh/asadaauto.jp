<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * トップページ（/）：案B「車選び型」（写真ヒーロー・STEP の検索・お約束・流れ・お支払いシミュレーション・買取の帯）の構成、
 * 根拠のない表示の削除、config 連動の表示、FAQ の構造化データ。
 *
 * 2026-09-27 の方向性の変更（scratchpad/direction-b.md）で仕様が変わったもの：
 * - お支払いシミュレーターは「計算例（実質年率3.9%の場合）」として常に表示する（config('shop.loan.example_*')）。
 *   旧仕様の「sample_rate が決まるまで非表示」のテストは、計算例の表示・注記・金利の読み方のテストに置き換えた。
 * - ボディタイプは <select> からイラスト付きのラジオのタイルに変えた（name・値・台数・在庫のあるものだけ、は同じ）。
 * - いま掲載中の車は、モックアップ b.html のとおりお気に入り・比較のボタン付きのカードにした（compact をやめた）。
 * - 見出しは「ご購入の流れ」と「お支払いシミュレーション（計算例）」に分けた。英字は aria-hidden の飾り（STEP. など）だけ。
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private function availableCar(array $attributes = []): Car
    {
        return Car::factory()->create(array_merge([
            'status' => 'available',
            'published_at' => now()->subDay(),
            'price_negotiable' => false,
        ], $attributes));
    }

    private function home(): TestResponse
    {
        return $this->get(route('home'))->assertOk();
    }

    public function test_home_has_sections_in_order_with_single_h1(): void
    {
        $this->availableCar(['body_type' => '軽自動車']);

        $html = $this->home()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(1, substr_count($html, '<main'));
        $this->assertLessThanOrEqual(10, substr_count($html, '<h2'));

        $this->home()->assertSeeInOrder([
            'id="top"',
            '納得して選べる、',
            '尼崎の中古車。',
            'id="search"',
            '条件から在庫を探す',
            '使い方から選ぶ',
            'id="stock"',
            'いま掲載中の車',
            'id="promise"',
            '当店の4つのお約束',
            'id="flow"',
            'ご購入の流れ',
            'id="loan"',
            'お支払いシミュレーション',
            'id="buy"',
            '今のお車の下取り・買取',
            'お探しの車が',
            'id="faq"',
            'よくある質問',
            'id="access"',
            '店舗案内',
            '当店について',
            '代表からのごあいさつ',
            'ご相談・ご来店はこちら',
        ], false);

        // h1 はヒーローのキャッチ（斜めの黒・赤の帯）
        $this->assertMatchesRegularExpression('/<h1 class="c-hero__catch" id="home-hero-title">\s*<span class="c-slant c-slant--black">納得して選べる、<\/span>\s*<span class="c-slant c-slant--red">尼崎の中古車。<\/span>/u', $html);
    }

    public function test_home_removes_expired_and_unfounded_content(): void
    {
        $this->availableCar();

        $response = $this->home();
        $html = $response->getContent();

        foreach ([
            '開催中', '8月31日', 'ランキング', '多数', '第三者機関', 'Asada Zenko', 'NEW',
            'aggregateRating', 'lin.ee', 'ございます', '10年以上', '120回', '最短', '最長3年',
            'オーナーズボイス', 'No Image', '/images/hero-bg.jpg', '/images/hero-car.jpg', '事故歴', 'CONTACT', 'home-mobile-bar',
            'しつこい', '他社', '高価買取', 'homeLoanSim', '車両本体価格（総額）',
        ] as $text) {
            $response->assertDontSee($text, false);
        }

        // 英字の「STEP」は読み上げない飾り（aria-hidden の「STEP.」）としてだけ使う
        $withoutDecoration = str_replace('<span aria-hidden="true">STEP.</span>', '', $html);
        $this->assertStringNotContainsString('STEP', $withoutDecoration);

        $this->assertStringNotContainsString(' style="', $html);
        $response->assertSee('store-hero-bg.png.webp', false)
            ->assertSee('店舗外観（看板が目印です）');
    }

    public function test_hero_shows_real_count_and_contacts_from_config(): void
    {
        $this->availableCar();
        $this->availableCar();
        config(['shop.tel' => '06-0000-1111', 'shop.tel_href' => 'tel:0600001111']);

        $html = $this->home()
            ->assertSee('掲載中の車を見る（2台）')
            ->assertSee('aria-label="ただいま2台掲載中。掲載中の車を見る"', false)
            ->assertSee('電話で相談する')
            ->assertDontSee('06-4960-8765')
            ->getContent();

        // ヒーロー下のパネルの電話ボタン：config の番号・tel: リンク・読み上げ名
        $deck = substr($html, strpos($html, '<div class="c-deck">'), 4000);
        $this->assertMatchesRegularExpression('/<a[^>]*class="c-btn2 c-btn2--black[^"]*"[^>]*aria-label="電話をかける 06-0000-1111"[^>]*href="tel:0600001111"/u', $deck);
        $this->assertStringContainsString('06-0000-1111', $deck);
    }

    public function test_line_button_is_shown_only_when_configured(): void
    {
        config(['shop.line_url' => null]);
        $this->home()->assertDontSee('LINEで相談');

        config(['shop.line_url' => 'https://lin.ee/example']);
        $this->home()->assertSee('LINEで相談')->assertSee('https://lin.ee/example', false);
    }

    public function test_search_form_keeps_parameter_names_and_lists_body_types_in_stock(): void
    {
        $this->availableCar(['body_type' => 'SUV']);
        $this->availableCar(['body_type' => 'SUV']);
        $this->availableCar(['body_type' => 'コンパクト']);
        $this->availableCar(['body_type' => 'セダン', 'status' => 'sold']);

        $response = $this->home();
        $html = $response->getContent();

        $response->assertSee('action="'.route('cars.index').'"', false)
            ->assertSee('name="make"', false)
            ->assertSee('name="body_type"', false)
            ->assertSee('name="max_price"', false)
            ->assertSee('name="max_mileage"', false)
            ->assertSee('role="radiogroup" aria-labelledby="home-step1"', false)
            ->assertSee('<option value="1000000">100万円以下</option>', false)
            ->assertSee('<option value="5000000">500万円以下</option>', false)
            // 売約済みの車しかないボディタイプは選択肢に出さない
            ->assertDontSee('value="セダン"', false);

        // ボディタイプはラジオのタイル。値は DB の値のまま、表示名は CarText、台数は公開在庫の実数
        $this->assertMatchesRegularExpression('/name="body_type" value="SUV"\s*>.*?<span class="c-type-choice__name">SUV<\/span>\s*<span class="c-type-choice__count">2台<\/span>/su', $html);
        $this->assertMatchesRegularExpression('/name="body_type" value="コンパクト"\s*>.*?<span class="c-type-choice__name">コンパクトカー<\/span>\s*<span class="c-type-choice__count">1台<\/span>/su', $html);
        $this->assertMatchesRegularExpression('/name="body_type" value=""\s+checked>.*?<span class="c-type-choice__name">すべて<\/span>\s*<span class="c-type-choice__count">3台<\/span>/su', $html);

        $this->assertSame(9, preg_match_all('/<option value="\d+">\d+万円以下<\/option>/u', $html));

        // 「条件に合う車」は公開在庫の実数から数える（売約済みは含めない）
        $response->assertSee('条件に合う車')
            ->assertSee('<span x-text="hits">3</span>', false)
            ->assertSee('この条件で探す');
    }

    public function test_search_count_data_contains_only_public_cars(): void
    {
        $this->availableCar(['make' => 'マツダ', 'body_type' => 'SUV', 'price' => 3350000, 'mileage' => 11200]);
        $this->availableCar(['make' => 'スズキ', 'body_type' => '軽自動車', 'price' => 900000, 'price_negotiable' => true, 'mileage' => 29000]);
        $this->availableCar(['make' => 'トヨタ', 'status' => 'sold']);
        $this->availableCar(['make' => 'ホンダ', 'published_at' => now()->addDay()]);

        $html = $this->home()->getContent();

        // @js() は JSON.parse('…') の形で出すので、JavaScript の文字列を読み戻してから JSON として読む
        preg_match("/x-data=\"homeSearch\\(JSON\\.parse\\('(.*?)'\\)\\)\"/s", $html, $matches);
        $this->assertNotEmpty($matches);
        $data = json_decode((string) json_decode('"'.$matches[1].'"'), true);

        $this->assertCount(2, $data);
        $this->assertSame(['マツダ', 'スズキ'], array_values(array_intersect(['マツダ', 'スズキ'], array_column($data, 'm'))));
        // 応談の車は価格の条件では数えない（在庫一覧の絞り込みと同じ）
        $suzuki = collect($data)->firstWhere('m', 'スズキ');
        $this->assertNull($suzuki['p']);
        $this->assertSame(29000, $suzuki['k']);
    }

    public function test_purpose_entries_are_hidden_when_no_car_matches(): void
    {
        $this->availableCar(['body_type' => 'SUV', 'price' => 2500000]);

        $this->home()
            ->assertSee('休日・レジャーに')
            ->assertDontSee('通勤・お買い物に')
            ->assertDontSee('子育て・家族に')
            ->assertDontSee('予算を抑えたい');

        $this->availableCar(['body_type' => '軽自動車', 'price' => 900000]);

        $this->home()
            ->assertSee('通勤・お買い物に')
            ->assertSee('予算を抑えたい')
            ->assertSee('max_price=1000000', false);
    }

    public function test_chips_link_only_to_filters_the_stock_list_supports(): void
    {
        $this->availableCar(['make' => 'マツダ', 'mileage' => 20000, 'price' => 1800000]);
        $this->availableCar(['make' => 'マツダ', 'mileage' => 60000, 'price' => 2500000]);
        $this->availableCar(['make' => '日産', 'mileage' => 45000, 'price' => 2390000, 'status' => 'sold']);

        $response = $this->home();
        $html = $response->getContent();

        $response->assertSee('メーカー・条件から探す')
            ->assertSee('href="'.route('cars.index', ['make' => 'マツダ']).'"', false)
            ->assertSee('href="'.e(route('cars.index', ['max_mileage' => 30000])).'"', false)
            ->assertSee('href="'.e(route('cars.index', ['max_price' => 2000000])).'"', false)
            // 売約済みだけのメーカーは出さない
            ->assertDontSee('href="'.route('cars.index', ['make' => '日産']).'"', false);

        // 台数は実数（マツダ2台・走行3万km以下1台・支払総額200万円以下1台）
        $this->assertMatchesRegularExpression('/<span>マツダ<\/span>\s*<span class="c-chip__n">2台<\/span>/u', $html);
        $this->assertMatchesRegularExpression('/<span>走行3万km以下<\/span>\s*<span class="c-chip__n">1台<\/span>/u', $html);
        $this->assertMatchesRegularExpression('/<span>支払総額200万円以下<\/span>\s*<span class="c-chip__n">1台<\/span>/u', $html);

        // 在庫一覧に絞り込みがない条件（店長おすすめ・燃料・修復歴）は作らない
        foreach (['featured=', 'fuel=', 'repair=', 'hybrid'] as $query) {
            $this->assertStringNotContainsString($query, $html);
        }
    }

    public function test_stock_shows_up_to_six_public_cars_with_shared_card(): void
    {
        foreach (range(1, 8) as $i) {
            $this->availableCar(['published_at' => now()->subDays($i)]);
        }
        $this->availableCar(['status' => 'sold']);

        $response = $this->home();

        $this->assertSame(6, substr_count($response->getContent(), '<article class="c-car-card'));
        $response->assertSee('全8台のうち、新しく掲載した6台を表示しています。')
            ->assertSee('全<b>8</b>台', false)
            ->assertSee('すべての在庫を見る（8台）')
            ->assertSee('支払総額は兵庫県内で登録し、当店で店頭納車する場合の価格です。');
    }

    public function test_stock_shows_empty_state_when_no_cars(): void
    {
        $this->home()
            ->assertSee('ただいま掲載中の車はありません')
            ->assertSee('探してほしい車を伝える')
            ->assertDontSee('掲載中の車を見る（0台）')
            ->assertDontSee('c-hero__badge', false)
            ->assertDontSee('<article class="c-car-card', false);
    }

    public function test_promise_uses_config_for_warranty_and_maintenance(): void
    {
        config(['shop.warranty' => null, 'shop.maintenance' => null]);
        $this->home()
            ->assertSee('<span class="p-home-promise__label">保証</span>お問い合わせください', false)
            ->assertSee('<span class="p-home-promise__label">定期点検整備</span>お問い合わせください', false);

        config(['shop.warranty' => '保証付き（3か月・3,000km）']);
        $this->home()->assertSee('保証付き（3か月・3,000km）');
    }

    public function test_loan_simulator_is_shown_as_calculation_example(): void
    {
        config(['shop.loan.example_rate' => 3.9, 'shop.loan.example_months' => [36, 48, 60, 72, 84], 'shop.loan.default_months' => 60]);

        $this->home()
            ->assertSee('id="loan"', false)
            ->assertSee('お支払いシミュレーション<small>（計算例）</small>', false)
            ->assertSee('x-data="loanSim(', false)
            ->assertSee('<label class="c-loan__label" for="home-loan-price">支払総額</label>', false)
            ->assertSee('for="home-loan-rate"', false)
            ->assertSee('例：実質年率3.9%で計算')
            ->assertSee('60回・実質年率3.9%・頭金0円・ボーナス払いなし')
            ->assertSee('計算例です。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。')
            ->assertSee('value="84"', false)
            ->assertSee(route('contact.index', ['purpose' => 'loan']), false)
            ->assertDontSee('value="96"', false);

        // 回数の例を変えると選択肢も変わる
        config(['shop.loan.example_months' => [36, 48], 'shop.loan.default_months' => 48]);
        $this->home()->assertSee('value="48"', false)->assertDontSee('value="84"', false);
    }

    public function test_loan_rate_is_read_as_percent(): void
    {
        config(['shop.loan.example_rate' => 0.9]);

        // 1未満でも割合としては扱わない（0.9 は 0.9%）
        $this->home()->assertSee('実質年率0.9%')->assertDontSee('実質年率90%');
    }

    public function test_buy_band_badges_state_only_confirmed_promises(): void
    {
        $html = $this->home()->getContent();

        $band = substr($html, strpos($html, 'id="buy"'), 6000);
        $this->assertSame(3, substr_count($band, 'c-round-badge c-round-badge--yellow'));
        // 査定無料・査定だけでもOK・下取りもOK（下取りは「よくある質問」の答えと同じ事実）。オーナー未確認の約束は出さない
        $this->assertMatchesRegularExpression('/<span class="c-round-badge__top">査定<\/span>\s*<span class="c-round-badge__num">無料<\/span>/u', $band);
        $this->assertMatchesRegularExpression('/<span class="c-round-badge__top">査定だけ<\/span>\s*<span class="c-round-badge__num">でもOK<\/span>/u', $band);
        $this->assertMatchesRegularExpression('/<span class="c-round-badge__num">下取り<\/span>\s*<span class="c-round-badge__bottom">もOK<\/span>/u', $band);
        $this->assertStringContainsString('お乗り換えのときの下取りも、買取だけのご相談もお受けしています。', $html);
        foreach (['しつこい', '営業電話', '即日', '高価'] as $text) {
            $this->assertStringNotContainsString($text, $band);
        }
    }

    public function test_greeting_is_signed_by_representative_without_romaji(): void
    {
        $this->home()
            ->assertSee('代表　朝田 繕行')
            ->assertDontSee('Asada')
            ->assertDontSee('staff-greeting-avatar', false);
    }

    public function test_faq_structured_data_matches_visible_questions(): void
    {
        $html = $this->home()->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schemas = array_map(fn (string $json) => json_decode($json, true), $matches[1]);
        $faq = collect($schemas)->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq);
        $this->assertCount(7, $faq['mainEntity']);
        $this->assertNull(collect($schemas)->firstWhere('aggregateRating'));
        $this->assertSame(1, collect($schemas)->where('@type', 'FAQPage')->count());
        $this->assertSame(1, collect($schemas)->where('@type', 'WebSite')->count());

        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString('<span class="c-faq__qtext">'.e($question['name']).'</span>', $html);
            $firstLine = strtok($question['acceptedAnswer']['text'], "\n");
            $this->assertStringContainsString(e($firstLine), $html);
        }

        $names = array_column($faq['mainEntity'], 'name');
        $this->assertStringContainsString('支払総額', $names[0]);
        $this->assertStringContainsString('修復歴', $names[1]);
        $this->assertStringContainsString('書類', $names[2]);
        $this->assertStringContainsString('ローン', $names[3]);
        $this->assertStringContainsString('予約', $names[4]);
        $this->assertStringContainsString('下取り', $names[5]);
        $this->assertStringContainsString('兵庫県の外', $names[6]);
    }

    public function test_access_section_links_to_store_and_directions(): void
    {
        $this->home()
            ->assertSee('href="'.route('store').'"', false)
            ->assertSee('店舗案内・アクセスを詳しく見る')
            ->assertSee('href="'.e(config('shop.directions_url')).'" target="_blank" rel="noopener"', false)
            ->assertSee('地図アプリで道順を見る')
            ->assertSee('class="c-hours"', false)
            ->assertSee('この看板が目印です');
    }

    public function test_kobutsu_license_is_shown_only_when_number_is_configured(): void
    {
        config(['shop.kobutsu.number' => null]);
        $this->home()->assertDontSee('古物商許可');

        config(['shop.kobutsu.number' => '123456789012']);
        $this->home()->assertSee('古物商許可')->assertSee('第123456789012号');
    }

    public function test_controller_passes_only_what_the_page_uses(): void
    {
        $this->availableCar(['body_type' => '軽自動車', 'price' => 900000]);
        $this->availableCar(['body_type' => 'SUV', 'price' => 2500000]);
        $this->availableCar(['body_type' => '軽自動車', 'price' => 800000, 'price_negotiable' => true]);

        $response = $this->home();

        // 使わなくなったランキング・特集のクエリは実行しない
        foreach (['rankings', 'rankingTypes', 'specials', 'featuredCars', 'bodyTypes'] as $key) {
            $response->assertViewMissing($key);
        }
        // 「予算を抑えたい」の台数はコントローラが数える（応談の車は数えない）
        $response->assertViewHas('budgetLimit', 1000000)
            ->assertViewHas('budgetCount', 1)
            ->assertSee('支払総額100万円以下');
    }

    public function test_stock_uses_shared_cards_with_favorite_and_compare(): void
    {
        $this->availableCar();

        $html = $this->home()->getContent();

        // モックアップ b.html のとおり、トップのカードにもお気に入り・比較のボタンを出す（compact を使わない）
        $this->assertStringContainsString('<article class="c-car-card">', $html);
        $this->assertStringNotContainsString('c-car-card--compact', $html);
        $this->assertStringContainsString('在庫確認・見積もり（無料）', $html);
        $this->assertStringContainsString('c-car-card__toggles', $html);
    }
}
