<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('stock_no');
        });

        foreach (DB::table('cars')->get(['id', 'make', 'model', 'grade', 'model_year', 'stock_no']) as $car) {
            $parts = array_filter([
                $car->make,
                $car->model,
                $car->grade,
                (string) $car->model_year,
                $car->stock_no,
            ]);
            DB::table('cars')->where('id', $car->id)->update([
                'slug' => Str::slug(implode(' ', $parts)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }
};
