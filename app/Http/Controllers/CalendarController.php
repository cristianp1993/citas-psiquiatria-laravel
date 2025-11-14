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

        return Inertia::render('Admin/Calendar', ['doctor' => $doctor]);
    }
}