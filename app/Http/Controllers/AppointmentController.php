<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller {
    public function index(Request $request)
{
    $q = Appointment::with('doctor')
        ->when($request->doctor, fn($qq) => $qq->whereHas('doctor', fn($d) => $d->where('slug', 'like', '%' . $request->doctor . '%')))
        ->when($request->status, fn($qq) => $qq->where('status', $request->status))
        ->orderBy('start_at', 'asc');

    return inertia('Admin/Appointments/Index', [
        'appointments' => $q->paginate(20),
        'filters' => $request->only(['doctor', 'status'])
    ]);
}

    public function show(Appointment $appointment){
        $appointment->load('doctor');
        return inertia('Admin/Appointments/Show', ['appointment' => $appointment]);
    }

    public function update(Request $request, Appointment $appointment){
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,rejected'
        ]);
        $appointment->update($data);
        return redirect()->route('appointments.index')->with('success', 'Cita actualizada.');
    }

    public function destroy(Appointment $appointment){
        $appointment->delete();
        return redirect()->route('appointments.index')->with('success', 'Cita eliminada.');
    }

    public function accept(Appointment $appointment){
        if($appointment->status!=='pending') return back();
        $appointment->update(['status'=>'confirmed']);
        \Mail::to($appointment->patient_email)->send(new \App\Mail\AppointmentAccepted($appointment));
        return back();
    }

    public function reject(Appointment $appointment){
        if(!in_array($appointment->status,['pending','confirmed'])) return back();
        $appointment->update(['status'=>'rejected']);
        \Mail::to($appointment->patient_email)->send(new \App\Mail\AppointmentRejected($appointment));
        return back();
    }
}
