<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Prospecto;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ColdOutreachMail;

class Prospectos extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $priorityFilter = '';
    public $fuenteFilter = '';
    public $giroFilter = '';
    public $vacantesFilter = '';
    public $sortField = 'creado_at';
    public $sortDirection = 'desc';

    // Propiedades optimizadas para evitar consultas repetitivas
    public $girosDisponibles = [];
    public $plantillasList = [];

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

    // WhatsApp Modal Properties
    public $showWhatsappModal = false;
    public $selectedProspectForWhatsapp = null;
    public $whatsappMessage = '';
    public $selectedTemplate = null;

    // WhatsApp Template CRUD Properties
    public $showTemplateManager = false;
    public $tempTemplateId = null;
    public $tempTemplateTitulo = '';
    public $tempTemplateMensaje = '';

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

    public function updatingFuenteFilter()
    {
        $this->resetPage();
    }

    public function updatingGiroFilter()
    {
        $this->resetPage();
    }

    public function updatingVacantesFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
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
        $prospecto = Prospecto::where('organizacion_id', Auth::user()->organizacion_id)->findOrFail($id);
        
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

    public function mount()
    {
        // Si no hay plantillas en la base de datos, creamos las 3 por defecto
        try {
            if (\App\Models\PlantillaWhatsapp::count() === 0) {
                \App\Models\PlantillaWhatsapp::create([
                    'titulo' => 'Modelo de Control y Optimización (Flotas)',
                    'mensaje' => 'Hola, me comunico de Locknode. Analizando el crecimiento operativo de {empresa}, nos gustaría compartirles nuestro modelo de control y optimización de flotas. ¿Con quién podría revisar esto brevemente?'
                ]);
                \App\Models\PlantillaWhatsapp::create([
                    'titulo' => 'Reducir combustible y mermas (Propuesta Rápida)',
                    'mensaje' => 'Hola, en Locknode ayudamos a empresas en el sector de logística a reducir mermas de combustible y eficientar rutas de entrega. Nos interesaría presentarle una propuesta rápida para {empresa}. ¿Tendría 5 minutos esta semana?'
                ]);
                \App\Models\PlantillaWhatsapp::create([
                    'titulo' => 'Alianzas y Automatización (Ubicación/Sector)',
                    'mensaje' => 'Hola, veo que manejan logística y transporte en {ubicacion}. En Locknode estamos haciendo alianzas para automatizar operaciones de {empresa}. ¿A qué correo podría enviarle nuestra presentación?'
                ]);
            }
        } catch (\Exception $e) {
            // Silencioso en caso de que la migración no se haya ejecutado aún
        }

        // Cargar listas una sola vez en el ciclo de vida del componente
        $this->cargarGirosDisponibles();
        $this->cargarPlantillas();
    }

    public function cargarGirosDisponibles()
    {
        try {
            $orgId = Auth::user()->organizacion_id;
            // Almacenar en caché por 1 hora (3600 segundos) para evitar costosos table scans
            $this->girosDisponibles = \Illuminate\Support\Facades\Cache::remember(
                "giros_disponibles_org_" . ($orgId ?? 'null'), 
                3600, 
                function() use ($orgId) {
                    return \Illuminate\Support\Facades\DB::table('prospectos_scrapping')
                        ->where('organizacion_id', $orgId)
                        ->whereNotNull('giro_negocio')
                        ->where('giro_negocio', '!=', '')
                        ->distinct()
                        ->pluck('giro_negocio')
                        ->toArray();
                }
            );
        } catch (\Exception $e) {
            $this->girosDisponibles = [];
        }
    }

    public function cargarPlantillas()
    {
        try {
            $this->plantillasList = \App\Models\PlantillaWhatsapp::all()->toArray();
        } catch (\Exception $e) {
            $this->plantillasList = [];
        }
    }

    public function getTemplates()
    {
        // Retornar la lista en caché local en lugar de consultar BD
        return collect(json_decode(json_encode($this->plantillasList)));
    }

    public function saveTemplate()
    {
        $this->validate([
            'tempTemplateTitulo' => 'required|string|max:255',
            'tempTemplateMensaje' => 'required|string',
        ]);

        if ($this->tempTemplateId) {
            $template = \App\Models\PlantillaWhatsapp::findOrFail($this->tempTemplateId);
            $template->update([
                'titulo' => $this->tempTemplateTitulo,
                'mensaje' => $this->tempTemplateMensaje,
            ]);
            session()->flash('message', 'Plantilla actualizada.');
        } else {
            \App\Models\PlantillaWhatsapp::create([
                'titulo' => $this->tempTemplateTitulo,
                'mensaje' => $this->tempTemplateMensaje,
            ]);
            session()->flash('message', 'Plantilla creada.');
        }

        $this->cargarPlantillas(); // Recargar caché
        $this->reset(['tempTemplateId', 'tempTemplateTitulo', 'tempTemplateMensaje']);
    }

    public function editTemplate($id)
    {
        $template = \App\Models\PlantillaWhatsapp::findOrFail($id);
        $this->tempTemplateId = $template->id;
        $this->tempTemplateTitulo = $template->titulo;
        $this->tempTemplateMensaje = $template->mensaje;
    }

    public function deleteTemplate($id)
    {
        \App\Models\PlantillaWhatsapp::findOrFail($id)->delete();
        $this->cargarPlantillas(); // Recargar caché
        session()->flash('message', 'Plantilla eliminada.');
    }

    public function cancelTemplateEdit()
    {
        $this->reset(['tempTemplateId', 'tempTemplateTitulo', 'tempTemplateMensaje', 'showTemplateManager']);
    }

    public function openWhatsappModal($id)
    {
        $this->selectedProspectForWhatsapp = Prospecto::findOrFail($id);
        $this->whatsappMessage = '';
        $this->selectedTemplate = null;
        $this->showWhatsappModal = true;
    }

    public function updatedSelectedTemplate($templateId)
    {
        if ($templateId) {
            $template = collect($this->plantillasList)->firstWhere('id', $templateId);
            if ($template) {
                $mensaje = is_array($template) ? $template['mensaje'] : $template->mensaje;
                $this->whatsappMessage = $this->parseTemplate($mensaje, $this->selectedProspectForWhatsapp->empresa, $this->selectedProspectForWhatsapp->ubicacion_local);
            }
        } else {
            $this->whatsappMessage = '';
        }
    }

    private function parseTemplate($template, $empresa, $ubicacion)
    {
        if (!$ubicacion) {
            $ubicacion = 'México';
        }
        
        // Limpiar dirección muy larga para el mensaje
        if (strlen($ubicacion) > 30) {
            $parts = explode(',', $ubicacion);
            if (count($parts) > 1) {
                // Tomar ciudad y estado
                $ubicacion = trim($parts[count($parts) - 3] ?? $parts[0]);
            }
        }

        return str_replace(['{empresa}', '{ubicacion}'], [$empresa, $ubicacion], $template);
    }

    public function closeWhatsappModal()
    {
        $this->showWhatsappModal = false;
        $this->reset(['selectedProspectForWhatsapp', 'whatsappMessage', 'selectedTemplate', 'showTemplateManager', 'tempTemplateId', 'tempTemplateTitulo', 'tempTemplateMensaje']);
    }

    public function markWhatsappAsSent()
    {
        if ($this->selectedProspectForWhatsapp) {
            $this->selectedProspectForWhatsapp->estado_contacto = 'enviado';
            $this->selectedProspectForWhatsapp->save();
        }
        $this->closeWhatsappModal();
    }

    public function save()
    {
        $this->validate();

        if ($this->prospectoId) {
            $prospecto = Prospecto::where('organizacion_id', Auth::user()->organizacion_id)->findOrFail($this->prospectoId);
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
                'user_id' => Auth::id(),
                'organizacion_id' => Auth::user()->organizacion_id,
            ]);
            session()->flash('message', 'Prospecto creado exitosamente.');
        }

        \Illuminate\Support\Facades\Cache::forget("giros_disponibles_org_" . (Auth::user()->organizacion_id ?? 'null'));
        $this->cargarGirosDisponibles(); // Recargar si se creó nuevo giro
        $this->showCreateModal = false;
        $this->reset(['prospectoId', 'empresa', 'ubicacion_local', 'director_nombre', 'correo_corporativo', 'telefono_whatsapp', 'estado_contacto', 'priority']);
    }

    public function updateStatus($id, $status)
    {
        $prospecto = Prospecto::where('organizacion_id', Auth::user()->organizacion_id)->find($id);
        if ($prospecto) {
            $prospecto->estado_contacto = $status;
            $prospecto->save();
        }
    }

    public function updatePriority($id, $priority)
    {
        $prospecto = Prospecto::where('organizacion_id', Auth::user()->organizacion_id)->find($id);
        if ($prospecto) {
            $prospecto->priority = $priority;
            $prospecto->save();
        }
    }

    public function sendColdEmail($id)
    {
        $prospecto = Prospecto::where('organizacion_id', Auth::user()->organizacion_id)->find($id);
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

        // Filtrar leads: Solo los de la organización del usuario autenticado
        $query->where('organizacion_id', Auth::user()->organizacion_id);

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

        if ($this->fuenteFilter) {
            $query->where('fuente_descubrimiento', $this->fuenteFilter);
        }

        if ($this->giroFilter) {
            $query->where('giro_negocio', $this->giroFilter);
        }

        if ($this->vacantesFilter !== '') {
            $query->where('vacantes_activas', $this->vacantesFilter);
        }

        return view('livewire.prospectos', [
            'prospectos' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
            'girosDisponibles' => $this->girosDisponibles
        ]);
    }
}
