<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/28/2018
 * Time: 1:10 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents;


use App\User;
use Illuminate\Http\JsonResponse;
use LeadMax\TrackYourStats\Offer\FreeSignUp;
use LeadMax\TrackYourStats\User\PostBackURLs\FreePostBackURL;

class FreeSignUpRegistrationEvent extends URLEvent
{

    public $clickId;

    public function __construct($click_id)
    {
        $this->clickId = $click_id;
    }

    public static function getEventString(): string
    {
        return "free";
    }

    public function fire()
    {
        try {
            if ($this->registerSignUp()) {
                return $this->firePostBackURL();
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Unknown error.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }

    }

    private function firePostBackURL()
    {
        $this->getAllDataFromDatabase();
        $user_id = $this->userData->idrep;

        $postBackUrl = new FreePostBackURL($user_id, $this->offerData->idoffer);

        $urlProcessor = $this->setUpURLProcessorWithDBData($postBackUrl->getPriorityURL());

        $urlProcessor->processURL();


        $urlProcessor->curlURL();

        return response()->json([
            'status' => 200,
            'message' => 'Free sign up registered.',
            'post_back_url' => $urlProcessor->url,
        ], 200);
    }

    private function registerSignUp()
    {
        if (FreeSignUp::selectOne($this->clickId)) {
            abort(422, 'Click already registered.');
        }


        $freeSignUp = new FreeSignUp();
        $freeSignUp->setClickId($this->clickId);
        $freeSignUp->getUserIdFromClickId();

        if (User::find($freeSignUp->getUserId())->isBanned()) {
            abort(403, 'User is banned.');
        }

        return $freeSignUp->save();
    }

}