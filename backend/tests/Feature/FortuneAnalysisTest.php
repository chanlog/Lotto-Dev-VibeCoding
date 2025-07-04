<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\FortuneAnalysis;

class FortuneAnalysisTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 사주 분석 요청 테스트
     */
    public function test_user_can_request_fortune_analysis()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/analyze', [
                            'birth_date' => '1990-05-15',
                            'birth_time' => '14:30',
                            'gender' => 'male'
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'analysis' => [
                            'id',
                            'wealth_luck',
                            'general_luck',
                            'lucky_numbers',
                            'lucky_colors',
                            'analysis_summary',
                            'today_fortune',
                            'created_at'
                        ]
                    ]
                ]);
    }

    /**
     * 잘못된 생년월일로 사주 분석 실패 테스트
     */
    public function test_fortune_analysis_with_invalid_birth_date()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/analyze', [
                            'birth_date' => '2030-05-15', // 미래 날짜
                            'birth_time' => '14:30',
                            'gender' => 'male'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['birth_date']);

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/analyze', [
                            'birth_date' => '1850-05-15', // 너무 과거
                            'birth_time' => '14:30',
                            'gender' => 'male'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['birth_date']);
    }

    /**
     * 사주 기반 로또 번호 생성 테스트
     */
    public function test_user_can_generate_fortune_based_lotto_numbers()
    {
        $user = User::factory()->create();
        
        // 먼저 사주 분석 생성
        $fortuneAnalysis = FortuneAnalysis::factory()->create([
            'user_id' => $user->id,
            'lucky_numbers' => [7, 14, 21, 28, 35, 42]
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/generate-numbers', [
                            'analysis_id' => $fortuneAnalysis->id,
                            'count' => 3
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'numbers' => [
                            '*' => [
                                'numbers',
                                'type',
                                'analysis_summary'
                            ]
                        ]
                    ]
                ]);

        // 행운 번호가 포함되어 있는지 확인
        $generatedNumbers = $response->json('data.numbers');
        foreach ($generatedNumbers as $numberSet) {
            $numbers = $numberSet['numbers'];
            $hasLuckyNumber = false;
            foreach ($fortuneAnalysis->lucky_numbers as $luckyNumber) {
                if (in_array($luckyNumber, $numbers)) {
                    $hasLuckyNumber = true;
                    break;
                }
            }
            $this->assertTrue($hasLuckyNumber, '행운 번호가 하나 이상 포함되어야 합니다');
        }
    }

    /**
     * 사주 분석 기록 조회 테스트
     */
    public function test_user_can_get_fortune_analysis_history()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 사용자의 분석 기록 생성
        FortuneAnalysis::factory()->count(3)->create(['user_id' => $user->id]);
        // 다른 사용자의 분석 기록 생성 (보이면 안됨)
        FortuneAnalysis::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/fortune/history');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.analyses');

        // 자신의 분석만 반환되는지 확인
        $analyses = $response->json('data.analyses');
        foreach ($analyses as $analysis) {
            $this->assertEquals($user->id, $analysis['user_id']);
        }
    }

    /**
     * 사주 분석 저장 테스트
     */
    public function test_user_can_save_fortune_analysis()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/save-analysis', [
                            'birth_date' => '1990-05-15',
                            'birth_time' => '14:30',
                            'gender' => 'female',
                            'wealth_luck' => 85,
                            'general_luck' => 78,
                            'lucky_numbers' => [3, 9, 15, 27, 33, 41],
                            'lucky_colors' => ['빨강', '금색'],
                            'analysis_summary' => '재물운이 상승하는 시기입니다.',
                            'today_fortune' => '오늘은 특히 금전적인 행운이 따를 것입니다.'
                        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => '사주 분석이 저장되었습니다.'
                ]);

        $this->assertDatabaseHas('fortune_analyses', [
            'user_id' => $user->id,
            'birth_date' => '1990-05-15',
            'wealth_luck' => 85,
            'general_luck' => 78
        ]);
    }

    /**
     * 운세 점수 범위 유효성 검증 테스트
     */
    public function test_fortune_luck_score_validation()
    {
        $user = User::factory()->create();

        // 점수가 0-100 범위를 벗어난 경우
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/save-analysis', [
                            'birth_date' => '1990-05-15',
                            'birth_time' => '14:30',
                            'gender' => 'male',
                            'wealth_luck' => 150, // 범위 초과
                            'general_luck' => -10, // 범위 미만
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['wealth_luck', 'general_luck']);
    }

    /**
     * 행운 번호 유효성 검증 테스트
     */
    public function test_lucky_numbers_validation()
    {
        $user = User::factory()->create();

        // 6개 초과의 행운 번호
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/save-analysis', [
                            'birth_date' => '1990-05-15',
                            'birth_time' => '14:30',
                            'gender' => 'male',
                            'lucky_numbers' => [1, 7, 14, 21, 28, 35, 42], // 7개
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['lucky_numbers']);

        // 1-45 범위를 벗어난 번호
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/fortune/save-analysis', [
                            'birth_date' => '1990-05-15',
                            'birth_time' => '14:30',
                            'gender' => 'male',
                            'lucky_numbers' => [1, 7, 14, 21, 28, 50], // 50은 범위 초과
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['lucky_numbers.5']);
    }

    /**
     * 오늘의 운세 업데이트 테스트
     */
    public function test_today_fortune_can_be_updated()
    {
        $user = User::factory()->create();
        $analysis = FortuneAnalysis::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->putJson("/api/fortune/analyses/{$analysis->id}/today", [
                            'today_fortune' => '새로운 오늘의 운세입니다.'
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '오늘의 운세가 업데이트되었습니다.'
                ]);

        $this->assertDatabaseHas('fortune_analyses', [
            'id' => $analysis->id,
            'today_fortune' => '새로운 오늘의 운세입니다.'
        ]);
    }

    /**
     * 다른 사용자의 분석 수정 시도 실패 테스트
     */
    public function test_user_cannot_update_others_fortune_analysis()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $analysis = FortuneAnalysis::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->putJson("/api/fortune/analyses/{$analysis->id}/today", [
                            'today_fortune' => '해킹 시도'
                        ]);

        $response->assertStatus(403);
    }
}