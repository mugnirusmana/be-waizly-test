<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TaskController;

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

Route::group([
], function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::group([
    'middleware' => 'check_token'
], function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::group([
        'prefix' => '/be-test-2',
    ], function() {
        Route::get('/test-1', [EmployeeController::class, 'test1']);
        Route::get('/test-2', [EmployeeController::class, 'test2']);
        Route::get('/test-3', [EmployeeController::class, 'test3']);
        Route::get('/test-4', [EmployeeController::class, 'test4']);
        Route::get('/test-5', [EmployeeController::class, 'test5']);
        Route::get('/test-6', [EmployeeController::class, 'test6']);
        Route::get('/test-7', [EmployeeController::class, 'test7']);
        Route::get('/test-8', [EmployeeController::class, 'test8']);
    });

    Route::group([
        'prefix' => '/fe-test-1',
    ], function() {
        Route::get('/', [TaskController::class, 'list']);
        Route::post('/create', [TaskController::class, 'create']);
        Route::put('/edit/{id}', [TaskController::class, 'edit']);
        Route::get('/detail/{id}', [TaskController::class, 'detail']);
        Route::delete('/delete/{id}', [TaskController::class, 'destroy']);
        Route::post('/sort', [TaskController::class, 'sort']);
        Route::get('/todo/{id}', [TaskController::class, 'todo']);
        Route::get('/complete/{id}', [TaskController::class, 'complete']);
    });
});
