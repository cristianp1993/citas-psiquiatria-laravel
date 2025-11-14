<?php

namespace App\Services;

use App\Models\{Doctor, DoctorSchedule, Appointment};
use Carbon\Carbon;

class AvailabilityService
{
    public function isSlotAvailable(int $doctorId, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        $weekday = (int) $start->format('N');
        $inSchedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'))
            ->exists();

        if (!$inSchedule) {
            return false;
        }

        $conflict = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, (clone $end)->subSecond()])
                  ->orWhereBetween('end_at', [(clone $start)->addSecond(), $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->where('start_at', '<=', $start)->where('end_at', '>=', $end);
                  });
            })->exists();

        return !$conflict;
    }

    public function weeklyFreeSlots(Doctor $doctor, \DateTimeInterface $weekStart): array
    {
        $duration = config('appointments.duration');
        $slots = [];

        foreach ($doctor->schedules as $sch) {
            $day = (clone $weekStart)->modify('+' . ($sch->weekday - 1) . ' days');
            $start = Carbon::parse($day->format('Y-m-d') . ' ' . $sch->start_time, $day->getTimezone());
            $end = Carbon::parse($day->format('Y-m-d') . ' ' . $sch->end_time, $day->getTimezone());

            for ($t = $start->copy(); $t->lt($end); $t = $t->copy()->addMinutes($duration)) {
                $slotEnd = $t->copy()->addMinutes($duration);
                if ($slotEnd->gt($end)) {
                    break;
                }
                if ($this->isSlotAvailable($doctor->id, $t, $slotEnd)) {
                    $slots[] = [
                        'start' => $t->toIso8601String(),
                        'end' => $slotEnd->toIso8601String()
                    ];
                }
            }
        }

        return $slots;
    }
}