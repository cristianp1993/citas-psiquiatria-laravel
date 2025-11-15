<?php

namespace App\Http\Controllers;

use App\Models\{Doctor, Appointment};
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function dashboard(Request $request)
    {
        $doctors = Doctor::orderBy('name')->get(['id', 'name', 'slug']);
        $pending = Appointment::with('doctor')
            ->where('status', 'pending')
            ->orderBy('start_at')
            ->limit(10)
            ->get();
        $upcoming = Appointment::with('doctor')
            ->where('status', 'confirmed')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', compact('doctors', 'pending', 'upcoming'));
    }

    public function adminCalendar(Request $request)
    {
        $doctor = $request->doctor
            ? Doctor::where('slug', $request->doctor)->first()
            : null;

        return Inertia::render('Admin/Calendar', [
            'doctor' => $doctor,
            'duration' => config('appointments.duration')
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