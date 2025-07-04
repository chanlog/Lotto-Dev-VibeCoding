<?php

namespace Database\Factories;

use App\Models\LottoNumber;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LottoNumber>
 */
class LottoNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1-45 범위에서 중복없이 6개 번호 생성
        $numbers = [];
        while (count($numbers) < 6) {
            $number = fake()->numberBetween(1, 45);
            if (!in_array($number, $numbers)) {
                $numbers[] = $number;
            }
        }
        sort($numbers);

        return [
            'user_id' => User::factory(),
            'numbers' => $numbers,
            'type' => fake()->randomElement(['auto', 'semi', 'manual', 'fortune']),
            'memo' => fake()->optional()->sentence(),
            'is_winner' => fake()->boolean(5), // 5% 확률로 당첨
            'prize_amount' => fake()->boolean(5) ? fake()->numberBetween(5000, 2000000000) : 0,
            'draw_no' => fake()->optional()->numberBetween(1000, 1200),
        ];
    }

    /**
     * 당첨 번호 상태
     */
    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_winner' => true,
            'prize_amount' => fake()->numberBetween(5000, 2000000000),
        ]);
    }

    /**
     * 특정 타입
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * 특정 번호로 생성
     */
    public function withNumbers(array $numbers): static
    {
        return $this->state(fn (array $attributes) => [
            'numbers' => $numbers,
        ]);
    }
}