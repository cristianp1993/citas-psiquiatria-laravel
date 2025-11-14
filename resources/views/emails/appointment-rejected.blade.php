<x-mail::message>
# Cita no confirmada

Hola {{ $appointment->patient_name }},

Lamentamos informarte que **no podemos confirmar** tu cita solicitada con el **Dr. {{ $appointment->doctor->name }}**.

- **Fecha solicitada:** {{ $appointment->start_at->format('d/m/Y') }} a las {{ $appointment->start_at->format('H:i') }}

Puedes intentar agendar una nueva cita desde nuestro sitio web.

Agradecemos tu interés y disculpamos las molestias.<br>
{{ config('app.name') }}
</x-mail::message>