<?php

namespace App\Http\Controllers;

use App\Models\{Doctor,Appointment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PublicController extends Controller {
    public function index() {
    $doctors = Doctor::where('is_active',true)
        ->with('specialty')
        ->get(['id','name','slug','specialty_id']);

    return Inertia::render('Public/Index', [
        'doctors'  => $doctors,
        'duration' => config('appointments.duration'), // <- importante
    ]);
}

    public function doctor(Doctor $doctor) {
        return Inertia::render('Public/Doctor', [
            'doctor'=>$doctor,
            'duration' => config('appointments.duration')
        ]);
    }

    public function new(Request $request) {
        $request->validate(['doctor'=>'required','start'=>'required|date']);
        $doctor = Doctor::where('slug',$request->doctor)->firstOrFail();
        return Inertia::render('Public/NewAppointment', [
            'doctor'=>$doctor,
            'start'=>$request->start
        ]);
    }

    public function store(Request $request) {
    $data = $request->validate([
        'doctor_id'     => 'required|exists:doctors,id',
        'patient_name'  => 'required|string|max:120',
        'patient_email' => 'required|email',
        'patient_phone' => 'nullable|string|max:30',
        'start_at'      => 'required|date',
        'end_at'        => 'required|date|after:start_at', // <- nuevo
    ]);

    $start = now()->parse($data['start_at']);
    $end   = now()->parse($data['end_at']);

    // si quieres, puedes aquí validar que la diferencia coincida con tu duración config
    // $expectedDuration = (int) config('appointments.duration');
    // if ($start->diffInMinutes($end) !== $expectedDuration) { ... }

    $available = app('availability')->isSlotAvailable($data['doctor_id'], $start, $end);
    if (!$available) {
        return back()->withErrors(['start_at' => 'Horario no disponible'])->withInput();
    }

    $appointment = DB::transaction(function () use ($data, $start, $end) {
        return Appointment::create([
            'doctor_id'      => $data['doctor_id'],
            'patient_name'   => $data['patient_name'],
            'patient_email'  => $data['patient_email'],
            'patient_phone'  => $data['patient_phone'] ?? null,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => 'pending',
        ]);
    });

    \Mail::to($appointment->patient_email)->send(new \App\Mail\AppointmentCreated($appointment));

    return redirect()->route('public.index')->with('ok','Cita reservada en estado pendiente');
}

}
