<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cita no confirmada</title>
</head>
<body>
    <h1>Cita no confirmada</h1>

    <p>Hola {{ $appointment->patient_name }},</p>

    <p>
        Lamentamos informarte que <strong>no podemos confirmar</strong> tu cita
        solicitada con el Dr. {{ $appointment->doctor->name }}.
    </p>

    <ul>
        <li><strong>Fecha solicitada:</strong> {{ $appointment->start_at->format('d/m/Y') }}</li>
        <li><strong>Hora solicitada:</strong> {{ $appointment->start_at->format('H:i') }}</li>
    </ul>

    <p>
        Puedes intentar agendar una nueva cita desde nuestro sitio web.
    </p>

    <p>
        Agradecemos tu interés y lamentamos las molestias.<br>
        {{ config('app.name') }}
    </p>
</body>
</html>
