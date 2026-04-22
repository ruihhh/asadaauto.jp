<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarGrade;
use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $cars = [
            [
                'stock_no' => 'TK1023',
                'make' => 'トヨタ',
                'model' => 'プリウス',
                'grade' => 'S ツーリングセレクション',
                'body_type' => 'ハッチバック',
                'transmission' => 'CVT',
                'fuel_type' => 'ハイブリッド',
                'model_year' => 2021,
                'mileage' => 32500,
                'price' => 2280000,
                'color' => 'パールホワイト',
                'location' => '神奈川県横浜市',
                'description' => 'ワンオーナー・純正ナビ・バックカメラ付き。',
                'featured' => true,
                'status' => 'available',
                'published_at' => now()->subDays(1),
            ],
            [
                'stock_no' => 'HN2041',
                'make' => 'ホンダ',
                'model' => 'ヴェゼル',
                'grade' => 'e:HEV Z',
                'body_type' => 'SUV',
                'transmission' => 'CVT',
                'fuel_type' => 'ハイブリッド',
                'model_year' => 2022,
                'mileage' => 18700,
                'price' => 2790000,
                'color' => 'プレミアムクリスタルレッド',
                'location' => '東京都町田市',
                'description' => '衝突軽減ブレーキ・ETC2.0・前後ドラレコ。',
                'featured' => true,
                'status' => 'available',
                'published_at' => now()->subDays(3),
            ],
            [
                'stock_no' => 'NS3308',
                'make' => '日産',
                'model' => 'セレナ',
                'grade' => 'ハイウェイスター V',
                'body_type' => 'ミニバン',
                'transmission' => 'CVT',
                'fuel_type' => 'ガソリン',
                'model_year' => 2020,
                'mileage' => 45200,
                'price' => 2390000,
                'color' => 'ダイヤモンドブラック',
                'location' => '埼玉県川口市',
                'description' => '8人乗り・両側電動スライドドア。',
                'featured' => false,
                'status' => 'available',
                'published_at' => now()->subDays(2),
            ],
            [
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
                'color' => 'ポリメタルグレー',
                'location' => '千葉県船橋市',
                'description' => '4WD・レーダークルーズ・360度ビュー。',
                'featured' => true,
                'status' => 'available',
                'published_at' => now()->subHours(20),
            ],
            [
                'stock_no' => 'SZ5522',
                'make' => 'スズキ',
                'model' => 'ハスラー',
                'grade' => 'HYBRID X',
                'body_type' => '軽自動車',
                'transmission' => 'CVT',
                'fuel_type' => 'ハイブリッド',
                'model_year' => 2021,
                'mileage' => 28900,
                'price' => 1490000,
                'color' => 'デニムブルーメタリック',
                'location' => '東京都八王子市',
                'description' => '全方位モニター・シートヒーター付き。',
                'featured' => false,
                'status' => 'available',
                'published_at' => now()->subDays(5),
            ],
            [
                'stock_no' => 'DK6019',
                'make' => 'ダイハツ',
                'model' => 'タフト',
                'grade' => 'Gターボ',
                'body_type' => '軽自動車',
                'transmission' => 'CVT',
                'fuel_type' => 'ガソリン',
                'model_year' => 2022,
                'mileage' => 17300,
                'price' => 1580000,
                'color' => 'フォレストカーキ',
                'location' => '神奈川県相模原市',
                'description' => 'ガラスルーフ・スマートアシスト搭載。',
                'featured' => false,
                'status' => 'available',
                'published_at' => now()->subDays(7),
            ],
            [
                'stock_no' => 'TY7781',
                'make' => 'トヨタ',
                'model' => 'アルファード',
                'grade' => '2.5 S Cパッケージ',
                'body_type' => 'ミニバン',
                'transmission' => 'AT',
                'fuel_type' => 'ガソリン',
                'model_year' => 2019,
                'mileage' => 61200,
                'price' => 3980000,
                'color' => 'ブラック',
                'location' => '埼玉県越谷市',
                'description' => '本革シート・後席モニター・サンルーフ。',
                'featured' => true,
                'status' => 'sold',
                'published_at' => now()->subDays(10),
            ],
            [
                'stock_no' => 'SB8894',
                'make' => 'スバル',
                'model' => 'レヴォーグ',
                'grade' => 'STI Sport EX',
                'body_type' => 'ワゴン',
                'transmission' => 'CVT',
                'fuel_type' => 'ガソリン',
                'model_year' => 2022,
                'mileage' => 24100,
                'price' => 3290000,
                'color' => 'WRブルーパール',
                'location' => '東京都江戸川区',
                'description' => 'アイサイトX・前後シートヒーター。',
                'featured' => false,
                'status' => 'reserved',
                'published_at' => now()->subDays(4),
            ],
        ];

        foreach ($cars as $car) {
            // master テーブルに未登録の make/model/grade があれば追加
            $carMake  = CarMake::firstOrCreate(['name' => $car['make']]);
            $carModel = CarModel::firstOrCreate(['make_id' => $carMake->id, 'name' => $car['model']]);
            if (!empty($car['grade'])) {
                CarGrade::firstOrCreate(['model_id' => $carModel->id, 'name' => $car['grade']]);
            }

            Car::query()->updateOrCreate(
                ['stock_no' => $car['stock_no']],
                $car,
            );
        }
    }
}
