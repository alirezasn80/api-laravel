<?php

use App\Http\Controllers\API\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->as('api.')->group(function () {

    // Test
    Route::get('/test', [ApiController::class, 'test'])->name('test');

    // Login & Register
    Route::post('/register', [ApiController::class, 'register'])->name('register');
    Route::post('/login', [ApiController::class, 'login'])->name('login');

    // Public Data
    Route::get('/categories', [ApiController::class, 'getCategories'])->name('getCategories');
    Route::get('/products', [ApiController::class, 'getProducts'])->name('getProducts');
    Route::get('/shipping-methods', [ApiController::class, 'getAllShippingMethods'])->name('getAllShippingMethods');


    // Protected Routes (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // User
        Route::prefix('user')->group(function () {
            Route::get('/get-users', [ApiController::class, 'getAllUsers'])->name('getAllUsers')->middleware('admin');
            Route::put('/edit/{userId}', [ApiController::class, 'editUser'])->name('editUser');
            Route::delete('/delete/{userId}', [ApiController::class, 'deleteUser'])->name('deleteUser');
        });

        // Category
        Route::prefix('category')->group(function () {
            Route::post('/create', [ApiController::class, 'createCategory'])->name('createCategory');
            Route::post('/edit/{categoryId}', [ApiController::class, 'editCategory'])->name('editCategory');
            Route::delete('/delete/{categoryId}', [ApiController::class, 'deleteCategory'])->name('deleteCategory');
        });

        // Product
        Route::prefix('product')->group(function () {
            Route::post('/create', [ApiController::class, 'createProduct'])->name('createProduct');
            Route::post('/edit/{id}', [ApiController::class, 'editProduct'])->name('editProduct');
            Route::delete('/delete/{id}', [ApiController::class, 'deleteProduct'])->name('deleteProduct');
        });

        // Shipping Methods
        Route::prefix('shipping-method')->group(function () {
            Route::post('/create', [ApiController::class, 'createShippingMethod'])->name('createShippingMethod');
            Route::post('/edit/{shippingId}', [ApiController::class, 'editShippingMethods'])->name('editShippingMethods');
            Route::delete('/delete/{shippingId}', [ApiController::class, 'deleteShippingMethod'])->name('deleteShippingMethod');
        });

    });

});

