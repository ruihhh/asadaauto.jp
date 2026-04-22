<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get(route('admin.cars.index'))->assertRedirect(route('login'));
        $this->get(route('admin.cars.create'))->assertRedirect(route('login'));
        $this->get(route('admin.cars.export'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.cars.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.cars.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.cars.export'))->assertForbidden();
    }

    public function test_admin_user_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.cars.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cars.create'))->assertOk();
    }

    public function test_non_admin_cannot_create_car(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('admin.cars.store'), [
            'stock_no' => 'TEST001',
            'make' => 'トヨタ',
            'model' => 'プリウス',
        ])->assertForbidden();

        $this->assertDatabaseMissing('cars', ['stock_no' => 'TEST001']);
    }

    public function test_non_admin_cannot_delete_car(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $car = Car::factory()->create();

        $this->actingAs($user)->delete(route('admin.cars.destroy', $car))->assertForbidden();

        $this->assertDatabaseHas('cars', ['id' => $car->id]);
    }
}
