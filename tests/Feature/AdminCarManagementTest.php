<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminCarManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $imagesRoot;

    protected function setUp(): void
    {
        parent::setUp();

        // CarController は Storage ファサードではなく imagesRoot() 配下の実ファイルを
        // 直接操作する（cars/{carId}/{filename})。テスト用に一時ディレクトリへ向ける。
        $this->imagesRoot = sys_get_temp_dir() . '/asada_test_images_' . uniqid();
        config(['filesystems.images_root' => $this->imagesRoot]);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->imagesRoot);

        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (array_diff(scandir($dir), ['.', '..']) as $entry) {
            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->removeDir($path) : @unlink($path);
        }

        @rmdir($dir);
    }

    private function placeImage(int $carId, string $filename): string
    {
        $path = 'cars/' . $carId . '/' . $filename;
        $full = $this->imagesRoot . '/' . $path;
        @mkdir(dirname($full), 0775, true);
        file_put_contents($full, 'dummy');

        return $path;
    }

    private function adminUser(): User
    {
        return User::factory()->admin()->create();
    }

    private function carData(array $overrides = []): array
    {
        return array_merge([
            'stock_no' => 'TEST001',
            'make' => 'トヨタ',
            'model' => 'プリウス',
            'grade' => 'Z',
            'body_type' => 'セダン',
            'transmission' => 'CVT',
            'fuel_type' => 'ハイブリッド',
            'model_year' => 2022,
            'mileage' => 30000,
            'price' => 2500000,
            'status' => 'available',
        ], $overrides);
    }

    public function test_admin_can_view_car_list(): void
    {
        $admin = $this->adminUser();
        Car::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.cars.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_car(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.cars.store'), $this->carData());

        $response->assertRedirect(route('admin.cars.index'));
        $this->assertDatabaseHas('cars', ['stock_no' => 'TEST001', 'make' => 'トヨタ']);
    }

    public function test_admin_can_upload_image_when_creating_car(): void
    {
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->image('car.jpg', 800, 600);

        $this->actingAs($admin)->post(route('admin.cars.store'), array_merge(
            $this->carData(),
            ['image' => $file]
        ));

        $car = Car::where('stock_no', 'TEST001')->first();
        $this->assertNotNull($car->image_path);
        $this->assertStringStartsWith('cars/' . $car->id . '/', $car->image_path);
        $this->assertFileExists($this->imagesRoot . '/' . $car->image_path);
    }

    public function test_admin_can_update_car(): void
    {
        $admin = $this->adminUser();
        $car = Car::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->patch(
            route('admin.cars.update', $car),
            $this->carData(['stock_no' => $car->stock_no, 'status' => 'sold'])
        );

        $response->assertRedirect(route('admin.cars.edit', $car));
        $this->assertDatabaseHas('cars', ['id' => $car->id, 'status' => 'sold']);
    }

    public function test_old_image_is_deleted_when_new_image_is_uploaded(): void
    {
        $admin = $this->adminUser();

        $car = Car::factory()->create();
        $oldPath = $this->placeImage($car->id, 'old.jpg');
        $car->update(['image_path' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');
        $this->actingAs($admin)->patch(
            route('admin.cars.update', $car),
            $this->carData(['stock_no' => $car->stock_no, 'image' => $newFile])
        );

        $this->assertFileDoesNotExist($this->imagesRoot . '/' . $oldPath);
        $this->assertNotEquals($oldPath, $car->fresh()->image_path);
    }

    public function test_image_is_deleted_when_car_is_destroyed(): void
    {
        $admin = $this->adminUser();

        $car = Car::factory()->create();
        $imagePath = $this->placeImage($car->id, 'car.jpg');
        $car->update(['image_path' => $imagePath]);

        $this->actingAs($admin)->delete(route('admin.cars.destroy', $car));

        $this->assertFileDoesNotExist($this->imagesRoot . '/' . $imagePath);
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }

    public function test_admin_can_delete_car(): void
    {
        $admin = $this->adminUser();
        $car = Car::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.cars.destroy', $car));

        $response->assertRedirect(route('admin.cars.index'));
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }

    public function test_car_creation_requires_required_fields(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.cars.store'), []);

        $response->assertSessionHasErrors(['stock_no', 'make', 'model', 'body_type', 'transmission', 'fuel_type', 'model_year', 'mileage', 'price', 'status']);
    }

    public function test_admin_can_export_csv(): void
    {
        $admin = $this->adminUser();
        Car::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.cars.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
