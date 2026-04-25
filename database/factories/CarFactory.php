<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $makes = [
            'トヨタ' => ['プリウス', 'アクア', 'ヤリス'],
            'ホンダ' => ['フィット', 'ヴェゼル', 'フリード'],
            '日産' => ['ノート', 'セレナ', 'エクストレイル'],
            'マツダ' => ['CX-5', 'MAZDA3', 'デミオ'],
        ];

        $make = fake()->randomKey($makes);

        return [
            'stock_no' => strtoupper(fake()->bothify('AA####')),
            'make' => $make,
            'model' => fake()->randomElement($makes[$make]),
            'grade' => fake()->randomElement(['X', 'G', 'Z', 'S', 'Lパッケージ']),
            'body_type' => fake()->randomElement(['軽自動車', 'コンパクト', 'ミニバン', 'SUV', 'セダン']),
            'transmission' => fake()->randomElement(['AT', 'CVT', 'MT']),
            'fuel_type' => fake()->randomElement(['ガソリン', 'ハイブリッド', 'ディーゼル']),
            'model_year' => fake()->numberBetween(2016, 2025),
            'mileage' => fake()->numberBetween(5000, 120000),
            'price' => fake()->numberBetween(700000, 4000000),
            'base_price' => fake()->optional(0.8)->numberBetween(600000, 3500000),
            'color' => fake()->randomElement(['パール', 'ブラック', 'シルバー', 'ブルー', 'レッド']),
            'location' => fake()->randomElement(['東京都', '神奈川県', '千葉県', '埼玉県']),
            'description' => fake()->optional()->realText(80),
            'featured' => fake()->boolean(25),
            'status' => fake()->randomElement(['available', 'available', 'available', 'reserved', 'sold']),
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }
}
