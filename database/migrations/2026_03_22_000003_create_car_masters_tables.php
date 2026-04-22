<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_makes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 64)->unique();
            $table->timestamps();
        });

        Schema::create('car_models', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('make_id')->constrained('car_makes')->cascadeOnDelete();
            $table->string('name', 64);
            $table->unique(['make_id', 'name']);
            $table->timestamps();
        });

        Schema::create('car_grades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('model_id')->constrained('car_models')->cascadeOnDelete();
            $table->string('name', 64);
            $table->unique(['model_id', 'name']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_grades');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('car_makes');
    }
};
