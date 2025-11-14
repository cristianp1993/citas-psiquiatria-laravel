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
        // Implement create
    }

    public function store(Request $request)
    {
        // Implement store
    }

    public function show(Doctor $doctor)
    {
        // Implement show
    }

    public function edit(Doctor $doctor)
    {
        // Implement edit
    }

    public function update(Request $request, Doctor $doctor)
    {
        // Implement update
    }

    public function destroy(Doctor $doctor)
    {
        // Implement destroy
    }
}