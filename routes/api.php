<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\{Doctor, Appointment};

Route::get('/public/availability', function (Request $r) {
    $doctor = Doctor::where('slug', $r->doctor)
        ->with('schedules')
        ->firstOrFail();

    $monday = now()->startOfWeek();

    $freeSlots = app('availability')->weeklyFreeSlots($doctor, $monday) ?? [];

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
            'title' => '',
            'class' => 'available',
        ];
    });

    return response()->json(
        $appointmentEvents->merge($availableEvents)->values()
    );
});
