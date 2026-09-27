<?php

namespace Tests\Feature\Pages;

use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 車両詳細（/cars/{slug}）の刷新後の表示。
 */
class CarsShowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        parent::tearDown();
        // 車両詳細は1ページの部品が多く、テストごとのアプリの循環参照が残るとテスト全体が PHP のメモリ上限（128MB）に近づくため、ここで回収する
        gc_collect_cycles();
    }

    private function car(array $attributes = []): Car
    {
        return Car::factory()->create(array_merge([
            'stock_no' => 'MZ4187',
            'make' => 'マツダ',
            'model' => 'CX-5',
            'grade' => 'XD Black Tone Edition',
            'body_type' => 'SUV',
            'transmission' => 'AT',
            'fuel_type' => 'ディーゼル',
            'model_year' => 2023,
            'mileage' => 11200,
            'price' => 3350000,
            'base_price' => 3000000,
            'price_negotiable' => false,
            'color' => 'ポリメタルグレー',
            'location' => '千葉県船橋市',
            'description' => '4WD・レーダークルーズ・360度ビュー。',
            'image_path' => 'cars/mazda_cx5.jpg',
            'featured' => true,
            'status' => 'available',
            'published_at' => now()->subDays(30),
            'accident_count' => 0,
            'has_service_record' => false,
            'inspection_type' => null,
            'inspection_expiry' => null,
            'equipment' => ['ABS', '衝突被害軽減ブレーキ', '全周囲カメラ', 'バックカメラ', 'メモリーナビ', 'ETC', 'ドライブレコーダー', 'シートヒーター', 'Wエアコン'],
        ], $attributes));
    }

    /** レイアウト（ヘッダー・フッター・構造化データ）を除いた、ページ本文だけの HTML */
    private function main(TestResponse $response): string
    {
        $html = $response->getContent();
        $start = strpos($html, '<main');
        $end = strpos($html, '</main>');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }

    /** @return list<array<string, mixed>> */
    private function jsonLd(TestResponse $response): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $m);

        return array_map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_detail_shows_price_and_the_four_key_facts_near_the_price(): void
    {
        $car = $this->car();
        $response = $this->get(route('cars.show', $car))->assertOk();
        $main = $this->main($response);

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $this->assertStringContainsString('<h1 class="c-page-title">マツダ CX-5</h1>', $main);
        $this->assertStringContainsString('店長おすすめ', $main);
        $this->assertStringContainsString('在庫番号 <strong class="p-detail-stock__no">MZ4187</strong>', $main);

        // 価格ブロック（支払総額・車両本体価格＋諸費用・条件注記）
        $this->assertStringContainsString('335.0', $main);
        $this->assertStringContainsString('3,350,000円', $main);
        $this->assertStringContainsString('車両本体価格 300.0万円＋諸費用 35.0万円', $main);
        $this->assertStringContainsString('兵庫県内で登録し、当店で店頭納車する場合', $main);

        // 価格のすぐ後に重要4項目（修復歴・車検・保証・定期点検整備）
        $keyfacts = strpos($main, 'p-detail-keyfacts');
        $this->assertGreaterThan(strpos($main, 'c-price--detail'), $keyfacts);
        $this->assertLessThan(strpos($main, 'p-detail-actions'), $keyfacts);
        $block = substr($main, $keyfacts, strpos($main, 'p-detail-actions') - $keyfacts);
        foreach (['修復歴', '車検', '保証', '定期点検整備', '（法定整備）', '要確認', 'お問い合わせ'] as $text) {
            $this->assertStringContainsString($text, $block);
        }
        $this->assertStringContainsString('p-detail-keyfacts__value--ok', $block);
    }

    public function test_inquiry_buttons_carry_stock_no_and_purpose(): void
    {
        $car = $this->car();
        $main = $this->main($this->get(route('cars.show', $car)));

        $this->assertStringContainsString('href="'.e(route('contact.index', ['stock_no' => 'MZ4187', 'purpose' => 'estimate'])).'"', $main);
        $this->assertStringContainsString('href="'.e(route('contact.index', ['stock_no' => 'MZ4187', 'purpose' => 'visit'])).'"', $main);
        $this->assertStringContainsString('この車の在庫確認・', $main);
        $this->assertStringContainsString('見学・試乗を予約する', $main);
        $this->assertStringContainsString('href="'.config('shop.tel_href').'"', $main);
        $this->assertStringContainsString('電話で問い合わせる', $main);
        $this->assertStringContainsString('c-open-status', $main);
        // お気に入り・比較は共通ストアを使う（旧 favBtn は使わない）
        $this->assertStringContainsString('$store.favorites.toggle(id)', $main);
        $this->assertStringContainsString('$store.compare.toggle(id, name)', $main);
    }

    public function test_line_button_is_shown_only_when_line_url_is_configured(): void
    {
        $car = $this->car();

        config(['shop.line_url' => null]);
        $this->assertStringNotContainsString('LINEで相談', $this->main($this->get(route('cars.show', $car))));

        config(['shop.line_url' => 'https://lin.ee/example']);
        $main = $this->main($this->get(route('cars.show', $car)));
        $this->assertStringContainsString('LINEで相談', $main);
        $this->assertStringContainsString('href="https://lin.ee/example"', $main);
    }

    public function test_negotiable_car_asks_for_price_and_hides_breakdown(): void
    {
        $car = $this->car(['price_negotiable' => true]);
        $main = $this->main($this->get(route('cars.show', $car))->assertOk());

        $this->assertStringContainsString('価格はお問い合わせください（応談）', $main);
        $this->assertStringContainsString('価格を問い合わせる', $main);
        $this->assertStringNotContainsString('id="breakdown"', $main);
    }

    public function test_breakdown_is_shown_only_with_base_price_and_has_no_estimate_wording(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car())));
        $this->assertStringContainsString('id="breakdown"', $main);
        $this->assertStringContainsString('35.0万円', $main);
        $this->assertStringContainsString('350,000円', $main);
        $this->assertStringContainsString('支払総額に含まれるもの', $main);
        $this->assertStringContainsString('支払総額に含まれないもの', $main);
        $this->assertStringNotContainsString('整備費用', $main);

        $noBase = $this->car(['stock_no' => 'SZ5522', 'base_price' => null]);
        $main = $this->main($this->get(route('cars.show', $noBase)));
        $this->assertStringNotContainsString('id="breakdown"', $main);
        $this->assertStringContainsString('車両本体価格はお問い合わせください', $main);
    }

    public function test_forbidden_wording_is_not_used(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car())));

        foreach (['事故歴', '整備記録', '整備履歴', '諸費用目安', '約 ', '¥', 'No Image', '★', 'ございます', 'style=', 'favBtn', 'twitter.com', 'LINE シェア', '販売中'] as $bad) {
            $this->assertStringNotContainsString($bad, $main, "「{$bad}」が残っている");
        }

        $source = file_get_contents(resource_path('views/cars/show.blade.php'));
        foreach (['事故歴', '整備記録', '諸費用目安', '¥', 'No Image', '★', 'style=', '<main', 'localStorage', '06-4960-8765'] as $bad) {
            $this->assertStringNotContainsString($bad, $source, "ビューに「{$bad}」がある");
        }
    }

    public function test_equipment_lists_only_equipped_items_and_highlights_up_to_six(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car())));

        // 主な装備は共通部品 x-site.equip-list（黒い丸に黄のアイコン）で最大6つ。その直下に全装備の開閉（details）を置く
        $block = substr($main, strpos($main, 'id="equipment"'));
        $this->assertSame(6, substr_count($main, 'class="c-equip"'));
        $this->assertLessThan(strpos($block, '<details'), strpos($block, 'class="c-equips"'));
        $this->assertStringContainsString('装備をすべて見る（9項目）', strip_tags($main));
        $this->assertStringContainsString('Wエアコン（前後独立エアコン）', $main);
        // 装備していない項目は出さない
        $this->assertStringNotContainsString('エアバッグ（運転席）', $main);
        $this->assertStringNotContainsString('パワーウインドウ', $main);

        $none = $this->car(['stock_no' => 'SZ5522', 'equipment' => null]);
        $main = $this->main($this->get(route('cars.show', $none)));
        $this->assertStringNotContainsString('p-detail-highlights', $main);
        $this->assertStringNotContainsString('id="equipment"', $main);
    }

    public function test_spec_table_uses_industry_terms(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car(['has_service_record' => true, 'inspection_expiry' => '2027-03-31']))));

        // 年式は西暦の数字だけを大きな書体にするため、数字を別の要素に分けている（文字としてつながっていることを確かめる）
        $this->assertStringContainsString('2023年（令和5年）', strip_tags($main));
        // 走行距離は数字だけを大きな書体にするため、数字と単位を別の要素に分けている（文字としてつながっていることを確かめる）
        $this->assertStringContainsString('1.1万km', strip_tags($main));
        $this->assertStringContainsString('修復歴とは、車の骨格（フレーム）部分を修理・交換した履歴のことです。', $main);
        // 車検の期限は「（」の前でだけ改行させるため語の単位に分けている（文字としてつながっていること・ゼロ埋めでないことを確かめる）
        $this->assertStringContainsString('2027年3月まで（令和9年3月）', strip_tags($main));
        $this->assertStringNotContainsString('2027年03月', $main);
        $this->assertStringContainsString('点検記録簿', $main);
        $this->assertStringContainsString('ボディカラー', $main);
        $this->assertStringContainsString('展示場所', $main);
        $this->assertStringContainsString('掲載日', $main);
    }

    public function test_car_not_at_shop_shows_warning_in_shop_section(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car())));
        $this->assertStringContainsString('c-alert c-alert--warn p-detail-shop__alert', $main);
        $this->assertStringContainsString('千葉県船橋市で保管中（現車確認はご予約ください）', $main);
        $this->assertStringContainsString('店舗のご案内', $main);

        $atShop = $this->car(['stock_no' => 'SZ5522', 'location' => '兵庫県尼崎市']);
        $main = $this->main($this->get(route('cars.show', $atShop)));
        $this->assertStringContainsString('この車が見られる店舗', $main);
        $this->assertStringNotContainsString('p-detail-shop__alert', $main);
    }

    public function test_related_cars_or_similar_search_links(): void
    {
        $car = $this->car();
        $main = $this->main($this->get(route('cars.show', $car)));

        $this->assertStringContainsString('似た条件の車を探す', $main);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['body_type' => 'SUV'])).'"', $main);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['min_price' => 2850000, 'max_price' => 3850000])).'"', $main);
        $this->assertStringContainsString('285.0万〜385.0万円', $main);

        // 価格帯の下限は0
        $cheap = $this->car(['stock_no' => 'KK0001', 'make' => 'ダイハツ', 'price' => 300000, 'base_price' => null]);
        $cheapMain = $this->main($this->get(route('cars.show', $cheap)));
        $this->assertStringContainsString('href="'.e(route('cars.index', ['min_price' => 0, 'max_price' => 800000])).'"', $cheapMain);

        $this->car(['stock_no' => 'MZ0002', 'model' => 'MAZDA3', 'slug' => null]);
        $main = $this->main($this->get(route('cars.show', $car)));
        $this->assertStringContainsString('マツダのほかの在庫', $main);
        $this->assertStringContainsString('マツダ MAZDA3', $main);
        $this->assertStringNotContainsString('似た条件の車を探す', $main);
    }

    public function test_similar_cars_are_shown_as_cards_when_no_car_of_the_same_make(): void
    {
        $car = $this->car();
        // 同じボディタイプ（SUV）のほかのメーカーの車 → 出す
        $this->car(['stock_no' => 'HN2041', 'make' => 'ホンダ', 'model' => 'ヴェゼル', 'slug' => null, 'price' => 2790000, 'base_price' => null]);
        // ボディタイプも価格帯（285万〜385万円）も違う車 → 出さない
        $this->car(['stock_no' => 'SZ5522', 'make' => 'スズキ', 'model' => 'ハスラー', 'body_type' => '軽自動車', 'slug' => null, 'price' => 1490000, 'base_price' => null]);
        // 売約済みの車 → 出さない（公開在庫だけ）
        $this->car(['stock_no' => 'TY0001', 'make' => 'トヨタ', 'model' => 'RAV4', 'slug' => null, 'status' => 'sold']);

        $main = $this->main($this->get(route('cars.show', $car)));
        $related = substr($main, strpos($main, 'id="related"'));

        $this->assertStringContainsString('似た条件の車を探す', $related);
        $this->assertStringContainsString('ボディタイプや価格が近い在庫', $related);
        $this->assertStringContainsString('ホンダ ヴェゼル', $related);
        $this->assertStringNotContainsString('スズキ ハスラー', $related);
        $this->assertStringNotContainsString('トヨタ RAV4', $related);
        // この車自身は出さない
        $this->assertStringNotContainsString('マツダ CX-5', $related);
        // 条件を広げるリンクも残す
        $this->assertStringContainsString('条件を広げて探す', $related);
        $this->assertStringContainsString('href="'.e(route('cars.index', ['body_type' => 'SUV'])).'"', $related);
    }

    public function test_spec_tiles_follow_inquiry_and_loan_starts_from_this_car_price(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car())));

        // スマホの並び（写真 → 車名 → 価格 → 重要4項目 → 問い合わせ）を崩さず、その後に車両の状態・仕様
        $order = array_map(fn (string $needle) => strpos($main, $needle), [
            'p-detail-gallery', '<h1 class="c-page-title">', 'c-price--detail', 'p-detail-keyfacts', 'p-detail-actions', 'id="spec"',
        ]);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);

        // 仕様のタイル：ボディカラーは色見本（色の系統）＋色名の文字
        $this->assertStringContainsString('p-detail-swatch--gray', $main);
        $this->assertStringContainsString('ポリメタルグレー', $main);

        // この車のお支払い例：支払総額 335万円・60回・実質年率3.9%（計算例）の月々を、JavaScript が動く前から出す
        $this->assertStringContainsString('この車の支払総額 335.0万円で計算しています。', $main);
        $this->assertStringContainsString('61,544', $main);
        $this->assertStringContainsString('計算例です。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。', $main);
    }

    public function test_gallery_with_several_photos_has_buttons_and_count(): void
    {
        $car = $this->car();
        CarImage::create(['car_id' => $car->id, 'path' => 'cars/1/a.jpg', 'sort_order' => 1]);
        CarImage::create(['car_id' => $car->id, 'path' => 'cars/1/b.jpg', 'sort_order' => 2]);

        $main = $this->main($this->get(route('cars.show', $car)));

        $this->assertStringContainsString('aria-label="前の写真"', $main);
        $this->assertStringContainsString('c-icon--chevron-left', $main);
        $this->assertStringContainsString('aria-label="次の写真"', $main);
        $this->assertStringContainsString('aria-label="3枚目の写真を表示"', $main);
        $this->assertStringContainsString('/ 3</p>', $main);
        $this->assertStringContainsString('role="dialog"', $main);
        $this->assertStringContainsString('alt="マツダ CX-5の写真（1枚目）"', $main);
    }

    public function test_car_without_photo_shows_placeholder(): void
    {
        $main = $this->main($this->get(route('cars.show', $this->car(['image_path' => null]))));

        $this->assertStringContainsString('写真準備中', $main);
        $this->assertStringNotContainsString('role="dialog"', $main);
    }

    public function test_share_block_is_at_the_bottom_and_not_near_inquiry(): void
    {
        $car = $this->car();
        $main = $this->main($this->get(route('cars.show', $car)));

        $share = strpos($main, 'id="share"');
        $this->assertNotFalse($share);
        $this->assertGreaterThan(strpos($main, 'id="related"'), $share);
        $this->assertStringContainsString('https://social-plugins.line.me/lineit/share?url='.rawurlencode(route('cars.show', $car)), $main);
        $this->assertStringContainsString('URLをコピーする', $main);
    }

    public function test_meta_and_structured_data(): void
    {
        $car = $this->car();
        $response = $this->get(route('cars.show', $car));
        $html = $response->getContent();

        $this->assertStringContainsString('<meta name="description" content="2023（R5）年式・走行1.1万km・修復歴なし。支払総額335.0万円（税込）。兵庫県尼崎市の中古車販売店アサダオートサポート。">', $html);
        $this->assertStringNotContainsString('vehicleIdentificationNumber', $html);

        $schemas = collect($this->jsonLd($response))->keyBy('@type');
        $vehicle = $schemas->get('Vehicle');
        $this->assertNotNull($vehicle);
        $this->assertSame('MZ4187', $vehicle['sku']);
        $this->assertSame('マツダ CX-5', $vehicle['name']);
        $this->assertSame(3350000, $vehicle['offers']['price']);
        $this->assertSame('修復歴なし', $vehicle['knownVehicleDamages']);

        $crumbs = $schemas->get('BreadcrumbList');
        $this->assertNotNull($crumbs);
        $this->assertSame(['ホーム', '中古車在庫一覧', 'マツダ CX-5'], array_column($crumbs['itemListElement'], 'name'));
        // AutoDealer はレイアウトの1つだけ
        $this->assertSame(1, substr_count($html, '"@type":"AutoDealer"') + substr_count($html, '"@type": "AutoDealer"'));
    }
}
