<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->boolean('price_negotiable')->default(false)->after('base_price');
            $table->unsignedBigInteger('price')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn('price_negotiable');
            $table->unsignedBigInteger('price')->nullable(false)->change();
        });
    }
};
