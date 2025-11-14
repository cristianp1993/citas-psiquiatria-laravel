<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\{Doctor, Appointment};

// disponibilidad de doctores sin autenticación)
Route::get('/public/availability', function (Request $r) {
    $doctor = Doctor::where('slug', $r->doctor)->with('schedules')->firstOrFail();
    $monday = now()->startOfWeek();
    $slots = app('availability')->weeklyFreeSlots($doctor, $monday);
    return response()->json($slots);
});

// Ruta protegida: eventos del calendario del administrador
Route::get('/admin/calendar', function (Request $r) {
    $doctor = Doctor::where('slug', $r->doctor)->firstOrFail();
    $apps = Appointment::where('doctor_id', $doctor->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->get(['start_at', 'end_at', 'status']);

    return $apps->map(fn($a) => [
        'start' => $a->start_at->toIso8601String(),
        'end' => $a->end_at->toIso8601String(),
        'title' => $a->status === 'pending' ? 'Pendiente' : 'Confirmada'
    ]);
})->middleware('auth:sanctum');