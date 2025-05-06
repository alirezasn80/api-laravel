<?php

use App\Http\Controllers\API\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {

    Route::get('/test', action: [ApiController::class, 'test'])->name('test');

    Route::post('/register', action: [ApiController::class, 'register'])->name('register');

    Route::post('/login', action: [ApiController::class, 'login'])->name('login');

    Route::get('/get-users', [ApiController::class, 'getAllUsers'])->name('getAllUsers');

    Route::put('/user/{userId}', [ApiController::class, 'editUser'])->name('editUser');

    Route::delete('/user/{userId}', [ApiController::class, 'deleteUser'])->name('deleteUser');
});
