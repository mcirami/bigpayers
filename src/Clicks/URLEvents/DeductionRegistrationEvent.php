<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/28/2018
 * Time: 1:12 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents;

use App\User;
use Illuminate\Http\JsonResponse;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Offer\Deduction;
use LeadMax\TrackYourStats\User\PostBackURLs\DeductionPostBackURL;

class DeductionRegistrationEvent extends URLEvent
{

    public function __construct($click_id)
    {
        $this->clickId = $click_id;
    }

    public static function getEventString(): string
    {
        return "deduct";
    }

    public function fire()
    {
        try {
            if ($this->registerDeduction()) {
                return $this->firePostBackURL();
            } else {
                return response()->json(['status' => 500, 'message' => 'Unknown error.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    private function registerDeduction()
    {
        $conversion = Conversion::selectOne($this->clickId)->fetch(\PDO::FETCH_OBJ);
        if ($conversion == false) {
            abort(404, 'Conversion does not exist.');
        }

        // We should allow deductions on a banned user.
//        if (User::find($conversion->user_id)->isBanned()) {
//            abort(403, 'User is banned.');
//        }

        $deduction = new Deduction($conversion->id);

        if (Deduction::doesDeductionExist($conversion->id)) {
            abort(422, 'Conversion already deducted.');
        }

        return $deduction->deductConversion();
    }

    private function firePostBackURL()
    {
        $this->getAllDataFromDatabase();
        $user_id = $this->userData->idrep;

        $postBackUrl = new DeductionPostBackURL($user_id, $this->offerData->idoffer);

        $urlProcessor = $this->setUpURLProcessorWithDBData($postBackUrl->getPriorityURL());

        $urlProcessor->processURL();

        $urlProcessor->curlURL();

        return response()->json([
            'status' => 200,
            'message' => 'Deduction registered.',
            'post_back_url' => $urlProcessor->url,
        ]);
    }

}