<x-mail::message>
# ¡Cita confirmada!

Hola {{ $appointment->patient_name }},

Tu cita con el Dr. {{ $appointment->doctor->name }} ha sido confirmada.

- **Fecha:** {{ $appointment->start_at->format('d/m/Y') }}
- **Hora:** {{ $appointment->start_at->format('H:i') }}

Gracias por confiar en nosotros.

Atentamente,  
{{ config('app.name') }}
</x-mail::message>