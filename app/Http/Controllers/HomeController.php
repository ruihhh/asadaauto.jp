<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totalPublic = Car::publicInventory()->count();

        $newArrivals = Car::publicInventory()
            ->with('images')
            ->latest('published_at')
            ->latest('id')
            ->limit(10)
            ->get();

        $featuredCars = Car::publicInventory()
            ->with('images')
            ->where('featured', true)
            ->latest('published_at')
            ->limit(5)
            ->get();

        // ボディタイプ別台数
        $bodyTypeCounts = Car::publicInventory()
            ->selectRaw('body_type, count(*) as cnt')
            ->groupBy('body_type')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'body_type');

        // ランキング（閲覧数の代わりに新着順でジャンル別）
        $rankingTypes = ['軽自動車', 'コンパクトカー', 'ミニバン', 'SUV', 'セダン', 'スポーツ'];
        $rankings = [];
        foreach ($rankingTypes as $type) {
            $rankings[$type] = Car::publicInventory()
                ->with('images')
                ->where('body_type', 'like', "%{$type}%")
                ->latest('published_at')
                ->limit(6)
                ->get();
        }
        // ALL ランキング
        $rankings['ALL'] = Car::publicInventory()
            ->with('images')
            ->latest('published_at')
            ->limit(6)
            ->get();

        // メーカー一覧（ショートカット用）
        $makes = Car::publicInventory()
            ->select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        // ボディタイプ一覧
        $bodyTypes = Car::publicInventory()
            ->select('body_type')
            ->distinct()
            ->orderBy('body_type')
            ->pluck('body_type');

        // おすすめ特集カテゴリ
        $specials = [
            'SUV・四駆' => Car::publicInventory()->with('images')
                ->where(fn ($q) => $q->where('body_type', 'like', '%SUV%')->orWhere('body_type', 'like', '%クロカン%'))
                ->latest('published_at')->limit(5)->get(),
            'ミニバン' => Car::publicInventory()->with('images')
                ->where('body_type', 'like', '%ミニバン%')
                ->latest('published_at')->limit(5)->get(),
            'スポーツ・クーペ' => Car::publicInventory()->with('images')
                ->where(fn ($q) => $q->where('body_type', 'like', '%スポーツ%')->orWhere('body_type', 'like', '%クーペ%'))
                ->latest('published_at')->limit(5)->get(),
        ];

        return view('home', compact(
            'totalPublic',
            'newArrivals',
            'featuredCars',
            'bodyTypeCounts',
            'rankings',
            'rankingTypes',
            'makes',
            'bodyTypes',
            'specials',
        ));
    }
}
