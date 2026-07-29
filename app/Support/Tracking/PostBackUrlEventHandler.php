<?php

namespace App\Support\Tracking;


use App\Support\Tracking\Events\Listeners\BonusListener;
use App\Support\Tracking\Events\Listeners\ConversionListener;
use App\Support\Tracking\Events\Listeners\DeductionListener;
use App\Support\Tracking\Events\Listeners\FreeSignUpListener;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostBackUrlEventHandler
{


    const FUNCTION_CONVERT = "";
    const FUNCTION_DEDUCT = "deduct";
    const FUNCTION_FREE = "free";


    public $eventListeners = [];

    public function __construct()
    {
        $this->createEventObjects();
    }


    public function handleRequest()
    {
        foreach ($this->eventListeners as $listener) {
            if ($listener->shouldBeDispatched()) {
                return $listener->dispatch();
            }
        }

        return response()->json(['status' => 404, 'message' => 'Unknown request.'], 404);
    }


    public function createEventObjects()
    {
        $this->eventListeners[] = new ConversionListener();
        $this->eventListeners[] = new DeductionListener();
        $this->eventListeners[] = new FreeSignUpListener();
        $this->eventListeners[] = new BonusListener();
    }


}
