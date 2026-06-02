<?php

namespace App\Http\Controllers;

use App\Company;
use App\Offer;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Clicks\PendingConversion;
use LeadMax\TrackYourStats\Offer\SaleLog;
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
                    $imageUploader->uploadDirectory = env("SALE_LOG_DIRECTORY")."/".$this->companySubDomain()."/{$saleLog->id}";
                    if ($imageUploader->uploadFiles('images')) {
                        if (Session::userType() == \App\Privilege::ROLE_AFFILIATE) {
                            return redirect("/report/sale-log");
                        } else {
                            return redirect("/report/sale-log?uid={$conversion->user_id}");
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

    public function showSaleLog($saleLogId)
    {
        $saleLogId = (int) $saleLogId;

        $this->authorizeSaleLogAccess($saleLogId);

        return view('chatlog.view', [
            'saleLogId' => $saleLogId,
            'images' => $this->saleLogImages($saleLogId),
            'subDomain' => $this->companySubDomain(),
        ]);
    }

    public function uploadSaleLogImages(Request $request, $saleLogId)
    {
        $saleLogId = (int) $saleLogId;

        $this->authorizeSaleLogAccess($saleLogId);

        $imageUploader = new ImagesUploader();

        if (!$imageUploader->isValidateFiles('images')) {
            return back()->withErrors("Error uploading images. Please make sure every selected file is a JPG or PNG under the upload limit.");
        }

        $imageUploader->uploadDirectory = $this->saleLogDirectory($saleLogId);

        if (!$imageUploader->uploadFiles('images')) {
            return back()->withErrors("Error uploading images. Please try again.");
        }

        return redirect("/chat-log/view/{$saleLogId}")->with('message', 'Images uploaded successfully.');
    }

    public function deleteSaleLogImage(Request $request, $saleLogId)
    {
        $saleLogId = (int) $saleLogId;

        $this->authorizeSaleLogAccess($saleLogId);

        $payload = $request->validate([
            'fileName' => 'required|string|max:255',
        ]);
        $fileName = basename($payload['fileName']);

        abort_if($fileName !== $payload['fileName'], 404);

        $filePath = $this->saleLogDirectory($saleLogId)."/{$fileName}";

        if (is_file($filePath)) {
            unlink($filePath);
        }

        return redirect("/chat-log/view/{$saleLogId}")->with('message', 'Image deleted.');
    }

    public function legacyDeleteSaleLogImage(Request $request)
    {
        $saleLogId = (int) $request->input('id');
        $fileName = (string) $request->input('fileName');

        if ($saleLogId <= 0 || $fileName === '' || basename($fileName) !== $fileName) {
            return response('false', 400);
        }

        $this->authorizeSaleLogAccess($saleLogId);

        $filePath = $this->saleLogDirectory($saleLogId)."/{$fileName}";

        if (!is_file($filePath)) {
            return response('false', 404);
        }

        return response(unlink($filePath) ? 'true' : 'false');
    }


    public function getSaleLogImage($saleLogId, $fileName)
    {

        $file = $this->saleLogDirectory((int) $saleLogId).'/'.basename($fileName);


        if (file_exists($file)) {
            return response(file_get_contents($file))
                ->header('Content-Type', 'image/*');
        } else {
            return response('404', 404);
        }


    }

    private function saleLogImages(int $saleLogId): array
    {
        $directory = $this->saleLogDirectory($saleLogId);

        if (!is_dir($directory)) {
            return [];
        }

        return collect(scandir($directory))
            ->reject(fn ($fileName) => $fileName === '.' || $fileName === '..')
            ->values()
            ->all();
    }

    private function saleLogDirectory(int $saleLogId): string
    {
        return env('SALE_LOG_DIRECTORY').'/'.$this->companySubDomain()."/{$saleLogId}";
    }

    private function companySubDomain(): string
    {
        $company = Company::instance()->first();

        abort_unless($company, 404, 'Company install not found.');

        return $company->getSubDomain();
    }

    private function authorizeSaleLogAccess(int $saleLogId): void
    {
        $saleLogRecord = SaleLog::selectOneQuery($saleLogId)->fetch(\PDO::FETCH_OBJ);

        abort_if(!$saleLogRecord, 404);
        abort_unless((new SaleLog())->verifyLoggedInUserOwnsSaleLog($saleLogId), 403);
    }


}
