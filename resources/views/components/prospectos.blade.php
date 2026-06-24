<?php

use Livewire\Component;
use App\Models\Prospecto;

new class extends Component
{
    public $search = '';
    public $statusFilter = '';

    use \Livewire\WithPagination;

    public function with()
    {
        $query = Prospecto::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('empresa', 'like', '%' . $this->search . '%')
                  ->orWhere('director_nombre', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('estado_contacto', $this->statusFilter);
        }

        return [
            'prospectos' => $query->orderBy('creado_at', 'desc')->paginate(15)
        ];
    }

    public function updateStatus($id, $status)
    {
        $prospecto = Prospecto::find($id);
        if ($prospecto) {
            $prospecto->estado_contacto = $status;
            $prospecto->save();
        }
    }
};
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto bg-white p-8 rounded-xl shadow-sm">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">CRM de Prospectos - Locknode</h1>

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 space-y-4 md:space-y-0">
            <input type="text" wire:model.live="search" placeholder="Buscar por empresa o director..." class="w-full md:w-1/3 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
            
            <select wire:model.live="statusFilter" class="w-full md:w-1/4 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                <option value="">Todos los Estados</option>
                <option value="Nuevo">Nuevo</option>
                <option value="Contactado">Contactado</option>
                <option value="Interesado">Interesado</option>
                <option value="Cerrado">Venta Cerrada</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-sm">
                        <th class="py-3 px-4">Empresa</th>
                        <th class="py-3 px-4">Contacto</th>
                        <th class="py-3 px-4">Teléfono</th>
                        <th class="py-3 px-4">Estado Actual</th>
                        <th class="py-3 px-4 text-center">Acción (Cambiar a)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($prospectos as $prospecto)
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                        <td class="py-3 px-4 font-medium">{{ $prospecto->empresa }}</td>
                        <td class="py-3 px-4">
                            {{ $prospecto->director_nombre }} <br>
                            <span class="text-xs text-gray-500">{{ $prospecto->correo_corporativo }}</span>
                        </td>
                        <td class="py-3 px-4">{{ $prospecto->telefono_whatsapp }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $prospecto->estado_contacto == 'Nuevo' || !$prospecto->estado_contacto ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $prospecto->estado_contacto == 'Contactado' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $prospecto->estado_contacto == 'Interesado' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $prospecto->estado_contacto == 'Cerrado' ? 'bg-green-100 text-green-800' : '' }}
                            ">
                                {{ $prospecto->estado_contacto ?: 'Nuevo' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center space-x-1">
                            @if($prospecto->estado_contacto !== 'Contactado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'Contactado')" class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">Contactado</button>
                            @endif
                            @if($prospecto->estado_contacto !== 'Interesado')
                            <button wire:click="updateStatus({{ $prospecto->id }}, 'Interesado')" class="text-xs bg-purple-500 text-white px-2 py-1 rounded hover:bg-purple-600 transition">Interesado</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No se encontraron prospectos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $prospectos->links() }}
        </div>
    </div>
</div>