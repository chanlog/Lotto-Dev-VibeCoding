<?php

namespace Database\Factories;

use App\Models\FortuneAnalysis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FortuneAnalysis>
 */
class FortuneAnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $wealthLuck = fake()->numberBetween(30, 95);
        $generalLuck = fake()->numberBetween(30, 95);
        
        // 행운 번호 생성 (3-6개)
        $luckyNumbers = [];
        $count = fake()->numberBetween(3, 6);
        while (count($luckyNumbers) < $count) {
            $number = fake()->numberBetween(1, 45);
            if (!in_array($number, $luckyNumbers)) {
                $luckyNumbers[] = $number;
            }
        }
        sort($luckyNumbers);

        $luckyColors = fake()->randomElements([
            '빨강', '파랑', '노랑', '초록', '보라', '분홍', 
            '주황', '하늘색', '금색', '은색', '검정', '흰색'
        ], fake()->numberBetween(1, 3));

        return [
            'user_id' => User::factory(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'birth_time' => fake()->time(),
            'gender' => fake()->randomElement(['male', 'female']),
            'wealth_luck' => $wealthLuck,
            'general_luck' => $generalLuck,
            'lucky_numbers' => $luckyNumbers,
            'lucky_colors' => $luckyColors,
            'analysis_summary' => FortuneAnalysis::generateAnalysisSummary($wealthLuck, $generalLuck, $luckyNumbers),
            'today_fortune' => fake()->sentence(20),
            'analysis_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * 높은 운세
     */
    public function highLuck(): static
    {
        return $this->state(fn (array $attributes) => [
            'wealth_luck' => fake()->numberBetween(80, 100),
            'general_luck' => fake()->numberBetween(80, 100),
        ]);
    }

    /**
     * 낮은 운세
     */
    public function lowLuck(): static
    {
        return $this->state(fn (array $attributes) => [
            'wealth_luck' => fake()->numberBetween(20, 40),
            'general_luck' => fake()->numberBetween(20, 40),
        ]);
    }

    /**
     * 오늘 분석
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'analysis_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * 특정 행운 번호로 생성
     */
    public function withLuckyNumbers(array $numbers): static
    {
        return $this->state(fn (array $attributes) => [
            'lucky_numbers' => $numbers,
        ]);
    }

    /**
     * 남성
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'male',
        ]);
    }

    /**
     * 여성
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'female',
        ]);
    }
}