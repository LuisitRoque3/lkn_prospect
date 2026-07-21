<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = 'prospectos_scrapping';
    protected $guarded = [];

    const CREATED_AT = 'creado_at';
    const UPDATED_AT = 'actualizado_at';

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    /**
     * Limpia el teléfono dejándolo solo en números.
     */
    public function getCleanPhoneAttribute()
    {
        if (empty($this->telefono_whatsapp)) return null;
        // Remueve todo lo que no sea número
        $numero = preg_replace('/[^0-9]/', '', $this->telefono_whatsapp);
        // Si el número tiene 10 dígitos (estándar México), le agrega el 52
        if (strlen($numero) == 10) {
            $numero = '52' . $numero;
        }
        return $numero;
    }

    /**
     * Genera la URL dinámica para WhatsApp
     */
    public function getWhatsappUrlAttribute()
    {
        $phone = $this->clean_phone;
        if (!$phone) return '#';
        
        $empresa = urlencode($this->empresa);
        $mensaje = "Hola, me comunico de Locknode. He analizado el crecimiento operativo de {$empresa} y me gustaría compartirles nuestro modelo de control. ¿Con quién podría rebotar esto brevemente?";
        return "https://wa.me/{$phone}?text={$mensaje}";
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'organizacion_id');
    }
}
