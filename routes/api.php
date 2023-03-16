<?php

use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/user/pay/{email}', [WalletController::class, 'pay']);
Route::post('/user/transfer/{email}', [WalletController::class, 'transfer']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
