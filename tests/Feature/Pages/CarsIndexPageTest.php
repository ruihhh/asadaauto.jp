<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 在庫一覧（/cars）と新着の中古車（/cars?sort=latest）の刷新（WU cars-index）。
 */
class CarsIndexPageTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attributes = []): Car
    {
        return Car::factory()->create(array_merge([
            'status' => 'available',
            'price_negotiable' => false,
            'published_at' => now()->subDays(30),
        ], $attributes));
    }

    /** レイアウト（ヘッダー・フッター・構造化データ）を除いた本文だけ */
    private function main(TestResponse $response): string
    {
        $html = $response->getContent();
        $start = strpos($html, '<main');
        $end = strrpos($html, '</main>');

        return substr($html, $start, $end - $start);
    }

    /** @return array<int, array<string, mixed>> */
    private function jsonLd(TestResponse $response): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);

        return array_map(fn ($json) => json_decode($json, true), $matches[1]);
    }

    public function test_list_uses_common_parts_without_removed_copy(): void
    {
        $this->car(['make' => 'マツダ', 'model' => 'CX-5', 'body_type' => 'SUV', 'price' => 3350000]);

        $response = $this->get('/cars')->assertOk();
        $main = $this->main($response);

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
        $this->assertStringContainsString('中古車在庫一覧', $main);
        $this->assertStringContainsString('該当', $main);
        $this->assertStringContainsString('class="c-car-card', $main);
        $this->assertStringContainsString('マツダ CX-5', $main);
        $this->assertStringContainsString('支払総額は兵庫県内で登録し', $main);
        foreach (['仲介手数料', '売約済みになります', '早い者勝ち', 'No Image', 'NEW', '事故歴', '¥', 'style=', 'favBtn', "Alpine.store('compare'", '取り置き', '随時入荷'] as $removed) {
            $this->assertStringNotContainsString($removed, $main, $removed);
        }
    }

    public function test_chips_and_use_cases_only_for_body_types_in_stock(): void
    {
        $this->car(['body_type' => 'SUV']);
        $this->car(['body_type' => 'ミニバン']);
        $this->car(['body_type' => 'セダン', 'status' => 'sold']);

        $main = $this->main($this->get('/cars')->assertOk());

        $this->assertStringContainsString(e(route('cars.index', ['body_type' => 'SUV'])), $main);
        $this->assertStringContainsString(e(route('cars.index', ['body_type' => 'ミニバン'])), $main);
        $this->assertStringNotContainsString(e(route('cars.index', ['body_type' => 'セダン'])), $main);
        $this->assertStringContainsString('家族や友人と大勢で乗りたい', $main);
        $this->assertStringNotContainsString('乗り心地や静かさを重視したい', $main);
        $this->assertStringNotContainsString('燃費を重視', $main);
    }

    public function test_sort_defaults_to_blank_and_filters_never_switch_to_latest(): void
    {
        $this->car();

        $main = $this->main($this->get('/cars')->assertOk());
        $this->assertStringContainsString('<option value="" selected>新着順（標準）</option>', $main);
        $this->assertStringNotContainsString('value="latest"', $main);

        // 並び替えを選んでいるときだけ、絞り込みのフォームに hidden で引き継ぐ
        $sorted = $this->main($this->get('/cars?sort=price_asc')->assertOk());
        $this->assertStringContainsString('<input type="hidden" name="sort" value="price_asc">', $sorted);
        $this->assertStringContainsString('<option value="price_asc" selected>支払総額が安い順</option>', $sorted);
        $this->assertStringNotContainsString('新着の中古車', $sorted);
    }

    public function test_price_and_mileage_are_selects_that_send_integers(): void
    {
        $this->car();

        $main = $this->main($this->get('/cars')->assertOk());

        $this->assertStringNotContainsString('type="number"', $main);
        $this->assertStringContainsString('支払総額（下限）', $main);
        $this->assertStringContainsString('支払総額（上限）', $main);
        $this->assertStringContainsString('<option value="1000000" >100万円以上</option>', $main);
        $this->assertStringContainsString('<option value="3000000" >300万円以下</option>', $main);
        $this->assertStringContainsString('<option value="50000" >5万km以下</option>', $main);
        // 車検は値を変えずに、表示名だけわかりやすくする
        $this->assertStringContainsString('<option value="3年付" >3年付き（納車時に取得）</option>', $main);
        $this->assertStringContainsString('<option value="あり" >残りあり</option>', $main);
    }

    public function test_current_conditions_can_be_removed_one_by_one(): void
    {
        $this->car(['make' => 'トヨタ', 'price' => 1500000]);

        $response = $this->get('/cars?make=トヨタ&max_price=2000000&page=1')->assertOk();
        $main = $this->main($response);

        $this->assertStringContainsString('いまの条件', $main);
        // × のリンクは、その条件と page だけを外す（ほかの条件は残す）。移った先では結果の頭（#cars-result）から見せる
        $this->assertStringContainsString('aria-label="メーカー：トヨタ の条件を外す"', $main);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['max_price' => '2000000'])).'#cars-result"', $main);
        $this->assertStringContainsString('aria-label="支払総額：200万円以下 の条件を外す"', $main);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['make' => 'トヨタ'])).'#cars-result"', $main);
        $this->assertStringContainsString('id="cars-result"', $main);
        // STEP.2 の入力欄は閉じたまま出し（スマホで結果を押し下げない）、ボディタイプ以外に指定中の条件の数を見せる
        $this->assertStringContainsString('<details class="p-cars-more">', $main);
        $this->assertStringContainsString('指定中 <b>2</b>件', $main);
        // 絞り込み中は検索エンジンに登録しない
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_empty_result_shows_next_actions(): void
    {
        $this->car(['price' => 3000000]);

        $main = $this->main($this->get('/cars?max_price=500000')->assertOk());

        // 見出しは語の区切りでだけ改行させるため u-nowrap の span に分けている（文字としては1文）
        $this->assertStringContainsString('条件に合う車が見つかりませんでした', strip_tags($main));
        $this->assertStringContainsString('c-illust--empty', $main);
        $this->assertStringContainsString('条件を外して、すべての在庫を見る', $main);
        $this->assertStringContainsString('探してほしい車を伝える', $main);
        $this->assertStringContainsString(e(route('contact.index', ['purpose' => 'search'])), $main);
        $this->assertStringContainsString(config('shop.tel_href'), $main);
        $this->assertStringNotContainsString('name="sort"', $main);
    }

    public function test_latest_mode_without_recent_cars_shows_notice(): void
    {
        $this->car(['make' => 'マツダ', 'model' => 'CX-5', 'published_at' => now()->subDays(30)]);

        $response = $this->get('/cars?sort=latest')->assertOk();
        $main = $this->main($response);

        $this->assertStringContainsString('新着の中古車', $main);
        $this->assertStringContainsString('この7日間に新しく掲載した車はありません', $main);
        $this->assertStringContainsString('入荷のお知らせを希望する', $main);
        $this->assertStringNotContainsString('name="sort"', $main);
        $this->assertStringNotContainsString('c-tag--new', $main);
        $response->assertSee('<link rel="canonical" href="'.route('cars.index', ['sort' => 'latest']).'">', false);

        $breadcrumb = collect($this->jsonLd($response))->firstWhere('@type', 'BreadcrumbList');
        $this->assertSame(['ホーム', '中古車在庫一覧', '新着の中古車'], array_column($breadcrumb['itemListElement'], 'name'));
    }

    public function test_latest_mode_tags_only_recent_cars(): void
    {
        $this->car(['make' => 'マツダ', 'model' => 'CX-5', 'published_at' => now()->subDays(30)]);
        $this->car(['make' => 'ホンダ', 'model' => 'フィット', 'published_at' => now()->subDay()]);

        $main = $this->main($this->get('/cars?sort=latest')->assertOk());

        $this->assertStringNotContainsString('この7日間に新しく掲載した車はありません', $main);
        $this->assertSame(1, substr_count($main, 'c-tag c-tag--new'));
    }

    public function test_pagination_keeps_conditions(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->car(['body_type' => 'SUV']);
        }

        $response = $this->get('/cars?body_type=SUV')->assertOk();
        $main = $this->main($response);

        $this->assertStringContainsString('class="c-pagination"', $main);
        $this->assertStringContainsString('全13台中 1〜12台目を表示', $main);
        $this->assertStringContainsString('body_type=SUV&amp;page=2', $main);

        // 2ページ目は検索エンジンに登録しない
        $this->get('/cars?page=2')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_structured_data_matches_the_screen(): void
    {
        $this->car(['make' => 'マツダ', 'model' => 'CX-5']);

        $response = $this->get('/cars')->assertOk();
        $schemas = collect($this->jsonLd($response));

        $breadcrumb = $schemas->firstWhere('@type', 'BreadcrumbList');
        $this->assertSame(['ホーム', '中古車在庫一覧'], array_column($breadcrumb['itemListElement'], 'name'));

        $itemList = $schemas->firstWhere('@type', 'ItemList');
        $this->assertSame(1, $itemList['numberOfItems']);
        $this->assertSame('マツダ CX-5', $itemList['itemListElement'][0]['name']);

        // 絞り込み中は ItemList を出さない
        $filtered = collect($this->jsonLd($this->get('/cars?make=マツダ')));
        $this->assertNull($filtered->firstWhere('@type', 'ItemList'));
    }

    public function test_contact_band_is_not_repeated_after_consult_section(): void
    {
        $this->car();

        // 案B（2026-09-27 決定）：ページの最後の連絡先はレイアウトのご相談帯（電話・フォーム・店舗）が1回だけ出す。
        // ページ末尾の「どの車が合うか迷ったら」は探し方の案内だけにして、同じ内容の連絡先の区画を本文に重ねて作らない
        // （旧仕様は本文に c-contact-actions を置いてご相談帯を消していた）。連絡先が二重にならない、という意図は同じ
        $response = $this->get(route('cars.index'))->assertOk();
        $main = $this->main($response);

        $this->assertStringContainsString('どの車が合うか迷ったら', $main);
        $this->assertStringNotContainsString('c-contact-actions', $main);
        $this->assertSame(1, substr_count($response->getContent(), 'class="l-band__title"'));
        $response->assertSee('<h2 id="band-title" class="l-band__title">ご相談・ご来店はこちら</h2>', false);
    }

    public function test_body_type_tiles_are_links_with_counts_for_types_in_stock(): void
    {
        $this->car(['body_type' => 'SUV', 'price' => 1500000]);
        $this->car(['body_type' => 'SUV', 'price' => 3500000]);
        $this->car(['body_type' => 'ミニバン', 'price' => 2500000]);
        $this->car(['body_type' => 'セダン', 'status' => 'sold']);

        $main = $this->main($this->get('/cars')->assertOk());

        // STEP.1：在庫のあるボディタイプだけ、イラスト・台数付きのタイル（押すとすぐに絞り込み、結果の頭へ）
        $this->assertStringContainsString('ボディタイプで選ぶ', $main);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['body_type' => 'SUV'])).'#cars-result"', $main);
        $this->assertMatchesRegularExpression('#<span class="p-cars-type__name">SUV</span>\s*<span class="p-cars-type__count"><b>2</b>台</span>#', $main);
        $this->assertStringNotContainsString('<span class="p-cars-type__name">セダン</span>', $main);
        $this->assertStringContainsString('条件を追加して絞り込む', $main);

        // ほかの条件が付いているときは、その条件で数えた台数（0台のタイプはリンクにしない）
        $filtered = $this->main($this->get('/cars?max_price=2000000')->assertOk());
        $this->assertMatchesRegularExpression('#<span class="p-cars-type__name">SUV</span>\s*<span class="p-cars-type__count"><b>1</b>台</span>#', $filtered);
        $this->assertStringNotContainsString('href="'.e(route('cars.index', ['max_price' => '2000000', 'body_type' => 'ミニバン'])), $filtered);
        $this->assertStringContainsString('（いまの条件では該当なし）', $filtered);
    }
}
