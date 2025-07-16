<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\LoginController;

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


// Public Routes
Route::middleware('guest')->group(function () {
    Route::post('register', [RegisterController::class, 'storePatient'])->name('register.patient');
    Route::post('login', [LoginController::class, 'store'])->name('login');
});


// Auth Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [LoginController::class, 'destroy'])
                ->name('logout');
});



// Doctor Routes
Route::middleware(['auth:sanctum', 'CheckTypes:doctor'])->prefix('doctor')->name('doctor.')->group(function () {

    // Profile Routes
    Route::controller(App\Http\Controllers\Api\Doctor\DoctorController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

});



// Admin Routes
Route::middleware(['auth:sanctum', 'CheckTypes:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Profile Routes
    Route::controller(App\Http\Controllers\Api\Admin\AdminController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    Route::controller(App\Http\Controllers\Api\Admin\DoctorController::class)->prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::post('/', 'store')->name('store');
        Route::get('/{doctor}', 'show')->name('show');
        Route::put('/{doctor}', 'update')->name('update');
        Route::delete('/{doctor}', 'destroy')->name('destroy');
    });

});


// Patient Routes
Route::middleware(['auth:sanctum', 'CheckTypes:patient'])->prefix('patient')->name('patient.')->group(function () {

    // Profile Routes
    Route::controller(App\Http\Controllers\Api\Patient\PatientController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

});
