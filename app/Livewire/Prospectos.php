<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Prospecto;

class Prospectos extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Create Modal Properties
    public $showCreateModal = false;
    public $empresa = '';
    public $director_nombre = '';
    public $correo_corporativo = '';
    public $telefono_whatsapp = '';
    public $estado_contacto = 'pendiente';

    protected $rules = [
        'empresa' => 'required|string|max:255',
        'director_nombre' => 'nullable|string|max:255',
        'correo_corporativo' => 'nullable|email|max:255',
        'telefono_whatsapp' => 'nullable|string|max:255',
        'estado_contacto' => 'required|in:pendiente,enviado,respondido,descartado',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['empresa', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto']);
        $this->estado_contacto = 'pendiente';
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function save()
    {
        $this->validate();

        Prospecto::create([
            'empresa' => $this->empresa,
            'director_nombre' => $this->director_nombre,
            'correo_corporativo' => $this->correo_corporativo,
            'telefono_whatsapp' => $this->telefono_whatsapp,
            'estado_contacto' => $this->estado_contacto,
        ]);

        $this->showCreateModal = false;
        $this->reset(['empresa', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto']);
        
        session()->flash('message', 'Prospecto creado exitosamente.');
    }

    public function updateStatus($id, $status)
    {
        $prospecto = Prospecto::find($id);
        if ($prospecto) {
            $prospecto->estado_contacto = $status;
            $prospecto->save();
        }
    }

    public function render()
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

        return view('livewire.prospectos', [
            'prospectos' => $query->orderBy('creado_at', 'desc')->paginate(15)
        ]);
    }
}
