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
        Schema::create('appraisal_requests', function (Blueprint $table) {
            $table->id();
            // 車両情報
            $table->string('make', 100)->comment('メーカー');
            $table->string('model', 100)->comment('車名');
            $table->string('grade', 100)->nullable()->comment('グレード');
            $table->smallInteger('model_year')->comment('年式');
            $table->unsignedInteger('mileage')->comment('走行距離(km)');
            $table->string('color', 60)->nullable()->comment('色');
            $table->enum('condition', ['good', 'normal', 'damaged'])->default('normal')->comment('車両状態');
            // 申込者情報
            $table->string('name', 100)->comment('お名前');
            $table->string('email', 255)->comment('メールアドレス');
            $table->string('phone', 20)->comment('電話番号');
            $table->string('zip', 10)->nullable()->comment('郵便番号');
            $table->text('message')->nullable()->comment('備考');
            // 管理用
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_requests');
    }
};
