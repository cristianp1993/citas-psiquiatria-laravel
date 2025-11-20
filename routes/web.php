<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;

// Rutas públicas


Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/doctors/{doctor:slug}', [PublicController::class, 'doctor'])->name('public.doctor');
Route::get('/appointments/new', [PublicController::class, 'new'])->name('public.appointments.new');
Route::post('/appointments', [PublicController::class, 'store'])->name('public.appointments.store');

// Rutas protegidas (auth + verified)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard / Home
    Route::get('/dashboard', [CalendarController::class, 'dashboard'])->name('dashboard');
    Route::get('/home', [CalendarController::class, 'dashboard'])->name('home');

    // Todo lo de admin bajo /admin
    Route::prefix('admin')->group(function () {

        // Vista de calendario admin 
        Route::get('/calendar', [CalendarController::class, 'adminCalendar'])
            ->name('admin.calendar');

        // JSON para VueCal admin
        Route::get('/calendar-data', [CalendarController::class, 'calendarData'])
            ->name('admin.calendar.data');

        // Crear cita desde el calendario admin
        Route::post('/appointments', function (Request $r) {
            $data = $r->validate([
                'doctor_slug' => 'required|exists:doctors,slug',
                'start'       => 'required|date',
            ]);

            $doctor = \App\Models\Doctor::where('slug', $data['doctor_slug'])->firstOrFail();
            $user   = auth()->user();

            if (! $user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $duration = (int) config('appointments.duration');

            $start = now()->parse($data['start']);
            $end   = (clone $start)->addMinutes($duration);

            $available = app('availability')->isSlotAvailable($doctor->id, $start, $end);

            if (! $available) {
                return response()->json(['error' => 'Horario no disponible'], 422);
            }

            $appointment = \App\Models\Appointment::create([
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
        })->name('admin.appointments.store');

        // CRUD Doctores
        Route::resource('doctors', DoctorController::class);

        // Listado / show / update / delete de citas
        Route::resource('appointments', AppointmentController::class)
            ->only(['index', 'show', 'update', 'destroy']);

        // Aceptar / rechazar cita (HTML normal)
        Route::post('/appointments/{appointment:slug}/accept', [AppointmentController::class, 'accept'])
            ->name('appointments.accept');

        Route::post('/appointments/{appointment:slug}/reject', [AppointmentController::class, 'reject'])
            ->name('appointments.reject');
    });
});
