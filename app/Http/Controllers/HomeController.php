<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** トップの「いま掲載中の車」に出す台数 */
    private const STOCK_LIMIT = 6;

    /** 「使い方から選ぶ」の［予算を抑えたい］の上限（支払総額・円） */
    public const BUDGET_LIMIT = 1000000;

    public function index(): View
    {
        $totalPublic = Car::publicInventory()->count();

        // いま掲載中の車（新しい順）
        $newArrivals = Car::publicInventory()
            ->with('images')
            ->latest('published_at')
            ->latest('id')
            ->limit(self::STOCK_LIMIT)
            ->get();

        // ボディタイプ別の台数（条件から探す・使い方から選ぶ）
        $bodyTypeCounts = Car::publicInventory()
            ->selectRaw('body_type, count(*) as cnt')
            ->groupBy('body_type')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'body_type');

        // メーカーの選択肢（条件から探す）
        $makes = Car::publicInventory()
            ->select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        // 使い方から選ぶ［予算を抑えたい］：支払総額が上限以下の台数（応談・価格未入力の車は数えない）
        $budgetLimit = self::BUDGET_LIMIT;
        $budgetCount = Car::publicInventory()
            ->where('price_negotiable', false)
            ->whereNotNull('price')
            ->where('price', '<=', $budgetLimit)
            ->count();

        return view('home', compact(
            'totalPublic',
            'newArrivals',
            'bodyTypeCounts',
            'makes',
            'budgetLimit',
            'budgetCount',
        ));
    }
}
