<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\LottoNumber;

class LottoNumberTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 로또 번호 생성 테스트
     */
    public function test_user_can_generate_lotto_numbers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/generate', [
                            'type' => 'auto', // auto, semi, fortune
                            'count' => 5
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'numbers' => [
                            '*' => [
                                'id',
                                'numbers',
                                'type',
                                'created_at'
                            ]
                        ]
                    ]
                ]);
    }

    /**
     * 반자동 로또 번호 생성 테스트
     */
    public function test_user_can_generate_semi_auto_lotto_numbers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/generate', [
                            'type' => 'semi',
                            'preferred_numbers' => [7, 14, 21],
                            'count' => 3
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'numbers'
                    ]
                ]);

        // 선호 번호가 포함되어 있는지 확인
        $generatedNumbers = $response->json('data.numbers');
        foreach ($generatedNumbers as $numberSet) {
            $numbers = $numberSet['numbers'];
            $this->assertTrue(
                in_array(7, $numbers) || in_array(14, $numbers) || in_array(21, $numbers),
                '선호 번호가 포함되어야 합니다'
            );
        }
    }

    /**
     * 로또 번호 저장 테스트
     */
    public function test_user_can_save_lotto_numbers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', [
                            'numbers' => [1, 7, 14, 21, 28, 35],
                            'type' => 'manual',
                            'memo' => '행운의 번호들'
                        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => '로또 번호가 저장되었습니다.'
                ]);

        $this->assertDatabaseHas('lotto_numbers', [
            'user_id' => $user->id,
            'numbers' => json_encode([1, 7, 14, 21, 28, 35]),
            'type' => 'manual',
            'memo' => '행운의 번호들'
        ]);
    }

    /**
     * 잘못된 로또 번호 저장 실패 테스트
     */
    public function test_user_cannot_save_invalid_lotto_numbers()
    {
        $user = User::factory()->create();

        // 번호가 6개가 아닌 경우
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', [
                            'numbers' => [1, 7, 14, 21, 28], // 5개만
                            'type' => 'manual'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['numbers']);

        // 번호 범위를 벗어난 경우 (1~45)
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', [
                            'numbers' => [1, 7, 14, 21, 28, 50], // 50은 범위 초과
                            'type' => 'manual'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['numbers']);

        // 중복 번호가 있는 경우
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', [
                            'numbers' => [1, 7, 14, 21, 28, 28], // 28 중복
                            'type' => 'manual'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['numbers']);
    }

    /**
     * 사용자 로또 번호 목록 조회 테스트
     */
    public function test_user_can_get_their_lotto_numbers()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 사용자의 번호 생성
        LottoNumber::factory()->count(3)->create(['user_id' => $user->id]);
        // 다른 사용자의 번호 생성 (보이면 안됨)
        LottoNumber::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/lotto/my-numbers');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.numbers');

        // 자신의 번호만 반환되는지 확인
        $numbers = $response->json('data.numbers');
        foreach ($numbers as $number) {
            $this->assertEquals($user->id, $number['user_id']);
        }
    }

    /**
     * 로또 번호 삭제 테스트
     */
    public function test_user_can_delete_their_lotto_number()
    {
        $user = User::factory()->create();
        $lottoNumber = LottoNumber::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->deleteJson("/api/lotto/my-numbers/{$lottoNumber->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '로또 번호가 삭제되었습니다.'
                ]);

        $this->assertDatabaseMissing('lotto_numbers', [
            'id' => $lottoNumber->id
        ]);
    }

    /**
     * 다른 사용자의 로또 번호 삭제 실패 테스트
     */
    public function test_user_cannot_delete_others_lotto_number()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lottoNumber = LottoNumber::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->deleteJson("/api/lotto/my-numbers/{$lottoNumber->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('lotto_numbers', [
            'id' => $lottoNumber->id
        ]);
    }

    /**
     * 로또 번호 유효성 검증 테스트
     */
    public function test_lotto_number_validation()
    {
        $user = User::factory()->create();

        // 필수 필드 누락
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['numbers', 'type']);

        // 타입이 잘못된 경우
        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/lotto/save', [
                            'numbers' => [1, 7, 14, 21, 28, 35],
                            'type' => 'invalid_type'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }
}