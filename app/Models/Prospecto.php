<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $table = 'prospectos_scrapping';
    protected $guarded = [];

    const CREATED_AT = 'creado_at';
    const UPDATED_AT = 'actualizado_at';
