<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('specialty')->get();
        return Inertia::render('Admin/Doctors/Index', ['doctors' => $doctors]);
    }

    public function create()
    {
        $specialties = \App\Models\Specialty::all();
        return Inertia::render('Admin/Doctors/Create', ['specialties' => $specialties]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'specialty_id' => 'required|exists:specialties,id',
            'is_active' => 'boolean'
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name'] . '-' . \Illuminate\Support\Str::random(5));
        $data['is_active'] = $data['is_active'] ?? true;

        Doctor::create($data);

        return redirect()->route('doctors.index')->with('success', 'Doctor creado exitosamente.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load('specialty', 'schedules');
        return Inertia::render('Admin/Doctors/Show', ['doctor' => $doctor]);
    }

    public function edit(Doctor $doctor)
    {
        $doctor->load('specialty', 'schedules'); 
        $specialties = \App\Models\Specialty::all();

        return Inertia::render('Admin/Doctors/Edit', [
            'doctor' => $doctor,
            'specialties' => $specialties,
        ]);
    }

    public function update(Request $request, Doctor $doctor)
{
    $data = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|unique:doctors,email,' . $doctor->id,
        'specialty_id' => 'required|exists:specialties,id',
        'is_active'    => 'boolean',
        'schedules'    => 'array',
        'schedules.*.weekday'    => 'nullable|integer|min:1|max:7',
        'schedules.*.start_time' => 'nullable|date_format:H:i',
        'schedules.*.end_time'   => 'nullable|date_format:H:i',
    ]);

    if (!isset($data['is_active'])) {
        $data['is_active'] = false;
    }

    if ($doctor->name !== $data['name']) {
        $data['slug'] = \Illuminate\Support\Str::slug($data['name'] . '-' . \Illuminate\Support\Str::random(5));
    }

    $doctor->update($data);
    
    $doctor->schedules()->delete();

    foreach ($request->input('schedules', []) as $schedule) {
        // Si el usuario deja vacío, saltamos
        if (empty($schedule['weekday']) || empty($schedule['start_time']) || empty($schedule['end_time'])) {
            continue;
        }

        $doctor->schedules()->create([
            'weekday'    => $schedule['weekday'],
            'start_time' => $schedule['start_time'],
            'end_time'   => $schedule['end_time'],
        ]);
    }

    return redirect()->route('doctors.index')->with('success', 'Doctor actualizado exitosamente.');
}

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('doctors.index')->with('success', 'Doctor eliminado exitosamente.');
    }
}