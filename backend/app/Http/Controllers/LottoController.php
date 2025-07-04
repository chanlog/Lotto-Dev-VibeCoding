<?php

namespace App\Http\Controllers;

use App\Models\LottoNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LottoController extends Controller
{
    /**
     * 로또 번호 생성
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:auto,semi,fortune',
            'count' => 'integer|min:1|max:10',
            'preferred_numbers' => 'array|max:6',
            'preferred_numbers.*' => 'integer|min:1|max:45',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type');
        $count = $request->input('count', 1);
        $preferredNumbers = $request->input('preferred_numbers', []);

        try {
            switch ($type) {
                case 'auto':
                    $generatedNumbers = LottoNumber::generateAutoNumbers($count);
                    break;
                    
                case 'semi':
                    if (empty($preferredNumbers)) {
                        return response()->json([
                            'success' => false,
                            'message' => '반자동 생성에는 선호 번호가 필요합니다.'
                        ], 422);
                    }
                    $generatedNumbers = LottoNumber::generateSemiAutoNumbers($preferredNumbers, $count);
                    break;
                    
                case 'fortune':
                    // TODO: 사주 분석 서비스와 연동
                    $generatedNumbers = LottoNumber::generateAutoNumbers($count);
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => '지원하지 않는 생성 타입입니다.'
                    ], 422);
            }

            $formattedNumbers = [];
            foreach ($generatedNumbers as $index => $numbers) {
                $formattedNumbers[] = [
                    'id' => $index + 1,
                    'numbers' => $numbers,
                    'type' => $type,
                    'created_at' => now()->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '로또 번호가 생성되었습니다.',
                'data' => [
                    'numbers' => $formattedNumbers
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
     * 로또 번호 저장
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numbers' => 'required|array|size:6',
            'numbers.*' => 'integer|min:1|max:45',
            'type' => 'required|in:auto,semi,manual,fortune',
            'memo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'errors' => $validator->errors()
            ], 422);
        }

        $numbers = $request->input('numbers');

        // 번호 유효성 검증
        if (!LottoNumber::validateNumbers($numbers)) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 로또 번호입니다.',
                'errors' => [
                    'numbers' => ['로또 번호는 1-45 범위의 중복되지 않는 6개 번호여야 합니다.']
                ]
            ], 422);
        }

        try {
            $lottoNumber = LottoNumber::create([
                'user_id' => $request->user()->id,
                'numbers' => $numbers,
                'type' => $request->input('type'),
                'memo' => $request->input('memo'),
            ]);

            return response()->json([
                'success' => true,
                'message' => '로또 번호가 저장되었습니다.',
                'data' => [
                    'lotto_number' => [
                        'id' => $lottoNumber->id,
                        'numbers' => $lottoNumber->numbers,
                        'type' => $lottoNumber->type,
                        'memo' => $lottoNumber->memo,
                        'created_at' => $lottoNumber->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '번호 저장 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 사용자의 로또 번호 목록 조회
     */
    public function myNumbers(Request $request)
    {
        try {
            $numbers = LottoNumber::where('user_id', $request->user()->id)
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'numbers' => $numbers->items(),
                    'pagination' => [
                        'current_page' => $numbers->currentPage(),
                        'last_page' => $numbers->lastPage(),
                        'per_page' => $numbers->perPage(),
                        'total' => $numbers->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '번호 목록 조회 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 로또 번호 삭제
     */
    public function deleteMyNumber(Request $request, $id)
    {
        try {
            $lottoNumber = LottoNumber::where('id', $id)
                                   ->where('user_id', $request->user()->id)
                                   ->first();

            if (!$lottoNumber) {
                return response()->json([
                    'success' => false,
                    'message' => '삭제할 수 없는 번호입니다.'
                ], 403);
            }

            $lottoNumber->delete();

            return response()->json([
                'success' => true,
                'message' => '로또 번호가 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '번호 삭제 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 최신 당첨번호 조회 (동행복권 API 연동 예정)
     */
    public function latest(Request $request)
    {
        // TODO: 실제 동행복권 API 연동
        return response()->json([
            'success' => true,
            'data' => [
                'draw_no' => 1150,
                'draw_date' => '2024-07-06',
                'numbers' => [3, 12, 17, 25, 33, 42],
                'bonus_number' => 18,
                'prize_amounts' => [
                    1 => 2800000000, // 1등
                    2 => 58000000,   // 2등
                    3 => 1500000,    // 3등
                    4 => 50000,      // 4등
                    5 => 5000        // 5등
                ],
                'winners' => [
                    1 => 12,
                    2 => 85,
                    3 => 2547,
                    4 => 125896,
                    5 => 2156789
                ]
            ]
        ]);
    }
}