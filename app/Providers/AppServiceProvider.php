<?php

namespace App\Providers;

use App\Models\Car;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** フッターの「ボディタイプから探す」のキャッシュキー */
    public const FOOTER_BODY_TYPES_CACHE_KEY = 'footer.body_types';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // フッターの「ボディタイプから探す」：公開在庫のボディタイプごとの台数（在庫がある種類だけ、多い順）
        View::composer('components.site.footer', function ($view): void {
            $view->with('footerBodyTypes', Cache::remember(self::FOOTER_BODY_TYPES_CACHE_KEY, 600, fn (): array => Car::query()
                ->publicInventory()
                ->whereNotNull('body_type')
                ->where('body_type', '!=', '')
                ->selectRaw('body_type, count(*) as cnt')
                ->groupBy('body_type')
                ->orderByDesc('cnt')
                ->orderBy('body_type')
                ->pluck('cnt', 'body_type')
                ->map(fn ($count): int => (int) $count)
                ->all()));
        });

        // 管理画面で車両を更新したら、フッターの台数をすぐ作り直す
        $forgetFooterBodyTypes = fn () => Cache::forget(self::FOOTER_BODY_TYPES_CACHE_KEY);
        Car::saved($forgetFooterBodyTypes);
        Car::deleted($forgetFooterBodyTypes);
    }
}
