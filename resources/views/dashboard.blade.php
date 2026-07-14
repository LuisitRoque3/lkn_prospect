<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Locknode CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#fdfaf6] text-[#3d2b1f] antialiased min-h-screen">
    
    <!-- NAVIGATION BAR -->
    <nav class="bg-white border-b border-[#3d2b1f]/10 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-[#a3583d] bg-[#a3583d]/10 px-3 py-1 rounded-full">
                        Locknode CRM
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs font-black uppercase text-[#3d2b1f]">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-[#3d2b1f]/50 font-semibold">{{ auth()->user()->email }}</p>
                    </div>
                    
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#3d2b1f]/10 rounded-xl text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition-all">
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN BODY -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Prospectos Component -->
        <livewire:prospectos />
        
        <!-- Configurador Component -->
        <livewire:configurador />

    </main>

    @livewireScripts
</body>
</html>
