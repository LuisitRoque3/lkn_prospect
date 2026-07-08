<div class="min-h-screen bg-[#fdfaf6] py-8 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- HEADER -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-[#3d2b1f]/10">
            <div class="space-y-1">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#a3583d]">
                    Locknode CRM
                </span>
                <h1 class="text-3xl font-black uppercase tracking-tight text-[#3d2b1f]">
                    Panel de Nuevos Invitados
                </h1>
                <p class="text-xs text-[#3d2b1f]/60 font-medium">Gestiona tu prospección corporativa y local.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span>+ Nuevo Prospecto</span>
                </button>
                <div class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/50 bg-white px-3 py-1.5 rounded-lg border border-[#3d2b1f]/10 shadow-sm">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                    <span>Motores Activos</span>
                </div>
            </div>
        </header>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center shadow-sm">
                <span class="font-bold">{{ session('message') }}</span>
                <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
            </div>
        @endif

        <!-- FILTROS Y BUSCADOR -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative md:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="text-[#3d2b1f]/40">🔍</span>
                </div>
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Buscar por empresa, director o ubicación..." 
                       class="w-full pl-10 pr-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] placeholder-[#3d2b1f]/40 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
            </div>
            <div class="md:col-span-1">
                <select wire:model.live="statusFilter" 
                        class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="enviado">Contactado</option>
                    <option value="respondido">En Conversación</option>
                    <option value="descartado">Descartado</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <select wire:model.live="priorityFilter" 
                        class="w-full px-4 py-3 bg-white border border-[#3d2b1f]/10 rounded-2xl text-xs text-[#3d2b1f] font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
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
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-5 shadow-sm space-y-4 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full 
                        {{ $prospecto->priority == 'alfa' ? 'bg-red-500' : '' }}
                        {{ $prospecto->priority == 'bravo' ? 'bg-yellow-400' : '' }}
                        {{ $prospecto->priority == 'charlie' || !$prospecto->priority ? 'bg-gray-300' : '' }}">
                    </div>
                    
                    <!-- Fila Superior: Nombre y Estado -->
                    <div class="flex justify-between items-start gap-2 pl-2">
                        <div>
                            <h3 class="text-base font-black uppercase tracking-tight text-[#3d2b1f] leading-tight">
                                {{ $prospecto->empresa }}
                            </h3>
                            <p class="text-[10px] text-[#3d2b1f]/50 mt-1 uppercase tracking-wider font-semibold">📍 {{ $prospecto->ubicacion_local ?: 'Sin ubicación' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="shrink-0 inline-block px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-wider
                                {{ $prospecto->estado_contacto == 'pendiente' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                {{ $prospecto->estado_contacto == 'enviado' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                {{ $prospecto->estado_contacto == 'respondido' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                                {{ $prospecto->estado_contacto == 'descartado' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                            ">
                                {{ $prospecto->estado_contacto ?: 'pendiente' }}
                            </span>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="space-y-3 text-xs text-[#3d2b1f]/80 border-t border-[#3d2b1f]/5 pt-4 pl-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#fdfaf6] flex items-center justify-center border border-[#3d2b1f]/10">
                                👤
                            </div>
                            <div>
                                <p class="font-bold text-[#3d2b1f] text-sm">{{ $prospecto->director_nombre }}</p>
                                @if($prospecto->correo_corporativo && $prospecto->correo_corporativo !== 'N/A')
                                    <span class="inline-flex mt-1 items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-[10px] font-mono">
                                        ✉️ {{ $prospecto->correo_corporativo }}
                                    </span>
                                @else
                                    <span class="inline-flex mt-1 items-center gap-1 bg-gray-50 text-gray-400 px-2 py-0.5 rounded text-[10px]">
                                        Sin Email
                                    </span>
                                @endif
                                @if($prospecto->telefono_whatsapp)
                                    <span class="inline-flex mt-1 ml-1 items-center gap-1 bg-green-50 px-2 py-0.5 rounded text-[10px] font-mono text-green-700">
                                        📞 {{ $prospecto->telefono_whatsapp }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Botones de Acción (Omnicanal y Edición) -->
                        <div class="grid grid-cols-4 gap-2 pt-2">
                            <!-- Botón Editar -->
                            <button wire:click="edit({{ $prospecto->id }})" 
                                    class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 rounded-xl transition-all group/edit">
                                <span class="text-base group-hover/edit:scale-110 transition-transform">✏️</span>
                                <span class="text-[9px] font-black uppercase tracking-wider">Editar</span>
                            </button>

                            <!-- Botón Llamar -->
                            @if($prospecto->telefono_whatsapp)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $prospecto->telefono_whatsapp) }}" 
                                   class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition-all group/call">
                                    <span class="text-base group-hover/call:scale-110 transition-transform">📞</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">Llamar</span>
                                </a>
                            @else
                                <div class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-gray-50 border border-gray-100 text-gray-400 rounded-xl">
                                    <span class="text-base opacity-50">📞</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">Sin Tel.</span>
                                </div>
                            @endif

                            <!-- Botón WhatsApp -->
                            @if($prospecto->telefono_whatsapp)
                                <a href="{{ $prospecto->whatsapp_url }}" 
                                   target="_blank" 
                                   class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-[#25D366]/10 hover:bg-[#25D366]/20 border border-[#25D366]/20 text-[#075E54] rounded-xl transition-all group/wa">
                                    <span class="text-base group-hover/wa:scale-110 transition-transform">💬</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">WhatsApp</span>
                                </a>
                            @else
                                <div class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-gray-50 border border-gray-100 text-gray-400 rounded-xl">
                                    <span class="text-base opacity-50">💬</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">Sin Teléfono</span>
                                </div>
                            @endif

                            <!-- Botón Email -->
                            @if($prospecto->correo_corporativo && $prospecto->correo_corporativo !== 'N/A')
                                <button wire:click="sendColdEmail({{ $prospecto->id }})" 
                                        class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-[#a3583d]/10 hover:bg-[#a3583d]/20 border border-[#a3583d]/20 text-[#8f4730] rounded-xl transition-all group/mail">
                                    <span class="text-base group-hover/mail:scale-110 transition-transform">✉️</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">Enviar Mail</span>
                                </button>
                            @else
                                <div class="col-span-1 flex flex-col items-center justify-center gap-1 p-2 bg-gray-50 border border-gray-100 text-gray-400 rounded-xl">
                                    <span class="text-base opacity-50">✉️</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider">Sin Correo</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-8 text-center text-[#3d2b1f]/50 text-xs">
                    No se encontraron prospectos.
                </div>
            @endforelse
        </div>

        <!-- ESCRITORIO: VISTA EN TABLA PREMIUM -->
        <div class="hidden md:block overflow-hidden bg-white border border-[#3d2b1f]/10 rounded-3xl shadow-sm">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#3d2b1f]/10 bg-[#f4e8d8]/30">
                        <th class="p-5 font-black uppercase text-[#a3583d] tracking-wider">Empresa & Ubicación</th>
                        <th class="p-5 font-black uppercase text-[#3d2b1f]/70 tracking-wider">Contacto Principal</th>
                        <th class="p-5 font-black uppercase text-[#3d2b1f]/70 tracking-wider text-center">Acciones Directas</th>
                        <th class="p-5 font-black uppercase text-[#3d2b1f]/70 tracking-wider text-center">Estado de Embudo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3d2b1f]/5 text-[#3d2b1f]/80">
                    @forelse($prospectos as $prospecto)
                        <tr class="hover:bg-[#fdfaf6] transition-colors group">
                            <!-- Columna 1: Empresa -->
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-8 rounded-full 
                                        {{ $prospecto->priority == 'alfa' ? 'bg-red-500' : '' }}
                                        {{ $prospecto->priority == 'bravo' ? 'bg-yellow-400' : '' }}
                                        {{ $prospecto->priority == 'charlie' || !$prospecto->priority ? 'bg-gray-300' : '' }}">
                                    </div>
                                    <div>
                                        <h4 class="font-black uppercase tracking-tight text-[#3d2b1f] text-sm">{{ $prospecto->empresa }}</h4>
                                        <p class="text-[10px] text-[#3d2b1f]/50 font-bold uppercase mt-0.5 tracking-wider truncate max-w-[200px]" title="{{ $prospecto->ubicacion_local }}">
                                            📍 {{ $prospecto->ubicacion_local ?: 'Ubicación Desconocida' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Columna 2: Contacto -->
                            <td class="p-5">
                                <div class="font-bold text-[#3d2b1f]">{{ $prospecto->director_nombre }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($prospecto->correo_corporativo && $prospecto->correo_corporativo !== 'N/A')
                                        <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded text-[9px] font-mono text-gray-600">
                                            ✉️ {{ $prospecto->correo_corporativo }}
                                        </span>
                                    @endif
                                    @if($prospecto->telefono_whatsapp)
                                        <span class="inline-flex items-center gap-1 bg-green-50 border border-green-100 px-2 py-0.5 rounded text-[9px] font-mono text-green-700">
                                            📞 {{ $prospecto->telefono_whatsapp }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Columna 3: Omnicanal y Edición -->
                            <td class="p-5 text-center">
                                <div class="flex justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <!-- Edit Button -->
                                    <button wire:click="edit({{ $prospecto->id }})" 
                                            title="Editar Prospecto"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 hover:bg-blue-500 hover:text-white border border-blue-200 text-blue-600 rounded-lg transition-all shadow-sm transform hover:scale-110">
                                        ✏️
                                    </button>

                                    <!-- Call Button -->
                                    @if($prospecto->telefono_whatsapp)
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $prospecto->telefono_whatsapp) }}" 
                                           title="Llamar Prospecto"
                                           class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 hover:bg-gray-200 border border-gray-200 text-gray-700 rounded-lg transition-all shadow-sm transform hover:scale-110">
                                            📞
                                        </a>
                                    @else
                                        <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 border border-gray-100 text-gray-300 rounded-lg cursor-not-allowed" title="Sin Teléfono">📞</div>
                                    @endif

                                    <!-- WhatsApp Button -->
                                    @if($prospecto->telefono_whatsapp)
                                        <a href="{{ $prospecto->whatsapp_url }}" 
                                           target="_blank" 
                                           title="Enviar WhatsApp"
                                           class="inline-flex items-center justify-center w-8 h-8 bg-[#25D366]/10 hover:bg-[#25D366] hover:text-white border border-[#25D366]/20 text-[#075E54] rounded-lg transition-all shadow-sm transform hover:scale-110">
                                            💬
                                        </a>
                                    @else
                                        <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 border border-gray-100 text-gray-300 rounded-lg cursor-not-allowed" title="Sin Teléfono">💬</div>
                                    @endif

                                    <!-- Email Button -->
                                    @if($prospecto->correo_corporativo && $prospecto->correo_corporativo !== 'N/A')
                                        <button wire:click="sendColdEmail({{ $prospecto->id }})" 
                                                title="Enviar Email de Prospección"
                                                class="inline-flex items-center justify-center w-8 h-8 bg-[#a3583d]/10 hover:bg-[#a3583d] hover:text-white border border-[#a3583d]/20 text-[#8f4730] rounded-lg transition-all shadow-sm transform hover:scale-110">
                                            ✉️
                                        </button>
                                    @else
                                        <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 border border-gray-100 text-gray-300 rounded-lg cursor-not-allowed" title="Sin Correo">✉️</div>
                                    @endif
                                </div>
                                @if($prospecto->open_count > 0)
                                    <div class="mt-2 inline-flex items-center gap-1 text-[9px] text-blue-700 font-bold uppercase tracking-wider bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">
                                        <span>🔥 Abierto {{ $prospecto->open_count }}x</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Columna 4: Status -->
                            <td class="p-5 text-center space-y-2">
                                <select wire:change="updateStatus({{ $prospecto->id }}, $event.target.value)" 
                                    class="w-full text-[10px] font-black uppercase tracking-wider rounded-lg px-3 py-2 border cursor-pointer appearance-none text-center shadow-sm focus:ring-1 focus:ring-gray-300
                                    {{ $prospecto->estado_contacto == 'pendiente' || !$prospecto->estado_contacto ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                    {{ $prospecto->estado_contacto == 'enviado' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                    {{ $prospecto->estado_contacto == 'respondido' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                    {{ $prospecto->estado_contacto == 'descartado' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                ">
                                    <option value="pendiente" {{ $prospecto->estado_contacto == 'pendiente' ? 'selected' : '' }}>🔵 Pendiente</option>
                                    <option value="enviado" {{ $prospecto->estado_contacto == 'enviado' ? 'selected' : '' }}>🟡 Contactado</option>
                                    <option value="respondido" {{ $prospecto->estado_contacto == 'respondido' ? 'selected' : '' }}>🟢 En Conversación</option>
                                    <option value="descartado" {{ $prospecto->estado_contacto == 'descartado' ? 'selected' : '' }}>🔴 Descartado</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-10 text-center text-[#3d2b1f]/50">
                                <div class="text-4xl mb-3 opacity-20">📭</div>
                                <p class="font-bold text-sm uppercase tracking-wider">No se encontraron prospectos</p>
                                <p class="mt-1 text-xs">El motor de Google Maps poblará esta tabla automáticamente.</p>
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
        
        <!-- MODAL DE CREACIÓN / EDICIÓN -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#3d2b1f]/40 backdrop-blur-sm transition-opacity">
                <div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-6 shadow-2xl w-full max-w-lg space-y-6 transform transition-all">
                    
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b border-[#3d2b1f]/10">
                        <h2 class="text-sm font-black uppercase tracking-wider text-[#3d2b1f]">
                            {{ isset($prospectoId) && $prospectoId ? 'Editar Prospecto' : 'Agregar Nuevo Prospecto' }}
                        </h2>
                        <button wire:click="closeCreateModal" class="text-[#3d2b1f]/60 hover:text-[#3d2b1f] text-xl font-black w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                            &times;
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form wire:submit.prevent="save" class="space-y-4 text-xs">
                        <!-- Empresa y Ubicación -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Empresa *</label>
                                <input type="text" wire:model="empresa" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                @error('empresa') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Ubicación Local</label>
                                <input type="text" wire:model="ubicacion_local" placeholder="Ej: Querétaro, Qro" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                @error('ubicacion_local') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Director -->
                        <div class="space-y-1">
                            <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Contacto Principal</label>
                            <input type="text" wire:model="director_nombre" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                            @error('director_nombre') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contacto Directo -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Teléfono / WhatsApp</label>
                                <input type="text" wire:model="telefono_whatsapp" placeholder="Ej: +521234567890" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                @error('telefono_whatsapp') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Correo Corporativo</label>
                                <input type="email" wire:model="correo_corporativo" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                @error('correo_corporativo') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Estado y Prioridad -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Estado de Embudo</label>
                                <select wire:model="estado_contacto" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                    <option value="pendiente">🔵 Pendiente</option>
                                    <option value="enviado">🟡 Contactado</option>
                                    <option value="respondido">🟢 En Conversación</option>
                                    <option value="descartado">🔴 Descartado</option>
                                </select>
                                @error('estado_contacto') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1 col-span-2 sm:col-span-1">
                                <label class="block font-black uppercase text-[#3d2b1f]/70 text-[9px] tracking-wider">Prioridad Táctica</label>
                                <select wire:model="priority" class="w-full px-4 py-3 bg-[#fdfaf6] border border-[#3d2b1f]/10 rounded-xl text-xs font-bold text-[#3d2b1f] shadow-inner focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d] transition-all">
                                    <option value="alfa">🔴 Alfa (Alta)</option>
                                    <option value="bravo">🟡 Bravo (Media)</option>
                                    <option value="charlie">⚪ Charlie (Baja)</option>
                                </select>
                                @error('priority') <span class="text-red-500 font-bold text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-6">
                            <button type="button" wire:click="closeCreateModal" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-[10px] font-black uppercase tracking-wider rounded-xl transition-all">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-[10px] font-black uppercase tracking-wider rounded-xl transition-all shadow-md transform hover:-translate-y-0.5">
                                {{ isset($prospectoId) && $prospectoId ? 'Actualizar' : 'Guardar' }} Prospecto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        
    </div>
</div>
