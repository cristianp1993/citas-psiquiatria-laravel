<?php

namespace App\Http\Controllers;

use App\Models\{Doctor, Appointment};
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function dashboard(Request $request)
    {
        $doctorSlug = $request->doctor;

        $doctors = Doctor::orderBy('name')->get(['id', 'name', 'slug']);

        // Pendientes
        $pending = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'pending')
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        // Próximas confirmadas
        $upcoming = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'confirmed')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        // (Opcional) rechazadas, por si quieres la 3ª card:
        $rejected = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'rejected')
            ->orderBy('start_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'doctors' => $doctors,
            'pending' => $pending,
            'upcoming' => $upcoming,
            'rejected' => $rejected,
            'filters' => [
                'doctor' => $doctorSlug,
            ],
        ]);
    }

    public function adminCalendar(Request $request)
{
    // Todos los doctores para el selector
    $doctors = Doctor::orderBy('name')->get(['id', 'name', 'slug']);

    // Slug seleccionado (si viene por query) o el primero
    $selectedSlug = $request->doctor ?? ($doctors->first()->slug ?? null);

    $doctor = $selectedSlug
        ? Doctor::where('slug', $selectedSlug)->first()
        : null;

    return Inertia::render('Admin/Calendar', [
        'doctors'  => $doctors,
        'doctor'   => $doctor,
        'duration' => config('appointments.duration'),
        'filters'  => [
            'doctor' => $selectedSlug,
        ],
    ]);
}

    public function calendarData(Request $request)
    {
        $doctor = Doctor::where('slug', $request->doctor)->firstOrFail();

        // Para simplificar, devolvemos todas las citas (pendientes y confirmadas)
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->orderBy('start_at')
            ->get();

        $events = $appointments->map(function ($a) {
            return [
                'start' => $a->start_at->toIso8601String(),
                'end'   => $a->end_at->toIso8601String(),
                'title' => $a->patient_name,
                'class' => $a->status, // 'pending' o 'confirmed'
            ];
        });

        return response()->json($events);
    }
}