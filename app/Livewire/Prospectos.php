<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Prospecto;
use Illuminate\Support\Facades\Mail;
use App\Mail\ColdOutreachMail;

class Prospectos extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $priorityFilter = '';

    // Create/Edit Modal Properties
    public $showCreateModal = false;
    public $prospectoId;
    public $empresa = '';
    public $ubicacion_local = '';
    public $director_nombre = '';
    public $correo_corporativo = '';
    public $telefono_whatsapp = '';
    public $estado_contacto = 'pendiente';
    public $priority = 'charlie';

    protected $rules = [
        'empresa' => 'required|string|max:255',
        'ubicacion_local' => 'nullable|string|max:255',
        'director_nombre' => 'nullable|string|max:255',
        'correo_corporativo' => 'nullable|email|max:255',
        'telefono_whatsapp' => 'nullable|string|max:255',
        'estado_contacto' => 'required|in:pendiente,enviado,respondido,descartado',
        'priority' => 'required|in:alfa,bravo,charlie',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['prospectoId', 'empresa', 'ubicacion_local', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto', 'priority']);
        $this->estado_contacto = 'pendiente';
        $this->priority = 'charlie';
        $this->showCreateModal = true;
    }

    public function edit($id)
    {
        $this->resetErrorBag();
        $prospecto = Prospecto::findOrFail($id);
        
        $this->prospectoId = $prospecto->id;
        $this->empresa = $prospecto->empresa;
        $this->ubicacion_local = $prospecto->ubicacion_local;
        $this->director_nombre = $prospecto->director_nombre;
        $this->correo_corporativo = $prospecto->correo_corporativo;
        $this->telefono_whatsapp = $prospecto->telefono_whatsapp;
        $this->estado_contacto = $prospecto->estado_contacto;
        $this->priority = $prospecto->priority;
        
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function save()
    {
        $this->validate();

        if ($this->prospectoId) {
            $prospecto = Prospecto::findOrFail($this->prospectoId);
            $prospecto->update([
                'empresa' => $this->empresa,
                'ubicacion_local' => $this->ubicacion_local,
                'director_nombre' => $this->director_nombre,
                'correo_corporativo' => $this->correo_corporativo,
                'telefono_whatsapp' => $this->telefono_whatsapp,
                'estado_contacto' => $this->estado_contacto,
                'priority' => $this->priority,
            ]);
            session()->flash('message', 'Prospecto actualizado exitosamente.');
        } else {
            // Generar uuid para tracking solo al crear
            $uuid = (string) \Illuminate\Support\Str::uuid();
            Prospecto::create([
                'empresa' => $this->empresa,
                'ubicacion_local' => $this->ubicacion_local,
                'director_nombre' => $this->director_nombre,
                'correo_corporativo' => $this->correo_corporativo,
                'telefono_whatsapp' => $this->telefono_whatsapp,
                'estado_contacto' => $this->estado_contacto,
                'priority' => $this->priority,
                'tracking_uuid' => $uuid,
            ]);
            session()->flash('message', 'Prospecto creado exitosamente.');
        }

        $this->showCreateModal = false;
        $this->reset(['prospectoId', 'empresa', 'ubicacion_local', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto', 'priority']);
    }

    public function updateStatus($id, $status)
    {
        $prospecto = Prospecto::find($id);
        if ($prospecto) {
            $prospecto->estado_contacto = $status;
            $prospecto->save();
        }
    }

    public function updatePriority($id, $priority)
    {
        $prospecto = Prospecto::find($id);
        if ($prospecto) {
            $prospecto->priority = $priority;
            $prospecto->save();
        }
    }

    public function sendColdEmail($id)
    {
        $prospecto = Prospecto::find($id);
        if ($prospecto && $prospecto->correo_corporativo) {
            // Generar UUID si es un prospecto viejo que no lo tiene
            if (!$prospecto->tracking_uuid) {
                $prospecto->tracking_uuid = (string) \Illuminate\Support\Str::uuid();
            }
            
            // Enviar correo
            Mail::to($prospecto->correo_corporativo)->send(new ColdOutreachMail($prospecto));
            
            // Actualizar estado a enviado automáticamente
            $prospecto->estado_contacto = 'enviado';
            $prospecto->save();
            
            session()->flash('message', 'Correo de prospección enviado a ' . $prospecto->empresa);
        } elseif ($prospecto) {
            session()->flash('error', 'El prospecto ' . $prospecto->empresa . ' no tiene un correo registrado.');
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

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        return view('livewire.prospectos', [
            'prospectos' => $query->orderBy('creado_at', 'desc')->paginate(15)
        ]);
    }
}
