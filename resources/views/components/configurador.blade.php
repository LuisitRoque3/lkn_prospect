<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $selectedGiros = [];
    public $selectedCiudades = [];
    public $selected_org_id = '';
    
    // Inputs para agregar nuevos elementos al vuelo
    public $nuevoGiro = '';
    public $nuevaCiudad = '';
    public $customGiros = [];
    public $customCiudades = [];

    // Pestaña Interna activa: 'extractor', 'organizaciones', 'usuarios'
    public $currentSection = 'extractor';

    // Propiedades para CRUD Organizaciones
    public $orgNombre = '';
    
    // Propiedades para Asignación de Usuarios
    public $userToAssign = '';
    public $orgToAssign = '';

    // Catálogo Base estático
    public $listaCiudades = [
        "CDMX", "Monterrey", "Guadalajara", "Puebla", "Tijuana", 
        "Leon", "Querétaro", "Toluca", "San Luis Potosí", "Mérida", 
        "Aguascalientes", "Saltillo", "Hermosillo", "Mexicali", "Culiacán", "Chihuahua"
    ];

    public $listaGiros = [
        "transporte y logística",
        "instalaciones eléctricas",
        "talleres mecánicos",
        "agencias de seguridad privada",
        "climatización y aire acondicionado",
        "servicios de limpieza industrial",
        "construcción y contratistas",
        "distribuidores mayoristas"
    ];

    public function mount()
    {
        // Seguridad: Asegurar que el usuario sea administrador
        abort_unless(Auth::user()->is_admin, 403);
    }

    public function changeSection($section)
    {
        $this->currentSection = $section;
        $this->resetErrorBag();
    }

    // Listas dinámicas combinando el catálogo base + elementos de tareas guardadas + elementos agregados al vuelo
    public function getGirosList()
    {
        $giros = $this->listaGiros;
        
        try {
            $configs = DB::table('configuraciones_extraccion')->pluck('giro');
            foreach ($configs as $config) {
                $decoded = json_decode($config, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $g) {
                        $giros[] = strtolower($g);
                    }
                } else if ($config) {
                    $giros[] = strtolower($config);
                }
            }
        } catch (\Exception $e) {}

        $giros = array_merge($giros, $this->customGiros);
        return array_values(array_unique(array_map('trim', $giros)));
    }

    public function getCiudadesList()
    {
        $ciudades = $this->listaCiudades;
        
        try {
            $configs = DB::table('configuraciones_extraccion')->pluck('ciudad');
            foreach ($configs as $config) {
                $decoded = json_decode($config, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $c) {
                        $ciudades[] = $c;
                    }
                } else if ($config) {
                    $ciudades[] = $config;
                }
            }
        } catch (\Exception $e) {}

        $ciudades = array_merge($ciudades, $this->customCiudades);
        return array_values(array_unique(array_map('trim', $ciudades)));
    }

    // Acciones de agregar al vuelo
    public function agregarGiroPersonalizado()
    {
        $giroClean = strtolower(trim($this->nuevoGiro));
        if (empty($giroClean)) return;
        
        if (!in_array($giroClean, $this->getGirosList())) {
            $this->customGiros[] = $giroClean;
        }
        
        if (!in_array($giroClean, $this->selectedGiros)) {
            $this->selectedGiros[] = $giroClean;
        }
        
        $this->nuevoGiro = '';
    }

    public function agregarCiudadPersonalizada()
    {
        $ciudadClean = trim($this->nuevaCiudad);
        if (empty($ciudadClean)) return;
        
        if (!in_array($ciudadClean, $this->getCiudadesList())) {
            $this->customCiudades[] = $ciudadClean;
        }
        
        if (!in_array($ciudadClean, $this->selectedCiudades)) {
            $this->selectedCiudades[] = $ciudadClean;
        }
        
        $this->nuevaCiudad = '';
    }

    // Acciones de Marcación Rápida
    public function seleccionarTodosGiros()
    {
        $this->selectedGiros = $this->getGirosList();
    }

    public function seleccionarTodasCiudades()
    {
        $this->selectedCiudades = $this->getCiudadesList();
    }

    // ==========================================
    // SECCIÓN A: TAREAS DE EXTRACCIÓN (CRON)
    // ==========================================
    public function agregar()
    {
        abort_unless(Auth::user()->is_admin, 403);
        
        $this->validate([
            'selectedGiros' => 'required|array|min:1',
            'selectedCiudades' => 'required|array|min:1',
            'selected_org_id' => 'required|exists:organizaciones,id',
        ]);

        DB::table('configuraciones_extraccion')->insert([
            'user_id' => Auth::id(), // Creador
            'organizacion_id' => $this->selected_org_id, // Organización asignada
            'giro' => json_encode($this->selectedGiros),
            'ciudad' => json_encode($this->selectedCiudades),
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->reset(['selectedGiros', 'selectedCiudades', 'selected_org_id', 'customGiros', 'customCiudades']);
        session()->flash('message', 'Tarea programada asignada con éxito a la organización.');
    }

    public function toggleEstado($id)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $config = DB::table('configuraciones_extraccion')->where('id', $id)->first();

        if ($config) {
            DB::table('configuraciones_extraccion')
                ->where('id', $id)
                ->update(['estado' => !$config->estado, 'updated_at' => now()]);
        }
    }

    public function eliminar($id)
    {
        abort_unless(Auth::user()->is_admin, 403);
        DB::table('configuraciones_extraccion')->where('id', $id)->delete();
        session()->flash('message', 'Tarea programada eliminada.');
    }

    public function getConfigs()
    {
        return DB::table('configuraciones_extraccion')
            ->join('organizaciones', 'configuraciones_extraccion.organizacion_id', '=', 'organizaciones.id')
            ->select('configuraciones_extraccion.*', 'organizaciones.nombre as org_name')
            ->orderBy('configuraciones_extraccion.created_at', 'desc')
            ->get();
    }

    // ==========================================
    // SECCIÓN B: CRUD ORGANIZACIONES
    // ==========================================
    public function crearOrganizacion()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $this->validate([
            'orgNombre' => 'required|string|max:255|unique:organizaciones,nombre',
        ]);

        DB::table('organizaciones')->insert([
            'nombre' => $this->orgNombre,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->reset(['orgNombre']);
        session()->flash('message_org', 'Organización creada con éxito.');
    }

    public function eliminarOrganizacion($id)
    {
        abort_unless(Auth::user()->is_admin, 403);
        DB::table('organizaciones')->where('id', $id)->delete();
        session()->flash('message_org', 'Organización eliminada.');
    }

    public function getOrganizaciones()
    {
        return DB::table('organizaciones')->orderBy('nombre', 'asc')->get();
    }

    // ==========================================
    // SECCIÓN C: ASIGNACIÓN DE USUARIOS
    // ==========================================
    public function asignarUsuario()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $this->validate([
            'userToAssign' => 'required|exists:users,id',
            'orgToAssign' => 'nullable|exists:organizaciones,id',
        ]);

        DB::table('users')
            ->where('id', $this->userToAssign)
            ->update([
                'organizacion_id' => $this->orgToAssign ?: null,
                'updated_at' => now()
            ]);

        $this->reset(['userToAssign', 'orgToAssign']);
        session()->flash('message_users', 'Relación de usuario actualizada.');
    }

    public function getUsersList()
    {
        return DB::table('users')
            ->leftJoin('organizaciones', 'users.organizacion_id', '=', 'organizaciones.id')
            ->select('users.*', 'organizaciones.nombre as org_name')
            ->orderBy('users.name', 'asc')
            ->get();
    }
};
?>

<div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-4 sm:p-6 shadow-sm space-y-6">
    <div class="border-b border-[#3d2b1f]/10 pb-4">
        <h2 class="text-lg font-black uppercase tracking-tight text-[#3d2b1f]">
            ⚙️ Panel de Control de Administración
        </h2>
        <p class="text-xs text-[#3d2b1f]/60 font-medium mt-1">
            Gestión B2B: Administra organizaciones, asigna usuarios a grupos y programa tareas automáticas de extracción.
        </p>
    </div>

    <!-- SUB-MENÚ DE PESTAÑAS INTERNAS -->
    <div class="flex overflow-x-auto gap-2 pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none border-b border-[#3d2b1f]/5">
        <button wire:click="changeSection('extractor')" 
                class="px-4 py-2 text-[10px] font-black uppercase tracking-wider border-b-2 transition-all whitespace-nowrap {{ $currentSection === 'extractor' ? 'border-[#a3583d] text-[#a3583d]' : 'border-transparent text-[#3d2b1f]/60 hover:text-[#3d2b1f]' }}">
            ⚙️ Tareas del Cron
        </button>
        <button wire:click="changeSection('organizaciones')" 
                class="px-4 py-2 text-[10px] font-black uppercase tracking-wider border-b-2 transition-all whitespace-nowrap {{ $currentSection === 'organizaciones' ? 'border-[#a3583d] text-[#a3583d]' : 'border-transparent text-[#3d2b1f]/60 hover:text-[#3d2b1f]' }}">
            🏢 Organizaciones
        </button>
        <button wire:click="changeSection('usuarios')" 
                class="px-4 py-2 text-[10px] font-black uppercase tracking-wider border-b-2 transition-all whitespace-nowrap {{ $currentSection === 'usuarios' ? 'border-[#a3583d] text-[#a3583d]' : 'border-transparent text-[#3d2b1f]/60 hover:text-[#3d2b1f]' }}">
            👥 Asignar Usuarios
        </button>
    </div>

    <!-- ======================================================================
         SECCIÓN A: TAREAS DEL CRON
         ====================================================================== -->
    @if($currentSection === 'extractor')
        <div class="space-y-6">
            @if (session()->has('message'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center">
                    <span class="font-bold">{{ session('message') }}</span>
                    <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
                </div>
            @endif

            <!-- Formulario de Tarea de Selección Múltiple -->
            <form wire:submit="agregar" class="space-y-4 bg-[#fdfaf6] p-4 rounded-2xl border border-[#3d2b1f]/5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Checkbox de Giros -->
                    <div class="space-y-2 border border-[#3d2b1f]/10 p-3 rounded-xl bg-white max-h-60 overflow-y-auto flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100 mb-2">
                                <span class="text-[10px] font-black uppercase text-[#3d2b1f]/70">1. Seleccionar Giros</span>
                                <button type="button" wire:click="seleccionarTodosGiros" class="text-[9px] text-[#a3583d] font-bold hover:underline">Marcar Todos</button>
                            </div>
                            <div class="grid grid-cols-1 gap-2 max-h-36 overflow-y-auto pr-1">
                                @foreach($this->getGirosList() as $g)
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                        <input type="checkbox" value="{{ $g }}" wire:model="selectedGiros" class="text-[#a3583d] focus:ring-[#a3583d]/20 border-[#3d2b1f]/10 rounded">
                                        <span class="capitalize text-[#3d2b1f]/85">{{ $g }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Input para agregar nuevo giro personalizado -->
                        <div class="flex gap-2 pt-2 border-t border-gray-100 mt-2">
                            <input type="text" wire:model="nuevoGiro" placeholder="Escribe otro giro..." class="flex-1 px-2.5 py-1.5 border border-[#3d2b1f]/10 rounded-lg text-xs" wire:keydown.enter.prevent="agregarGiroPersonalizado">
                            <button type="button" wire:click="agregarGiroPersonalizado" class="px-3 py-1.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-bold rounded-lg transition-all active:scale-95">+</button>
                        </div>
                        @error('selectedGiros') <span class="text-[10px] text-red-600 font-bold block pt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Checkbox de Ciudades -->
                    <div class="space-y-2 border border-[#3d2b1f]/10 p-3 rounded-xl bg-white max-h-60 overflow-y-auto flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100 mb-2">
                                <span class="text-[10px] font-black uppercase text-[#3d2b1f]/70">2. Seleccionar Ciudades</span>
                                <button type="button" wire:click="seleccionarTodasCiudades" class="text-[9px] text-[#a3583d] font-bold hover:underline">Marcar Todas</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto pr-1">
                                @foreach($this->getCiudadesList() as $c)
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                        <input type="checkbox" value="{{ $c }}" wire:model="selectedCiudades" class="text-[#a3583d] focus:ring-[#a3583d]/20 border-[#3d2b1f]/10 rounded">
                                        <span class="text-[#3d2b1f]/85">{{ $c }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Input para agregar nueva ciudad personalizada -->
                        <div class="flex gap-2 pt-2 border-t border-gray-100 mt-2">
                            <input type="text" wire:model="nuevaCiudad" placeholder="Escribe otra ciudad..." class="flex-1 px-2.5 py-1.5 border border-[#3d2b1f]/10 rounded-lg text-xs" wire:keydown.enter.prevent="agregarCiudadPersonalizada">
                            <button type="button" wire:click="agregarCiudadPersonalizada" class="px-3 py-1.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-bold rounded-lg transition-all active:scale-95">+</button>
                        </div>
                        @error('selectedCiudades') <span class="text-[10px] text-red-600 font-bold block pt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Selección de Organización y Botón de Envío -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end pt-2 border-t border-[#3d2b1f]/5">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                            3. Asignar Leads Extraídos a Grupo
                        </label>
                        <select wire:model="selected_org_id" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                            <option value="">Selecciona organización...</option>
                            @foreach($this->getOrganizaciones() as $org)
                                <option value="{{ $org->id }}">{{ $org->nombre }}</option>
                            @endforeach
                        </select>
                        @error('selected_org_id') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <button type="submit" class="w-full py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-95">
                            + Programar Tarea Múltiple
                        </button>
                    </div>
                </div>
            </form>

            <!-- Lista de Tareas -->
            <div class="space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]/60">
                    Tareas de Extracción Activas
                </h3>

                <!-- MÓVIL: TARJETAS -->
                <div class="block sm:hidden space-y-4">
                    @forelse($this->getConfigs() as $c)
                        @php
                            $girosArr = json_decode($c->giro, true);
                            $ciudadesArr = json_decode($c->ciudad, true);
                            
                            $girosText = is_array($girosArr) ? implode(', ', array_map('ucwords', $girosArr)) : ucwords($c->giro ?? '');
                            $ciudadesText = is_array($ciudadesArr) ? implode(', ', $ciudadesArr) : ($c->ciudad ?? '');
                            
                            $girosCount = is_array($girosArr) ? count($girosArr) : 1;
                            $ciudadesCount = is_array($ciudadesArr) ? count($ciudadesArr) : 1;
                        @endphp
                        <div class="bg-[#fdfaf6] border border-[#3d2b1f]/10 p-4 rounded-2xl space-y-3 shadow-sm">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black uppercase text-[#a3583d]">
                                    Giros ({{ $girosCount }}): <span class="text-[#3d2b1f] tracking-tight normal-case font-semibold">{{ $girosText }}</span>
                                </p>
                                <p class="text-[10px] font-black uppercase text-gray-500">
                                    Ciudades ({{ $ciudadesCount }}): <span class="text-[#3d2b1f] tracking-tight normal-case font-semibold">{{ $ciudadesText }}</span>
                                </p>
                            </div>
                            <div class="text-[10px] text-[#3d2b1f]/80 border-t border-[#3d2b1f]/5 pt-2">
                                <p class="font-semibold">Grupo Destino: <span class="font-black text-[#3d2b1f]">{{ $c->org_name }}</span></p>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <button wire:click="toggleEstado({{ $c->id }})" class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-md border transition-all active:scale-95
                                    {{ $c->estado ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-50 text-gray-400 border-gray-100' }}">
                                    {{ $c->estado ? 'Activo' : 'Pausado' }}
                                </button>
                                <button wire:click="eliminar({{ $c->id }})" class="text-red-500 hover:text-red-700 text-[10px] font-black uppercase tracking-wider active:scale-95">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-[#fdfaf6] border border-[#3d2b1f]/10 p-4 rounded-2xl text-center text-xs text-[#3d2b1f]/50">
                            No hay tareas programadas.
                        </div>
                    @endforelse
                </div>

                <!-- ESCRITORIO: TABLA -->
                <div class="hidden sm:block overflow-hidden border border-[#3d2b1f]/10 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-[#3d2b1f]/10">
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Giros Asignados</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Ciudades Asignadas</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Grupo Destino</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Estado (Cron)</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3d2b1f]/5">
                            @forelse($this->getConfigs() as $c)
                                @php
                                    $girosArr = json_decode($c->giro, true);
                                    $ciudadesArr = json_decode($c->ciudad, true);
                                    
                                    $girosText = is_array($girosArr) ? implode(', ', array_map('ucwords', $girosArr)) : ucwords($c->giro ?? '');
                                    $ciudadesText = is_array($ciudadesArr) ? implode(', ', $ciudadesArr) : ($c->ciudad ?? '');
                                    
                                    $girosCount = is_array($girosArr) ? count($girosArr) : 1;
                                    $ciudadesCount = is_array($ciudadesArr) ? count($ciudadesArr) : 1;
                                @endphp
                                <tr class="hover:bg-[#fdfaf6]/50">
                                    <td class="p-3 max-w-[220px]">
                                        <div class="font-bold text-[#3d2b1f]">({{ $girosCount }}) seleccionados</div>
                                        <div class="text-[10px] text-gray-500 truncate" title="{{ $girosText }}">{{ $girosText }}</div>
                                    </td>
                                    <td class="p-3 max-w-[220px]">
                                        <div class="font-bold text-[#3d2b1f]">({{ $ciudadesCount }}) seleccionadas</div>
                                        <div class="text-[10px] text-gray-500 truncate" title="{{ $ciudadesText }}">{{ $ciudadesText }}</div>
                                    </td>
                                    <td class="p-3 font-bold text-[#3d2b1f]">{{ $c->org_name }}</td>
                                    <td class="p-3 text-center">
                                        <button wire:click="toggleEstado({{ $c->id }})" class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-md border transition-all active:scale-95
                                            {{ $c->estado 
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100' 
                                                : 'bg-gray-50 text-gray-400 border-gray-100 hover:bg-gray-100' }}">
                                            {{ $c->estado ? 'Activo' : 'Pausado' }}
                                        </button>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button wire:click="eliminar({{ $c->id }})" class="text-red-500 hover:text-red-700 font-bold active:scale-95">
                                            🗑️ Borrar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-5 text-center text-[#3d2b1f]/40 font-medium">
                                        No hay tareas programadas en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================
         SECCIÓN B: CRUD ORGANIZACIONES (GRUPOS)
         ====================================================================== -->
    @if($currentSection === 'organizaciones')
        <div class="space-y-6">
            @if (session()->has('message_org'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center">
                    <span class="font-bold">{{ session('message_org') }}</span>
                    <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
                </div>
            @endif

            <!-- Formulario de Creación -->
            <form wire:submit="crearOrganizacion" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-[#fdfaf6] p-4 rounded-2xl border border-[#3d2b1f]/5">
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                        Nombre de la Organización / Grupo
                    </label>
                    <input type="text" wire:model="orgNombre" placeholder="Ej: Equipo Comercial Monterrey" class="w-full px-4 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                    @error('orgNombre') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <button type="submit" class="w-full py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-95">
                        Crear Organización
                    </button>
                </div>
            </form>

            <!-- Listado de Organizaciones -->
            <div class="space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]/60">
                    Organizaciones Registradas
                </h3>

                <div class="overflow-hidden border border-[#3d2b1f]/10 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-[#3d2b1f]/10">
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Nombre del Grupo</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3d2b1f]/5">
                            @forelse($this->getOrganizaciones() as $org)
                                <tr class="hover:bg-[#fdfaf6]/50">
                                    <td class="p-3 font-black text-[#3d2b1f] uppercase">{{ $org->nombre }}</td>
                                    <td class="p-3 text-center">
                                        <button wire:click="eliminarOrganizacion({{ $org->id }})" class="text-red-500 hover:text-red-700 font-bold active:scale-95">
                                            🗑️ Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="p-5 text-center text-[#3d2b1f]/40 font-medium">
                                        No hay organizaciones creadas. Comienza creando una arriba.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================
         SECCIÓN C: ASIGNACIÓN DE USUARIOS A ORGANIZACIONES
         ====================================================================== -->
    @if($currentSection === 'usuarios')
        <div class="space-y-6">
            @if (session()->has('message_users'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center">
                    <span class="font-bold">{{ session('message_users') }}</span>
                    <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
                </div>
            @endif

            <!-- Formulario de Asignación -->
            <form wire:submit="asignarUsuario" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-[#fdfaf6] p-4 rounded-2xl border border-[#3d2b1f]/5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                        Seleccionar Usuario
                    </label>
                    <select wire:model="userToAssign" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                        <option value="">Selecciona usuario...</option>
                        @foreach($this->getUsersList() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('userToAssign') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                        Asociar al Grupo / Org
                    </label>
                    <select wire:model="orgToAssign" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                        <option value="">Ninguna (Quitar de grupo)</option>
                        @foreach($this->getOrganizaciones() as $org)
                            <option value="{{ $org->id }}">{{ $org->nombre }}</option>
                        @endforeach
                    </select>
                    @error('orgToAssign') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="w-full py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-95">
                        Guardar Relación
                    </button>
                </div>
            </form>

            <!-- Listado General de Relaciones -->
            <div class="space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]/60">
                    Estructura de Usuarios Activos
                </h3>

                <div class="overflow-hidden border border-[#3d2b1f]/10 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-[#3d2b1f]/10">
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Nombre / Email</th>
                                <th class="p-3 font-bold text-[#3d2b1f]/70">Organización Asignada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#3d2b1f]/5">
                            @foreach($this->getUsersList() as $u)
                                <tr class="hover:bg-[#fdfaf6]/50">
                                    <td class="p-3">
                                        <div class="font-bold text-[#3d2b1f]">{{ $u->name }}</div>
                                        <div class="text-[9px] font-mono text-gray-500">{{ $u->email }}</div>
                                    </td>
                                    <td class="p-3">
                                        @if($u->org_name)
                                            <span class="inline-block px-2.5 py-1 bg-amber-50 border border-amber-100 text-[#a3583d] text-[10px] font-black uppercase tracking-wider rounded-lg">
                                                {{ $u->org_name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-medium">Sin organización asignada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
