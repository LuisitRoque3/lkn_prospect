<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $giro = '';
    public $ciudad = '';
    public $selected_user_id = '';

    // Catálogo para seleccionar
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

    protected $rules = [
        'giro' => 'required|string',
        'ciudad' => 'required|string',
        'selected_user_id' => 'required|exists:users,id',
    ];

    public function mount()
    {
        // Seguridad: Asegurar que el usuario sea administrador
        abort_unless(Auth::user()->is_admin, 403);
    }

    public function agregar()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $this->validate();

        DB::table('configuraciones_extraccion')->insert([
            'user_id' => $this->selected_user_id,
            'giro' => strtolower($this->giro),
            'ciudad' => $this->ciudad,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->reset(['giro', 'ciudad', 'selected_user_id']);
        session()->flash('message', 'Tarea programada asignada con éxito al usuario.');
    }

    public function toggleEstado($id)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $config = DB::table('configuraciones_extraccion')
            ->where('id', $id)
            ->first();

        if ($config) {
            DB::table('configuraciones_extraccion')
                ->where('id', $id)
                ->update(['estado' => !$config->estado, 'updated_at' => now()]);
        }
    }

    public function eliminar($id)
    {
        abort_unless(Auth::user()->is_admin, 403);
        DB::table('configuraciones_extraccion')
            ->where('id', $id)
            ->delete();

        session()->flash('message', 'Tarea programada eliminada.');
    }

    public function getConfigs()
    {
        return DB::table('configuraciones_extraccion')
            ->join('users', 'configuraciones_extraccion.user_id', '=', 'users.id')
            ->select('configuraciones_extraccion.*', 'users.name as user_name', 'users.email as user_email')
            ->orderBy('configuraciones_extraccion.created_at', 'desc')
            ->get();
    }

    public function getUsers()
    {
        return DB::table('users')->select('id', 'name', 'email')->orderBy('name', 'asc')->get();
    }
};
?>

<div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-6 shadow-sm space-y-6">
    <div class="border-b border-[#3d2b1f]/10 pb-4">
        <h2 class="text-lg font-black uppercase tracking-tight text-[#3d2b1f]">
            ⚙️ Configuración del Extractor (Administrador)
        </h2>
        <p class="text-xs text-[#3d2b1f]/60 font-medium mt-1">
            Programa tareas de extracción automáticas para giros y ciudades específicas, y asigna a qué usuario le caerán los leads obtenidos.
        </p>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center">
            <span class="font-bold">{{ session('message') }}</span>
            <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    <!-- Formulario para agregar -->
    <form wire:submit="agregar" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end bg-[#fdfaf6] p-4 rounded-2xl border border-[#3d2b1f]/5">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                Giro del Negocio
            </label>
            <select wire:model="giro" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                <option value="">Selecciona un giro...</option>
                @foreach($listaGiros as $g)
                    <option value="{{ $g }}">{{ ucwords($g) }}</option>
                @endforeach
            </select>
            @error('giro') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                Ciudad / Ubicación
            </label>
            <select wire:model="ciudad" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                <option value="">Selecciona una ciudad...</option>
                @foreach($listaCiudades as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
            @error('ciudad') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-[#3d2b1f]/70 mb-1.5">
                Asignar Leads a
            </label>
            <select wire:model="selected_user_id" class="w-full px-3 py-2.5 bg-white border border-[#3d2b1f]/10 rounded-xl text-xs text-[#3d2b1f] focus:outline-none focus:ring-2 focus:ring-[#a3583d]/20 focus:border-[#a3583d]">
                <option value="">Selecciona usuario...</option>
                @foreach($this->getUsers() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
            @error('selected_user_id') <span class="text-[10px] text-red-600 font-bold">{{ $message }}</span> @enderror
        </div>

        <div>
            <button type="submit" class="w-full py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md">
                + Programar Tarea
            </button>
        </div>
    </form>

    <!-- Lista de configuraciones actuales -->
    <div class="space-y-3">
        <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]/60">
            Tareas de Extracción Activas
        </h3>

        <!-- MÓVIL: TARJETAS DE TAREAS -->
        <div class="block sm:hidden space-y-4">
            @forelse($this->getConfigs() as $c)
                <div class="bg-[#fdfaf6] border border-[#3d2b1f]/10 p-4 rounded-2xl space-y-3 shadow-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-[#a3583d]">{{ $c->giro }}</span>
                        <span class="text-[10px] text-[#3d2b1f]/60 font-bold">📍 {{ $c->ciudad }}</span>
                    </div>
                    <div class="text-[10px] text-[#3d2b1f]/80 border-t border-[#3d2b1f]/5 pt-2">
                        <p class="font-semibold">Asignado a: <span class="font-black text-[#3d2b1f]">{{ $c->user_name }}</span></p>
                        <p class="text-[8px] font-mono opacity-50">{{ $c->user_email }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <button wire:click="toggleEstado({{ $c->id }})" class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-md border transition-all
                            {{ $c->estado ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-50 text-gray-400 border-gray-100' }}">
                            {{ $c->estado ? 'Activo' : 'Pausado' }}
                        </button>
                        <button wire:click="eliminar({{ $c->id }})" class="text-red-500 hover:text-red-700 text-[10px] font-black uppercase tracking-wider">
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
                        <th class="p-3 font-bold text-[#3d2b1f]/70">Giro</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70">Ciudad</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70">Usuario Asignado</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Estado (Cron)</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3d2b1f]/5">
                    @forelse($this->getConfigs() as $c)
                        <tr class="hover:bg-[#fdfaf6]/50">
                            <td class="p-3 font-bold uppercase text-[#3d2b1f]">{{ $c->giro }}</td>
                            <td class="p-3 font-medium text-[#3d2b1f]/80">{{ $c->ciudad }}</td>
                            <td class="p-3">
                                <div class="font-bold text-[#3d2b1f]">{{ $c->user_name }}</div>
                                <div class="text-[9px] font-mono text-gray-500">{{ $c->user_email }}</div>
                            </td>
                            <td class="p-3 text-center">
                                <button wire:click="toggleEstado({{ $c->id }})" class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-md border transition-all
                                    {{ $c->estado 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100' 
                                        : 'bg-gray-50 text-gray-400 border-gray-100 hover:bg-gray-100' }}">
                                    {{ $c->estado ? 'Activo' : 'Pausado' }}
                                </button>
                            </td>
                            <td class="p-3 text-center">
                                <button wire:click="eliminar({{ $c->id }})" class="text-red-500 hover:text-red-700 font-bold">
                                    🗑️ Borrar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-5 text-center text-[#3d2b1f]/40 font-medium">
                                No hay tareas programadas para el extractor en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
