<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\UID;

class ClickIdToolController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'clickid' => 'required|string',
            'action' => 'required|in:encode,decode',
        ]);

        $value = $validated['action'] === 'encode'
            ? UID::encode($validated['clickid'])
            : UID::decode($validated['clickid']);

        return response($value);
    }
}
