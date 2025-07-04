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
        Schema::create('fortune_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('birth_date'); // 생년월일
            $table->time('birth_time')->nullable(); // 태어난 시간
            $table->enum('gender', ['male', 'female']); // 성별
            $table->tinyInteger('wealth_luck')->unsigned(); // 재물운 (0-100)
            $table->tinyInteger('general_luck')->unsigned(); // 전체운 (0-100)
            $table->json('lucky_numbers')->nullable(); // 행운 번호 배열
            $table->json('lucky_colors')->nullable(); // 행운 색상 배열
            $table->text('analysis_summary'); // 사주 분석 요약
            $table->text('today_fortune')->nullable(); // 오늘의 운세
            $table->date('analysis_date')->default(now()); // 분석 날짜
            $table->timestamps();

            $table->index(['user_id', 'analysis_date']);
            $table->index(['analysis_date']);
            $table->index(['wealth_luck']);
            $table->index(['general_luck']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fortune_analyses');
    }
};