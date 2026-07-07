<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Oportunidad de Colaboración</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>Hola {{ $prospecto->director_nombre ?: 'equipo' }},</p>

    <p>He estado analizando el trabajo que hacen en <strong>{{ $prospecto->empresa }}</strong> y creo que hay una gran oportunidad para colaborar y potenciar sus resultados.</p>

    <p>Me encantaría tener una breve llamada de 10 minutos para explorar si hay sinergia. ¿Tendrás disponibilidad esta semana?</p>

    <p>Saludos cordiales,</p>
    <p><strong>El Equipo</strong></p>

    {{-- Tracking Pixel --}}
    @if($prospecto->tracking_uuid)
        <img src="{{ url('/track/' . $prospecto->tracking_uuid) }}" width="1" height="1" alt="" style="display:none;" />
    @endif
</body>
</html>
