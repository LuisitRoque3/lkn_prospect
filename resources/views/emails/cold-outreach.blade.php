<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certeza operativa y el crecimiento de {{ $prospecto->empresa }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>Hola {{ $prospecto->director_nombre ?: 'equipo' }},</p>

    <p>He seguido de cerca el trabajo de <strong>{{ $prospecto->empresa }}</strong> y es evidente que han logrado escalar de manera importante.</p>

    <p>Sin embargo, en mi experiencia analizando operaciones, cuando una empresa crece a este ritmo casi siempre llega a un punto de quiebre estructural: el volumen del negocio supera su forma de organizar la información. Cuando esto ocurre, las proyecciones financieras y operativas empiezan a depender de la intuición o de cruzar datos manualmente en hojas de cálculo que ya no dan abasto.</p>

    <p>En Locknode nos especializamos exactamente en resolver este cuello de botella. No ofrecemos estimaciones genéricas ni tecnología que arroja alucinaciones; implementamos una infraestructura de control que estructura matemática aplicada sobre tus datos reales para darte certeza absoluta del estado de tu operación y hacia dónde se proyecta.</p>

    <p>Me encantaría tener una breve llamada de 10 minutos para mostrarte cómo funciona nuestro modelo de control. ¿Cómo se ve tu agenda para la próxima semana?</p>
    
    <p>Si estabilizar el flujo de datos no es la máxima prioridad del equipo en este momento, lo entiendo perfectamente.</p>

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
