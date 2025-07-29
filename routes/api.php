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

    // Schedule Routes
    Route::controller(App\Http\Controllers\Api\Doctor\ScheduleController::class)->prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{schedule}', 'show')->name('show');
        Route::put('/{schedule}', 'update')->name('update');
        Route::delete('/{schedule}', 'destroy')->name('destroy');
        Route::post('/repeat',  'repeat')->name('repeat');
    });

    // Slot Routes
    Route::controller(App\Http\Controllers\Api\Doctor\SlotController::class)->prefix('slots')->name('slots.')->group(function () {
        Route::get('/', 'indexByDate')->name('index.byDate');
        Route::delete('/', 'destroyByDate')->name('destroy.byDate');
        Route::delete('/{doctorSlot}', 'destroy')->name('destroy');
    });

    // Patients Routes
    Route::controller(App\Http\Controllers\Api\Doctor\PatientController::class)->prefix('patients')->name('patients.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::get('/{patient}', 'show')->name('show');
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

    // Doctor Profile Routes
    Route::controller(App\Http\Controllers\Api\Admin\DoctorController::class)->prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::post('/', 'store')->name('store');
        Route::get('/{doctor}', 'show')->name('show');
        Route::put('/{doctor}', 'update')->name('update');
        Route::delete('/{doctor}', 'destroy')->name('destroy');
    });

    // Specialization Routes
    Route::controller(App\Http\Controllers\Api\Admin\SpecializationController::class)->prefix('specializations')->name('specializations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{specialization}', 'show')->name('show');
        Route::put('/{specialization}', 'update')->name('update');
        Route::delete('/{specialization}', 'destroy')->name('destroy');
        Route::get('/{specialization}/doctors', 'doctors')->name('doctors');
    });

    // Patient Profile Routes
    Route::controller(App\Http\Controllers\Api\Admin\PatientController::class)->prefix('patients')->name('patients.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::post('/', 'store')->name('store');
        Route::get('/{patient}', 'show')->name('show');
        Route::put('/{patient}', 'update')->name('update');
        Route::delete('/{patient}', 'destroy')->name('destroy');
    });

    Route::controller(App\Http\Controllers\Api\Admin\ScheduleController::class)->prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/doctor/{doctor}', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{schedule}', 'show')->name('show');
        Route::put('/{schedule}', 'update')->name('update');
        Route::delete('/{schedule}', 'destroy')->name('destroy');
        Route::post('/doctor/{doctor}/repeat',  'repeat')->name('repeat');
    });


    Route::controller(App\Http\Controllers\Api\Admin\SlotController::class)->prefix('slots')->name('slots.')->group(function () {
        Route::get('/', 'indexByDoctorAndDate')->name('index.byDoctor.andDate');
        Route::delete('/', 'destroyByDoctorAndDate')->name('destroy.byDoctor.andDate');
        Route::delete('/{doctorSlot}', 'destroy')->name('destroy');
    });


    // Appointment Routes
    Route::controller(App\Http\Controllers\Api\Admin\AppointmentController::class)->prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::get('/between-dates', 'appointmentsBetweenDates')->name('appointments.betweenDates');
        Route::post('/', 'store')->name('store');
        Route::get('/{appointment}', 'show')->name('show');
        Route::put('/{appointment}', 'update')->name('update');
        Route::delete('/{appointment}', 'destroy')->name('destroy');
        Route::get('/doctor/{doctor}',  'indexByDoctor');
        Route::get('/patient/{patient}',  'indexByPatient');
        Route::get('/doctor/{doctor}/patient/{patient}',  'indexByDoctorAndPatient');
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




