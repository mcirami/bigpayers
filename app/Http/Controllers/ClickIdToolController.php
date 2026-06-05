<?php

namespace App\Http\Controllers;

use App\Support\LegacyUid as UID;
use Illuminate\Http\Request;

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
