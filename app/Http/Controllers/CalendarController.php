<?php

namespace App\Http\Controllers;

use App\Models\{Doctor, Appointment};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function dashboard(Request $request)
    {
        $doctorSlug = $request->doctor;

        $doctors = Doctor::orderBy('name')->get(['id', 'name', 'slug']);

      
        $pending = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'pending')
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        $upcoming = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'confirmed')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->limit(10)
            ->get();

      
        $rejected = Appointment::with('doctor')
            ->when($doctorSlug, function ($q) use ($doctorSlug) {
                $q->whereHas('doctor', fn ($d) => $d->where('slug', $doctorSlug));
            })
            ->where('status', 'rejected')
            ->orderBy('start_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'doctors'  => $doctors,
            'pending'  => $pending,
            'upcoming' => $upcoming,
            'rejected' => $rejected,
            'filters'  => [
                'doctor' => $doctorSlug,
            ],
        ]);
    }

    public function adminCalendar(Request $request)
    {
        
        $doctors = Doctor::orderBy('name')->get(['id', 'name', 'slug']);

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


    public function availability(Request $request)
    {
        try {
            $slug = $request->query('doctor');

            $doctor = Doctor::with('schedules')
                ->where('slug', $slug)
                ->first();

            if (! $doctor) {
                return response()->json([]);
            }

            $monday = now()->startOfWeek();
            $endDate = $monday->copy()->addWeeks(8);

            $freeSlots = [];
            try {
                
    if ($doctor->schedules && $doctor->schedules->count() > 0) {
        $current = clone $monday;
        while ($current->lessThanOrEqualTo($endDate)) {
            try {
                $weeklySlots = app('availability')->weeklyFreeSlots($doctor, $current) ?? [];
                $freeSlots = array_merge($freeSlots, $weeklySlots);
            } catch (\Throwable $e) {
                \Log::error('Error en weeklyFreeSlots para semana', [
                    'doctor_id'   => $doctor->id,
                    'doctor_slug' => $doctor->slug,
                    'week_start'  => $current->toDateString(),
                    'message'     => $e->getMessage(),
                ]);
            }
            $current->addWeek();
        }
    }
        } catch (\Throwable $e) {
            \Log::error('Error en weeklyFreeSlots (availability)', [
                'doctor_id'   => $doctor->id,
                'doctor_slug' => $doctor->slug,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);
            $freeSlots = [];
        }

        $appointments = collect();
        try {
            $statuses = ['pending', 'confirmed'];

            if ($request->is('admin/*')) {
                $statuses[] = 'rejected';
            }

            $appointments = Appointment::where('doctor_id', $doctor->id)
                ->whereIn('status', $statuses)
                ->whereNotNull('start_at')
                ->whereNotNull('end_at')
                ->orderBy('start_at')
                ->get(['id', 'start_at', 'end_at', 'status']);
        } catch (\Throwable $e) {
            \Log::error('Error cargando citas en availability', [
                'doctor_id'   => $doctor->id,
                'doctor_slug' => $doctor->slug,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);
            $appointments = collect();
        }

        $appointmentEvents = $appointments->map(function ($a) {
            $title = $a->status === 'pending'
                ? 'Cita pendiente'
                : ($a->status === 'confirmed'
                    ? 'Cita confirmada'
                    : 'Cita rechazada');

            return [
                'start' => $a->start_at->toIso8601String(),
                'end'   => $a->end_at->toIso8601String(),
                'title' => $title,
                'class' => $a->status,
            ];
        });

        $availableEvents = collect($freeSlots)->map(function ($s) {
            $start = $s['start'] instanceof Carbon
                ? $s['start']
                : Carbon::parse($s['start']);

            $end = $s['end'] instanceof Carbon
                ? $s['end']
                : Carbon::parse($s['end']);

            
            $appTimezone = config('app.timezone', 'UTC');
            $startInAppTz = $start->clone()->setTimezone($appTimezone);
            $endInAppTz = $end->clone()->setTimezone($appTimezone);

            return [
                'start' => $startInAppTz->toIso8601String(),
                'end'   => $endInAppTz->toIso8601String(),
                'title' => 'Disponible',
                'class' => 'available',
            ];
        });
        
        return response()->json(
            $appointmentEvents->concat($availableEvents)->values()
        );
    } catch (\Throwable $e) {
        \Log::error('Error general en availability', [
            'doctor_slug' => $request->query('doctor'),
            'path'        => $request->path(),
            'message'     => $e->getMessage(),
            'file'        => $e->getFile(),
            'line'        => $e->getLine(),
        ]);

        return response()->json([]);
    }
    }

    
    public function calendarData(Request $request)
    {
        return $this->availability($request);
    }
}
