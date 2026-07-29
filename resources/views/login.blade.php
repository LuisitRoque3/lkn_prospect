<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Iniciar Sesión - Locknode CRM</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#a3583d">
    <link rel="apple-touch-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#fdfaf6] text-[#3d2b1f] antialiased min-h-screen relative"
      x-data="{ isOffline: !navigator.onLine, showOnlineToast: false }"
      @online.window="isOffline = false; showOnlineToast = true; setTimeout(() => showOnlineToast = false, 3000)"
      @offline.window="isOffline = true">
    <livewire:auth.login />

    <!-- Offline Notification Toast -->
    <div x-show="isOffline" 
         x-transition
         class="fixed bottom-6 left-4 right-4 sm:left-4 sm:right-auto sm:w-72 z-[9999] bg-amber-50 border border-amber-200 rounded-2xl p-3 shadow-lg flex items-center gap-2.5"
         x-cloak>
        <span class="text-lg">⚠️</span>
        <div>
            <p class="text-[10px] font-black text-amber-800 uppercase tracking-wider">Sin Conexión</p>
            <p class="text-[9px] text-amber-700/80 font-bold">Modo lectura activo. Se requiere red para ingresar.</p>
        </div>
    </div>

    <!-- Back Online Notification Toast -->
    <div x-show="showOnlineToast" 
         x-transition
         class="fixed bottom-6 left-4 right-4 sm:left-4 sm:right-auto sm:w-72 z-[9999] bg-emerald-50 border border-emerald-200 rounded-2xl p-3 shadow-lg flex items-center gap-2.5"
         x-cloak>
        <span class="text-lg text-emerald-600">✓</span>
        <div>
            <p class="text-[10px] font-black text-emerald-800 uppercase tracking-wider">Conexión Restablecida</p>
            <p class="text-[9px] text-emerald-700/80 font-bold">Sincronización online activada.</p>
        </div>
    </div>

    @livewireScripts

    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registrado con éxito', reg))
                    .catch(err => console.error('Error al registrar Service Worker', err));
            });
        }
    </script>
</body>
</html>
