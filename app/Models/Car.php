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
        'equipment',
    ];

    public const EQUIPMENT_CATEGORIES = [
        '安全装備' => [
            'ABS', 'ESC（横滑り防止）', '衝突被害軽減ブレーキ', 'アダプティブクルーズコントロール',
            'レーンキープアシスト', 'パーキングアシスト', 'バックカメラ', '全周囲カメラ',
            '障害物センサー', 'アクセル踏み間違い防止装置', 'オートマチックハイビーム',
            'エアバッグ（運転席）', 'エアバッグ（助手席）', 'エアバッグ（サイド/カーテン）',
            'アイドリングストップ', '盗難防止装置',
        ],
        '快適装備' => [
            'エアコン', 'Wエアコン', 'メモリーナビ', 'フルセグTV', 'DVD再生',
            'Apple CarPlay', 'Android Auto', 'ETC', 'ETC2.0', 'ドライブレコーダー',
            '1500W電源', '寒冷地仕様', 'ミュージックプレイヤー接続可',
        ],
        'インテリア' => [
            'スマートキー', 'パワーウインドウ', '本革シート', 'シートヒーター', 'シートエアコン',
            '電動シート', 'オットマン', '後席モニター', '3列シート', 'ベンチシート',
            'ウォークスルー', 'フルフラットシート',
        ],
        'エクステリア' => [
            'アルミホイール', 'フロントフォグランプ', 'サンルーフ', 'ルーフレール', 'フルエアロ',
            '両側電動スライドドア', '片側電動スライドドア', 'パワーバックドア', 'ローダウン',
        ],
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
            'equipment' => 'array',
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
