<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LottoNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numbers',
        'type',
        'memo',
        'is_winner',
        'prize_amount',
        'draw_no'
    ];

    protected $casts = [
        'numbers' => 'array',
        'is_winner' => 'boolean',
        'prize_amount' => 'integer'
    ];

    /**
     * 이 로또 번호의 소유자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 번호 유효성 검증
     */
    public static function validateNumbers(array $numbers): bool
    {
        // 6개의 번호여야 함
        if (count($numbers) !== 6) {
            return false;
        }

        // 1~45 범위의 번호여야 함
        foreach ($numbers as $number) {
            if (!is_numeric($number) || $number < 1 || $number > 45) {
                return false;
            }
        }

        // 중복 번호가 없어야 함
        if (count($numbers) !== count(array_unique($numbers))) {
            return false;
        }

        return true;
    }

    /**
     * 자동 번호 생성
     */
    public static function generateAutoNumbers(int $count = 1): array
    {
        $result = [];
        
        for ($i = 0; $i < $count; $i++) {
            $numbers = [];
            while (count($numbers) < 6) {
                $number = rand(1, 45);
                if (!in_array($number, $numbers)) {
                    $numbers[] = $number;
                }
            }
            sort($numbers);
            $result[] = $numbers;
        }

        return $result;
    }

    /**
     * 반자동 번호 생성 (선호 번호 포함)
     */
    public static function generateSemiAutoNumbers(array $preferredNumbers, int $count = 1): array
    {
        $result = [];
        
        for ($i = 0; $i < $count; $i++) {
            $numbers = [];
            
            // 선호 번호 중 랜덤하게 1-3개 선택
            $selectedPreferred = array_slice($preferredNumbers, 0, rand(1, min(3, count($preferredNumbers))));
            $numbers = array_merge($numbers, $selectedPreferred);
            
            // 나머지 번호 랜덤 생성
            while (count($numbers) < 6) {
                $number = rand(1, 45);
                if (!in_array($number, $numbers)) {
                    $numbers[] = $number;
                }
            }
            
            sort($numbers);
            $result[] = $numbers;
        }

        return $result;
    }

    /**
     * 번호 타입별 라벨
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'auto' => '자동',
            'semi' => '반자동',
            'manual' => '수동',
            'fortune' => '운세기반',
            default => '알 수 없음'
        };
    }
}