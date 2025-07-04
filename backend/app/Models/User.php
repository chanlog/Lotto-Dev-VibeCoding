<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 사용자의 로또 번호들
     */
    public function lottoNumbers()
    {
        return $this->hasMany(LottoNumber::class);
    }

    /**
     * 사용자의 사주 분석 기록들
     */
    public function fortuneAnalyses()
    {
        return $this->hasMany(FortuneAnalysis::class);
    }

    /**
     * 사용자의 로또 구매 기록들
     */
    public function lottoTickets()
    {
        return $this->hasMany(LottoTicket::class);
    }
}