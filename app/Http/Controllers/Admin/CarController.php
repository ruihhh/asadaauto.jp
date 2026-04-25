<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarGrade;
use App\Models\CarImage;
use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request): View
    {
        $query = Car::latest();

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($inner) use ($q): void {
                $inner->where('stock_no', 'like', "%{$q}%")
                    ->orWhere('make', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('grade', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('make')) {
            $query->where('make', $request->query('make'));
        }

        $cars = $query->paginate(20)->withQueryString();
        $makes = Car::select('make')->distinct()->orderBy('make')->pluck('make');
        $statusCounts = Car::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

        return view('admin.cars.index', compact('cars', 'makes', 'statusCounts'));
    }

    public function create(): View
    {
        return view('admin.cars.create', ['masterData' => $this->masterData()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_no' => 'required|string|max:32|unique:cars',
            'make' => 'required|string|max:64',
            'model' => 'required|string|max:64',
            'grade' => 'nullable|string|max:64',
            'body_type' => 'required|string|max:32',
            'transmission' => 'required|string|max:16',
            'fuel_type' => 'required|string|max:24',
            'model_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'required|integer|min:0',
            'price' => 'required|integer|min:0|max:9999999999',
            'color' => 'nullable|string|max:32',
            'location' => 'nullable|string|max:64',
            'description' => 'nullable|string',
            'accident_count' => 'integer|min:0|max:99',
            'has_service_record' => 'boolean',
            'inspection_expiry' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'featured' => 'boolean',
            'status' => 'required|string|in:available,sold,reserved',
            'published_at' => 'nullable|date',
        ]);

        $validated['featured'] = $request->has('featured');
        $validated['has_service_record'] = $request->has('has_service_record');
        $validated['accident_count'] = (int) ($request->input('accident_count', 0));

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('cars', 'public');
        }

        $this->syncMaster($validated['make'], $validated['model'], $validated['grade'] ?? null);

        $car = Car::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('cars', 'public');
                $car->images()->create(['path' => $path, 'sort_order' => $i]);
            }
        }

        return redirect()->route('admin.cars.index')->with('success', '車両を登録しました。');
    }

    public function edit(Car $car): View
    {
        $car->load('images');
        return view('admin.cars.edit', ['car' => $car, 'masterData' => $this->masterData()]);
    }

    public function update(Request $request, Car $car): RedirectResponse
    {
        $validated = $request->validate([
            'stock_no' => 'required|string|max:32|unique:cars,stock_no,' . $car->id,
            'make' => 'required|string|max:64',
            'model' => 'required|string|max:64',
            'grade' => 'nullable|string|max:64',
            'body_type' => 'required|string|max:32',
            'transmission' => 'required|string|max:16',
            'fuel_type' => 'required|string|max:24',
            'model_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'required|integer|min:0',
            'price' => 'required|integer|min:0|max:9999999999',
            'color' => 'nullable|string|max:32',
            'location' => 'nullable|string|max:64',
            'description' => 'nullable|string',
            'accident_count' => 'integer|min:0|max:99',
            'has_service_record' => 'boolean',
            'inspection_expiry' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'featured' => 'boolean',
            'status' => 'required|string|in:available,sold,reserved',
            'published_at' => 'nullable|date',
        ]);

        $validated['featured'] = $request->has('featured');
        $validated['has_service_record'] = $request->has('has_service_record');
        $validated['accident_count'] = (int) ($request->input('accident_count', 0));

        if ($request->hasFile('image')) {
            if ($car->image_path) {
                Storage::disk('public')->delete($car->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('cars', 'public');
        }

        $this->syncMaster($validated['make'], $validated['model'], $validated['grade'] ?? null);

        $car->update($validated);

        if ($request->hasFile('images')) {
            $nextOrder = $car->images()->max('sort_order') + 1;
            foreach ($request->file('images') as $i => $file) {
                $path = $file->store('cars', 'public');
                $car->images()->create(['path' => $path, 'sort_order' => $nextOrder + $i]);
            }
        }

        return redirect()->route('admin.cars.edit', $car)->with('success', '車両情報を更新しました。');
    }

    public function destroy(Car $car): RedirectResponse
    {
        if ($car->image_path) {
            Storage::disk('public')->delete($car->image_path);
        }
        foreach ($car->images as $img) {
            Storage::disk('public')->delete($img->path);
        }
        $car->delete();
        return redirect()->route('admin.cars.index')->with('success', '車両を削除しました。');
    }

    public function toggleFeatured(Car $car): \Illuminate\Http\JsonResponse
    {
        $car->update(['featured' => !$car->featured]);
        return response()->json(['featured' => $car->featured]);
    }

    public function updateStatus(Request $request, Car $car): \Illuminate\Http\JsonResponse
    {
        $request->validate(['status' => 'required|in:available,reserved,sold']);
        $car->update(['status' => $request->status]);
        return response()->json(['status' => $car->status]);
    }

    public function imageDestroy(Car $car, CarImage $image): RedirectResponse
    {
        abort_unless($image->car_id === $car->id, 404);
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return redirect()->route('admin.cars.edit', $car)->with('success', '画像を削除しました。');
    }

    private function masterData(): array
    {
        $makes = CarMake::orderBy('name')->pluck('name');

        $models = CarModel::with('make')
            ->orderBy('name')
            ->get()
            ->groupBy(fn(CarModel $m) => $m->make->name)
            ->map(fn($ms) => $ms->pluck('name')->values());

        $grades = CarGrade::with('carModel.make')
            ->orderBy('name')
            ->get()
            ->groupBy(fn(CarGrade $g) => $g->carModel->make->name . '@@' . $g->carModel->name)
            ->map(fn($gs) => $gs->pluck('name')->values());

        return compact('makes', 'models', 'grades');
    }

    private function syncMaster(string $make, string $model, ?string $grade): void
    {
        $carMake  = CarMake::firstOrCreate(['name' => $make]);
        $carModel = CarModel::firstOrCreate(['make_id' => $carMake->id, 'name' => $model]);

        if ($grade) {
            CarGrade::firstOrCreate(['model_id' => $carModel->id, 'name' => $grade]);
        }
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $cars = Car::orderBy('id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cars_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($cars): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, [
                '在庫番号', 'メーカー', 'モデル', 'グレード', 'ボディタイプ',
                'トランスミッション', '燃料', '年式', '走行距離(km)', '価格(円)',
                '車体色', '保管場所', 'ステータス', '注目', '公開日時', '登録日時',
                '事故回数', '整備記録', '車検期限',
            ]);

            foreach ($cars as $car) {
                fputcsv($stream, [
                    $car->stock_no,
                    $car->make,
                    $car->model,
                    $car->grade,
                    $car->body_type,
                    $car->transmission,
                    $car->fuel_type,
                    $car->model_year,
                    $car->mileage,
                    $car->price,
                    $car->color,
                    $car->location,
                    $car->status,
                    $car->featured ? '1' : '0',
                    $car->published_at?->format('Y-m-d H:i:s'),
                    $car->created_at->format('Y-m-d H:i:s'),
                    $car->accident_count,
                    $car->has_service_record ? 'あり' : 'なし',
                    $car->inspection_expiry?->format('Y-m-d'),
                ]);
            }

            fclose($stream);
        }, 200, $headers);
    }
}
