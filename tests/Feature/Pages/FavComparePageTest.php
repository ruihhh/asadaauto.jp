<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * お気に入り（/favorites）と車両比較（/cars/compare）の刷新後の表示。
 */
class FavComparePageTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attributes = []): Car
    {
        return Car::factory()->create(array_merge([
            'status' => 'available',
            'published_at' => now()->subDays(30),
            'price_negotiable' => false,
            'featured' => false,
            'image_path' => null,
            'accident_count' => 0,
            'has_service_record' => false,
            'inspection_type' => null,
            'inspection_expiry' => null,
            'equipment' => null,
        ], $attributes));
    }

    /** 3台（安い・走行距離が少ない・新しいが別々の車） */
    private function threeCars(): array
    {
        return [
            $this->car(['make' => 'マツダ', 'model' => 'CX-5', 'stock_no' => 'MZ4187', 'price' => 3350000, 'base_price' => 3000000, 'model_year' => 2023, 'mileage' => 11200, 'location' => '千葉県船橋市', 'color' => 'ポリメタルグレー', 'transmission' => 'AT']),
            $this->car(['make' => 'ホンダ', 'model' => 'ヴェゼル', 'stock_no' => 'HN2041', 'price' => 2790000, 'base_price' => null, 'model_year' => 2022, 'mileage' => 18700, 'location' => '東京都町田市', 'color' => null, 'transmission' => 'CVT']),
            $this->car(['make' => '日産', 'model' => 'セレナ', 'stock_no' => 'NS3308', 'price' => 2390000, 'base_price' => null, 'model_year' => 2020, 'mileage' => 45200, 'location' => '兵庫県尼崎市', 'color' => 'ダイヤモンドブラック', 'transmission' => 'CVT']),
        ];
    }

    private function assertCommonRules(string $html): void
    {
        $this->assertSame(1, substr_count($html, '<h1'), 'h1 は1つだけ');
        $this->assertSame(1, substr_count($html, '<main'), '<main> はレイアウトの1つだけ');

        // ページ本文（<main> の中。¥ はレイアウトの AutoDealer の priceRange にあるため本文だけで確かめる）
        $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));
        foreach (['円 円', 'No Image', '事故歴', '変速機', '車体色', '保管場所', '整備記録', '¥', 'ございます', 'style='] as $word) {
            $this->assertStringNotContainsString($word, $main, "「{$word}」を出さない");
        }
    }

    public function test_favorites_without_ids_shows_empty_state_with_howto(): void
    {
        $response = $this->get(route('cars.favorites'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertCommonRules($html);
        $response->assertSee('<h1 class="c-page-title">お気に入り</h1>', false);
        $response->assertSee('この端末のブラウザに保存');
        $response->assertSee('お気に入りの車はまだありません');
        $response->assertSee('在庫一覧を見る');
        $response->assertSee('noindex, follow', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        // 読み込み中と空状態は Alpine の起動前に見せない
        $this->assertMatchesRegularExpression('/class="p-fav-loading"[^>]*x-cloak/', $html);
        // 車がないときはフッター直前のご相談帯を出す
        $response->assertSee('ご相談・ご来店はこちら');

        // 案B：見出し帯の英字の飾り・空状態のハートの絵・「登録のしかた」の STEP 図（1 → 2 → 3 ＋ 在庫一覧へ）
        $response->assertSee('<span class="c-page-header__en" aria-hidden="true" lang="en">FAVORITES</span>', false);
        $response->assertSee('p-fav-empty__heart', false);
        $response->assertSee('お気に入りの登録のしかた');
        $this->assertSame(3, substr_count($html, 'class="c-arrow-step__no"'));
        $response->assertSeeText('［お気に入り］を押す');
        $this->assertSame(1, substr_count($html, 'c-arrow-step--go'));
        // 掲載中の車が0台のときは、STEP 図に台数を出さない（「0台」を大きく見せない）
        $this->assertStringNotContainsString('c-arrow-step__count"', $html);
    }

    public function test_favorites_howto_shows_real_public_count(): void
    {
        $this->threeCars();
        $this->car(['status' => 'sold']);

        $response = $this->get(route('cars.favorites'));

        // 公開中の3台だけを数える（売約済みは数えない）
        $response->assertSee('<p class="c-arrow-step__count">3<small>台</small></p>', false);
    }

    public function test_favorites_lists_cars_in_saved_order_and_counts_unavailable(): void
    {
        [$cx5, $vezel] = $this->threeCars();
        $sold = $this->car(['make' => 'トヨタ', 'model' => 'アルファード', 'stock_no' => 'TY7781', 'status' => 'sold']);

        $response = $this->get(route('cars.favorites', ['ids' => implode(',', [$vezel->id, $sold->id, $cx5->id])]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertCommonRules($html);

        // 登録した順（ヴェゼル → CX-5）。売約済みは出さず、台数だけ知らせる
        $response->assertSeeInOrder(['ホンダ ヴェゼル', 'マツダ CX-5']);
        $response->assertDontSee('トヨタ アルファード');
        $response->assertSeeText('1台は売約済み・掲載終了のため表示できません');
        $response->assertSee('探してほしい車を伝える');

        // 共通の車両カード（支払総額と車両本体価格）
        $this->assertSame(2, substr_count($html, '<article class="c-car-card'));
        $response->assertSee('車両本体価格 300.0万円');

        // まとめて問い合わせ（在庫番号をカンマ区切りで）＋電話
        $response->assertSee('お気に入りの車について', false);
        $response->assertSee(e(route('contact.index', ['purpose' => 'favorites', 'list' => 'HN2041,MZ4187'])), false);
        $response->assertSee(config('shop.tel_href'), false);
        // お電話で伝える在庫番号（登録した順に1つずつの札で）
        $response->assertSee('お電話では、この在庫番号をお伝えください');
        $this->assertMatchesRegularExpression('/class="p-fav-stock__no"[^>]*>HN2041<.*class="p-fav-stock__no"[^>]*>MZ4187</s', $html);
        // フォームへの2段ボタン（在庫番号入りの URL は Alpine でも組み直す）
        $this->assertMatchesRegularExpression('/class="c-btn2 c-btn2--yellow"[^>]*x-bind:href="formUrl"/', $html);
        $response->assertSee('フォームで問い合わせる');

        // 登録台数を大きく（見出し帯の丸いバッジ・スマホの帯）。外したら Alpine で数え直す
        $response->assertSee('c-round-badge--lg', false);
        $response->assertSee('<span x-text="count">2</span>', false);
        $response->assertSee('お気に入りに登録中');

        // 旧実装の直接の localStorage 操作をしない（ストアを使う）
        $this->assertStringNotContainsString('favBtn(', $html);
        $this->assertStringNotContainsString('window.location.reload', $html);

        // まとめて問い合わせがあるので、ご相談帯は重ねて出さない
        $response->assertDontSee('ご相談・ご来店はこちら');
    }

    public function test_favorites_bulk_inquiry_is_limited_to_ten_stock_numbers(): void
    {
        $cars = collect(range(1, 11))->map(fn (int $n) => $this->car(['stock_no' => sprintf('AB%04d', $n)]));

        $response = $this->get(route('cars.favorites', ['ids' => $cars->pluck('id')->implode(',')]));

        $list = $cars->take(10)->pluck('stock_no')->implode(',');
        $response->assertSee(e(route('contact.index', ['purpose' => 'favorites', 'list' => $list])), false);
        $response->assertDontSee(e(route('contact.index', ['purpose' => 'favorites', 'list' => $list.',AB0011'])), false);
    }

    public function test_compare_without_ids_shows_empty_state_and_three_steps(): void
    {
        $response = $this->get(route('cars.compare'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertCommonRules($html);
        $response->assertSee('<h1 class="c-page-title">車両比較</h1>', false);
        $response->assertSee('最大3台まで並べて比べられます。');
        $response->assertSee('比較する車が選ばれていません');
        $response->assertSee('比較のしかた');
        // 1 → 2 → 3 の STEP 図（矢印のパネル）と、最後に在庫一覧へ
        $this->assertSame(3, substr_count($html, 'class="c-arrow-step__no"'));
        $response->assertSeeText('［比較に追加］を押す');
        $response->assertSeeText('画面の下の［比較する］を押す');
        $this->assertSame(1, substr_count($html, 'c-arrow-step--go'));
        // 見出し帯の3つの枠は、車がないのですべて「空き」（最初から見せる）
        $this->assertSame(3, substr_count($html, 'p-compare-slot--empty'));
        $this->assertSame(0, preg_match_all('/p-compare-slot--empty" x-show="count <= \\d"\\s*x-cloak\\s*>/', $html));
        // 比較の一覧（$store.compare）に車があれば ids 付きの URL に移る
        $response->assertSee('comparePage(', false);
        $response->assertSee('$store.compare.url', false);
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_compare_table_uses_standard_labels_and_car_text_values(): void
    {
        [$cx5, $vezel, $serena] = $this->threeCars();

        $response = $this->get(route('cars.compare', ['ids' => implode(',', [$cx5->id, $vezel->id, $serena->id])]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertCommonRules($html);

        // 項目名（スマホの狭い列で折り返す位置に <wbr> を入れているので、タグを除いた文字で確かめる）。各行にアイコン
        $labels = ['車両本体価格', '諸費用', '保証', '定期点検整備（法定整備）', '年式', '走行距離', '車検', '修復歴', '点検記録簿', 'ミッション', '燃料', 'ボディタイプ', 'ボディカラー', '主な装備', '展示場所', '在庫番号'];
        $response->assertSeeText('支払総額（税込）');
        foreach ($labels as $label) {
            $response->assertSeeText($label);
        }
        $this->assertSame(count($labels), substr_count($html, 'class="p-compare-label__ic"'));

        // 並びは URL の順
        $response->assertSeeInOrder(['マツダ CX-5', 'ホンダ ヴェゼル', '日産 セレナ']);

        // 値は CarText の表記（数字と単位は別の要素にして数字を大きく出すので、タグを除いた文字で確かめる）
        $response->assertSee('<span class="p-compare-plate__num">335.0</span>', false);
        $response->assertSeeText('335.0万円');
        $response->assertSeeText('300.0万円');
        $response->assertSeeText('35.0万円');
        $response->assertSeeText('2023年（令和5年）');
        $response->assertSee('<span class="p-compare-value__sub u-nowrap">（令和5年）</span>', false);
        $response->assertSeeText('1.1万km');
        $response->assertSee('AT（CVT）');
        // スマホの狭い列で語の途中で折り返さないよう <wbr> を入れても、文字はそのまま（ボディカラー・装備名）
        $response->assertSeeText('ポリメタルグレー');
        $response->assertSee('ポリメタル<wbr>グレー', false);
        $response->assertSeeText('千葉県船橋市で保管中（現車確認はご予約ください）');
        $response->assertSeeText('当店（'.config('shop.area').'）に展示');
        $response->assertSee('要確認');

        // 見出し行：写真（ない車は「写真準備中」）・車名・支払総額。見出し帯の枠に3台の車名
        $this->assertSame(3, substr_count($html, 'class="p-compare-photo__none"'));
        $this->assertSame(3, substr_count($html, 'class="p-compare-plate__price"'));
        $response->assertSeeInOrder(['<span class="p-compare-slot__name">マツダ CX-5</span>', '<span class="p-compare-slot__name">ホンダ ヴェゼル</span>', '<span class="p-compare-slot__name">日産 セレナ</span>'], false);
        // 空きの枠（外したときに Alpine で出す）は、3台のときは最初から隠しておく
        $this->assertSame(3, preg_match_all('/p-compare-slot--empty" x-show="count <= \\d"\\s*x-cloak\\s*>/', $html));

        // いちばん安い（セレナ）・走行距離がいちばん少ない（CX-5）・いちばん新しい（CX-5）。
        // 印は色付きのバッジ（アイコン＋文字）。該当する車だけ最初から見せ（x-cloak なし）、ほかは x-cloak で隠す
        $badge = fn (string $key, int $id, bool $shown) => '/best\\(\''.$key.'\', '.$id.'\\)"'.($shown ? '' : '\\s*x-cloak').'\\s*><svg[^>]*c-icon[^>]*>.*?<\\/svg>\\s*'.['price' => 'いちばん安い', 'mileage' => 'いちばん少ない', 'year' => 'いちばん新しい'][$key].'</s';
        $this->assertMatchesRegularExpression($badge('price', $serena->id, true), $html);
        $this->assertMatchesRegularExpression($badge('mileage', $cx5->id, true), $html);
        $this->assertMatchesRegularExpression($badge('year', $cx5->id, true), $html);
        $this->assertMatchesRegularExpression($badge('price', $cx5->id, false), $html);
        $this->assertMatchesRegularExpression($badge('mileage', $serena->id, false), $html);
        $this->assertSame(3, substr_count($html, 'class="p-compare-badge p-compare-badge--price"') - 1, '支払総額の印は3台分（＋色の見かたの1つ）');
        $response->assertSee('色の印の見かた');

        // 各列の操作
        $this->assertSame(3, substr_count($html, '在庫確認・</span>'));
        $response->assertSee(e(route('contact.index', ['stock_no' => 'MZ4187', 'purpose' => 'stock'])), false);
        $this->assertSame(3, substr_count($html, '>詳しく見る</a>'));
        $this->assertSame(3, substr_count($html, '>比較から外す</button>'));

        // 項目名の列（左に固定）と、スマホで3台のときの横スクロールの案内
        $response->assertSee('p-compare-table__label', false);
        $response->assertSee('表を指で左にずらすと、3台目が見られます。');
        $response->assertSee('is-wide', false);

        // 支払総額の条件注記
        $response->assertSee('兵庫県内で登録し、当店で店頭納車する場合');
        // 「修復歴」の項目名の隣に置くので、主語のない定義文を使う
        $response->assertSee(\App\Support\CarText::REPAIR_DEFINITION_SHORT);
        $response->assertDontSee(\App\Support\CarText::REPAIR_DEFINITION);
    }

    public function test_compare_skips_unavailable_cars_and_notifies(): void
    {
        [$cx5] = $this->threeCars();
        $sold = $this->car(['make' => 'トヨタ', 'model' => 'アルファード', 'status' => 'sold']);

        $response = $this->get(route('cars.compare', ['ids' => $cx5->id.','.$sold->id]));

        $response->assertOk();
        $response->assertSee('マツダ CX-5');
        $response->assertDontSee('トヨタ アルファード');
        $response->assertSeeText('1台は売約済み・掲載終了のため表示できません');
        // 1台だけでは「いちばん◯◯」を付けない
        $this->assertDoesNotMatchRegularExpression("/best\\('price', {$cx5->id}\\)\"\\s*>/", $response->getContent());
    }

    public function test_compare_shows_negotiable_price_without_number(): void
    {
        $car = $this->car(['make' => 'トヨタ', 'model' => 'プリウス', 'price' => null, 'price_negotiable' => true]);

        $response = $this->get(route('cars.compare', ['ids' => (string) $car->id]));

        $response->assertOk();
        $response->assertSee('価格はお問い合わせください（応談）');
    }
}
