<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SimpleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 기본 API 테스트
     */
    public function test_basic_api_response()
    {
        $response = $this->getJson('/api/lotto/latest');
        
        if ($response->status() !== 200) {
            // 에러 내용 출력
            dump($response->getContent());
            dump($response->status());
        }
        
        $response->assertStatus(200);
    }

    /**
     * 인증 테스트
     */
    public function test_user_creation()
    {
        $user = User::factory()->create();
        $this->assertNotNull($user);
    }

    /**
     * 데이터베이스 연결 테스트
     */
    public function test_database_connection()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }
}