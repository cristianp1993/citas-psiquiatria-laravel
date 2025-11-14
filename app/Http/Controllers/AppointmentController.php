<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller {
    public function index(Request $request){
        $q = Appointment::with('doctor')
            ->when($request->doctor, fn($qq)=>$qq->whereHas('doctor',fn($d)=>$d->where('slug',$request->doctor)))
            ->orderBy('start_at','asc');
        return inertia('Admin/Appointments', ['appointments'=>$q->paginate(20)]);
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
