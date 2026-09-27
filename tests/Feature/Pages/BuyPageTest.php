<?php

namespace Tests\Feature\Pages;

use App\Mail\BuyAppraisalMail;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * 買取査定（/buy）と受付完了（/buy/thanks）。
 * フォームは id="appraisal-form" の1本だけで、3ステップ（お車の情報／ご連絡先／そのほか）に分かれている。
 */
class BuyPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 部品の多いページを何度も描画するため、描画中に自動の GC が何度も走ると PHP が GC の間隔を広げてしまい、
     * 後続の別クラスのテストで回収が遅れて memory_limit（128MB）に届くことがある（ビューのキャッシュが空のときに再現）。
     * このクラスのテスト中は自動の GC を止め、各テストの後でまとめて回収する（GC の間隔を後続に持ち越さない）
     */
    protected function setUp(): void
    {
        parent::setUp();
        gc_disable();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
    }

    /** 各ステップの必須項目をすべて満たした送信内容 */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'make' => 'トヨタ',
            'model' => 'プリウス',
            'model_year' => 2018,
            'mileage' => 45000,
            'condition' => 'normal',
            'name' => 'テスト 太郎',
            'phone' => '000-0000-0000',
            'email' => 'test@example.com',
        ], $overrides);
    }

    /** <main> の中身だけを取り出す（ヘッダー・フッターの文言を除いて確かめるため） */
    private function mainHtml(string $html): string
    {
        $start = strpos($html, '<main');
        $end = strpos($html, '</main>');

        return $start !== false && $end !== false ? substr($html, $start, $end - $start) : '';
    }

    /** h1 の文字だけ（タグと空白を除く） */
    private function h1Text(string $html): string
    {
        preg_match('/<h1\b[^>]*>(.*?)<\/h1>/s', $html, $m);

        return preg_replace('/\s+/u', '', strip_tags($m[1] ?? ''));
    }

    public function test_buy_page_has_one_h1_and_a_single_appraisal_form(): void
    {
        $response = $this->get(route('buy.index'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertSame(1, substr_count($html, '<main'));
        $this->assertSame(1, substr_count($html, 'id="appraisal-form"'));
        $this->assertSame(1, substr_count($html, 'action="'.route('buy.send').'"'));

        // h1 は写真の上の斜め帯2本（黒「尼崎の中古車買取・」＋赤「無料査定」。案B）。語の途中で折り返さない
        $this->assertSame('尼崎の中古車買取・無料査定', $this->h1Text($html));
        $response->assertSee('<span class="c-slant c-slant--black"><span class="u-nowrap">尼崎の中古車買取・</span></span>', false)
            ->assertSee('<span class="c-slant c-slant--red"><span class="u-nowrap">無料査定</span></span>', false)
            ->assertSee('無料査定のお申し込み')
            ->assertSee('data-submit-once', false)
            ->assertSee('class="l-mbar l-mbar--buy"', false)
            ->assertDontSee('ご相談・ご来店はこちら')
            ->assertSeeInOrder(['4つのお約束', 'こんなお車もご相談ください', 'ご売却の流れ', 'ご売却に必要な書類', 'よくある質問', 'お店での査定もできます', 'お申し込みは'], false);

        foreach (['make', 'model', 'model_year', 'mileage', 'condition', 'name', 'phone', 'email', 'grade', 'color', 'zip', 'message'] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }

        // 車の状態は3択で選んでもらう（hidden で「普通」を固定しない）。初期値は normal
        $this->assertDoesNotMatchRegularExpression('/type="hidden"\s+name="condition"/', $html);
        foreach (['good', 'normal', 'damaged'] as $value) {
            $response->assertSee('value="'.$value.'"', false);
        }
        $this->assertMatchesRegularExpression('/value="normal"[^>]*\bchecked\b/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="good"[^>]*\bchecked\b/', $html);

        // ページ内の申し込みボタンは、すべてこのフォームに飛ぶ
        $response->assertSee('href="#appraisal-form"', false);
    }

    public function test_buy_page_does_not_show_unfounded_or_outdated_claims(): void
    {
        $html = $this->get(route('buy.index'))->assertOk()->getContent();
        $main = $this->mainHtml($html);

        foreach (['車庫証明', '500台', '97%', '他社より', 'FREE', 'OUR', '自動返信', '最短即日', 'どんなお車でも', '事故歴', 'ございます', '高価買取', 'キャンセル無料', 'プライバシーポリシー', '契約後の減額', 'しつこい'] as $banned) {
            $this->assertStringNotContainsString($banned, $html, "「{$banned}」が表示されている");
        }

        // 「STEP.」は、ご売却の流れの札の飾り（読み上げない英字）としてだけ使う（案B の決定）。意味を伝える英字にはしない
        $this->assertStringContainsString('<span aria-hidden="true">STEP.</span>', $html);
        $this->assertStringNotContainsString('STEP', str_replace('<span aria-hidden="true">STEP.</span>', '', $html));
        $this->assertStringNotContainsString('style=', $main);
        $this->assertStringNotContainsString('validation.', $main);
    }

    public function test_buy_page_is_built_with_the_graphical_parts_of_plan_b(): void
    {
        $response = $this->get(route('buy.index'))->assertOk();
        $html = $response->getContent();
        $main = $this->mainHtml($html);

        // ファーストビュー：写真の帯（飾りなので alt 空）＋黄の丸バッジ＋「査定無料」の黄の強調＋進み具合（今のステップに aria-current）
        $this->assertMatchesRegularExpression('/<img class="p-buy-hero__img" src="[^"]+buy-cta-bg\.jpg" alt=""/', $main);
        $this->assertGreaterThanOrEqual(2, substr_count($main, 'c-round-badge c-round-badge--yellow'));
        $response->assertSee('<em class="p-buy-hero__mark">査定無料</em>', false)
            ->assertSee('aria-current="step"', false);
        $this->assertSame(3, substr_count($main, 'class="p-buy-progress__item'));

        // お約束（黒の斜線地に4枚）・こんなお車も（イラスト付き4つ）・ご売却の流れ（メダル5つ）・必要書類（普通車・軽自動車の2表）
        $this->assertSame(1, substr_count($main, 'l-section--dark'));
        $this->assertSame(4, substr_count($main, 'class="c-promise"'));
        $this->assertSame(4, substr_count($main, 'class="p-buy-case"'));
        $this->assertSame(5, substr_count($main, 'class="c-flow__step"'));
        $response->assertSeeInOrder(['普通車の場合', '軽自動車の場合']);
        foreach (['事故車・故障車', '車検切れ', 'ローンが'] as $case) {
            $response->assertSeeText($case);
        }

        // お店での査定：店舗の写真・道順（新しいタブ）・営業状況。最後の案内は2段ボタン（フォーム・電話）
        $response->assertSee('images/store-hero-bg.png', false)
            ->assertSee('href="'.e(config('shop.directions_url')).'" target="_blank" rel="noopener"', false)
            ->assertSee('c-open-status', false)
            ->assertSee('href="'.config('shop.tel_href').'"', false);
        $this->assertMatchesRegularExpression('/<a[^>]+class="c-btn2 c-btn2--yellow[^"]*"[^>]+href="#appraisal-form"|<a[^>]+href="#appraisal-form"[^>]+class="c-btn2 c-btn2--yellow/', $main);
    }

    public function test_year_options_show_japanese_era_and_cover_the_validation_range(): void
    {
        $next = (int) date('Y') + 1;

        $response = $this->get(route('buy.index'))
            ->assertOk()
            ->assertSee('車検証の「初度登録年月」に書かれています');
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/<option value="2018"\s*>2018年（平成30年）<\/option>/u', $html);
        $this->assertMatchesRegularExpression('/<option value="1980"\s*>1980年（昭和55年）<\/option>/u', $html);
        $this->assertMatchesRegularExpression('/<option value="'.$next.'"\s*>/', $html);
        $this->assertStringNotContainsString('<option value="1979"', $html);
    }

    public function test_make_suggestions_merge_stock_makes_without_duplicates(): void
    {
        Car::factory()->create(['make' => 'トヨタ', 'status' => 'available', 'published_at' => now()->subDay()]);
        Car::factory()->create(['make' => 'BMW', 'status' => 'available', 'published_at' => now()->subDay()]);

        $html = $this->get(route('buy.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<option value="トヨタ"></option>'));
        $this->assertSame(1, substr_count($html, '<option value="BMW"></option>'));
        $this->assertSame(1, substr_count($html, '<option value="輸入車"></option>'));
    }

    public function test_structured_data_matches_the_page(): void
    {
        $response = $this->get(route('buy.index'))->assertOk();

        $response->assertSee('"@type": "BreadcrumbList"', false)
            ->assertSee('"@type": "Service"', false)
            ->assertSee('"@type": "FAQPage"', false)
            ->assertSee('"name": "売却に必要な書類は何ですか？"', false)
            ->assertSee('売却に必要な書類は何ですか？')
            ->assertSee('"@id": "'.url('/').'#organization"', false);
    }

    public function test_server_errors_open_the_step_that_has_the_first_error(): void
    {
        // ご連絡先（ステップ2）だけに誤り
        $this->followingRedirects()
            ->from(route('buy.index'))
            ->post(route('buy.send'), $this->validPayload(['name' => '', 'email' => 'not-an-email']))
            ->assertOk()
            ->assertSee('x-data="buyAppraisalForm(2)"', false)
            ->assertSee('お名前を入力してください。')
            ->assertSee('id="name-error"', false)
            ->assertSee('href="#email"', false)
            ->assertSee('value="プリウス"', false)
            ->assertDontSee('validation.');

        // そのほか（ステップ3）だけに誤り
        $this->followingRedirects()
            ->from(route('buy.index'))
            ->post(route('buy.send'), $this->validPayload(['zip' => '12345678901']))
            ->assertOk()
            ->assertSee('x-data="buyAppraisalForm(3)"', false)
            ->assertSee('id="zip-error"', false);

        // お車の情報（ステップ1）とステップ3の両方に誤りがあれば、ステップ1を開く
        $this->followingRedirects()
            ->from(route('buy.index'))
            ->post(route('buy.send'), $this->validPayload(['model' => '', 'zip' => '12345678901']))
            ->assertOk()
            ->assertSee('x-data="buyAppraisalForm(1)"', false)
            ->assertSee('車種を入力してください。');
    }

    public function test_valid_request_is_saved_and_redirects_to_thanks(): void
    {
        Mail::fake();

        $this->post(route('buy.send'), $this->validPayload(['condition' => 'good', 'zip' => '6610975']))
            ->assertRedirect(route('buy.thanks'));

        $this->assertDatabaseHas('appraisal_requests', [
            'make' => 'トヨタ',
            'model' => 'プリウス',
            'model_year' => 2018,
            'mileage' => 45000,
            'condition' => 'good',
            'zip' => '6610975',
        ]);
        Mail::assertSent(BuyAppraisalMail::class);
    }

    public function test_thanks_page_uses_the_shared_completion_layout(): void
    {
        $response = $this->get(route('buy.thanks'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $response->assertSee('<title>査定のお申し込みを受け付けました | '.config('shop.name').'</title>', false)
            ->assertSee('<span class="u-nowrap">査定のお申し込みを</span><span class="u-nowrap">受け付けました</span>', false)
            ->assertSee('class="c-thanks p-buy-thanks"', false)
            ->assertSee('このあとの流れ')
            ->assertSee('<span class="u-nowrap">車検証を</span><span class="u-nowrap">お手元にご用意ください</span>', false)
            ->assertSee('在庫の車を見る')
            ->assertSee('店舗案内・アクセスを見る')
            ->assertSee('トップページへ戻る')
            // このあとの流れは、お問い合わせの完了ページと同じメダル型のイラストの図（統合で c-steps の番号だけの手順から変えた）
            ->assertSeeInOrder(['c-medal-steps__title', 'c-illust--p-explain', '内容を確認します', 'c-illust--mail-check', 'ご連絡します', 'c-illust--buy', 'お車を見て査定します'], false)
            // お急ぎの方はお電話で（電話番号は2段ボタンの大きな数字。途中で折り返さない）
            ->assertSee('お急ぎの方はお電話で')
            ->assertSee('c-btn2__big c-btn2__big--num', false)
            ->assertSee('content="noindex, nofollow"', false)
            ->assertDontSee('自動返信')
            ->assertDontSee('✅')
            ->assertDontSee('ご相談・ご来店はこちら');
        $this->assertStringNotContainsString('style=', $this->mainHtml($html));
    }

    public function test_optional_items_appear_only_when_configured(): void
    {
        config([
            'shop.kobutsu.number' => null,
            'shop.buy.no_reduction_after_contract' => false,
            'shop.buy.payment_timing' => null,
            'shop.buy.visit_area' => null,
            'shop.line_url' => null,
        ]);
        // お約束の見出しは語の単位で折り返す span に分かれるので、文字だけで確かめる（assertSeeText）
        $this->get(route('buy.index'))
            ->assertOk()
            ->assertSee('4つのお約束')
            ->assertDontSeeText('ご契約後の減額はしません')
            ->assertDontSee('お支払いの時期：')
            ->assertDontSee('出張査定の地域：')
            ->assertDontSee('古物商許可')
            ->assertDontSee('LINEで相談');

        config([
            'shop.kobutsu.number' => '123456789012',
            'shop.kobutsu.holder' => '朝田 繕行',
            'shop.buy.no_reduction_after_contract' => true,
            'shop.buy.payment_timing' => 'お引き渡しから3営業日以内にお振り込み',
            'shop.buy.visit_area' => '尼崎市・西宮市・伊丹市',
            'shop.line_url' => 'https://lin.ee/example',
        ]);
        $response = $this->get(route('buy.index'))
            ->assertOk()
            ->assertSee('5つのお約束')
            ->assertSeeText('ご契約後の減額はしません')
            ->assertSee('お支払いの時期：お引き渡しから3営業日以内にお振り込み')
            ->assertSee('出張査定の地域：尼崎市・西宮市・伊丹市');
        // 店舗の表の行見出しは、アイコン（読み上げない svg）＋文字
        $this->assertMatchesRegularExpression('/<th scope="row" class="c-table__th">(?:(?!<\/th>).)*古物商許可<\/th>/su', $response->getContent());
        $response->assertSee('兵庫県公安委員会 第123456789012号（朝田 繕行）')
            ->assertSee('"areaServed": "尼崎市・西宮市・伊丹市"', false)
            ->assertSee('LINEで相談');
    }
}
