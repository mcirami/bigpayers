<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class LanderController extends Controller
{


    public function getAsset($subDomain, $asset)
    {
        $path = storage_path('landers/' . $subDomain . '/' . $asset);

        if ( ! File::exists($path)) {
            abort(404);
        }


        $file = File::get($path);
        $type = File::mimeType($path);

        $response = \Illuminate\Support\Facades\Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

}
