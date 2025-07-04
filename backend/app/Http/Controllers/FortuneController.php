<?php

namespace App\Http\Controllers;

use App\Models\FortuneAnalysis;
use App\Models\LottoNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FortuneController extends Controller
{
    /**
     * 사주 분석 요청
     */
    public function analyze(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'birth_date' => 'required|date|before:today|after:1900-01-01',
            'birth_time' => 'nullable|date_format:H:i',
            'gender' => 'required|in:male,female'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $birthDate = Carbon::parse($request->input('birth_date'));
            $birthTime = $request->input('birth_time') ? 
                Carbon::parse($request->input('birth_date') . ' ' . $request->input('birth_time')) : 
                null;
            $gender = $request->input('gender');

            // 사주 분석 로직 (실제로는 복잡한 사주 알고리즘을 적용)
            $analysis = $this->performFortuneAnalysis($birthDate, $birthTime, $gender);

            // 분석 결과 저장
            $fortuneAnalysis = FortuneAnalysis::create([
                'user_id' => $request->user()->id,
                'birth_date' => $birthDate->format('Y-m-d'),
                'birth_time' => $birthTime?->format('H:i:s'),
                'gender' => $gender,
                'wealth_luck' => $analysis['wealth_luck'],
                'general_luck' => $analysis['general_luck'],
                'lucky_numbers' => $analysis['lucky_numbers'],
                'lucky_colors' => $analysis['lucky_colors'],
                'analysis_summary' => $analysis['analysis_summary'],
                'today_fortune' => $analysis['today_fortune'],
                'analysis_date' => now()->format('Y-m-d')
            ]);

            return response()->json([
                'success' => true,
                'message' => '사주 분석이 완료되었습니다.',
                'data' => [
                    'analysis' => [
                        'id' => $fortuneAnalysis->id,
                        'wealth_luck' => $fortuneAnalysis->wealth_luck,
                        'general_luck' => $fortuneAnalysis->general_luck,
                        'lucky_numbers' => $fortuneAnalysis->lucky_numbers,
                        'lucky_colors' => $fortuneAnalysis->lucky_colors,
                        'analysis_summary' => $fortuneAnalysis->analysis_summary,
                        'today_fortune' => $fortuneAnalysis->today_fortune,
                        'wealth_luck_grade' => $fortuneAnalysis->getWealthLuckGrade(),
                        'general_luck_grade' => $fortuneAnalysis->getGeneralLuckGrade(),
                        'created_at' => $fortuneAnalysis->created_at
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '사주 분석 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 사주 기반 로또 번호 생성
     */
    public function generateNumbers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'analysis_id' => 'nullable|integer|exists:fortune_analyses,id',
            'count' => 'integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = $request->input('count', 1);
            
            if ($request->has('analysis_id')) {
                // 기존 분석을 기반으로 번호 생성
                $analysis = FortuneAnalysis::where('id', $request->input('analysis_id'))
                                         ->where('user_id', $request->user()->id)
                                         ->first();

                if (!$analysis) {
                    return response()->json([
                        'success' => false,
                        'message' => '해당 사주 분석을 찾을 수 없습니다.'
                    ], 404);
                }
            } else {
                // 최근 분석을 사용하거나 기본 분석 생성
                $analysis = FortuneAnalysis::where('user_id', $request->user()->id)
                                         ->latest()
                                         ->first();

                if (!$analysis) {
                    return response()->json([
                        'success' => false,
                        'message' => '사주 분석이 필요합니다. 먼저 사주 분석을 받아주세요.'
                    ], 422);
                }
            }

            $generatedNumbers = $analysis->generateFortuneBasedNumbers($count);

            $formattedNumbers = [];
            foreach ($generatedNumbers as $index => $numbers) {
                $formattedNumbers[] = [
                    'numbers' => $numbers,
                    'type' => 'fortune',
                    'analysis_summary' => "재물운 {$analysis->wealth_luck}점, 전체운 {$analysis->general_luck}점 기반",
                    'lucky_numbers_used' => array_intersect($numbers, $analysis->lucky_numbers ?? [])
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '사주 기반 로또 번호가 생성되었습니다.',
                'data' => [
                    'numbers' => $formattedNumbers,
                    'analysis_info' => [
                        'wealth_luck' => $analysis->wealth_luck,
                        'general_luck' => $analysis->general_luck,
                        'lucky_numbers' => $analysis->lucky_numbers,
                        'analysis_date' => $analysis->analysis_date
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '번호 생성 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 사주 분석 기록 조회
     */
    public function history(Request $request)
    {
        try {
            $analyses = FortuneAnalysis::where('user_id', $request->user()->id)
                                     ->orderBy('created_at', 'desc')
                                     ->paginate(10);

            $formattedAnalyses = $analyses->getCollection()->map(function ($analysis) {
                return [
                    'id' => $analysis->id,
                    'birth_date' => $analysis->birth_date->format('Y-m-d'),
                    'gender' => $analysis->getGenderKorean(),
                    'wealth_luck' => $analysis->wealth_luck,
                    'general_luck' => $analysis->general_luck,
                    'wealth_luck_grade' => $analysis->getWealthLuckGrade(),
                    'general_luck_grade' => $analysis->getGeneralLuckGrade(),
                    'lucky_numbers' => $analysis->lucky_numbers,
                    'lucky_colors' => $analysis->lucky_colors,
                    'analysis_summary' => $analysis->analysis_summary,
                    'analysis_date' => $analysis->analysis_date->format('Y-m-d'),
                    'created_at' => $analysis->created_at,
                    'user_id' => $analysis->user_id
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'analyses' => $formattedAnalyses,
                    'pagination' => [
                        'current_page' => $analyses->currentPage(),
                        'last_page' => $analyses->lastPage(),
                        'per_page' => $analyses->perPage(),
                        'total' => $analyses->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '분석 기록 조회 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 사주 분석 저장
     */
    public function saveAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'birth_date' => 'required|date|before:today|after:1900-01-01',
            'birth_time' => 'nullable|date_format:H:i',
            'gender' => 'required|in:male,female',
            'wealth_luck' => 'required|integer|min:0|max:100',
            'general_luck' => 'required|integer|min:0|max:100',
            'lucky_numbers' => 'nullable|array|max:6',
            'lucky_numbers.*' => 'integer|min:1|max:45',
            'lucky_colors' => 'nullable|array|max:5',
            'analysis_summary' => 'required|string|max:1000',
            'today_fortune' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fortuneAnalysis = FortuneAnalysis::create([
                'user_id' => $request->user()->id,
                'birth_date' => $request->input('birth_date'),
                'birth_time' => $request->input('birth_time'),
                'gender' => $request->input('gender'),
                'wealth_luck' => $request->input('wealth_luck'),
                'general_luck' => $request->input('general_luck'),
                'lucky_numbers' => $request->input('lucky_numbers', []),
                'lucky_colors' => $request->input('lucky_colors', []),
                'analysis_summary' => $request->input('analysis_summary'),
                'today_fortune' => $request->input('today_fortune'),
                'analysis_date' => now()->format('Y-m-d')
            ]);

            return response()->json([
                'success' => true,
                'message' => '사주 분석이 저장되었습니다.',
                'data' => [
                    'analysis' => [
                        'id' => $fortuneAnalysis->id,
                        'created_at' => $fortuneAnalysis->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '분석 저장 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 오늘의 운세 업데이트
     */
    public function updateTodayFortune(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'today_fortune' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $analysis = FortuneAnalysis::where('id', $id)
                                     ->where('user_id', $request->user()->id)
                                     ->first();

            if (!$analysis) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 분석을 찾을 수 없습니다.'
                ], 403);
            }

            $analysis->update([
                'today_fortune' => $request->input('today_fortune')
            ]);

            return response()->json([
                'success' => true,
                'message' => '오늘의 운세가 업데이트되었습니다.',
                'data' => [
                    'today_fortune' => $analysis->today_fortune
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '운세 업데이트 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 사주 분석 로직 (실제로는 더 복잡한 알고리즘 적용)
     */
    private function performFortuneAnalysis(Carbon $birthDate, ?Carbon $birthTime, string $gender): array
    {
        // 생년월일 기반 기본 운세 계산
        $year = $birthDate->year;
        $month = $birthDate->month;
        $day = $birthDate->day;
        
        // 간단한 운세 계산 알고리즘 (실제로는 사주팔자 등 복잡한 계산)
        $wealthLuck = ($year + $month * 3 + $day * 2) % 61 + 40; // 40-100 범위
        $generalLuck = ($year * 2 + $month + $day * 3) % 61 + 40; // 40-100 범위
        
        // 성별에 따른 보정
        if ($gender === 'female') {
            $wealthLuck = min(100, $wealthLuck + 5);
            $generalLuck = min(100, $generalLuck + 3);
        }

        // 태어난 시간이 있으면 추가 보정
        if ($birthTime) {
            $hour = $birthTime->hour;
            if ($hour >= 6 && $hour <= 10) { // 아침 시간
                $wealthLuck = min(100, $wealthLuck + 10);
            } elseif ($hour >= 14 && $hour <= 18) { // 오후 시간
                $generalLuck = min(100, $generalLuck + 8);
            }
        }

        // 행운 번호 생성 (생년월일 기반)
        $luckyNumbers = $this->generateLuckyNumbers($birthDate, $gender);
        
        // 행운 색상 생성
        $luckyColors = $this->generateLuckyColors($wealthLuck, $generalLuck);

        // 분석 요약 생성
        $analysisSummary = FortuneAnalysis::generateAnalysisSummary(
            $wealthLuck, 
            $generalLuck, 
            $luckyNumbers
        );

        // 오늘의 운세 생성
        $todayFortune = $this->generateTodayFortune($wealthLuck, $generalLuck);

        return [
            'wealth_luck' => $wealthLuck,
            'general_luck' => $generalLuck,
            'lucky_numbers' => $luckyNumbers,
            'lucky_colors' => $luckyColors,
            'analysis_summary' => $analysisSummary,
            'today_fortune' => $todayFortune
        ];
    }

    /**
     * 행운 번호 생성
     */
    private function generateLuckyNumbers(Carbon $birthDate, string $gender): array
    {
        $numbers = [];
        $year = $birthDate->year % 100;
        $month = $birthDate->month;
        $day = $birthDate->day;

        // 생년월일 기반 기본 번호
        $numbers[] = $month;
        $numbers[] = $day <= 31 ? $day : $day % 31 + 1;
        $numbers[] = ($year % 45) + 1;

        // 성별 기반 추가 번호
        if ($gender === 'male') {
            $numbers[] = 7; // 남성 행운 번호
            $numbers[] = 21;
        } else {
            $numbers[] = 3; // 여성 행운 번호
            $numbers[] = 9;
        }

        // 추가 랜덤 번호 (중복 제거)
        while (count($numbers) < 6) {
            $newNumber = rand(1, 45);
            if (!in_array($newNumber, $numbers)) {
                $numbers[] = $newNumber;
            }
        }

        sort($numbers);
        return array_slice($numbers, 0, 6);
    }

    /**
     * 행운 색상 생성
     */
    private function generateLuckyColors(int $wealthLuck, int $generalLuck): array
    {
        $colors = [];

        if ($wealthLuck >= 80) {
            $colors[] = '금색';
        } elseif ($wealthLuck >= 60) {
            $colors[] = '노랑';
        } else {
            $colors[] = '초록';
        }

        if ($generalLuck >= 80) {
            $colors[] = '빨강';
        } elseif ($generalLuck >= 60) {
            $colors[] = '주황';
        } else {
            $colors[] = '파랑';
        }

        return array_unique($colors);
    }

    /**
     * 오늘의 운세 생성
     */
    private function generateTodayFortune(int $wealthLuck, int $generalLuck): string
    {
        $fortunes = [];

        if ($wealthLuck >= 80) {
            $fortunes[] = "오늘은 금전적인 행운이 강하게 작용하는 날입니다.";
        } elseif ($wealthLuck >= 60) {
            $fortunes[] = "재물 관련하여 좋은 소식이 있을 수 있습니다.";
        } else {
            $fortunes[] = "금전 관리에 신중함이 필요한 하루입니다.";
        }

        if ($generalLuck >= 80) {
            $fortunes[] = "전반적으로 매우 좋은 하루가 될 것입니다.";
        } elseif ($generalLuck >= 60) {
            $fortunes[] = "긍정적인 에너지가 당신을 감쌀 것입니다.";
        } else {
            $fortunes[] = "차분하고 신중하게 하루를 보내시기 바랍니다.";
        }

        return implode(' ', $fortunes);
    }
}