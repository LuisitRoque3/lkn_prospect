<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Dashboard - Locknode CRM</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#a3583d">
    <link rel="apple-touch-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#fdfaf6] text-[#3d2b1f] antialiased min-h-dvh pb-safe" 
      x-data="{ 
          activeTab: 'leads', 
          isKeyboardOpen: false, 
          isOffline: !navigator.onLine, 
          showOnlineToast: false,
          deferredPrompt: null, 
          showIOSPrompt: false 
      }"
      @focusin="isKeyboardOpen = ['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName)" 
      @focusout="setTimeout(() => { isKeyboardOpen = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) }, 50)"
      @online.window="isOffline = false; showOnlineToast = true; setTimeout(() => showOnlineToast = false, 3000)"
      @offline.window="isOffline = true"
      @beforeinstallprompt.window="event.preventDefault(); deferredPrompt = event">
    
    <!-- NAVIGATION BAR -->
    <nav class="bg-white border-b border-[#3d2b1f]/10 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                
                <!-- Logo & Tabs (Left) -->
                <div class="flex items-center gap-6">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-[#a3583d] bg-[#a3583d]/10 px-3 py-1 rounded-full whitespace-nowrap">
                        Locknode CRM
                    </span>
                    
                    <!-- Navegación por pestañas (Desktop) -->
                    <div class="hidden sm:flex space-x-2">
                        <button @click="activeTab = 'leads'"
                                :class="activeTab === 'leads' ? 'bg-[#a3583d] text-white' : 'text-[#3d2b1f]/70 hover:bg-gray-100'"
                                class="px-4 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all">
                            📊 Leads CRM
                        </button>
                        @if(auth()->user()->is_admin)
                            <button @click="activeTab = 'config'"
                                    :class="activeTab === 'config' ? 'bg-[#a3583d] text-white' : 'text-[#3d2b1f]/70 hover:bg-gray-100'"
                                    class="px-4 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all">
                                ⚙️ Configuración
                            </button>
                        @endif
                    </div>
                </div>

                <!-- User Profile & Logout (Right) -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right">
                        <div class="flex items-center gap-1.5 justify-end">
                            @if(auth()->user()->is_admin)
                                <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[8px] font-black uppercase rounded">Admin</span>
                            @endif
                            <p class="text-xs font-black uppercase text-[#3d2b1f] leading-none">{{ auth()->user()->name }}</p>
                        </div>
                        <p class="text-[10px] text-[#3d2b1f]/50 font-semibold mt-0.5">{{ auth()->user()->email }}</p>
                    </div>
                    
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 border border-[#3d2b1f]/10 rounded-xl text-[10px] font-black uppercase tracking-wider text-red-700 hover:bg-red-50 hover:border-red-200 transition-all">
                            <span>Salir</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
        
    </nav>

    <!-- MAIN BODY -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 pb-32 sm:pb-6">
        
        <!-- Prospectos (CRM) Tab -->
        <div x-show="activeTab === 'leads'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <livewire:prospectos />
        </div>
        
        <!-- Configuración (Cron) Tab -->
        @if(auth()->user()->is_admin)
            <div x-show="activeTab === 'config'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <livewire:configurador />
            </div>
        @endif

    </main>

    <!-- BOTTOM TAB BAR (MOBILE-FIRST) - LOCKSPEND STYLE -->
    <div class="sm:hidden fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-lg z-50 bg-white border-t border-[#3d2b1f]/10 pb-safe shadow-[0_-4px_16px_rgba(0,0,0,0.06)] flex justify-around items-center py-3 px-6"
         x-show="!isKeyboardOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4">
        <!-- Leads Tab -->
        <button type="button" 
                @click="activeTab = 'leads'"
                :class="activeTab === 'leads' ? 'text-[#a3583d] scale-105 font-black' : 'text-[#3d2b1f]/60 font-semibold'"
                class="flex flex-col items-center gap-1 cursor-pointer transition-all duration-200">
            <span class="text-xl">📊</span>
            <span class="text-[8px] font-black uppercase tracking-wider">Leads</span>
        </button>
        @if(auth()->user()->is_admin)
            <!-- Configuración Tab -->
            <button type="button" 
                    @click="activeTab = 'config'"
                    :class="activeTab === 'config' ? 'text-[#a3583d] scale-105 font-black' : 'text-[#3d2b1f]/60 font-semibold'"
                    class="flex flex-col items-center gap-1 cursor-pointer transition-all duration-200">
                <span class="text-xl">⚙️</span>
                <span class="text-[8px] font-black uppercase tracking-wider">Configurar</span>
            </button>
        @endif
    </div>

    <!-- PWA Install Banner (Android/Desktop) -->
    <div x-show="deferredPrompt && !isKeyboardOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-10 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-4 sm:w-80 z-[9999] bg-white border border-[#a3583d]/20 rounded-2xl p-4 shadow-xl flex items-center justify-between gap-3"
         x-cloak>
        <div class="flex-1">
            <p class="text-xs font-black text-[#3d2b1f] uppercase tracking-wide">📲 Instalar Locknode CRM</p>
            <p class="text-[10px] text-[#3d2b1f]/60 font-semibold mt-0.5">Agrégalo a tu pantalla de inicio para una experiencia fluida.</p>
        </div>
        <div class="flex gap-2">
            <button @click="deferredPrompt = null" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-[9px] font-black uppercase text-gray-500">
                Luego
            </button>
            <button @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(choice => { if(choice.outcome === 'accepted') { deferredPrompt = null } })" 
                    class="px-2.5 py-1.5 bg-[#a3583d] text-white rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm">
                Instalar
            </button>
        </div>
    </div>

    <!-- PWA iOS Install Banner -->
    <div x-show="showIOSPrompt && !isKeyboardOpen" 
         x-transition
         class="fixed inset-0 z-[9999] bg-black/40 backdrop-blur-sm flex items-end justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm space-y-4 border border-[#3d2b1f]/10 shadow-2xl">
            <div class="text-center space-y-2">
                <span class="text-3xl">📲</span>
                <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]">Instalar en iOS</h3>
                <p class="text-[11px] text-[#3d2b1f]/75 font-semibold leading-relaxed">
                    Para instalar Locknode CRM en tu iPhone o iPad:
                </p>
                <div class="bg-[#fdfaf6] p-3 rounded-xl text-left text-[10px] space-y-2 border border-gray-100">
                    <p class="font-medium flex items-center gap-1.5">
                        <span class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">1</span> 
                        Presiona el botón <strong>Compartir</strong> <span class="text-xs">📤</span> abajo en tu navegador.
                    </p>
                    <p class="font-medium flex items-center gap-1.5">
                        <span class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">2</span> 
                        Selecciona <strong>Agregar a Inicio</strong> <span class="text-xs">➕</span> en la lista de opciones.
                    </p>
                </div>
            </div>
            <button @click="showIOSPrompt = false" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-[#3d2b1f] text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                Entendido
            </button>
        </div>
    </div>

    <!-- Offline Notification Toast -->
    <div x-show="isOffline" 
         x-transition
         class="fixed bottom-20 left-4 right-4 sm:left-4 sm:right-auto sm:w-72 z-[9999] bg-amber-50 border border-amber-200 rounded-2xl p-3 shadow-lg flex items-center gap-2.5"
         x-cloak>
        <span class="text-lg">⚠️</span>
        <div>
            <p class="text-[10px] font-black text-amber-800 uppercase tracking-wider">Sin Conexión</p>
            <p class="text-[9px] text-amber-700/80 font-bold">Modo lectura activo. Se requiere red para cambios.</p>
        </div>
    </div>

    <!-- Back Online Notification Toast -->
    <div x-show="showOnlineToast" 
         x-transition
         class="fixed bottom-20 left-4 right-4 sm:left-4 sm:right-auto sm:w-72 z-[9999] bg-emerald-50 border border-emerald-200 rounded-2xl p-3 shadow-lg flex items-center gap-2.5"
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

        // Lógica de Vibración Háptica
        window.hapticPulse = function (type = 'light') {
            if (!navigator.vibrate) return;
            switch (type) {
                case 'success':
                    navigator.vibrate([30, 50, 30]);
                    break;
                case 'warning':
                    navigator.vibrate([100, 50, 100]);
                    break;
                case 'light':
                default:
                    navigator.vibrate(20);
                    break;
            }
        };

        // Detectar iOS para el prompt
        document.addEventListener('DOMContentLoaded', () => {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;
            
            // Si es iOS y no está instalada, sugerir instalación después de 6 segundos
            if (isIOS && !isStandalone) {
                setTimeout(() => {
                    const alpineRoot = document.querySelector('[x-data]');
                    if (alpineRoot && alpineRoot.__x) {
                        alpineRoot.__x.$data.showIOSPrompt = true;
                    }
                }, 6000);
            }
        });
    </script>
</body>
</html>
