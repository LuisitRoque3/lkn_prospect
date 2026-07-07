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
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-sm">
                    <span>+ Nuevo Prospecto</span>
                </button>
                <div class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/50">
                    <span class="w-2.5 h-2.5 bg-[#a3583d] rounded-full animate-pulse"></span>
                    <span>Base de Datos Activa</span>
                </div>
            </div>
        </header>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-2xl flex justify-between items-center shadow-sm">
                <span>{{ session('message') }}</span>
                <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-2xl flex justify-between items-center shadow-sm">
                <span>{{ session('error') }}</span>
                <button type="button" class="text-red-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
            </div>
        @endif

        <!-- FILTROS Y BUSCADOR -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative md:col-span-2">
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Buscar por empresa o director..." 
                       class="w-full pl-4 pr-4 py-3.5 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] placeholder-[#3d2b1f]/40 shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
            </div>
            </div>
            <div class="flex gap-2">
                <select wire:model.live="statusFilter" 
                        class="w-1/2 px-4 py-3.5 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="enviado">Enviado</option>
                    <option value="respondido">Respondido</option>
                    <option value="descartado">Descartado</option>
                </select>
                <select wire:model.live="priorityFilter" 
                        class="w-1/2 px-4 py-3.5 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                    <option value="">Prioridad</option>
                    <option value="alfa">Alfa (Alta)</option>
                    <option value="bravo">Bravo (Med)</option>
                    <option value="charlie">Charlie (Baja)</option>
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
                        </h3>
                        <div class="flex flex-col items-end gap-1">
                            <span class="shrink-0 inline-block px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                                {{ $prospecto->estado_contacto == 'pendiente' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                {{ $prospecto->estado_contacto == 'enviado' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                {{ $prospecto->estado_contacto == 'respondido' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                                {{ $prospecto->estado_contacto == 'descartado' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                            ">
                                {{ $prospecto->estado_contacto ?: 'pendiente' }}
                            </span>
                            <span class="shrink-0 inline-block px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                                {{ $prospecto->priority == 'alfa' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}
                                {{ $prospecto->priority == 'bravo' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}
                                {{ $prospecto->priority == 'charlie' || !$prospecto->priority ? 'bg-gray-100 text-gray-700 border border-gray-200' : '' }}
                            ">
                                {{ $prospecto->priority ?: 'charlie' }}
                            </span>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="space-y-2 text-xs text-[#3d2b1f]/70 border-t border-[#3d2b1f]/5 pt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">👤</span>
                            <div>
                                <p class="font-bold text-[#3d2b1f]">{{ $prospecto->director_nombre }}</p>
                                @if($prospecto->correo_corporativo)
                                    <a href="mailto:{{ $prospecto->correo_corporativo }}" class="block text-[10px] text-[#3d2b1f]/50 hover:text-[#a3583d] transition-colors underline decoration-dotted">
                                        {{ $prospecto->correo_corporativo }}
                                    </a>
                                @else
                                    <p class="text-[10px] text-[#3d2b1f]/30">—</p>
                                @endif
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
                        @endif
                        @if($prospecto->open_count > 0)
                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#3d2b1f]/5 mt-2">
                                <div class="flex items-center gap-1.5 text-[10px] text-blue-700 font-bold uppercase tracking-wider bg-blue-50 px-2 py-1 rounded-lg">
                                    <span>👁️ Abierto {{ $prospecto->open_count }} vez/veces</span>
                                </div>
                                <span class="text-[9px] text-[#3d2b1f]/50">{{ $prospecto->opened_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="flex justify-end gap-2 border-t border-[#3d2b1f]/5 pt-3">
                        @if($prospecto->correo_corporativo)
                            <button wire:click="sendColdEmail({{ $prospecto->id }})" 
                                    class="px-3 py-2 bg-[#a3583d] hover:bg-[#8f4730] text-white text-[10px] font-black uppercase tracking-wider rounded-xl transition-all shadow-sm">
                                Enviar Correo
                            </button>
                        @endif
                        @if($prospecto->estado_contacto !== 'enviado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'enviado')" 
                                    class="px-3 py-2 bg-[#fdf8f0] hover:bg-amber-100 border border-amber-200 text-amber-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Enviado
                            </button>
                        @endif
                        @if($prospecto->estado_contacto !== 'respondido')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'respondido')" 
                                    class="px-3 py-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Respondido
                            </button>
                        @endif
                        @if($prospecto->estado_contacto !== 'descartado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'descartado')" 
                                    class="px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Descartar
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
                        <th class="p-4 font-black uppercase text-[#a3583d] tracking-wider">Estado & Prioridad</th>
                        <th class="p-4 font-black uppercase text-[#3d2b1f]/70 tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3d2b1f]/5 text-[#3d2b1f]/80">
                    @forelse($prospectos as $prospecto)
                        <tr class="hover:bg-[#fdfaf6]/50 transition-colors">
                            <td class="p-4 font-black uppercase tracking-tight text-[#3d2b1f] max-w-xs truncate">
                                {{ $prospecto->empresa }}
                                @if($prospecto->open_count > 0)
                                    <div class="mt-2 flex items-center gap-1.5 text-[9px] text-blue-700 font-bold uppercase tracking-wider bg-blue-50 px-2 py-1 rounded-lg w-fit border border-blue-100">
                                        <span>👁️ Abierto {{ $prospecto->open_count }}x</span>
                                        <span class="text-blue-700/50">({{ $prospecto->opened_at->diffForHumans() }})</span>
                                    </div>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-[#3d2b1f]">{{ $prospecto->director_nombre }}</div>
                                @if($prospecto->correo_corporativo)
                                    <a href="mailto:{{ $prospecto->correo_corporativo }}" class="inline-block text-[10px] text-[#3d2b1f]/50 hover:text-[#a3583d] transition-colors underline decoration-dotted">
                                        {{ $prospecto->correo_corporativo }}
                                    </a>
                                @else
                                    <div class="text-[10px] text-[#3d2b1f]/30">—</div>
                                @endif
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
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider
                                        {{ $prospecto->estado_contacto == 'pendiente' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                        {{ $prospecto->estado_contacto == 'enviado' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                        {{ $prospecto->estado_contacto == 'respondido' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                                        {{ $prospecto->estado_contacto == 'descartado' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                                    ">
                                        {{ $prospecto->estado_contacto ?: 'pendiente' }}
                                    </span>
                                    <select wire:change="updatePriority({{ $prospecto->id }}, $event.target.value)" class="text-[9px] font-black uppercase tracking-wider rounded-full px-2 py-0.5 border cursor-pointer
                                        {{ $prospecto->priority == 'alfa' ? 'bg-red-100 text-red-800 border-red-200' : '' }}
                                        {{ $prospecto->priority == 'bravo' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : '' }}
                                        {{ $prospecto->priority == 'charlie' || !$prospecto->priority ? 'bg-gray-100 text-gray-700 border-gray-200' : '' }}
                                    ">
                                        <option value="alfa" {{ $prospecto->priority == 'alfa' ? 'selected' : '' }}>ALFA</option>
                                        <option value="bravo" {{ $prospecto->priority == 'bravo' ? 'selected' : '' }}>BRAVO</option>
                                        <option value="charlie" {{ $prospecto->priority == 'charlie' ? 'selected' : '' }}>CHARLIE</option>
                                    </select>
                                </div>
                            </td>
                            <td class="p-4 text-right space-x-1">
                                @if($prospecto->correo_corporativo)
                                    <button wire:click="sendColdEmail({{ $prospecto->id }})" 
                                            class="px-2.5 py-1.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-[9px] font-black uppercase tracking-wider rounded-lg transition-all shadow-sm">
                                        Enviar Mail
                                    </button>
                                @endif
                                @if($prospecto->estado_contacto !== 'enviado')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'enviado')" 
                                            class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Enviado
                                    </button>
                                @endif
                                @if($prospecto->estado_contacto !== 'respondido')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'respondido')" 
                                            class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Respondido
                                    </button>
                                @endif
                                @if($prospecto->estado_contacto !== 'descartado')
                                    <button wire:click="updateStatus({{ $prospecto->id }}, 'descartado')" 
                                            class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-800 text-[9px] font-black uppercase tracking-wider rounded-lg transition-all">
                                        Descartar
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

        <!-- MODAL DE CREACIÓN -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#3d2b1f]/40 backdrop-blur-sm transition-opacity">
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-6 shadow-xl w-full max-w-lg space-y-6 transform transition-all">
                    
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b border-[#3d2b1f]/10">
                        <h2 class="text-sm font-black uppercase tracking-wider text-[#3d2b1f]">
                            Agregar Nuevo Prospecto
                        </h2>
                        <button wire:click="closeCreateModal" class="text-[#3d2b1f]/60 hover:text-[#3d2b1f] text-lg font-bold">
                            &times;
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form wire:submit.prevent="save" class="space-y-4 text-xs">
                        <!-- Empresa -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Empresa *</label>
                            <input type="text" wire:model="empresa" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                            @error('empresa') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Director -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Director / Contacto</label>
                            <input type="text" wire:model="director_nombre" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                            @error('director_nombre') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Correo Corporativo -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Correo Corporativo</label>
                            <input type="email" wire:model="correo_corporativo" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                            @error('correo_corporativo') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Teléfono / WhatsApp -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Teléfono / WhatsApp</label>
                            <input type="text" wire:model="telefono_whatsapp" placeholder="Ej: +521234567890" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                            @error('telefono_whatsapp') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estado de Contacto -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Estado de Contacto</label>
                            <select wire:model="estado_contacto" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado (Contactado)</option>
                                <option value="respondido">Respondido (Interesado)</option>
                                <option value="descartado">Descartado</option>
                            </select>
                            @error('estado_contacto') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Prioridad -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70">Prioridad Táctica</label>
                            <select wire:model="priority" class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] shadow-sm focus:outline-none focus:ring-1 focus:ring-[#a3583d] focus:border-[#a3583d] transition-all">
                                <option value="alfa">Alfa (Alta Prioridad)</option>
                                <option value="bravo">Bravo (Media Prioridad)</option>
                                <option value="charlie">Charlie (Baja Prioridad)</option>
                            </select>
                            @error('priority') <span class="text-red-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-4 border-t border-[#3d2b1f]/10">
                            <button type="button" wire:click="closeCreateModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-[#a3583d] hover:bg-[#8f4730] text-white text-[10px] font-black uppercase tracking-wider rounded-xl transition-all shadow-sm">
                                Guardar Prospecto
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif
        
    </div>
</div>
