<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request): View
    {
        $carsQuery = Car::query()->publicInventory();

        $keyword = trim((string) $request->query('q', ''));
        if ($keyword !== '') {
            $carsQuery->where(function (Builder $query) use ($keyword): void {
                $query->where('make', 'like', "%{$keyword}%")
                    ->orWhere('model', 'like', "%{$keyword}%")
                    ->orWhere('grade', 'like', "%{$keyword}%")
                    ->orWhere('stock_no', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('make')) {
            $carsQuery->where('make', (string) $request->query('make'));
        }

        if ($request->filled('body_type')) {
            $carsQuery->where('body_type', (string) $request->query('body_type'));
        }

        if ($request->filled('min_price')) {
            $carsQuery->where('price', '>=', max(0, (int) $request->query('min_price')));
        }

        if ($request->filled('max_price')) {
            $carsQuery->where('price', '<=', max(0, (int) $request->query('max_price')));
        }

        if ($request->filled('max_mileage')) {
            $carsQuery->where('mileage', '<=', max(0, (int) $request->query('max_mileage')));
        }

        $sort = (string) $request->query('sort', 'latest');
        match ($sort) {
            'price_asc' => $carsQuery->orderBy('price'),
            'price_desc' => $carsQuery->orderByDesc('price'),
            'mileage_asc' => $carsQuery->orderBy('mileage'),
            'year_desc' => $carsQuery->orderByDesc('model_year'),
            default => $carsQuery->latest('published_at')->latest('id'),
        };

        $cars = $carsQuery
            ->paginate(12)
            ->withQueryString();

        $publicInventory = Car::query()->publicInventory();

        // sort=latest が URL に明示されているときだけ「新着車両」ページと判定
        $isLatest = $request->query('sort') === 'latest';

        return view('cars.index', [
            'cars' => $cars,
            'isLatest' => $isLatest,
            'filters' => [
                'q' => $keyword,
                'make' => (string) $request->query('make', ''),
                'body_type' => (string) $request->query('body_type', ''),
                'min_price' => (string) $request->query('min_price', ''),
                'max_price' => (string) $request->query('max_price', ''),
                'max_mileage' => (string) $request->query('max_mileage', ''),
                'sort' => $sort,
            ],
            'makes' => (clone $publicInventory)
                ->select('make')
                ->distinct()
                ->orderBy('make')
                ->pluck('make'),
            'bodyTypes' => (clone $publicInventory)
                ->select('body_type')
                ->distinct()
                ->orderBy('body_type')
                ->pluck('body_type'),
        ]);
    }

    public function show(Car $car): View
    {
        abort_unless($car->status === 'available' || auth()->check(), 404);

        $car->load('images');

        $relatedCars = Car::query()
            ->publicInventory()
            ->where('make', $car->make)
            ->where('id', '!=', $car->id)
            ->with('images')
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('cars.show', compact('car', 'relatedCars'));
    }

    public function compare(Request $request): View
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));
        $ids = array_slice($ids, 0, 3);

        $cars = Car::query()
            ->publicInventory()
            ->with('images')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        // Preserve order
        $orderedCars = collect($ids)->map(fn ($id) => $cars->get($id))->filter();

        return view('cars.compare', ['cars' => $orderedCars]);
    }

    public function favorites(Request $request): View
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));

        $cars = $ids
            ? Car::query()->publicInventory()->with('images')->whereIn('id', $ids)->get()
            : collect();

        return view('cars.favorites', compact('cars'));
    }
}
