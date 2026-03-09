<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\FriendRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

//user routes
Route::middleware('auth:sanctum')->post('/user/update', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->post('/user/image/update', [UserController::class, 'updateImage']);

//activity routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/activities/paginated', [ActivityController::class, 'paginated']);
    Route::get('/activities/count', [ActivityController::class, 'count']);
    Route::get('/activities/latest', [ActivityController::class, 'latest']);
   Route::apiResource('activities', ActivityController::class); 
});

//analytics routes
Route::middleware('auth:sanctum')->get('/analytics/activities/dashboard', [AnalyticsController::class, 'dashboard']);

//category routes
Route::get('/categories', [CategoryController::class, 'index']);


//friend requests routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/friend-requests/sent', [FriendRequestController::class, 'index_sent']);
    Route::get('/friend-requests/received', [FriendRequestController::class, 'index_received']);
    Route::post('/friend-requests/send', [FriendRequestController::class, 'send']);
    Route::post('/friend-requests/accept', [FriendRequestController::class, 'accept']);
    Route::post('/friend-requests/reject', [FriendRequestController::class, 'reject']);
});
//friends routes
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/friends', [FriendController::class, 'index']);
    Route::post('/friends', [FriendController::class, 'store']);
    Route::put('/friends/{id}', [FriendController::class, 'update']);
    Route::delete('/friends/{id}', [FriendController::class, 'destroy']);
});