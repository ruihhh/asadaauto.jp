<?php

namespace Tests\Feature;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 公開サイト共通レイアウト（ヘッダー・ナビ・ご相談帯・フッター・固定下部バー）と個人情報の取り扱いページ。
 * 旧ページ本文の影響を受けないよう、本文がすべて新しい部品でできている /privacy で確かめる。
 */
class SiteLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_page_is_accessible_and_explains_google_analytics(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('<h1 class="c-page-title">個人情報の取り扱い</h1>', false)
            ->assertSee('アクセス解析ツールについて')
            ->assertSee('Google アナリティクス 4')
            ->assertSee('https://policies.google.com/technologies/partner-sites?hl=ja', false)
            ->assertSee('個人を特定する情報は含まれません');
    }

    public function test_layout_shows_shop_contacts_and_hides_removed_links(): void
    {
        $response = $this->get(route('privacy'));

        $response->assertOk()
            ->assertSee('本文へ移動')
            ->assertSee('href="tel:0649608765"', false)
            ->assertSee('aria-label="メインメニュー"', false)
            ->assertSee('ご相談・ご来店はこちら')
            ->assertSee('class="l-mbar l-mbar--default"', false)
            ->assertSee('has-mbar', false)
            ->assertDontSee('管理者ログイン')
            ->assertDontSee('All rights reserved')
            ->assertDontSee('featured=1', false)
            ->assertDontSee('第三者機関');
    }

    public function test_line_buttons_appear_only_when_line_url_is_configured(): void
    {
        config(['shop.line_url' => null]);
        $this->get(route('privacy'))->assertOk()->assertDontSee('LINEで相談');

        config(['shop.line_url' => 'https://lin.ee/example']);
        $this->get(route('privacy'))->assertOk()->assertSee('LINEで相談')->assertSee('https://lin.ee/example', false);
    }

    public function test_kobutsu_license_is_shown_only_when_number_is_configured(): void
    {
        config(['shop.kobutsu.number' => null]);
        $this->get(route('privacy'))->assertOk()->assertDontSee('古物商許可');

        config(['shop.kobutsu.number' => '123456789012', 'shop.kobutsu.holder' => '朝田 繕行']);
        $this->get(route('privacy'))->assertOk()->assertSee('古物商許可 兵庫県公安委員会 第123456789012号（朝田 繕行）');
    }

    public function test_footer_lists_only_body_types_in_stock(): void
    {
        Car::factory()->create(['body_type' => 'コンパクト', 'status' => 'available', 'published_at' => now()->subDay()]);
        Car::factory()->create(['body_type' => 'セダン', 'status' => 'sold', 'published_at' => now()->subDay()]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('コンパクトカー')
            ->assertSee('body_type=%E3%82%B3%E3%83%B3%E3%83%91%E3%82%AF%E3%83%88', false)
            ->assertDontSee('body_type=%E3%82%BB%E3%83%80%E3%83%B3', false);
    }

    public function test_header_primary_button_switches_on_buy_pages(): void
    {
        $this->get(route('privacy'))->assertSee('在庫確認・来店予約');
        $this->get(route('buy.index'))->assertSee('無料査定を申し込む');

        // 買取査定のページでは、ヘッダーの主ボタン（2段ボタン）が［無料査定を申し込む］（#appraisal-form）になる
        $buyHeader = $this->header($this->get(route('buy.index'))->getContent());
        $this->assertStringContainsString('href="'.route('buy.index').'#appraisal-form"', $buyHeader);
        $this->assertStringContainsString('<span class="c-btn2__big">無料査定を申し込む</span>', $buyHeader);

        // 受付完了では、申し込んだ直後なので通常の［在庫確認・来店予約］に戻す
        $thanksHeader = $this->header($this->get(route('buy.thanks'))->getContent());
        $this->assertStringContainsString('class="c-btn2 c-btn2--red l-header__cta" href="'.e(route('contact.index', ['purpose' => 'visit'])).'"', $thanksHeader);
        $this->assertStringContainsString('<span class="c-btn2__big">在庫確認・来店予約</span>', $thanksHeader);
        $this->assertStringNotContainsString('#appraisal-form', $thanksHeader);
        $this->assertStringNotContainsString('無料査定を申し込む', $thanksHeader);
    }

    /** ヘッダー（<header class="l-header">〜</header>）だけを取り出す */
    private function header(string $html): string
    {
        $start = strpos($html, '<header class="l-header">');
        $this->assertNotFalse($start, 'ヘッダーが見つかりません');

        return substr($html, $start, strpos($html, '</header>', $start) - $start);
    }

    public function test_public_pages_load_only_site_css_and_admin_keeps_tailwind(): void
    {
        $tailwind = \Illuminate\Support\Facades\Vite::asset('resources/css/app.css');

        foreach ([route('home'), route('cars.index'), route('store'), route('contact.index'), route('buy.index'), route('privacy')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('/css/site.css?v=', $html, $url);
            $this->assertStringNotContainsString('legacy.css', $html, $url);
            $this->assertStringNotContainsString($tailwind, $html, $url);
            // 円記号は使わない（構造化データを含むページ全体で確かめる）
            $this->assertStringNotContainsString('¥', $html, $url);
        }

        // 管理画面・認証画面は従来どおり Tailwind（resources/css/app.css）を読み込む
        $this->get(route('login'))->assertOk()->assertSee($tailwind, false)->assertDontSee('/css/site.css', false);
    }

    public function test_openbar_links_to_hours_on_store_page(): void
    {
        $this->get(route('privacy'))->assertSee('<a class="l-openbar__link" href="'.route('store').'">店舗案内 ›</a>', false);
        $this->get(route('store'))
            ->assertSee('<a class="l-openbar__link" href="#hours">営業時間 ›</a>', false)
            ->assertDontSee('<a class="l-openbar__link" href="'.route('store').'">', false);
    }

    public function test_dealer_structured_data_has_no_unfounded_price_range(): void
    {
        $this->get(route('privacy'))
            ->assertSee('"@type": "AutoDealer"', false)
            ->assertDontSee('priceRange', false);
    }

    public function test_layout_has_b_chrome_topbar_quick_links_side_buttons_and_fonts(): void
    {
        $html = $this->get(route('privacy'))->assertOk()->getContent();

        // 書体：Noto Sans JP（900 を含む）と Oswald
        $this->assertStringContainsString('https://fonts.bunny.net/css?family=noto-sans-jp:400,500,700,900|oswald:500,600,700&display=swap', $html);
        // Google tag は <head> の直後のまま（フォントの読み込みはそれより後）
        $this->assertLessThan(strpos($html, 'fonts.bunny.net'), strpos($html, 'googletagmanager.com/gtag/js?id=G-HHVN8S1CE9'));
        $this->assertStringStartsWith('<head>', trim(substr($html, strpos($html, '<head>'), 6)));
        $this->assertMatchesRegularExpression('/<head>\s*<!-- Google tag \(gtag\.js\) -->/', $html);

        // 上の黒い帯（業態＋はじめての方へ・当店のお約束・店舗案内）
        $this->assertStringContainsString('<p class="l-topbar__lines">尼崎市の中古車販売・買取・車検・一般整備・板金・保険</p>', $html);
        $this->assertStringContainsString('href="'.route('home').'#promise">当店のお約束</a>', $html);
        // ヘッダーの大きな電話番号とナビのお気に入り・比較の件数
        $this->assertStringContainsString('class="l-header__tel-number" href="tel:0649608765" aria-label="電話をかける 06-4960-8765"', $html);
        $this->assertStringContainsString('<span x-text="$store.favorites.count">0</span>', $html);
        // スマホのアイコン4つのクイックリンクと、PC 右端の固定ボタン
        $this->assertStringContainsString('<nav class="l-spquick" aria-label="よく使うページ">', $html);
        $this->assertSame(4, substr_count($html, 'class="l-spquick__link'));
        $this->assertStringContainsString('<nav class="l-sidefix" aria-label="すぐに使うメニュー">', $html);
        // 固定下部バーの電話の列は1タップで電話できる
        $mbar = substr($html, strpos($html, '<nav class="l-mbar'));
        $this->assertMatchesRegularExpression('/href="tel:0649608765"\s+aria-label="電話する 06-4960-8765"/', $mbar);
    }

    public function test_quick_links_mark_the_current_page(): void
    {
        $html = $this->get(route('store'))->assertOk()->getContent();
        $quick = substr($html, strpos($html, '<nav class="l-spquick"'), 2000);

        $this->assertStringContainsString('class="l-spquick__link is-active" href="'.route('store').'"  aria-current="page"', $quick);
        // ［ローン］はトップのお支払いシミュレーション（id="loan"）へ飛ぶ
        $this->assertStringContainsString('href="'.route('home').'#loan"', $quick);
        $this->assertStringContainsString('id="loan"', $this->get(route('home'))->assertOk()->getContent());
    }
}
