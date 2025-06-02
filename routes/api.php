<?php

use App\Http\Controllers\API\PlacesController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\OfferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CMSController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\API\CronController;
use App\Http\Controllers\API\WordDictionaryController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::controller(WordDictionaryController::class)->group(function () {
    Route::get('getFileSearch', 'getFileSearch');
});
Route::controller(CMSController::class)->group(function () {
    Route::post('/content', 'getWebView');
});
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/loginWithId', 'loginWithId');
    Route::post('/forgotPassword', 'forgotPassword');
    Route::post('/changePassword', 'changePassword');
    Route::post('/verification', 'verification');
    Route::post('/re_send_code', 'reSendCode');
    Route::post('/socialLogin', 'socialLogin');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', 'getUser');
        Route::post('/submitReview', 'submitReview');
        Route::get('/getReviews', 'getReviews');
        Route::post('/deleteReview', 'deleteReview');
        Route::post('/logout', 'logout');
        Route::post('/delete_account', 'deleteAccount');
        Route::post('completeProfile', 'completeProfile');
    });
});

Route::controller(CategoryController::class)->group(function () {
    Route::get('categories', 'index');
    Route::get('category_words', 'categoryWords');
});

Route::controller(PlacesController::class)->group(function () {
    Route::get('places', 'index');
});

Route::post('/createSub', [SubscriptionController::class, 'createSub']);
Route::post('/support', [AuthController::class, 'supportAPI']);

Route::get('/getSub', [SubscriptionController::class, 'getSub']);

Route::post('/payment/add-card', [SubscriptionController::class, 'addCardToProfile']);
Route::post('/payment', [SubscriptionController::class, 'processSubscriptionPayment']);
Route::get('/pay', [SubscriptionController::class, 'checkCredit']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getBuses', [BusinessController::class, 'getBuses']);

    Route::controller(OfferController::class)->group(function () {
        Route::post('/addOffer', 'addOffer');
        Route::post('/editOffer', 'editOffer');
        Route::post('/deleteOffer', 'deleteOffer');
        Route::post('/favOffer', 'favOffer');
        Route::post('/availOffer', 'availOffer');
        Route::post('/claimedOffer', 'claimedOffer');
        Route::get('/getOffers', 'getOffers');
        Route::get('/getCustomers', 'getCustomers');

    });
    Route::controller(WordDictionaryController::class)->group(function () {
        Route::Post('request_word', 'requestWord');
        Route::get('search_word', 'searchWord');
        Route::get('word_details', 'wordData');
        Route::get('getFileSearch', 'getFileSearch');
        Route::get('getFile', 'getFile');
    });
    Route::controller(AuthController::class)->group(function () {
        Route::Post('add_fav_word', 'addFavWord');
        Route::get('user_fav_words', 'userFavoriteWordsList');
    });

    // Route::controller(SubscriptionController::class)->group(function () {
    //     Route::get('/subscription_packages', 'subscriptionPackageListing');
    //     Route::post('/subscription_add', 'addSubscription');
    // });

    Route::controller(FeedbackController::class)->group(function () {
        Route::get('user_feedback', 'userFeedback');
        Route::POST('/add_feedback', 'addFeedback');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'notificationList');
        Route::get('/enable_notifications', 'enableNotifications');
        Route::post('/mark_all_notifications_read', 'markAllNotificationRead');
        Route::get('/delete_notification/{id}', 'deleteNotificationById');
    });
});

Route::controller(CronController::class)->group(function () {
    Route::get('cron_reminderNotify', 'reminderNotify');
    Route::get('cron_notifyBeforeDateEnd', 'notifyBeforeDateEnd');
});
