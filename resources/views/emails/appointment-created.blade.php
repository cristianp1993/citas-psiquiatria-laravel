<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cita registrada</title>
</head>
<body>
    <h1>¡Cita registrada!</h1>

    <p>Hola {{ $appointment->patient_name }},</p>

    <p>
        Tu solicitud de cita con el
        <strong>Dr. {{ $appointment->doctor->name }}</strong>
        se ha registrado correctamente.
    </p>

    <ul>
        <li><strong>Especialidad:</strong> {{ $appointment->doctor->specialty->name ?? 'No especificada' }}</li>
        <li><strong>Fecha:</strong> {{ $appointment->start_at->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ $appointment->start_at->format('H:i') }}</li>
        <li><strong>Duración:</strong> {{ config('appointments.duration', 30) }} minutos</li>
        <li><strong>Estado:</strong> Pendiente de confirmación</li>
    </ul>

    <p>
        Recibirás una confirmación por correo una vez sea aprobada.
    </p>

    <p>Gracias por confiar en nosotros.<br>
    {{ config('app.name') }}</p>
</body>
</html>
