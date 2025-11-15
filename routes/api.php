<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\{Doctor, Appointment};


Route::get('/public/availability', function (Request $r) {
    $doctor = Doctor::where('slug', $r->doctor)
        ->with('schedules')
        ->firstOrFail();

    $monday = now()->startOfWeek();

    // Si el servicio devuelve null, lo convertimos en []
    $freeSlots = app('availability')->weeklyFreeSlots($doctor, $monday) ?? [];

    // Citas del doctor (solo pending & confirmed)
    $apps = Appointment::where('doctor_id', $doctor->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->get(['start_at', 'end_at', 'status']);

    $appointmentEvents = $apps->map(fn ($a) => [
        'start' => $a->start_at->toIso8601String(),
        'end'   => $a->end_at->toIso8601String(),
        'title' => $a->status === 'pending'
            ? 'Cita pendiente'
            : 'Cita confirmada',
        'class' => $a->status,
    ]);

    $availableEvents = collect($freeSlots)->map(function ($s) {
        $start = $s['start'] instanceof \Carbon\Carbon ? $s['start']->toIso8601String() : $s['start'];
        $end   = $s['end']   instanceof \Carbon\Carbon ? $s['end']->toIso8601String()   : $s['end'];

        return [
            'start' => $start,
            'end'   => $end,
            // ponemos título vacío para que NO se vea "Disponible"
            'title' => '',
            'class' => 'available',
        ];
    });

    return response()->json(
        $appointmentEvents->merge($availableEvents)->values()
    );
});



Route::get('/admin/calendar', function (Request $r) {
    $doctor = Doctor::where('slug', $r->doctor)
        ->with('schedules')
        ->firstOrFail();

    $monday = now()->startOfWeek();

    // Huecos libres de la semana
    $availableSlots = app('availability')->weeklyFreeSlots($doctor, $monday);

    // Citas del doctor (solo pending & confirmed)
    $apps = Appointment::where('doctor_id', $doctor->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->get(['start_at', 'end_at', 'status']);

    // Eventos de citas
    $events = $apps->map(fn ($a) => [
        'start' => $a->start_at->toIso8601String(),
        'end'   => $a->end_at->toIso8601String(),
        'title' => $a->status === 'pending'
            ? 'Pendiente'
            : 'Confirmada',
        'class' => $a->status,
    ]);

    // Huecos disponibles
    $gaps = collect($availableSlots)->map(fn ($s) => [
        'start' => $s['start'],
        'end'   => $s['end'],
        'title' => 'Disponible',
        'class' => 'available',
    ]);

    $all = $events->merge($gaps)->values();

    return response()->json($all);
})->middleware('auth:sanctum');   


// =======================
//  RESERVA DIRECTA DESDE EL CALENDARIO ADMIN
//    Similar a PublicController::store pero usando el usuario autenticado
// =======================
Route::post('/admin/appointments', function (Request $r) {
    $data = $r->validate([
        'doctor_slug' => 'required|exists:doctors,slug',
        'start'       => 'required|date',
    ]);

    $doctor = Doctor::where('slug', $data['doctor_slug'])->firstOrFail();
    $user   = auth()->user(); 

    if (! $user) {
        return response()->json(['error' => 'No autenticado'], 401);
    }

    $duration = (int) config('appointments.duration');

    $start = now()->parse($data['start']);
    $end   = (clone $start)->addMinutes($duration);

    // Verificamos disponibilidad exacta igual que en el público
    $available = app('availability')->isSlotAvailable($doctor->id, $start, $end);

    if (! $available) {
        return response()->json(['error' => 'Horario no disponible'], 422);
    }

    $appointment = Appointment::create([
        'doctor_id'      => $doctor->id,
        'patient_name'   => $user->name,
        'patient_email'  => $user->email,
        'patient_phone'  => null,
        'start_at'       => $start,
        'end_at'         => $end,
        'status'         => 'pending',
    ]);

    \Mail::to($appointment->patient_email)
        ->send(new \App\Mail\AppointmentCreated($appointment));

    return response()->json([
        'success'     => true,
        'appointment' => $appointment,
    ], 201);
})->middleware('auth:sanctum');
