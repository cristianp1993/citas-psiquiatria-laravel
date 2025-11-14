<x-mail::message>
# ¡Cita registrada!

Hola {{ $appointment->patient_name }},

Tu solicitud de cita con el **Dr. {{ $appointment->doctor->name }}** se ha registrado correctamente.

- **Especialidad:** {{ $appointment->doctor->specialty->name ?? 'No especificada' }}
- **Fecha y hora:** {{ $appointment->start_at->format('d/m/Y') }} a las {{ $appointment->start_at->format('H:i') }}
- **Duración:** {{ config('appointments.duration', 30) }} minutos

Tu cita está actualmente en estado **pendiente**.  
Recibirás una confirmación por correo una vez sea aprobada.

Gracias por confiar en nosotros.<br>
{{ config('app.name') }}
</x-mail::message>