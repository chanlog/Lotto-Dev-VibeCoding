<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LottoController;
use App\Http\Controllers\FortuneController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 인증 관련 라우트
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// 로또 관련 라우트
Route::prefix('lotto')->group(function () {
    Route::get('latest', [LottoController::class, 'latest']);
    
    // 인증 필요 라우트
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('generate', [LottoController::class, 'generate']);
        Route::post('save', [LottoController::class, 'save']);
        Route::get('my-numbers', [LottoController::class, 'myNumbers']);
        Route::delete('my-numbers/{id}', [LottoController::class, 'deleteMyNumber']);
    });
});

// 사주 분석 관련 라우트
Route::prefix('fortune')->group(function () {
    Route::post('analyze', [FortuneController::class, 'analyze'])->middleware('auth:sanctum');
    Route::post('generate-numbers', [FortuneController::class, 'generateNumbers'])->middleware('auth:sanctum');
    
    // 인증 필요 라우트
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('history', [FortuneController::class, 'history']);
        Route::post('save-analysis', [FortuneController::class, 'saveAnalysis']);
        Route::put('analyses/{id}/today', [FortuneController::class, 'updateTodayFortune']);
    });
});