<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $giro = '';
    public $ciudad = '';

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
    ];

    public function agregar()
    {
        $this->validate();

        DB::table('configuraciones_extraccion')->insert([
            'user_id' => Auth::id(),
            'giro' => strtolower($this->giro),
            'ciudad' => $this->ciudad,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->reset(['giro', 'ciudad']);
        session()->flash('message', 'Parámetro de extracción agregado con éxito.');
    }

    public function toggleEstado($id)
    {
        $config = DB::table('configuraciones_extraccion')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($config) {
            DB::table('configuraciones_extraccion')
                ->where('id', $id)
                ->update(['estado' => !$config->estado, 'updated_at' => now()]);
        }
    }

    public function eliminar($id)
    {
        DB::table('configuraciones_extraccion')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        session()->flash('message', 'Parámetro de extracción eliminado.');
    }

    public function getConfigs()
    {
        return DB::table('configuraciones_extraccion')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
};
?>

<div class="bg-white border border-[#3d2b1f]/10 rounded-3xl p-6 shadow-sm space-y-6">
    <div class="border-b border-[#3d2b1f]/10 pb-4">
        <h2 class="text-lg font-black uppercase tracking-tight text-[#3d2b1f]">
            ⚙️ Configuración del Extractor (Cron)
        </h2>
        <p class="text-xs text-[#3d2b1f]/60 font-medium mt-1">
            Define los giros y ciudades que el motor extraerá automáticamente en la siguiente ejecución del Cron.
        </p>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-xs rounded-r-xl flex justify-between items-center">
            <span class="font-bold">{{ session('message') }}</span>
            <button type="button" class="text-emerald-800 font-bold hover:underline" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    <!-- Formulario para agregar -->
    <form wire:submit="agregar" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-[#fdfaf6] p-4 rounded-2xl border border-[#3d2b1f]/5">
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
            <button type="submit" class="w-full py-2.5 bg-[#a3583d] hover:bg-[#8f4730] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md">
                + Agregar Tarea
            </button>
        </div>
    </form>

    <!-- Lista de configuraciones actuales -->
    <div class="space-y-3">
        <h3 class="text-xs font-black uppercase tracking-wider text-[#3d2b1f]/60">
            Tareas Programadas Activas
        </h3>

        <div class="overflow-hidden border border-[#3d2b1f]/10 rounded-2xl">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-[#3d2b1f]/10">
                        <th class="p-3 font-bold text-[#3d2b1f]/70">Giro</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70">Ciudad</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Estado (Cron)</th>
                        <th class="p-3 font-bold text-[#3d2b1f]/70 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3d2b1f]/5">
                    @forelse($this->getConfigs() as $c)
                        <tr class="hover:bg-[#fdfaf6]/50">
                            <td class="p-3 font-bold uppercase text-[#3d2b1f]">{{ $c->giro }}</td>
                            <td class="p-3 font-medium text-[#3d2b1f]/80">{{ $c->ciudad }}</td>
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
                            <td colspan="4" class="p-5 text-center text-[#3d2b1f]/40 font-medium">
                                No tienes tareas programadas para el extractor. El motor usará la configuración aleatoria por defecto.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
