<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use LeadMax\TrackYourStats\System\Lander;

class LanderController extends Controller
{


    public function getAsset($subDomain, $asset)
    {
        $path = Lander::resolveAssetPath($subDomain, $asset);

        if (!$path || !File::exists($path) || File::isDirectory($path)) {
            abort(404);
        }


        $file = File::get($path);
        $type = File::mimeType($path);

        $response = \Illuminate\Support\Facades\Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

}
