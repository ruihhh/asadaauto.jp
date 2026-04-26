<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_no',
        'slug',
        'make',
        'model',
        'grade',
        'body_type',
        'transmission',
        'fuel_type',
        'model_year',
        'mileage',
        'price',
        'base_price',
        'price_negotiable',
        'color',
        'location',
        'description',
        'image_path',
        'featured',
        'status',
        'published_at',
        'accident_count',
        'has_service_record',
        'inspection_expiry',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $car): void {
            if (empty($car->slug)) {
                $car->slug = static::generateSlug($car);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function generateSlug(self $car): string
    {
        $makeRomaji = static::makeToRomaji($car->make);

        // ASCII のみ残す（日本語モデル名は除去される）
        $modelPart = Str::slug(($car->model ?? '') . ' ' . ($car->grade ?? ''));

        $parts = array_filter([
            $makeRomaji,
            $modelPart ?: null,
            (string) $car->model_year,
            strtolower($car->stock_no),
        ]);

        return implode('-', $parts);
    }

    private static function makeToRomaji(string $make): string
    {
        $map = [
            'トヨタ'   => 'toyota',
            'ホンダ'   => 'honda',
            '日産'     => 'nissan',
            'マツダ'   => 'mazda',
            'スズキ'   => 'suzuki',
            'ダイハツ' => 'daihatsu',
            'スバル'   => 'subaru',
            '三菱'     => 'mitsubishi',
            'レクサス' => 'lexus',
            'いすゞ'   => 'isuzu',
            'ヤマハ'   => 'yamaha',
        ];

        return $map[$make] ?? Str::slug($make) ?: 'car';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'price_negotiable' => 'boolean',
            'has_service_record' => 'boolean',
            'published_at' => 'datetime',
            'inspection_expiry' => 'date',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class)->orderBy('sort_order');
    }

    public function scopePublicInventory(Builder $query): Builder
    {
        return $query
            ->where('status', 'available')
            ->where(function (Builder $inner): void {
                $inner->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
