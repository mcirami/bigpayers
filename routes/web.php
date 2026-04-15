<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\ExportDataController;
use App\Privilege;
use Illuminate\Support\Facades\Route;
use LeadMax\TrackYourStats\User\Permissions;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LanderController;
use App\Http\Controllers\LegacyLoginController;
use App\Http\Controllers\RelevanceReactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\Report\ClickReportController;
use App\Http\Controllers\Report\AggregateReportController;
use App\Http\Controllers\Report\OfferReportController;
use App\Http\Controllers\Report\AdvertiserReportController;
use App\Http\Controllers\Report\BlackListReportController;
use App\Http\Controllers\Report\AdjustmentsReportController;
use App\Http\Controllers\Report\ChatLogReportController;
use App\Http\Controllers\Report\EmployeeReportController;
use App\Http\Controllers\Report\SubReportController;
use App\Http\Controllers\Report\PayoutReportController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\EmailPoolController;
use App\Http\Controllers\AdjustmentsController;
use App\Http\Controllers\Sms\SmsApiController;
use App\Http\Controllers\Sms\SmsController;
use App\Http\Controllers\Sms\SmsClientController;
use App\Http\Controllers\ChatLogController;
use App\Http\Controllers\Report\ConversionReportController;
use App\Http\Controllers\SmsOrderController;

Route::get('/', [IndexController::class, 'index']);
Route::post('/', [IndexController::class, 'index']);
Route::get('/login', [LegacyLoginController::class, 'showLoginForm']);
Route::post('/login', [LegacyLoginController::class, 'login']);
Route::any('/resources/landers/{subDomain}/{asset}', [LanderController::class, 'getAsset'])->where('asset', '.*');
Route::get('/logout', [LegacyLoginController::class, 'logout']);
Route::post('email/incoming', [RelevanceReactorController::class, 'incomingEmail']);
Route::post('email/incoming/distribute', [RelevanceReactorController::class, 'distributeEmail']);
Route::group(['middleware' => 'legacy.auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'home']);
	Route::get('verification', [SmsOrderController::class, 'show'])->middleware(
		'role:0,3',
		'permissions:' . Permissions::SMS_CHAT
	);
    Route::group(['prefix' => 'user'], function () {
        Route::get('manage', [UserController::class, 'viewManageUsers'])->middleware(['role:0,1,2']);
        Route::get('{id}/affiliates', [UserController::class, 'viewManagersAffiliates'])->middleware([
            'role:0,1,2',
            'permissions:' . Permissions::CREATE_MANAGERS
        ]);
	    Route::post('/block-sub-id', [UserController::class, 'blockUserSubId'])->middleware(['role:0']);
	    Route::post('/unblock-sub-id', [UserController::class, 'unblockUserSubId'])->middleware(['role:0']);
	    Route::post('/change-aff-payout', [UserController::class, 'changeAffPayout'])->middleware(['role:0']);
	    Route::post('/update-offer-access', [UserController::class, 'updateAffOfferAccess'])->middleware(['role:0']);
	    Route::get('/offers/{user}', [UserController::class, 'editUserOffers'])->middleware(['role:0']);
	    Route::post('/enable-user-offer-cap', [UserController::class, 'enableUserOfferCap'])->middleware(['role:0']);
	    Route::post('/set-user-offer-cap', [UserController::class, 'setUserOfferCap'])->middleware(['role:0']);

        Route::group(['prefix' => '/{id}/salary', 'middleware' => 'permissions:' . Permissions::EDIT_SALARIES],
            function () {
                Route::get('show', [SalaryController::class, 'showCreate'])->name('salary.show');
                Route::post('create', [SalaryController::class, 'create'])->name('salary.create');
                Route::get('showUpdate', [SalaryController::class, 'showUpdate'])->name('salary.show.update');
                Route::post('update', [SalaryController::class, 'update'])->name('salary.update');
            });
        Route::get('{id}/clicks', [ClickReportController::class, 'showUsersClicks'])->middleware('role:0,1,2')->name('userClicks');
        Route::get('{id}/clicks/export', [ExportDataController::class, 'exportUsersClicks'])->middleware('role:0,1,2')->name('exportUserClicks');
        Route::get('{id}/search-clicks', [ClickReportController::class, 'searchClicks'])->middleware('role:0')->name('clicks.search');

        Route::get('{id}/conversions-by-offer', [ConversionReportController::class, 'showUserConversionsByOffer'])->middleware('role:0,1,2')->name('userConversionsByOffer');
        Route::get('{id}/conversions', [ConversionReportController::class, 'showUserConversions'])->middleware('role:0,1,2')->name('userConversions');
        Route::get('{user}/{offer}/conversions-by-country', [ConversionReportController::class, 'showUserOfferConversionsByCountry'])->middleware('role:0,1,2,3')->name('userOfferConversionsByCountry');
        Route::get('{user}/{offer}/conversions-by-subid', [SubReportController::class, 'showUserConversionsBySubId'])->middleware('role:0,1,2')->name('userConversionsBySubId');
        Route::get('{user}/{offer}/subid-clicks-by-offer', [SubReportController::class, 'showSubIdClicksByOffer'])->middleware('role:0,1,2')->name('subIdClicksByOffer');
        Route::get('{user}/{offer}/subid-conversions-in-country', [SubReportController::class, 'showSubIdConversionsInCountry'])->middleware('role:0,1,2')->name('subIdConversionsInCountry');
        Route::get('{user}/{offer}/subid-offer-clicks-in-country', [SubReportController::class, 'showSubIdClicksByOfferInCountry'])->middleware('role:0,1,2')->name('subIdClicksByOfferInCountry');
        Route::get('{user}/{offer}/subid-offer-conversions-by-country', [SubReportController::class, 'subIdOfferConverisonsByCountry'])->middleware('role:0,1,2')->name('subIdOfferConverisonsByCountry');
    });
    Route::group(['prefix' => 'report'], function () {
        Route::get('daily', [AggregateReportController::class, 'show']);
	    Route::get('geo', [ConversionReportController::class, 'showConversionsByCountry'])->middleware('role:0,1');
	    Route::get('geo-by-offer', [ConversionReportController::class, 'showGeoByOffer'])->middleware('role:0,1');
	    Route::get('geo/clicks-in-country', [ClickReportController::class, 'clicksInCountry'])->middleware('role:0,1');
	    Route::get('geo/clicks-in-country/export', [ExportDataController::class, 'exportCountryClicks'])->middleware('role:0,1');
		Route::get('offer', [OfferReportController::class, 'show']);
	    Route::get('offer-data/export', [ExportDataController::class, 'exportOfferData'])->middleware('role:0,1')->name('exportOfferData');
	    Route::get('offer/{offer}/user-conversions', [OfferReportController::class, 'showConversionsByUser']);
        Route::get('offer/{offer}/conversions-by-country', [OfferReportController::class, 'showConversionsByCountry']);

	    Route::get('manager/{user}/conversions-by-offer', [ConversionReportController::class, 'showManagerConversionsByOffer']);
		Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD], function () {
            Route::get('blacklist', [BlackListReportController::class, 'show']);
        });
	    Route::get('advertiser', [AdvertiserReportController::class, 'show'])
	         ->middleware(['permissions:' . Permissions::VIEW_ADV_REPORTS,'role:0,1']);

	    Route::get('advertiser/{id}/conversions-by-offer', [AdvertiserReportController::class, 'showConversionsByOffer'])
	         ->middleware(['permissions:' . Permissions::VIEW_ADV_REPORTS,'role:0,1']);

		Route::get('adjustments', [AdjustmentsReportController::class, 'show'])->middleware([
            'permissions:' . Permissions::ADJUST_SALES,
            'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN
        ]);
        Route::get('sale-log', [ChatLogReportController::class, 'affiliate']);
	    Route::get('aff-data/export', [ExportDataController::class, 'exportAffData'])->middleware('role:0,1')->name('exportAffData');
        Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN . ',' . Privilege::ROLE_MANAGER],
            function () {
                Route::get('chat-log', [ChatLogReportController::class, 'show']);
                Route::get('affiliate', [EmployeeReportController::class,'show']);
                Route::get('chat-log/{userId}', [ChatLogReportController::class, 'admin']);
            });
        Route::group(['middleware' => 'role:' . Privilege::ROLE_AFFILIATE], function () {
            Route::get('sub', [SubReportController::class,'show']);
	        Route::get('sub/conversions', [SubReportController::class,'showSubConversions']);
            Route::group(['prefix' => 'payout'], function () {
                Route::get('', [PayoutReportController::class, 'report']);
                Route::get('pdf', [PayoutReportController::class, 'invoice']);
            });
        });
    });
    Route::group(['prefix' => 'offer'], function () {
        Route::get('manage', [OfferController::class, 'showManage']);
        Route::get('{id}/request', [OfferController::class, 'requestOffer'])->middleware('role:3');
        Route::group(['middleware' => 'role:0'], function () {
            Route::get('{id}/dupe', [OfferController::class, 'dupe']);
            Route::get('{id}/delete', [OfferController::class, 'delete']);
        });
        Route::get('{id}/clicks', [ClickReportController::class, 'offerClicks'])->middleware('role:0,1,2')->name('offerClicks');
        Route::get('{id}/search-clicks', [ClickReportController::class, 'searchClicks'])->middleware('role:0')->name('offer.clicks.search');
		Route::group(['middleware' => ['permissions:' . Permissions::CREATE_OFFERS]], function () {
            Route::get('create', [OfferController::class, 'showCreate']);
            Route::post('create', [OfferController::class, 'create']);
            Route::get("edit/{id}", [OfferController::class, 'showEdit']);
            Route::get('mass-assign', [OfferController::class, 'showMassAssign']);
            Route::post('mass-assign', [OfferController::class, 'massAssign']);
            Route::get('assignableUsers', [OfferController::class, 'getAssignableUsers']);
            Route::get("assignedUsers/{id}", [OfferController::class, 'getAssignedUsers']);
        });
    });
    Route::group(['prefix' => 'email/pools', 'middleware' => "permissions:" . Permissions::EMAIL_POOLS], function () {
        Route::get('', [EmailPoolController::class, 'showAffiliateEmailPools']);
        Route::get('{id}/download', [EmailPoolController::class, 'downloadEmailPool']);
        Route::get('{id}/claim', [EmailPoolController::class, 'claimEmailPool']);
    });
    Route::group(['prefix' => 'sales', 'middleware' => 'permissions:' . Permissions::ADJUST_SALES], function () {
        Route::get('add', [AdjustmentsController::class, 'showAddSaleLog']);
        Route::post('add', [AdjustmentsController::class, 'createSale']);
        Route::get('affiliate-offers/{id}', [AdjustmentsController::class, 'getAffiliatesOffers']);
        Route::get('affiliates', [AdjustmentsController::class, 'getAffiliates']);
    });
    Route::group(['prefix' => 'sms'], function () {
        Route::group(['prefix' => 'api'], function () {
            Route::post('messages/send', [SmsApiController::class, 'sendMessage']);
            Route::get('conversations', [SmsApiController::class, 'getConversations']);
            Route::get('conversations/{id}', [SmsApiController::class, 'getConversation']);
            Route::get('conversations/{id}/messages', [SmsApiController::class, 'getMessages']);
            Route::patch('conversations', [SmsApiController::class, 'patchConversation']);
            Route::patch('conversations/{conversationId}/read-new-messages', [SmsApiController::class, 'readNewMessages']);
        });
        Route::get('/', [SmsController::class, 'getChattingPage'])->middleware(['role:' . Privilege::ROLE_AFFILIATE]);
        Route::group(['prefix' => 'client', 'middleware' => 'role:' . Privilege::ROLE_GOD], function () {
            Route::get('add', [SmsClientController::class, 'create']);
            Route::post('add', [SmsClientController::class, 'store']);
            Route::get('edit', [SmsClientController::class, 'edit']);
            Route::post('update', [SmsClientController::class, 'update']);
            Route::post('create', [SmsClientController::class, 'createSMSWorker']);
        });
        Route::get("client", [SmsClientController::class, 'getUsersClient']);
    });
    Route::group(['prefix' => 'chat-log'], function () {
        Route::get('add/{pendingConversionId}', [ChatLogController::class, 'showUploadChatLog']);
        Route::post('upload', [ChatLogController::class, 'uploadChatLog']);
        Route::get('view/{saleLogId}/{fileName}', [ChatLogController::class, 'getSaleLogImage']);
    });
    Route::get("login/{userId}", [LegacyLoginController::class, 'adminLogin']);
});

Route::get('/css/company.css', function () {
    header('Content-Type: text/css');
    include resource_path('styles/company.php');
    exit;
});
