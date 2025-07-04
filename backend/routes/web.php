<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => '로또 분석 서비스 API',
        'version' => '1.0.0',
        'status' => 'active',
        'timestamp' => now()->toISOString(),
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => 'connected',
            'redis' => 'connected',
            'storage' => 'accessible',
        ],
    ]);
});

// API 문서 라우트 (개발 환경에서만)
if (app()->environment('local')) {
    Route::get('/docs', function () {
        return view('docs.api');
    });
}