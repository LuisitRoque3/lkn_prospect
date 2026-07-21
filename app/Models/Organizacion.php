<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizacion extends Model
{
    protected $table = 'organizaciones';
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organizacion_user', 'organizacion_id', 'user_id');
    }
}
