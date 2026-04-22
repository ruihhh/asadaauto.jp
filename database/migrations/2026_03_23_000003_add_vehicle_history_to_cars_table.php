<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->unsignedTinyInteger('accident_count')->default(0)->after('description');
            $table->boolean('has_service_record')->default(false)->after('accident_count');
            $table->date('inspection_expiry')->nullable()->after('has_service_record');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn(['accident_count', 'has_service_record', 'inspection_expiry']);
        });
    }
};
