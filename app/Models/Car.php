<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_no',
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
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
