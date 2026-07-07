<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certeza operativa y el crecimiento de {{ $prospecto->empresa }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>Hola {{ $prospecto->director_nombre ?: 'equipo' }},</p>

    <p>He notado el rápido crecimiento de <strong>{{ $prospecto->empresa }}</strong>. En mi experiencia, al escalar a este ritmo, casi siempre se llega a un punto crítico: el volumen del negocio supera su capacidad para organizar la información, y las proyecciones terminan dependiendo de la intuición o de hojas de cálculo saturadas.</p>

    <p>En Locknode resolvemos este cuello de botella. Implementamos infraestructura de control basada en matemática aplicada —sin estimaciones genéricas ni "alucinaciones"— para darte certeza absoluta sobre el estado real de tu operación y sus proyecciones.</p>

    <p>¿Tendrías 10 minutos la próxima semana para mostrarte cómo opera nuestro modelo? Si estabilizar sus datos no es prioridad ahora mismo, lo entiendo perfectamente.</p>

    <p>Saludos,</p>
    <p>
        <strong>Luis Angel Roque</strong><br>
        Locknode<br>
        4423182079
    </p>

    {{-- Tracking Pixel --}}
    @if($prospecto->tracking_uuid)
        <img src="{{ url('/track/' . $prospecto->tracking_uuid) }}" width="1" height="1" alt="" style="display:none;" />
    @endif
</body>
</html>
