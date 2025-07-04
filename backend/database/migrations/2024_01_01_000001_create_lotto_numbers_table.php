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
        Schema::create('lotto_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('numbers'); // [1, 7, 14, 21, 28, 35] 형태로 저장
            $table->enum('type', ['auto', 'semi', 'manual', 'fortune'])->default('auto');
            $table->string('memo')->nullable(); // 사용자 메모
            $table->boolean('is_winner')->default(false); // 당첨 여부
            $table->unsignedBigInteger('prize_amount')->default(0); // 당첨금액
            $table->unsignedInteger('draw_no')->nullable(); // 회차 번호
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['draw_no']);
            $table->index(['is_winner']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotto_numbers');
    }
};