<?php

namespace App\Support\Tracking\Events;

use App\Exceptions\RegistrationEventExceptions\ClickConvertedException;
use App\Exceptions\RegistrationEventExceptions\ConversionAlreadyPendingException;
use App\Exceptions\RegistrationEventExceptions\InvalidClickException;
use App\Offer;
use App\Support\Conversion;
use App\Support\OfferDomain\Offer as OfferSupport;
use App\Support\PendingConversion;
use App\Support\UserDomain\PostBackURLs\ConversionPostBackURL;
//use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\JsonResponse;

class ConversionRegistrationEvent extends UrlEvent
{

    public $clickId;

    public $customPayout;

    public function __construct($click_id, $customPayout = false)
    {
        $this->clickId = $click_id;
        $this->customPayout = $customPayout;
    }

    public static function getEventString(): string
    {
        return "convert";
    }

    public function fire()
    {
        try {
            if ($this->registerConversion()) {
                return $this->firePostBackURL();
            } else {

                return response()->json(['status' => 500, 'message' => 'Unknown error.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    private function registerConversion()
    {
        $conversion = new Conversion($this->clickId);

        if (!$conversion->isValidClick()) {
            throw new InvalidClickException($this->clickId);
        }

        if ($this->customPayout) {
            $conversion->paid = $this->customPayout;
        }


        if (Conversion::isClickConverted($this->clickId)) {
            throw new ClickConvertedException($this->clickId);
        }

        $offer = Offer::find($conversion->clickData->offer_idoffer);
        if ($offer->offer_type === OfferSupport::TYPE_PENDING_CONVERSION) {
            if (PendingConversion::isClickIdAlreadyRegistered($this->clickId)) {

                throw new ConversionAlreadyPendingException();
            }
            $conversion->getAffiliateData();

            $pendingConversion = new PendingConversion();
            $pendingConversion->click_id = $this->clickId;
            $pendingConversion->payout = $conversion->paid;

            return $pendingConversion->register();
        }

        return $conversion->registerSale();
    }

    private function firePostBackURL()
    {
        $this->getAllDataFromDatabase();
        $user_id = $this->userData->idrep;
        $offer_id = $this->clickData->offer_idoffer;

        $postBackUrl = new ConversionPostBackURL($user_id, $offer_id);

        $urlProcessor = $this->setUpURLProcessorWithDBData($postBackUrl->getPriorityURL());


        $urlProcessor->processURL();


        $urlProcessor->curlURL();

        return response()->json([
            'status' => 200,
            'message' => 'Conversion registered.',
            'post_back_url' => $urlProcessor->url,
        ], 200);
    }

}
