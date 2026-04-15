<?php

namespace App\Http\Controllers;

use App\Offer;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Clicks\PendingConversion;
use LeadMax\TrackYourStats\Offer\SaleLog;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Files\ImagesUploader;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;

class ChatLogController extends Controller
{


    public function showUploadChatLog($pendingConversionId)
    {
        $pendingConversion = \App\PendingConversion::findOrFail($pendingConversionId);


        $click = \App\Click::findOrFail($pendingConversion->click_id);

        $offer = Offer::findOrFail($click->offer_idoffer);

        return view('chatlog.upload', compact('offer', 'click', 'pendingConversion'));
    }

    public function uploadChatLog(Request $request)
    {

        $pendingConversion = \App\PendingConversion::findOrFail($request->input('pendingConversionId'));

        $imageUploader = new ImagesUploader();
        if (!$imageUploader->isValidateFiles('images')) {
            return back()->withErrors("Error Uploading images. Please make sure you don't have any extra image inputs that are empty.");
        } else {

            if (PendingConversion::activate($pendingConversion->id)) {
                $conversion = \App\Conversion::where('click_id', '=', $pendingConversion->click_id)->first();

                $saleLog = new SaleLog();
                $saleLog->conversion_id = $conversion->id;
                if ($saleLog->save()) {
                    $imageUploader->uploadDirectory = env("SALE_LOG_DIRECTORY")."/".Company::loadFromSession()->getSubDomain()."/{$saleLog->id}";
                    if ($imageUploader->uploadFiles('images')) {
                        if (Session::userType() == \App\Privilege::ROLE_AFFILIATE) {
                            return redirect("sale_log.php");
                        } else {
                            return redirect("sale_log.php?uid={$conversion->user_id}");
                        }
                    } else {
                        return back()->withErrors("Error Uploading images. Please make sure you don't have any extra image inputs that are empty.");
                    }
                } else {
                    return back()->withErrors("Error creating log.");
                }
            } else {
                return back()->withErrors('Error activating pending conversion! Try again later or contact an administrator if this error persists.');
            }

        }

    }


    public function getSaleLogImage($saleLogId, $fileName)
    {

        $file = env('SALE_LOG_DIRECTORY').'/'.$saleLogId.'/'.$fileName;


        if (file_exists($file)) {
            return response(file_get_contents($file))
                ->header('Content-Type', 'image/*');
        } else {
            return response('404', 404);
        }


    }


}
