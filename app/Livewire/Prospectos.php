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

    // Create Modal Properties
    public $showCreateModal = false;
    public $empresa = '';
    public $director_nombre = '';
    public $correo_corporativo = '';
    public $telefono_whatsapp = '';
    public $estado_contacto = 'pendiente';
    public $priority = 'charlie';

    protected $rules = [
        'empresa' => 'required|string|max:255',
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
        $this->reset(['empresa', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto', 'priority']);
        $this->estado_contacto = 'pendiente';
        $this->priority = 'charlie';
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function save()
    {
        $this->validate();

        // Generar uuid para tracking
        $uuid = (string) \Illuminate\Support\Str::uuid();

        Prospecto::create([
            'empresa' => $this->empresa,
            'director_nombre' => $this->director_nombre,
            'correo_corporativo' => $this->correo_corporativo,
            'telefono_whatsapp' => $this->telefono_whatsapp,
            'estado_contacto' => $this->estado_contacto,
            'priority' => $this->priority,
            'tracking_uuid' => $uuid,
        ]);

        $this->showCreateModal = false;
        $this->reset(['empresa', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto', 'priority']);
        
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
