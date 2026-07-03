<div class="min-h-screen bg-[#fdfaf6] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- HEADER -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-[#3d2b1f]/10">
            <div class="space-y-1">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#a3583d]">
                    Locknode CRM
                </span>
                <h1 class="text-2xl font-black uppercase tracking-tight text-[#3d2b1f]">
                    Control de Prospectos
                </h1>
            </div>
            <div class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/50">
                <span class="w-2.5 h-2.5 bg-[#a3583d] rounded-full animate-pulse"></span>
                <span>Base de Datos Activa</span>
            </div>
        </header>

        <!-- FILTROS Y BUSCADOR -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative md:col-span-2">
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Buscar por empresa o director..." 
                       class="w-full pl-4 pr-4 py-3.5 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] placeholder-[#3d2b1f]/40 shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
            </div>
            <div>
                <select wire:model.live="statusFilter" 
                        class="w-full px-4 py-3.5 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                    <option value="">Todos los Estados</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Contactado">Contactado</option>
                    <option value="Interesado">Interesado</option>
                    <option value="Cerrado">Venta Cerrada</option>
                </select>
            </div>
        </div>

        <!-- MÓVIL: VISTA EN TARJETAS (CARDS) -->
        <div class="block md:hidden space-y-4">
            @forelse($prospectos as $prospecto)
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-5 shadow-sm space-y-4">
                    <!-- Fila Superior: Nombre y Estado -->
                    <div class="flex justify-between items-start gap-2">
                        <h3 class="text-sm font-black uppercase tracking-tight text-[#3d2b1f] leading-snug">
                            {{ $prospecto->empresa }}
                        </h3>
                        <span class="shrink-0 inline-block px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                            {{ $prospecto->estado_contacto == 'Nuevo' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                            {{ $prospecto->estado_contacto == 'Contactado' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                            {{ $prospecto->estado_contacto == 'Interesado' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                            {{ $prospecto->estado_contacto == 'Cerrado' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                        ">
                            {{ $prospecto->estado_contacto ?: 'Nuevo' }}
                        </span>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="space-y-2 text-xs text-[#3d2b1f]/70 border-t border-[#3d2b1f]/5 pt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">👤</span>
                            <div>
                                <p class="font-bold text-[#3d2b1f]">{{ $prospecto->director_nombre }}</p>
                                <p class="text-[10px] text-[#3d2b1f]/50">{{ $prospecto->correo_corporativo }}</p>
                            </div>
                        </div>
                        @if($prospecto->telefono_whatsapp)
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">📞</span>
                                    <span class="font-medium text-[#3d2b1f]">{{ $prospecto->telefono_whatsapp }}</span>
                                </div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $prospecto->telefono_whatsapp) }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#e8f4ed] hover:bg-[#d8edd1] text-[#248a4d] text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="flex justify-end gap-2 border-t border-[#3d2b1f]/5 pt-3">
                        @if($prospecto->estado_contacto !== 'Contactado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'Contactado')" 
                                    class="px-3 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Contactar
                            </button>
                        @endif
                        @if($prospecto->estado_contacto !== 'Interesado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'Interesado')" 
                                    class="px-3 py-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Interesar
                            </button>
                        @endif
                        @if($prospecto->estado_contacto !== 'Cerrado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'Cerrado')" 
                                    class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Cerrar Venta
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-8 text-center text-[#3d2b1f]/50 text-xs">
                    No se encontraron prospectos.
                </div>
            @endforelse
        </div>

        <!-- ESCRITORIO: VISTA EN TABLA -->
        <div class="hidden md:block overflow-hidden bg-white border border-[#3d2b1f]/10 rounded-3xl shadow-sm">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#3d2b1f]/10 bg-[#f4e8d8]/20">
                        <th class="p-4 font-black uppercase text-[#a3583d] tracking-wider">Empresa</th>
                        <th class="p-4 font-black uppercase text-[#3d2b1f]/70 tracking-wider">Director / Contacto</th>
                        <th class="p-4 font-black uppercase text-[#3d2b1f]/70 tracking-wider">Teléfono / WhatsApp</th>
                        <th class="p-4 font-black uppercase text-[#a3583d] tracking-wider">Estado</th>
                        <th class="p-4 font-black uppercase text-[#3d2b1f]/70 tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3d2b1f]/5 text-[#3d2b1f]/80">
                    @forelse($prospectos as $prospecto)
                        <tr class="hover:bg-[#fdfaf6]/50 transition-colors">
                            <td class="p-4 font-black uppercase tracking-tight text-[#3d2b1f] max-w-xs truncate">
                                {{ $prospecto->empresa }}
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-[#3d2b1f]">{{ $prospecto->director_nombre }}</div>
                                <div class="text-[10px] text-[#3d2b1f]/50">{{ $prospecto->correo_corporativo }}</div>
                            </td>
                            <td class="p-4">
                                @if($prospecto->telefono_whatsapp)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-[#3d2b1f]">{{ $prospecto->telefono_whatsapp }}</span>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $prospecto->telefono_whatsapp) }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           class="inline-block px-2 py-1 bg-[#e8f4ed] hover:bg-[#d8edd1] text-[#248a4d] text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                            WhatsApp
                                        </a>
                                    </div>
                                @else
                                    <span class="text-[#3d2b1f]/40">—</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                                    {{ $prospecto->estado_contacto == 'Nuevo' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                    {{ $prospecto->estado_contacto == 'Contactado' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                    {{ $prospecto->estado_contacto == 'Interesado' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                                    {{ $prospecto->estado_contacto == 'Cerrado' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                                ">
                                    {{ $prospecto->estado_contacto ?: 'Nuevo' }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-1">
                                @if($prospecto->estado_contacto !== 'Contactado')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'Contactado')" 
                                            class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Contactar
                                    </button>
                                @endif
                                @if($prospecto->estado_contacto !== 'Interesado')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'Interesado')" 
                                            class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Interesar
                                    </button>
                                @endif
                                @if($prospecto->estado_contacto !== 'Cerrado')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'Cerrado')" 
                                            class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Cerrar Venta
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-[#3d2b1f]/50">
                                No se encontraron prospectos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="pt-4">
            {{ $prospectos->links() }}
        </div>
        
    </div>
</div>
