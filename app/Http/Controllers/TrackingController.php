<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prospecto;

class TrackingController extends Controller
{
    public function track($uuid)
    {
        // Buscar el prospecto por el UUID
        $prospecto = Prospecto::where('tracking_uuid', $uuid)->first();

        if ($prospecto) {
            $prospecto->opened_at = now();
            $prospecto->open_count = $prospecto->open_count + 1;
            $prospecto->save();
        }

        // Píxel transparente de 1x1 (GIF)
        $pixel = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
