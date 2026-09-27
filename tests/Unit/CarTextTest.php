<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Support\CarText;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class CarTextTest extends TestCase
{
    private function now(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-09-27 12:00', 'Asia/Tokyo');
    }

    /**
     * 東京の日時を、モデルに保存される形（アプリのタイムゾーンの日時）にする。
     */
    private function at(string $tokyo): CarbonImmutable
    {
        return CarbonImmutable::parse($tokyo, 'Asia/Tokyo')->setTimezone(config('app.timezone'));
    }

    public function test_price(): void
    {
        $this->assertSame('335.0万円', CarText::price(3350000));
        $this->assertSame('335.5万円', CarText::price(3355000));
        $this->assertSame('3,351,200円', CarText::price(3351200));
        $this->assertSame('1,200.0万円', CarText::price(12000000));
        $this->assertSame('お問い合わせください', CarText::price(null));

        $this->assertSame('335.0', CarText::priceMan(3350000));
        $this->assertNull(CarText::priceMan(3351200));
        $this->assertSame(['value' => '335.0', 'unit' => '万円'], CarText::priceParts(3350000));
        $this->assertSame(['value' => '3,351,200', 'unit' => '円'], CarText::priceParts(3351200));
        $this->assertSame('3,350,000円', CarText::yen(3350000));
    }

    public function test_fees(): void
    {
        $this->assertSame(350000, CarText::fees(new Car(['price' => 3350000, 'base_price' => 3000000])));
        $this->assertNull(CarText::fees(new Car(['price' => 3350000, 'base_price' => null])));
        $this->assertNull(CarText::fees(new Car(['price' => 3000000, 'base_price' => 3350000])));
        $this->assertSame('35.0万円', CarText::price(CarText::fees(new Car(['price' => 3350000, 'base_price' => 3000000]))));
    }

    public function test_price_note(): void
    {
        $this->assertStringStartsWith('支払総額は兵庫県内で登録し、当店で店頭納車する場合の価格です。', CarText::priceNote());

        $car = new Car;
        $car->updated_at = $this->at('2026-04-26 01:00'); // UTC では 4月25日
        $this->assertSame('価格は2026年4月26日時点のものです', CarText::priceAsOf($car));
    }

    public function test_mileage(): void
    {
        $this->assertSame('1.1万km', CarText::mileage(11200));
        $this->assertSame('4.6万km', CarText::mileage(45500));
        $this->assertSame('4.5万km', CarText::mileage(45499));
        $this->assertSame('10.0万km', CarText::mileage(100000));
        $this->assertSame('0.1万km', CarText::mileage(1000));
        $this->assertSame('1,000km未満', CarText::mileage(999));
        $this->assertSame('1,000km未満', CarText::mileage(0));
    }

    public function test_year(): void
    {
        $this->assertSame('2023（R5）年式', CarText::year(2023));
        $this->assertSame('2023年（令和5年）', CarText::year(2023, true));
        $this->assertSame('2019（H31/R1）年式', CarText::year(2019));
        $this->assertSame('2019年（平成31年/令和元年）', CarText::year(2019, true));
        $this->assertSame('1989（S64/H1）年式', CarText::year(1989));
        $this->assertSame('1989年（昭和64年/平成元年）', CarText::year(1989, true));
        $this->assertSame('2018（H30）年式', CarText::year(2018));
        $this->assertSame('2020（R2）年式', CarText::year(2020));
    }

    public function test_wareki(): void
    {
        $this->assertSame('令和9年3月', CarText::wareki(CarbonImmutable::parse('2027-03-01')));
        $this->assertSame('令和元年5月', CarText::wareki(CarbonImmutable::parse('2019-05-01')));
        $this->assertSame('平成31年4月', CarText::wareki(CarbonImmutable::parse('2019-04-30')));
        $this->assertSame('令和8年', CarText::wareki(CarbonImmutable::parse('2026-09-27'), 'year'));
        $this->assertSame('令和8年9月27日', CarText::wareki(CarbonImmutable::parse('2026-09-27'), 'day'));
    }

    public function test_date_is_formatted_in_tokyo(): void
    {
        $this->assertSame('2026年9月28日', CarText::date(CarbonImmutable::parse('2026-09-27 15:30', 'UTC')));
        $this->assertNull(CarText::date(null));
    }

    public function test_transmission(): void
    {
        $this->assertSame('AT（CVT）', CarText::transmission('CVT'));
        $this->assertSame('AT（CVT）', CarText::transmission('cvt'));
        $this->assertSame('AT', CarText::transmission('AT'));
        $this->assertSame('MT', CarText::transmission('MT'));
        $this->assertSame('AT（DCT）', CarText::transmission('DCT'));
        $this->assertSame('お問い合わせください', CarText::transmission(null));
    }

    public function test_fuel_and_body_type(): void
    {
        $this->assertSame('ガソリン', CarText::fuel('ガソリン'));
        $this->assertSame('ハイブリッド', CarText::fuel('ハイブリッド'));
        $this->assertSame('電気（EV）', CarText::fuel('電気'));
        $this->assertSame('お問い合わせください', CarText::fuel(''));

        $this->assertSame('コンパクトカー', CarText::bodyType('コンパクト'));
        $this->assertSame('ステーションワゴン', CarText::bodyType('ワゴン'));
        $this->assertSame('SUV', CarText::bodyType('SUV'));
        $this->assertSame('軽自動車', CarText::bodyType('軽自動車'));
    }

    public function test_inspection_with_expiry_date(): void
    {
        $car = new Car(['inspection_type' => 'あり', 'inspection_expiry' => '2027-03-01']);
        $this->assertSame(
            ['state' => 'expiry', 'label' => '2027年3月まで（令和9年3月）', 'short' => '2027年3月まで', 'tone' => 'ok'],
            CarText::inspection($car, $this->now()),
        );

        // 区分が未入力でも期限があれば期限で表示する
        $legacy = new Car(['inspection_type' => null, 'inspection_expiry' => '2026-09-01']);
        $this->assertSame('2026年9月まで（令和8年9月）', CarText::inspection($legacy, $this->now())['label']);
    }

    public function test_inspection_included_on_delivery(): void
    {
        foreach (['1', '2', '3'] as $years) {
            $result = CarText::inspection(new Car(['inspection_type' => "{$years}年付"]), $this->now());

            $this->assertSame('included', $result['state']);
            $this->assertSame("車検{$years}年付き（ご納車時に新しく取得）", $result['label']);
            $this->assertSame("{$years}年付き（納車時に取得）", $result['short']);
            $this->assertSame('ok', $result['tone']);
        }
    }

    public function test_inspection_other_patterns(): void
    {
        $this->assertSame(
            ['state' => 'unknown', 'label' => '要確認', 'short' => '要確認', 'tone' => 'neutral'],
            CarText::inspection(new Car, $this->now()),
        );

        $this->assertSame(
            ['state' => 'remaining', 'label' => '車検の残りあり（期限は要確認）', 'short' => '残りあり', 'tone' => 'neutral'],
            CarText::inspection(new Car(['inspection_type' => 'あり']), $this->now()),
        );

        $this->assertSame(
            ['state' => 'none', 'label' => '車検なし（車検の取得はお問い合わせください）', 'short' => 'なし', 'tone' => 'caution'],
            CarText::inspection(new Car(['inspection_type' => 'なし']), $this->now()),
        );

        $this->assertSame(
            ['state' => 'expired', 'label' => '車検なし（2026年8月で期限切れ）', 'short' => '期限切れ', 'tone' => 'caution'],
            CarText::inspection(new Car(['inspection_type' => 'あり', 'inspection_expiry' => '2026-08-01']), $this->now()),
        );
    }

    public function test_repair(): void
    {
        $none = CarText::repair(new Car(['accident_count' => 0]));
        $this->assertFalse($none['has']);
        $this->assertSame('なし', $none['label']);
        $this->assertSame('ok', $none['tone']);
        $this->assertNull($none['note']);

        $has = CarText::repair(new Car(['accident_count' => 2]));
        $this->assertTrue($has['has']);
        $this->assertSame('あり', $has['label']);
        $this->assertSame('修復歴あり', $has['text']);
        $this->assertSame('部位はお問い合わせください', $has['note']);
        $this->assertSame('caution', $has['tone']);

        $this->assertSame('お問い合わせください', CarText::repair(new Car)['label']);
    }

    public function test_location(): void
    {
        $this->assertSame('当店（尼崎市下坂部）に展示', CarText::location(new Car(['location' => null])));
        $this->assertSame('当店（尼崎市下坂部）に展示', CarText::location(new Car(['location' => '兵庫県尼崎市'])));
        $this->assertTrue(CarText::atShop(new Car(['location' => '尼崎市下坂部'])));

        $remote = new Car(['location' => '千葉県船橋市']);
        $this->assertFalse(CarText::atShop($remote));
        $this->assertSame('千葉県船橋市で保管中（現車確認はご予約ください）', CarText::location($remote));
    }

    public function test_is_new_within_seven_days_in_tokyo(): void
    {
        $now = $this->now(); // 2026-09-27 12:00（東京）

        $this->assertTrue(CarText::isNew(new Car(['published_at' => $this->at('2026-09-27 09:00')]), $now));
        $this->assertTrue(CarText::isNew(new Car(['published_at' => $this->at('2026-09-20 00:00')]), $now));
        $this->assertFalse(CarText::isNew(new Car(['published_at' => $this->at('2026-09-19 23:59')]), $now));
        $this->assertFalse(CarText::isNew(new Car(['published_at' => $this->at('2026-09-28 10:00')]), $now)); // 掲載予定
        $this->assertFalse(CarText::isNew(new Car, $now));
    }

    public function test_tags(): void
    {
        $car = new Car([
            'status' => 'reserved',
            'featured' => true,
            'published_at' => $this->at('2026-09-25 10:00'),
        ]);

        $this->assertSame(['商談中', '新着', '店長おすすめ'], array_column(CarText::tags($car, $this->now()), 'label'));
        $this->assertSame(['caution', 'new', 'pick'], array_column(CarText::tags($car, $this->now()), 'variant'));
        $this->assertSame([['label' => '売約済', 'variant' => 'sold']], CarText::tags(new Car(['status' => 'sold']), $this->now()));
    }

    public function test_equipment(): void
    {
        $car = new Car(['equipment' => [
            'ABS', 'ESC（横滑り防止）', '衝突被害軽減ブレーキ', 'バックカメラ', '全周囲カメラ', 'エアコン',
            'メモリーナビ', 'Wエアコン', 'ETC', 'ETC2.0', 'ドライブレコーダー', 'スマートキー', 'シートヒーター',
            'アルミホイール', '独自の装備',
        ]]);

        $this->assertSame(
            ['衝突被害軽減ブレーキ', '全周囲カメラ', 'メモリーナビ', 'ETC2.0', 'ドライブレコーダー', 'シートヒーター'],
            CarText::equipmentHighlights($car),
        );
        $this->assertCount(3, CarText::equipmentHighlights($car, 3));
        $this->assertSame([], CarText::equipmentHighlights(new Car));

        $groups = CarText::equipmentByCategory($car);
        $this->assertSame(['安全装備', '快適装備', '内装', '外装', 'その他'], array_keys($groups));
        $this->assertContains('ESC（横滑り防止装置）', $groups['安全装備']);
        $this->assertContains('Wエアコン（前後独立エアコン）', $groups['快適装備']);
        $this->assertSame(['独自の装備'], $groups['その他']);

        $this->assertSame('AC100V電源（1500W）', CarText::equipmentLabel('1500W電源'));
        $this->assertSame('内装', CarText::equipmentCategory('インテリア'));
        $this->assertSame('外装', CarText::equipmentCategory('エクステリア'));
    }

    public function test_name_warranty_and_maintenance(): void
    {
        $this->assertSame('トヨタ プリウス', CarText::name(new Car(['make' => 'トヨタ', 'model' => 'プリウス'])));

        $this->assertNull(CarText::warranty());
        $this->assertNull(CarText::maintenance());

        config(['shop.warranty' => '保証付き（3か月・3,000km）', 'shop.maintenance' => '定期点検整備付き']);
        $this->assertSame('保証付き（3か月・3,000km）', CarText::warranty());
        $this->assertSame('定期点検整備付き', CarText::maintenance());
    }

    public function test_repair_definition_has_a_version_without_subject(): void
    {
        $this->assertSame('車の骨格（フレーム）部分を修理・交換した履歴のことです。', CarText::REPAIR_DEFINITION_SHORT);
        $this->assertSame('修復歴とは、'.CarText::REPAIR_DEFINITION_SHORT, CarText::REPAIR_DEFINITION);
    }

    public function test_year_and_mileage_parts_join_back_to_the_display_text(): void
    {
        // カードでは数字だけを大きな書体にするため分けて出す。つなげると year() / mileage() と同じ文字になる
        $this->assertSame(['value' => '2023', 'rest' => '（R5）年式'], CarText::yearParts(2023));
        $this->assertSame(['value' => '2019', 'rest' => '（H31/R1）年式'], CarText::yearParts(2019));
        $this->assertSame(['value' => 'お問い合わせください', 'rest' => null], CarText::yearParts(null));
        $this->assertSame(CarText::year(2023), implode('', CarText::yearParts(2023)));

        $this->assertSame(['value' => '1.1', 'unit' => '万km'], CarText::mileageParts(11200));
        $this->assertSame(['value' => '1,000', 'unit' => 'km未満'], CarText::mileageParts(999));
        $this->assertNull(CarText::mileageParts(null));
        $this->assertSame(CarText::mileage(45300), implode('', CarText::mileageParts(45300)));
    }

    public function test_body_illust_covers_every_body_type_value(): void
    {
        // 管理画面で選べる値（DB の値）と、旧データの値・表示名
        $expected = [
            '軽自動車' => 'kei', 'コンパクト' => 'compact', 'コンパクトカー' => 'compact', 'ミニバン' => 'minivan',
            'SUV' => 'suv', 'SUV・クロスオーバー' => 'suv', 'SUV・四駆' => 'suv', 'セダン' => 'sedan',
            'HB' => 'hatchback', 'ハッチバック' => 'hatchback', 'ワゴン' => 'wagon', 'ステーションワゴン' => 'wagon',
            'クーペ' => 'sports', 'スポーツ' => 'sports', '福祉車両' => 'welfare', 'トラック' => 'truck',
            'その他' => 'other', '' => 'other',
        ];

        foreach ($expected as $value => $illust) {
            $this->assertSame($illust, CarText::bodyIllust($value), $value);
        }
        $this->assertSame('other', CarText::bodyIllust(null));
    }

    public function test_equipment_icon(): void
    {
        $this->assertSame('eq-brake', CarText::equipmentIcon('衝突被害軽減ブレーキ'));
        $this->assertSame('eq-360', CarText::equipmentIcon('全周囲カメラ'));
        $this->assertSame('eq-navi', CarText::equipmentIcon('メモリーナビ'));
        $this->assertSame('eq-etc', CarText::equipmentIcon('ETC2.0'));
        $this->assertSame('eq-dashcam', CarText::equipmentIcon('ドライブレコーダー'));
        $this->assertSame('eq-seat', CarText::equipmentIcon('シートヒーター'));
        $this->assertSame('eq-slide', CarText::equipmentIcon('両側電動スライドドア'));
        $this->assertSame('eq-aircon', CarText::equipmentIcon('Wエアコン（前後独立エアコン）'));
        $this->assertSame('eq-check', CarText::equipmentIcon('ABS'));
    }

    public function test_breakable_inserts_wbr_at_word_boundaries_and_escapes(): void
    {
        $cases = [
            '衝突被害軽減ブレーキ' => '衝突被害軽減<wbr>ブレーキ',
            'ドライブレコーダー' => 'ドライブ<wbr>レコーダー',
            'ポリメタルグレー' => 'ポリメタル<wbr>グレー',
            'デニムブルーメタリック' => 'デニム<wbr>ブルー<wbr>メタリック',
            '両側電動スライドドア' => '両側電動<wbr>スライド<wbr>ドア',
            '千葉県船橋市で保管中（現車確認はご予約ください）' => '千葉県<wbr>船橋市<wbr>で保管中<wbr>（現車確認は<wbr>ご予約<wbr>ください）',
            'ETC2.0' => 'ETC2.0',
        ];
        foreach ($cases as $text => $html) {
            $result = CarText::breakable($text);
            $this->assertInstanceOf(\Illuminate\Support\HtmlString::class, $result);
            $this->assertSame($html, (string) $result, $text);
            // 文字そのものは変えない
            $this->assertSame($text, str_replace('<wbr>', '', (string) $result));
        }

        // HTML はエスケープする
        $this->assertSame('A&amp;B&lt;x&gt;', (string) CarText::breakable('A&B<x>'));
        $this->assertSame('', (string) CarText::breakable(null));
    }
}
