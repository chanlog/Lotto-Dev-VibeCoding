<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FortuneAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_date',
        'birth_time',
        'gender',
        'wealth_luck',
        'general_luck',
        'lucky_numbers',
        'lucky_colors',
        'analysis_summary',
        'today_fortune',
        'analysis_date'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'birth_time' => 'datetime',
        'lucky_numbers' => 'array',
        'lucky_colors' => 'array',
        'wealth_luck' => 'integer',
        'general_luck' => 'integer',
        'analysis_date' => 'date'
    ];

    /**
     * 이 사주 분석의 소유자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 사주 기반 로또 번호 생성
     */
    public function generateFortuneBasedNumbers(int $count = 1): array
    {
        $result = [];
        $luckyNumbers = $this->lucky_numbers ?? [];
        
        for ($i = 0; $i < $count; $i++) {
            $numbers = [];
            
            // 행운 번호 중 2-4개를 랜덤하게 선택
            $luckyCount = min(rand(2, 4), count($luckyNumbers));
            if ($luckyCount > 0) {
                $selectedLucky = array_slice($luckyNumbers, 0, $luckyCount);
                $numbers = array_merge($numbers, $selectedLucky);
            }
            
            // 나머지 번호는 운세 점수에 따라 가중치 적용
            while (count($numbers) < 6) {
                $number = $this->generateWeightedRandomNumber();
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
     * 운세 점수에 따른 가중치 적용 랜덤 번호 생성
     */
    private function generateWeightedRandomNumber(): int
    {
        $wealthWeight = $this->wealth_luck / 100;
        $generalWeight = $this->general_luck / 100;
        
        // 높은 운세일수록 특정 범위의 번호에 가중치 부여
        if ($wealthWeight > 0.7) {
            // 재물운이 높으면 8, 18, 28, 38 등 8이 들어간 번호 선호
            $eightNumbers = [8, 18, 28, 38];
            if (rand(1, 100) <= 30) { // 30% 확률
                return $eightNumbers[array_rand($eightNumbers)];
            }
        }
        
        if ($generalWeight > 0.7) {
            // 전체운이 높으면 3, 7, 21 등 행운 번호 선호
            $generalLuckyNumbers = [3, 7, 9, 21, 27, 33, 39];
            if (rand(1, 100) <= 25) { // 25% 확률
                return $generalLuckyNumbers[array_rand($generalLuckyNumbers)];
            }
        }
        
        // 기본 랜덤 생성
        return rand(1, 45);
    }

    /**
     * 오늘의 운세 업데이트 여부 확인
     */
    public function canUpdateTodayFortune(): bool
    {
        return $this->analysis_date->isToday() || $this->analysis_date->isFuture();
    }

    /**
     * 운세 등급 반환
     */
    public function getWealthLuckGrade(): string
    {
        return match(true) {
            $this->wealth_luck >= 90 => '최상',
            $this->wealth_luck >= 80 => '상',
            $this->wealth_luck >= 70 => '중상',
            $this->wealth_luck >= 60 => '중',
            $this->wealth_luck >= 50 => '중하',
            $this->wealth_luck >= 40 => '하',
            default => '최하'
        };
    }

    /**
     * 전체 운세 등급 반환
     */
    public function getGeneralLuckGrade(): string
    {
        return match(true) {
            $this->general_luck >= 90 => '최상',
            $this->general_luck >= 80 => '상',
            $this->general_luck >= 70 => '중상',
            $this->general_luck >= 60 => '중',
            $this->general_luck >= 50 => '중하',
            $this->general_luck >= 40 => '하',
            default => '최하'
        };
    }

    /**
     * 성별 한글 변환
     */
    public function getGenderKorean(): string
    {
        return match($this->gender) {
            'male' => '남성',
            'female' => '여성',
            default => '미지정'
        };
    }

    /**
     * 사주 분석 요약 자동 생성
     */
    public static function generateAnalysisSummary(int $wealthLuck, int $generalLuck, array $luckyNumbers): string
    {
        $wealthGrade = match(true) {
            $wealthLuck >= 80 => '매우 좋은',
            $wealthLuck >= 60 => '좋은',
            $wealthLuck >= 40 => '보통인',
            default => '주의가 필요한'
        };

        $generalGrade = match(true) {
            $generalLuck >= 80 => '매우 밝은',
            $generalLuck >= 60 => '밝은',
            $generalLuck >= 40 => '평온한',
            default => '조심스러운'
        };

        $primaryLucky = $luckyNumbers[0] ?? 7;
        
        return "현재 {$wealthGrade} 재물운과 {$generalGrade} 전체운을 보이고 있습니다. " .
               "특히 {$primaryLucky}번이 당신의 주요 행운 번호로 나타납니다. " .
               "긍정적인 마음가짐으로 기회를 잡으시기 바랍니다.";
    }
}