<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Locknode CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#fdfaf6] text-[#3d2b1f] antialiased min-h-screen" x-data="{ activeTab: 'leads' }">
    
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
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 pb-24 sm:pb-6">
        
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

    <!-- BOTTOM TAB BAR (MOBILE-FIRST) -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-t border-[#3d2b1f]/10 pb-safe shadow-[0_-4px_12px_rgba(0,0,0,0.05)] rounded-t-3xl flex justify-around p-3">
        <button @click="activeTab = 'leads'"
                :class="activeTab === 'leads' ? 'text-[#a3583d] scale-105 font-black' : 'text-[#3d2b1f]/60 font-semibold'"
                class="flex flex-col items-center gap-1 transition-all active:scale-95 py-1 flex-1">
            <span class="text-xl">📊</span>
            <span class="text-[9px] uppercase tracking-wider">Leads</span>
        </button>
        @if(auth()->user()->is_admin)
            <button @click="activeTab = 'config'"
                    :class="activeTab === 'config' ? 'text-[#a3583d] scale-105 font-black' : 'text-[#3d2b1f]/60 font-semibold'"
                    class="flex flex-col items-center gap-1 transition-all active:scale-95 py-1 flex-1">
                <span class="text-xl">⚙️</span>
                <span class="text-[9px] uppercase tracking-wider">Configurar</span>
            </button>
        @endif
    </div>

    @livewireScripts
</body>
</html>
