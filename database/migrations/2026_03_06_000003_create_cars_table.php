<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table): void {
            $table->id();
            $table->string('stock_no', 32)->unique();
            $table->string('make', 64)->index();
            $table->string('model', 64)->index();
            $table->string('grade', 64)->nullable();
            $table->string('body_type', 32)->index();
            $table->string('transmission', 16);
            $table->string('fuel_type', 24);
            $table->unsignedSmallInteger('model_year')->index();
            $table->unsignedInteger('mileage')->index();
            $table->unsignedInteger('price')->index();
            $table->string('color', 32)->nullable();
            $table->string('location', 64)->nullable();
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status', 24)->default('available')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
