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
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\PlacesController as AdminPlacesController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\StockImageController;
use App\Http\Controllers\Admin\ManageWordsController;
use App\Http\Controllers\Admin\CMSController as ContentController;
use App\Http\Controllers\Admin\UploadFileController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\BusinessPromoCodeController;
use App\Http\Controllers\API\V2\SubscriptionController as V2SubscriptionController;

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


Route::post('/support', [AuthController::class, 'supportAPI']);

Route::controller(SubscriptionController::class)->group(function () {
    Route::post('/createSub', 'createSub');
    Route::get('/getSub', 'getSub');
    Route::post('/payment/add-card', 'addCardToProfile');
    Route::post('/payment/get-card', 'getCardInfo');
    Route::post('/payment', 'processSubscriptionPayment');
    Route::post('/unsub', 'processUnSubscriptionPayment');
    Route::post('/unsub/cancel', 'cancelUnsubcription');
    Route::post('/unsub/check', 'hasUnsubcribe');
    Route::get('/pay', 'checkCredit');
    Route::post('/promocode/apply', 'applyPromocode');
});


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

// Admin API Routes v2
Route::prefix('v2/admin')->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::post('/login', 'adminLoginSubmitv2');
        Route::post('/logout', 'adminLogoutv2');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/user', 'getCurrentAdminUser');
        });
        Route::controller(UserController::class)->group(function () {
            Route::prefix('/subscribedUsers')->group(function () {
                Route::get('/', 'getSubscribedUsersv2');
            });
        });

        Route::controller(PromoCodeController::class)->group(function () {
            Route::prefix('/promocodes')->group(function () {
                Route::get('/', 'getCodesv2');
                Route::get('/delete/{id}', 'promoCodeDeletev2');
                Route::post('/add', 'addPromoCodev2');
                Route::get('/add/form', 'addPromoCodeForm');
            });
        });

        Route::controller(BusinessPromoCodeController::class)->group(function () {
            Route::prefix('/business-promocodes')->group(function () {
                Route::get('/', 'getPromoCodes');
                Route::get('/{id}', 'getPromoCodeById');
                Route::post('/', 'createPromoCode');
                Route::put('/{id}', 'updatePromoCode');
                Route::delete('/{id}', 'deletePromoCode');
                Route::patch('/{id}/toggle-status', 'toggleStatus');
            });
        });

        Route::controller(AdminController::class)->group(function () {
            Route::prefix('/receipts')->group(function () {
                Route::get('/', 'getReceiptsv2');
            });
            Route::post('/assignSubscription', 'assignSubscriptionToUser');
        });

        Route::controller(AdminPlacesController::class)->group(function () {
            Route::prefix('/places')->group(function () {
                Route::get('/', 'getPlacesv2');
                Route::get('/action/{id}/{status}', 'actionPlaceByIdv2');
                Route::post('/add', 'addPlacev2');
                Route::get('/add/form', 'addPlaceForm');
                Route::get('/delete/{id}', 'placeDeletev2');
            });
        });

        Route::controller(ContentController::class)->group(function () {
            Route::prefix('/cms')->group(function () {
                Route::get('/', 'getCMSv2');
                Route::post('/add', 'addCMSv2');
            });
        });

        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'getDashboardv2');
        });

        Route::controller(StockImageController::class)->group(function () {
            Route::get('/stock/images', 'getStockImagesv2');
            Route::post('/stock/images/upload', 'stockImagesUploadv2');
            Route::DELETE('/stock/image/delete/{id}', 'stockImageDeletev2');
        });

        Route::prefix('/manage_words')->group(function () {
            Route::controller(ManageWordsController::class)->group(function () {
                Route::get('/', 'indexv2');
                Route::get('/add/form', 'manageWordAddForm');
                Route::get('/delete/{id}', 'deleteManageWordv2');
                Route::get('/{id}', 'getManageWordByIdv2');
                Route::post('/add', 'addManageWordv2');
                Route::post('/update', 'updateManageWordv2');
            });
        });

        Route::prefix('/users')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::get('/', 'getUsersv2');
                Route::post('/upload-profile-image', 'uploadProfileImagev2');
                Route::get('/action/{id}/{status}', 'actionUserByIdv2');
                Route::post('/', 'createUserByIdv2');
                Route::put('/{id}', 'updateUserByIdv2');
                Route::post('/{id}', 'updateUserByIdv2');
                Route::get('/{id}', 'getUserByIdv2');
            });
        });

        Route::prefix('/categories')->group(function () {
            Route::controller(AdminCategoryController::class)->group(function () {
                Route::post('/update', 'updateCategoryv2');
                Route::get('/', 'getCategoriesv2');
                Route::post('/add', 'addCategoryv2');
                Route::get('/add/form', 'addCategoryForm');
                Route::get('/{id}', 'getCategoryByIdv2');
                Route::delete('/{id}', 'deleteCategoryv2');
                Route::get('/action/{id}/{status}', 'actionCategoryByIdv2');
            });
        });

        Route::prefix('/subscriptions')->group(function () {
            Route::controller(AdminSubscriptionController::class)->group(function () {
                Route::get('/', 'getSubscriptionsv2');
                Route::post('/add', 'addSubscriptionv2');
                Route::put('/{id}', 'updateSubscriptionv2');
                Route::delete('/{id}', 'deleteSubscriptionv2');
                Route::get('/{id}', 'getSubscriptionByIdv2');
            });
        });

        Route::prefix('/upload-files')->group(function () {
            Route::controller(UploadFileController::class)->group(function () {
                Route::post('/update/{id}', 'updatev2');
                Route::get('/edit/{hash}', 'editv2');
                Route::get('/', 'indexv2');
                Route::post('/store', 'storev2');
                Route::get('/create', 'create');
                Route::get('/show/{id}', 'showv2');
                Route::get('/delete/{id}', 'destroyv2');
            });
        });

        Route::controller(PromoCodeController::class)->group(function () {
            Route::prefix('/promocodes')->group(function () {
                Route::get('/', 'getCodesv2');
                Route::get('/delete/{id}', 'promoCodeDeletev2');
                Route::post('/add', 'addPromoCodev2');
                Route::get('/add/form', 'addPromoCodeForm');
            });
        });

        Route::controller(AdminPlacesController::class)->group(function () {
            Route::prefix('/places')->group(function () {
                Route::get('/', 'getPlacesv2');
                Route::get('/action/{id}/{status}', 'actionPlaceByIdv2');
                Route::post('/add', 'addPlacev2');
                Route::get('/add/form', 'addPlaceForm');
                Route::get('/delete/{id}', 'placeDeletev2');
            });
        });

        Route::controller(ContentController::class)->group(function () {
            Route::prefix('/cms')->group(function () {
                Route::get('/', 'getCMSv2');
                Route::post('/add', 'addCMSv2');
            });
        });

        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'getDashboardv2');
        });

        Route::controller(StockImageController::class)->group(function () {
            Route::get('/stock/images', 'getStockImagesv2');
            Route::post('/stock/images/upload', 'stockImagesUploadv2');
            Route::DELETE('/stock/image/delete/{id}', 'stockImageDeletev2');
        });

        Route::prefix('/manage_words')->group(function () {
            Route::controller(ManageWordsController::class)->group(function () {
                Route::get('/', 'indexv2');
                Route::get('/add/form', 'manageWordAddForm');
                Route::get('/delete/{id}', 'deleteManageWordv2');
                Route::get('/{id}', 'getManageWordByIdv2');
                Route::post('/add', 'addManageWordv2');
                Route::post('/update', 'updateManageWordv2');
            });
        });

        Route::prefix('/users')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::get('/', 'getUsersv2');
                Route::post('/upload-profile-image', 'uploadProfileImagev2');
                Route::get('/action/{id}/{status}', 'actionUserByIdv2');
                Route::post('/', 'createUserByIdv2');
                Route::put('/{id}', 'updateUserByIdv2');
                Route::post('/{id}', 'updateUserByIdv2');
                Route::get('/{id}', 'getUserByIdv2');
            });
        });

        Route::prefix('/categories')->group(function () {
            Route::controller(AdminCategoryController::class)->group(function () {
                Route::post('/update', 'updateCategoryv2');
                Route::get('/', 'getCategoriesv2');
                Route::post('/add', 'addCategoryv2');
                Route::get('/add/form', 'addCategoryForm');
                Route::get('/{id}', 'getCategoryByIdv2');
                Route::delete('/{id}', 'deleteCategoryv2');
                Route::get('/action/{id}/{status}', 'actionCategoryByIdv2');
            });
        });

        Route::prefix('/subscriptions')->group(function () {
            Route::controller(AdminSubscriptionController::class)->group(function () {
                Route::get('/', 'getSubscriptionsv2');
                Route::post('/add', 'addSubscriptionv2');
                Route::put('/{id}', 'updateSubscriptionv2');
                Route::delete('/{id}', 'deleteSubscriptionv2');
                Route::get('/{id}', 'getSubscriptionByIdv2');
            });
        });

        Route::prefix('/upload-files')->group(function () {
            Route::controller(UploadFileController::class)->group(function () {
                Route::post('/update/{id}', 'updatev2');
                Route::get('/edit/{hash}', 'editv2');
                Route::get('/', 'indexv2');
                Route::post('/store', 'storev2');
                Route::get('/create', 'create');
                Route::get('/show/{id}', 'showv2');
                Route::get('/delete/{id}', 'destroyv2');
            });
        });
    });
});

// Subscription/Payment API Routes v2
Route::prefix('v2')->group(function () {
    Route::controller(V2SubscriptionController::class)->group(function () {
        Route::post('/createSub', 'createSub');
        Route::get('/getSub', 'getSubV2');
        Route::post('/payment/add-card', 'addCardToProfile');
        Route::post('/payment/get-card', 'getCardInfo');
        Route::post('/payment', 'processSubscriptionPayment');
        Route::post('/unsub', 'processUnSubscriptionPayment');
        Route::post('/unsub/cancel', 'cancelUnsubcription');
        Route::post('/unsub/check', 'hasUnsubcribe');
        Route::get('/pay', 'checkCredit');
        Route::post('/promocode/apply', 'applyPromocode');

        // Recurring subscription endpoints
        Route::post('/recurring/create', 'createRecurringSubscription');
        Route::post('/recurring/cancel', 'cancelRecurringSubscription');
        Route::get('/recurring/status', 'getRecurringSubscriptionStatus');
        Route::post('/recurring/update', 'updateRecurringSubscription');
    });
});
