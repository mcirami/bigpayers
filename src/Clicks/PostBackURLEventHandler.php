<?php

namespace LeadMax\TrackYourStats\Clicks;


use LeadMax\TrackYourStats\Clicks\URLEvents\Listeners\BonusListener;
use LeadMax\TrackYourStats\Clicks\URLEvents\Listeners\ConversionListener;
use LeadMax\TrackYourStats\Clicks\URLEvents\Listeners\DeductionListener;
use LeadMax\TrackYourStats\Clicks\URLEvents\Listeners\FreeSignUpListener;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostBackURLEventHandler
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