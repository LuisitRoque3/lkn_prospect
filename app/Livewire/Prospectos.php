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
    public $fuenteFilter = '';
    public $giroFilter = '';
    public $vacantesFilter = '';

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
    }

    public function getTemplates()
    {
        try {
            return \App\Models\PlantillaWhatsapp::all();
        } catch (\Exception $e) {
            // Fallback si la tabla no existe aún
            return collect([
                (object)[
                    'id' => 1,
                    'titulo' => 'Plantilla 1: Control y Optimización',
                    'mensaje' => "Hola, me comunico de Locknode. Analizando el crecimiento operativo de {empresa}, nos gustaría compartirles nuestro modelo de control y optimización de flotas. ¿Con quién podría revisar esto brevemente?"
                ],
                (object)[
                    'id' => 2,
                    'titulo' => 'Plantilla 2: Reducir combustible y mermas',
                    'mensaje' => "Hola, en Locknode ayudamos a empresas en el sector de logística a reducir mermas de combustible y eficientar rutas de entrega. Nos interesaría presentarle una propuesta rápida para {empresa}. ¿Tendría 5 minutos esta semana?"
                ],
                (object)[
                    'id' => 3,
                    'titulo' => 'Plantilla 3: Alianzas y Automatización',
                    'mensaje' => "Hola, veo que manejan logística y transporte en {ubicacion}. En Locknode estamos haciendo alianzas para automatizar operaciones de {empresa}. ¿A qué correo podría enviarle nuestra presentación?"
                ],
            ]);
        }
    }

    public function saveTemplate()
    {
        $this->validate([
            'tempTemplateTitulo' => 'required|string|max:255',
            'tempTemplateMensaje' => 'required|string',
        ], [
            'tempTemplateTitulo.required' => 'El título es obligatorio.',
            'tempTemplateMensaje.required' => 'El mensaje es obligatorio.',
        ]);

        $limit = 10;
        try {
            if (!$this->tempTemplateId && \App\Models\PlantillaWhatsapp::count() >= $limit) {
                session()->flash('template_error', "Solo se permiten hasta {$limit} plantillas en el catálogo.");
                return;
            }

            if ($this->tempTemplateId) {
                $plantilla = \App\Models\PlantillaWhatsapp::find($this->tempTemplateId);
                if ($plantilla) {
                    $plantilla->update([
                        'titulo' => $this->tempTemplateTitulo,
                        'mensaje' => $this->tempTemplateMensaje,
                    ]);
                }
            } else {
                \App\Models\PlantillaWhatsapp::create([
                    'titulo' => $this->tempTemplateTitulo,
                    'mensaje' => $this->tempTemplateMensaje,
                ]);
            }
        } catch (\Exception $e) {
            // Manejo por si no se ha ejecutado la migración
        }

        $this->reset(['tempTemplateId', 'tempTemplateTitulo', 'tempTemplateMensaje', 'showTemplateManager']);
    }

    public function editTemplate($id)
    {
        try {
            $plantilla = \App\Models\PlantillaWhatsapp::find($id);
            if ($plantilla) {
                $this->tempTemplateId = $plantilla->id;
                $this->tempTemplateTitulo = $plantilla->titulo;
                $this->tempTemplateMensaje = $plantilla->mensaje;
                $this->showTemplateManager = true;
            }
        } catch (\Exception $e) {}
    }

    public function deleteTemplate($id)
    {
        try {
            $plantilla = \App\Models\PlantillaWhatsapp::find($id);
            if ($plantilla) {
                $plantilla->delete();
            }
        } catch (\Exception $e) {}

        if ($this->selectedTemplate == $id) {
            $this->selectedTemplate = null;
            $this->whatsappMessage = '';
        }
    }

    public function cancelTemplateEdit()
    {
        $this->reset(['tempTemplateId', 'tempTemplateTitulo', 'tempTemplateMensaje', 'showTemplateManager']);
    }

    public function openWhatsappModal($id)
    {
        $this->selectedProspectForWhatsapp = Prospecto::findOrFail($id);
        
        $templates = $this->getTemplates();
        $firstTemplate = $templates->first();
        
        if ($firstTemplate) {
            $this->selectedTemplate = $firstTemplate->id;
            $this->whatsappMessage = $this->getFormattedMessage($firstTemplate->id);
        } else {
            $this->selectedTemplate = null;
            $this->whatsappMessage = '';
        }
        
        $this->showWhatsappModal = true;
    }

    public function updatedSelectedTemplate($value)
    {
        if ($this->selectedProspectForWhatsapp) {
            $this->whatsappMessage = $this->getFormattedMessage($value);
        }
    }

    private function getFormattedMessage($templateId)
    {
        if (!$templateId) return '';
        
        $templates = $this->getTemplates();
        $templateObj = $templates->firstWhere('id', $templateId);
        
        // Si no lo encuentra por ID, podría ser de los fallbacks por defecto
        if (!$templateObj && is_numeric($templateId)) {
            $fallbackTemplates = [
                1 => "Hola, me comunico de Locknode. Analizando el crecimiento operativo de {empresa}, nos gustaría compartirles nuestro modelo de control y optimización de flotas. ¿Con quién podría revisar esto brevemente?",
                2 => "Hola, en Locknode ayudamos a empresas en el sector de logística a reducir mermas de combustible y eficientar rutas de entrega. Nos interesaría presentarle una propuesta rápida para {empresa}. ¿Tendría 5 minutos esta semana?",
                3 => "Hola, veo que manejan logística y transporte en {ubicacion}. En Locknode estamos haciendo alianzas para automatizar operaciones de {empresa}. ¿A qué correo podría enviarle nuestra presentación?"
            ];
            $template = $fallbackTemplates[$templateId] ?? '';
        } else {
            $template = $templateObj ? $templateObj->mensaje : '';
        }
        
        $empresa = $this->selectedProspectForWhatsapp->empresa ?? '';
        $ubicacion = $this->selectedProspectForWhatsapp->ubicacion_local ?? 'su ciudad';
        
        // Limpiamos la ubicación de detalles de calle para que en el mensaje se lea natural (ej: "Querétaro")
        if (str_contains($ubicacion, ',')) {
            $parts = explode(',', $ubicacion);
            $ubicacion = trim(end($parts));
            // Si el último es México/MX, intentamos con el anterior
            if (in_array(strtolower($ubicacion), ['méxico', 'mexico', 'mx']) && count($parts) > 1) {
                $ubicacion = trim($parts[count($parts) - 2]);
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

        if ($this->fuenteFilter) {
            $query->where('fuente_descubrimiento', $this->fuenteFilter);
        }

        if ($this->giroFilter) {
            $query->where('giro_negocio', $this->giroFilter);
        }

        if ($this->vacantesFilter !== '') {
            $query->where('vacantes_activas', $this->vacantesFilter);
        }

        // Obtener giros únicos disponibles en la BD para el buscador/filtro dinámico
        $girosDisponibles = [];
        try {
            $girosDisponibles = \Illuminate\Support\Facades\DB::table('prospectos_scrapping')
                ->whereNotNull('giro_negocio')
                ->where('giro_negocio', '!=', '')
                ->distinct()
                ->pluck('giro_negocio')
                ->toArray();
        } catch (\Exception $e) {
            // Fallback si falla
        }

        return view('livewire.prospectos', [
            'prospectos' => $query->orderBy('creado_at', 'desc')->paginate(15),
            'girosDisponibles' => $girosDisponibles
        ]);
    }
}
