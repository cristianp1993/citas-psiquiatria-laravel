<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cita confirmada</title>
</head>
<body>
    <h1>¡Cita confirmada!</h1>

    <p>Hola {{ $appointment->patient_name }},</p>

    <p>
        Tu cita con el Dr. {{ $appointment->doctor->name }} ha sido
        <strong>confirmada</strong>.
    </p>

    <ul>
        <li><strong>Fecha:</strong> {{ $appointment->start_at->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ $appointment->start_at->format('H:i') }}</li>
    </ul>

    <p>Gracias por confiar en nosotros.<br>
    {{ config('app.name') }}</p>
</body>
</html>
