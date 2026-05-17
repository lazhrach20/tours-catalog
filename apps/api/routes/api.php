<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDepartureController;
use App\Http\Controllers\Admin\AdminGenerateController;
use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AdminRouteController;
use App\Http\Controllers\Admin\AdminTourController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/{slug}', [TourController::class, 'show']);

Route::prefix('admin')->group(function () {
    Route::post('auth/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AdminAuthController::class, 'logout']);

        Route::get('tours', [AdminTourController::class, 'index']);
        Route::post('tours/generate', [AdminGenerateController::class, 'store']);
        Route::post('tours', [AdminTourController::class, 'store']);
        Route::get('tours/{tour}', [AdminTourController::class, 'show']);
        Route::patch('tours/{tour}', [AdminTourController::class, 'update']);
        Route::delete('tours/{tour}', [AdminTourController::class, 'destroy']);
        Route::post('tours/{tour}/publish', [AdminTourController::class, 'publish']);

        Route::post('tours/{tour}/photos', [AdminPhotoController::class, 'store']);
        Route::patch('tours/{tour}/photos/reorder', [AdminPhotoController::class, 'reorder']);
        Route::patch('photos/{photo}/cover', [AdminPhotoController::class, 'setCover']);
        Route::delete('photos/{photo}', [AdminPhotoController::class, 'destroy']);

        Route::put('tours/{tour}/route-points', [AdminRouteController::class, 'replace']);

        Route::get('tours/{tour}/departures', [AdminDepartureController::class, 'index']);
        Route::post('tours/{tour}/departures', [AdminDepartureController::class, 'store']);
        Route::patch('tours/{tour}/departures/{departure}', [AdminDepartureController::class, 'update']);
        Route::delete('tours/{tour}/departures/{departure}', [AdminDepartureController::class, 'destroy']);

        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);
        Route::get('categories/{category}', [AdminCategoryController::class, 'show']);
        Route::patch('categories/{category}', [AdminCategoryController::class, 'update']);
        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy']);
    });
});
