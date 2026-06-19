<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            // 車検区分（3年付 / 2年付 / 1年付 / なし）。実日付の inspection_expiry とは別に保持する
            $table->string('inspection_type', 16)->nullable()->after('inspection_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn('inspection_type');
        });
    }
};
