<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*Rutas públicas */

Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('public.index');
Route::get('/doctors/{doctor:slug}', [App\Http\Controllers\PublicController::class, 'doctor'])->name('public.doctor');
Route::get('/appointments/new', [App\Http\Controllers\PublicController::class, 'new'])->name('public.appointments.new');
Route::post('/appointments', [App\Http\Controllers\PublicController::class, 'store'])->name('public.appointments.store');

/*Rutas protegidas con autenticación + verificación de email*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Rutas por defecto de Jetstream
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // rutas protegidas
    Route::get('/home', [App\Http\Controllers\CalendarController::class, 'dashboard'])->name('home');
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'adminCalendar'])->name('admin.calendar');

    Route::resource('/doctors', App\Http\Controllers\DoctorController::class);
    Route::resource('/appointments', App\Http\Controllers\AppointmentController::class)
        ->only(['index', 'show', 'update', 'destroy']);

    Route::post('/appointments/{appointment:slug}/accept', [App\Http\Controllers\AppointmentController::class, 'accept'])
        ->name('appointments.accept');
    Route::post('/appointments/{appointment:slug}/reject', [App\Http\Controllers\AppointmentController::class, 'reject'])
        ->name('appointments.reject');
});
