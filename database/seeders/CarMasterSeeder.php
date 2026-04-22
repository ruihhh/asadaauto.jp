<?php

namespace Database\Seeders;

use App\Models\CarGrade;
use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Database\Seeder;

class CarMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'トヨタ' => [
                'プリウス'      => ['E', 'S', 'S ツーリングセレクション', 'G', 'Z', 'PHV S', 'PHV G'],
                'アクア'        => ['B', 'X', 'G', 'Z', 'GR SPORT'],
                'ヤリス'        => ['X', 'G', 'Z', 'GR SPORT'],
                'ヤリスクロス'  => ['X', 'G', 'Z', 'GR SPORT'],
                'カローラ'      => ['X', 'G', 'W×B', 'GR SPORT'],
                'カローラクロス'=> ['X', 'G', 'Z'],
                'ライズ'        => ['X', 'G', 'Z', 'GR SPORT'],
                'ハリアー'      => ['S', 'G', 'Z', 'Z レザーパッケージ'],
                'RAV4'          => ['X', 'G', 'Adventure', 'PHV G'],
                'アルファード'  => ['X', 'S', 'Z', '2.5 S Cパッケージ', '3.5 Executive Lounge'],
                'ヴェルファイア' => ['Z', 'Z Premier', 'Executive Lounge'],
                'シエンタ'      => ['X', 'G', 'Z'],
                'ノア'          => ['X', 'G', 'Z', 'S-G', 'S-Z'],
                'ヴォクシー'    => ['S-G', 'S-Z'],
                'ランドクルーザー' => ['GX', 'AX', 'VX', 'ZX'],
                'クラウン'      => ['RS', 'RS Advanced', 'G-Executive'],
                'ルーミー'      => ['X', 'G', 'G-T', 'カスタムG', 'カスタムG-T'],
            ],
            'ホンダ' => [
                'フィット'      => ['B', 'HOME', 'LUXE', 'CROSSTAR', 'e:HEV HOME', 'e:HEV LUXE'],
                'ヴェゼル'      => ['X', 'G', 'PLaY', 'e:HEV X', 'e:HEV G', 'e:HEV Z', 'e:HEV PLaY'],
                'ZR-V'          => ['X', 'Z', 'e:HEV X', 'e:HEV Z'],
                'フリード'      => ['B', 'CROSSTAR', 'e:HEV B', 'e:HEV CROSSTAR'],
                'ステップワゴン' => ['AIR', 'SPADA', 'e:HEV AIR', 'e:HEV SPADA'],
                'N-BOX'         => ['G', 'G・L', 'カスタムG', 'カスタムG・L', 'カスタムG EX'],
                'N-ONE'         => ['Original', 'RS'],
                'シビック'      => ['EX', 'type R'],
                'アコード'      => ['EX', 'LX', 'e:HEV EX', 'e:HEV LX'],
            ],
            '日産' => [
                'ノート'        => ['X', 'S', 'AUTECH', 'e-POWER X', 'e-POWER S', 'e-POWER AUTECH'],
                'ルークス'      => ['X', 'G', 'ハイウェイスター X', 'ハイウェイスター G'],
                'セレナ'        => ['X', 'XV', 'ハイウェイスター G', 'ハイウェイスター V', 'AUTECH'],
                'エクストレイル' => ['S', 'X', 'G', 'e-4ORCE X', 'e-4ORCE G'],
                'キックス'      => ['X', 'G', 'e-POWER X', 'e-POWER G'],
                'リーフ'        => ['X', 'G', 'e+', 'NISMO'],
                'アリア'        => ['B6', 'B9', 'e-4ORCE B9'],
                'スカイライン'  => ['GT Type SP', 'GT Type P', '400R'],
            ],
            'マツダ' => [
                'MAZDA2'        => ['15S', '15S Touring', 'XD', 'XD Touring'],
                'MAZDA3'        => ['15S', '20S', 'XD', 'XD Touring', 'Fastback 20S', 'Fastback XD'],
                'CX-3'          => ['15S Touring', 'XD Touring'],
                'CX-30'         => ['20S', 'XD', 'e-SKYACTIV X', 'XD Touring'],
                'CX-5'          => ['25S', 'XD', 'XD Touring', 'XD Black Tone Edition', 'XD L Package'],
                'CX-60'         => ['XD', 'XD Exclusive Mode', 'PHEV', 'e-SKYACTIV D'],
                'CX-80'         => ['XD', 'XD Exclusive Mode', 'PHEV'],
                'ロードスター'  => ['S', 'S Special Package', 'S Leather Package', 'NR-A'],
            ],
            'スバル' => [
                'インプレッサ'  => ['G4 2.0i', 'G4 2.0i-L', 'SPORT 1.8i-L', 'SPORT 1.8i-S'],
                'クロストレック' => ['X-BREAK e-BOXER', 'Limited e-BOXER'],
                'レヴォーグ'    => ['1.8STI Sport', 'STI Sport R', 'STI Sport EX', 'GT-H EX'],
                'フォレスター'  => ['X-BREAK', 'Advance', 'SPORT', 'e-BOXER X-BREAK'],
                'アウトバック'  => ['Touring', 'Limited', 'X-BREAK'],
                'BRZ'           => ['S', 'R', 'STI Sport'],
                'WRX S4'        => ['GT-H', 'STI Sport R', 'STI Sport R EX'],
            ],
            'スズキ' => [
                'アルト'        => ['F', 'L', 'X'],
                'ワゴンR'       => ['FA', 'FX', 'FZ', 'HYBRID FX', 'HYBRID FZ'],
                'スペーシア'    => ['HYBRID X', 'HYBRID G', 'カスタム HYBRID XS', 'カスタム HYBRID GS'],
                'ハスラー'      => ['HYBRID G', 'HYBRID X', 'HYBRID G Turbo', 'HYBRID X Turbo'],
                'ジムニー'      => ['XC', 'XL', 'XG'],
                'ジムニーシエラ' => ['JC', 'JL'],
                'スイフト'      => ['XG', 'XL', 'RS', 'SPORT'],
                'ソリオ'        => ['G', 'S', 'HYBRID MX', 'HYBRID SX'],
            ],
            'ダイハツ' => [
                'ミライース'    => ['B', 'X', 'G'],
                'ムーヴ'        => ['L', 'X', 'カスタム X', 'カスタム RS'],
                'タント'        => ['L', 'X', 'カスタム X', 'カスタム RS'],
                'タフト'        => ['G', 'Gターボ', 'X', 'Xターボ'],
                'ロッキー'      => ['L', 'X', 'G', 'Premium G', 'e-SMART HYBRID G'],
                'ハイゼットカーゴ' => ['スペシャル', 'クルーズ', 'デッキバン'],
                'アトレー'      => ['RS', 'G'],
            ],
            'レクサス' => [
                'UX'  => ['200 version C', '250h version C', '250h F SPORT'],
                'NX'  => ['250 version L', '350h version L', '350h F SPORT', '450h+ version L'],
                'RX'  => ['350 version L', '500h F SPORT', '450h+ version L'],
                'LX'  => ['600 base', '600 F SPORT'],
                'IS'  => ['250 version L', '300h version L', 'IS 500 F SPORT Performance'],
                'ES'  => ['250 version L', '300h version L', 'ES 350 version L'],
                'LS'  => ['500 version L', '500h version L', '500h Executive'],
            ],
            '三菱' => [
                'eKクロス'      => ['M', 'G', 'T'],
                'eKクロス EV'   => ['M', 'P'],
                'アウトランダー' => ['G', 'P', 'PHEV G', 'PHEV P'],
                'エクリプスクロス' => ['G', 'P', 'PHEV G', 'PHEV P'],
                'デリカD:5'     => ['G Power Package', 'D Power Package', 'Urban Gear G'],
            ],
        ];

        foreach ($data as $makeName => $models) {
            $make = CarMake::firstOrCreate(['name' => $makeName]);

            foreach ($models as $modelName => $grades) {
                $model = CarModel::firstOrCreate([
                    'make_id' => $make->id,
                    'name'    => $modelName,
                ]);

                foreach ($grades as $gradeName) {
                    CarGrade::firstOrCreate([
                        'model_id' => $model->id,
                        'name'     => $gradeName,
                    ]);
                }
            }
        }
    }
}
