<?php

namespace Tests\Feature;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_only_public_inventory(): void
    {
        Car::factory()->create([
            'make' => 'トヨタ',
            'model' => 'プリウス',
            'status' => 'available',
            'published_at' => now()->subDay(),
        ]);

        Car::factory()->create([
            'make' => 'トヨタ',
            'model' => 'アルファード',
            'status' => 'sold',
            'published_at' => now()->subDay(),
        ]);

        Car::factory()->create([
            'make' => 'ホンダ',
            'model' => 'ヴェゼル',
            'status' => 'available',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('トヨタ プリウス');
        $response->assertDontSee('トヨタ アルファード');
        $response->assertDontSee('ホンダ ヴェゼル');
    }

    public function test_inventory_can_be_filtered_by_make_and_price(): void
    {
        Car::factory()->create([
            'make' => 'トヨタ',
            'model' => 'ヤリス',
            'price' => 1400000,
            'status' => 'available',
            'published_at' => now()->subHour(),
        ]);

        Car::factory()->create([
            'make' => 'トヨタ',
            'model' => 'ハリアー',
            'price' => 3800000,
            'status' => 'available',
            'published_at' => now()->subHour(),
        ]);

        Car::factory()->create([
            'make' => '日産',
            'model' => 'ノート',
            'price' => 1200000,
            'status' => 'available',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get('/cars?make=トヨタ&max_price=2000000');

        $response->assertOk();
        $response->assertSee('トヨタ ヤリス');
        $response->assertDontSee('トヨタ ハリアー');
        $response->assertDontSee('日産 ノート');
    }
}
