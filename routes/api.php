<?php

use App\Http\Api\V1\Building\IndexBuildingController;
use App\Http\Api\V1\Building\ShowBuildingController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Группа с проверкой API ключа
Route::prefix('v1')->middleware('api_key')->group(function () {

    // Здания
    Route::get('/buildings', IndexBuildingController::class);
    Route::get('/buildings/{id}', ShowBuildingController::class);

    // Другие эндпоинты (по аналогии):
    // Route::get('/buildings/{id}/organizations', ByBuildingOrganizationsController::class);
    // Route::get('/organizations/{id}', ShowOrganizationController::class);
});
