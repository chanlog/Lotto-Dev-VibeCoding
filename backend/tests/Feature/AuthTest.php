<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 사용자 회원가입 테스트
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'created_at',
                            'updated_at',
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * 중복 이메일로 회원가입 실패 테스트
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * 잘못된 데이터로 회원가입 실패 테스트
     */
    public function test_user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * 사용자 로그인 테스트
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'token'
                    ]
                ]);
    }

    /**
     * 잘못된 비밀번호로 로그인 실패 테스트
     */
    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => '로그인 정보가 올바르지 않습니다.',
                ]);
    }

    /**
     * 존재하지 않는 이메일로 로그인 실패 테스트
     */
    public function test_user_cannot_login_with_non_existent_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => '로그인 정보가 올바르지 않습니다.',
                ]);
    }

    /**
     * 인증된 사용자 정보 조회 테스트
     */
    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
    }

    /**
     * 비인증 사용자 정보 조회 실패 테스트
     */
    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * 사용자 로그아웃 테스트
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => '로그아웃되었습니다.',
                ]);
    }

    /**
     * 비밀번호 유효성 검증 테스트
     */
    public function test_password_validation()
    {
        $userData = [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => '123', // 너무 짧은 비밀번호
            'password_confirmation' => '123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /**
     * 비밀번호 확인 불일치 테스트
     */
    public function test_password_confirmation_mismatch()
    {
        $userData = [
            'name' => '테스트 사용자',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
}