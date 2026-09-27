<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use App\Support\BusinessHours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * お問い合わせ（/contact）・送信完了（/contact/thanks）の画面。
 * 送信・検証そのものは ContactFormTest が確かめる。ここでは表示（部品・文言・初期選択・エラー表示）を確かめる。
 */
class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_uses_new_parts_and_shows_phone_above_form(): void
    {
        config(['shop.line_url' => null]);

        $response = $this->get(route('contact.index'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'h1 はページに1つだけ');
        $this->assertSame(1, substr_count($html, '<main'), '<main> はレイアウトの1つだけ');
        $this->assertStringNotContainsString('placeholder=', $html);
        $this->assertStringNotContainsString(' style="', $html);

        $response
            ->assertSee('<h1 class="c-page-title">お問い合わせ・来店予約</h1>', false)
            ->assertSee('class="l-mbar l-mbar--contact"', false)
            ->assertDontSee('ご相談・ご来店はこちら')
            ->assertDontSee('LINEで相談')
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('必須の項目だけでも送信できます。')
            ->assertSee('例）MZ4187')
            ->assertSee('例）taro@example.com')
            ->assertSee('data-submit-once', false)
            ->assertSee('id="privacy-consent"', false)
            ->assertSee('この内容で送信する')
            ->assertDontSee('AA-123')
            ->assertDontSee('example@asadaauto.jp');

        // 電話の案内がフォームより上にある
        $this->assertLessThan(
            strpos($html, 'action="'.route('contact.send').'"'),
            strpos($html, 'お急ぎの方はお電話で'),
        );
        $this->assertStringContainsString('aria-label="電話をかける '.config('shop.tel').'"', $html);

        // ご用件の選択肢は送信に含めない（別の空フォームに属させる）
        $this->assertStringContainsString('<form id="contact-purpose" hidden></form>', $html);
        $this->assertSame(6, substr_count($html, 'form="contact-purpose"'));
    }

    /**
     * 案B「車選び型」の並び：見出し帯 → 電話のパネル（営業状況・大きな電話番号・営業時間・ご案内のバッジ）→ 入力の流れ STEP.1〜3
     * → ご用件のイラスト付きタイル → お客様情報 → 同意 → 送信ボタン → 送信後の流れ（メダル型のイラスト）。
     */
    public function test_contact_page_shows_b_design_parts_in_order(): void
    {
        config(['shop.line_url' => null]);

        $response = $this->get(route('contact.index'))->assertOk();
        $html = $response->getContent();
        $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        // 見出し帯の英字は飾り（読み上げない）。意味は日本語の h1 で伝える
        $response->assertSee('<span class="c-page-header__en" aria-hidden="true" lang="en">CONTACT</span>', false);

        $response->assertSeeInOrder([
            '<h1 class="c-page-title">お問い合わせ・来店予約</h1>',
            'お急ぎの方はお電話で',
            'c-open-status',
            '営業時間 '.BusinessHours::hoursLabel(),
            'aria-label="電話をかける '.config('shop.tel').'"',
            'c-btn2__big c-btn2__big--num',
            'お問い合わせのご案内',
            'href="#contact-step1"',
            'href="#contact-step2"',
            'href="#contact-step3"',
            'id="contact-step1"',
            'c-illust--f-search',
            'c-illust--p-total',
            'c-illust--f-store',
            'c-illust--loan',
            'c-illust--buy',
            'c-illust--service',
            'id="message"',
            'id="contact-step2"',
            'id="name"',
            'id="phone"',
            'id="email"',
            'id="contact-step3"',
            'id="privacy-consent"',
            'この内容で送信する',
            '送信後の流れ',
            'c-illust--p-explain',
            'c-illust--mail-check',
            'c-illust--f-store',
        ], false);

        // 入力の流れ（日本語のラベル。英字の「STEP.」は飾り）とご用件のタイルの名前
        $response->assertSeeTextInOrder(['ご用件', 'お客様情報', '送信']);
        $response->assertSeeTextInOrder(['在庫の確認', '見積もり', '見学・試乗の予約', 'ローンの相談', '下取り・買取', 'その他']);
        $this->assertSame(6, substr_count($main, 'aria-hidden="true" lang="en">STEP.<b>'), 'STEP の英字は上の矢印3つと各まとまりの3つだけ・飾り');
        $response->assertSeeInOrder(['担当者が内容を確認します', '電話またはメールでご連絡します', 'ご来店・お見積もりなどをご案内します']);

        // 必須・任意のバッジ（必須：お問い合わせ内容・お名前・メールアドレス・同意／任意：ご用件・在庫番号・お電話番号）
        $this->assertSame(4, substr_count($main, '<span class="c-badge-req">必須</span>'));
        $this->assertSame(3, substr_count($main, '<span class="c-badge-req c-badge-req--optional">任意</span>'));

        // 見出しは h1 → h2 → h3 → h4 の順（飛ばさない）
        $this->assertSame(2, substr_count($main, '<h2'));
        $this->assertSame(3, substr_count($main, '<h3'));
        $this->assertSame(1, substr_count($main, '<h4'));

        // 絵文字や記号のアイコンを使わない（アイコンは SVG）
        $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B50}]/u', $main);
    }

    public function test_parking_badge_follows_config(): void
    {
        config(['shop.parking' => null]);
        $this->get(route('contact.index'))
            ->assertOk()
            ->assertDontSee('c-icon--parking', false);

        config(['shop.parking' => '無料駐車場あり']);
        $this->get(route('contact.index'))
            ->assertOk()
            ->assertSee('<span class="c-deck__badge-t p-contact-deck__badge-auto">無料駐車場あり</span>', false);
    }

    public function test_line_button_appears_only_when_configured(): void
    {
        config(['shop.line_url' => 'https://lin.ee/example']);

        $this->get(route('contact.index'))
            ->assertOk()
            ->assertSee('https://lin.ee/example', false);
    }

    public function test_purpose_visit_is_preselected_and_template_is_inserted(): void
    {
        $html = $this->get(route('contact.index', ['purpose' => 'visit']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="visit"\s+x-model="purpose"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*id="message"[^>]*>【ご用件】見学・試乗の予約\n/u', $html);
        $this->assertStringContainsString('第1希望：　月　日（　）　時ごろ', $html);
    }

    public function test_unknown_purpose_is_ignored(): void
    {
        $html = $this->get(route('contact.index', ['purpose' => '<script>']))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/x-model="purpose"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*id="message"[^>]*><\/textarea>/', $html);
    }

    public function test_list_accepts_only_stock_numbers_up_to_ten(): void
    {
        $list = implode(',', ['MZ4187', 'HN-2041', '<b>x</b>', 'ＡＢ12', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9']);

        $response = $this->get(route('contact.index').'?list='.urlencode($list))->assertOk();
        $html = $response->getContent();

        // purpose がなければ「お気に入りの車について」を選んだ状態にする
        $this->assertMatchesRegularExpression('/value="favorites"\s+x-model="purpose"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/>【ご用件】お気に入りの車について\n【在庫番号】MZ4187、HN-2041、A1、A2、A3、A4、A5、A6、A7、A8\n【ご質問・ご希望】<\/textarea>/u', $html);
        $response->assertDontSee('<b>x</b>', false);
        $response->assertDontSee('ＡＢ12');
    }

    public function test_target_car_shows_photo_price_year_and_mileage(): void
    {
        Car::factory()->create([
            'stock_no' => 'MZ4187',
            'make' => 'マツダ',
            'model' => 'CX-5',
            'grade' => 'XD',
            'model_year' => 2023,
            'mileage' => 11200,
            'price' => 3350000,
            'base_price' => 3000000,
            'price_negotiable' => false,
            'image_path' => 'cars/mazda_cx5.jpg',
            'status' => 'available',
        ]);

        $response = $this->get(route('contact.index', ['stock_no' => 'MZ4187', 'purpose' => 'stock']))->assertOk();

        $response
            ->assertSee('お問い合わせの車')
            ->assertSee('マツダ CX-5')
            ->assertSee('alt="マツダ CX-5の写真"', false)
            ->assertSee('<span class="c-price__value">335.0</span>', false)
            ->assertSee('車両本体価格 300.0万円')
            // 年式・走行距離は数字だけを大きな書体にするため、数字と単位を別の要素に分けている（文字としてつながっていることを確かめる）
            ->assertSeeText('2023（R5）年式')
            ->assertSeeText('1.1万km')
            ->assertSee('<input type="hidden" name="stock_no" value="MZ4187">', false)
            ->assertSee('お電話では在庫番号 <strong>MZ4187</strong> とお伝えください', false)
            ->assertSee('この車を対象から外す')
            ->assertSee('href="'.route('contact.index', ['purpose' => 'stock']).'"', false)
            ->assertDontSee('id="stock_no"', false)
            ->assertDontSee('この車は売約済みです');

        // 車が決まっているときは「気になる車」の行を入れない
        $this->assertMatchesRegularExpression('/id="message"[^>]*>【ご用件】在庫の確認\n【ご質問・ご希望】<\/textarea>/u', $response->getContent());
    }

    public function test_sold_and_reserved_cars_show_a_warning(): void
    {
        Car::factory()->create(['stock_no' => 'SL0001', 'status' => 'sold']);
        Car::factory()->create(['stock_no' => 'RS0001', 'status' => 'reserved']);

        $this->get(route('contact.index', ['stock_no' => 'SL0001']))
            ->assertOk()
            ->assertSee('c-alert c-alert--warn', false)
            ->assertSee('この車は売約済みです');

        $this->get(route('contact.index', ['stock_no' => 'RS0001']))
            ->assertOk()
            ->assertSee('c-alert c-alert--warn', false)
            ->assertSee('この車はただいま商談中です');
    }

    public function test_validation_errors_are_japanese_and_linked_to_fields(): void
    {
        $response = $this->from(route('contact.index'))
            ->followingRedirects()
            ->post(route('contact.send'), ['stock_no' => 'NONEXISTENT', 'email' => 'not-an-email'])
            ->assertOk();

        $html = $response->getContent();
        $this->assertStringNotContainsString('validation.', $html);

        $response
            ->assertSee('入力内容を確認してください<span class="u-nowrap">（4件）</span>', false)
            ->assertSee('href="#name"', false)
            ->assertSee('href="#stock_no"', false)
            ->assertSee('id="name-error"', false)
            ->assertSee('aria-describedby="name-hint name-error"', false)
            ->assertSee('aria-describedby="email-hint email-note email-error"', false)
            ->assertSee('value="NONEXISTENT"', false)
            ->assertSee('value="not-an-email"', false);

        $this->assertGreaterThanOrEqual(4, substr_count($html, 'aria-invalid="true"'));

        // エラー一覧は画面の欄の並び（STEP.1 在庫番号・お問い合わせ内容 → STEP.2 お名前・メールアドレス）の順
        $response->assertSeeInOrder([
            'class="c-form-errors__link" href="#stock_no"',
            'class="c-form-errors__link" href="#message"',
            'class="c-form-errors__link" href="#name"',
            'class="c-form-errors__link" href="#email"',
            'id="stock_no"',
        ], false);
    }

    public function test_thanks_page_uses_common_thanks_template(): void
    {
        $response = $this->get(route('contact.thanks'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(1, substr_count($html, '<main'));

        $response
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<h1 id="thanks-title" class="c-thanks__title">お問い合わせを受け付けました</h1>', false)
            ->assertSee('定休日（'.config('shop.closed_label').'）をはさむ場合は、翌営業日以降のご連絡になります。')
            ->assertSeeInOrder(['担当者が内容を確認します', '電話またはメールでご連絡します', 'ご来店・お見積もりなどをご案内します'])
            // 電話の案内（案B：完了ページの部品が、お問い合わせページの電話のパネルと同じ見出し「お急ぎの方はお電話で」で出す）
            ->assertSee('お急ぎの方はお電話で')
            ->assertSee('aria-label="電話をかける '.config('shop.tel').'"', false)
            ->assertSee('在庫一覧を見る')
            ->assertSee('店舗案内・アクセスを見る')
            ->assertSee('トップページへ戻る')
            ->assertSee('class="l-mbar l-mbar--contact"', false)
            ->assertDontSee('ご相談・ご来店はこちら')
            ->assertDontSee('自動返信')
            // 公開中の車が0台なら台数を出さない
            ->assertDontSee('在庫一覧を見る（0台）');
    }

    /**
     * 送信完了：受付完了のイラスト → h1 → このあとの流れ（お問い合わせページの送信後の流れと同じメダル型のイラスト）
     * → お急ぎの方はお電話で（営業状況・大きな電話番号）→ 次の行動（在庫一覧は公開中の実数）。
     */
    public function test_thanks_page_shows_illustrated_flow_phone_and_next_actions(): void
    {
        Car::factory()->count(2)->create(['status' => 'available', 'published_at' => now()->subDay()]);
        Car::factory()->create(['status' => 'sold', 'published_at' => now()->subDay()]);
        Car::factory()->create(['status' => 'available', 'published_at' => now()->addDays(3)]);

        $response = $this->get(route('contact.thanks'))->assertOk();
        $html = $response->getContent();

        $response->assertSeeInOrder([
            'c-illust--mail-check',
            '<h1 id="thanks-title" class="c-thanks__title">お問い合わせを受け付けました</h1>',
            '<h2 class="c-subhead c-medal-steps__title">このあとの流れ</h2>',
            'c-illust--p-explain',
            '担当者が内容を確認します',
            'c-illust--mail-check',
            '電話またはメールでご連絡します',
            'c-illust--f-store',
            'ご来店・お見積もりなどをご案内します',
            'お急ぎの方はお電話で',
            'c-open-status',
            'aria-label="電話をかける '.config('shop.tel').'"',
            'c-btn2__big c-btn2__big--num',
            '営業時間 '.BusinessHours::hoursLabel(),
            '在庫一覧を見る（2台）',
            '店舗案内・アクセスを見る',
            'トップページへ戻る',
        ], false);

        // 番号だけの手順（c-steps）は出さず、流れ・電話の案内は1回だけ（完了ページの部品がメダル型の図と電話のパネルを出す。
        // 統合で、お問い合わせの完了ページ専用だった図・電話のパネルを部品に移し、買取査定の完了ページとそろえた）
        $this->assertStringNotContainsString('c-steps', $html);
        $this->assertSame(1, substr_count($html, 'class="c-thanks__tel"'));
        $this->assertSame(1, substr_count($html, '<div class="c-medal-steps '));
        $this->assertSame(1, substr_count($html, 'お急ぎの方はお電話'));
    }

    public function test_car_not_yet_published_is_not_shown_by_stock_no(): void
    {
        Car::factory()->create([
            'stock_no' => 'FT0001',
            'make' => 'トヨタ',
            'model' => 'アルファード',
            'price' => 5980000,
            'status' => 'available',
            'published_at' => now()->addDays(3),
        ]);

        $this->get(route('contact.index', ['stock_no' => 'FT0001']))
            ->assertOk()
            ->assertDontSee('トヨタ アルファード')
            ->assertDontSee('<input type="hidden" name="stock_no" value="FT0001">', false)
            // 在庫番号の欄には、入力された値だけをそのまま残す
            ->assertSee('id="stock_no"', false)
            ->assertSee('value="FT0001"', false);
    }
}
