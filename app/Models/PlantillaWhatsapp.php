<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaWhatsapp extends Model
{
    protected $table = 'plantillas_whatsapp';
    protected $fillable = ['titulo', 'mensaje'];
}
