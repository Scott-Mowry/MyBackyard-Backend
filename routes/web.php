<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PlacesController;
use App\Http\Controllers\API\CMSController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\StockImageController;
use App\Http\Controllers\Admin\ManageWordsController;
use App\Http\Controllers\Admin\CMSController as ContentController;
use App\Http\Controllers\Admin\UploadFileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/privacypolicy', [CMSController::class, 'getPolicyData']);
Route::get('/termsconditions', [CMSController::class, 'getTermsData']);
Route::get('/aboutus', [CMSController::class, 'getAboutData']);
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/admin/login');
});

// Route::get('/login', function () {
//     return redirect('/admin/login');
// });
// Route::get('/', function () {
//     return redirect('/admin/login');
// });

// Route::domain('admin.' . env('APP_URL'))->group(function () {
//     Route::get('/', function () {
//         return redirect('/admin/login');
//     });
// });

Route::prefix('admin')->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/login', 'adminLoginForm')->name('admin.login.form');
        Route::post('/login', 'adminLoginSubmit')->name('admin.login.submit');
    });

    Route::group(['middleware' => ['admin']], function () {
        Route::controller(UserController::class)->group(function () {
            Route::prefix('/subscribedUsers')->group(function () {
                Route::get('/', 'getSubscribedUsers')->name('admin.subUsers');
            });
        });

        Route::controller(PlacesController::class)->group(function () {
            Route::prefix('/places')->group(function () {
                Route::get('/', 'getPlaces')->name('admin.places');
                Route::get('/action/{id}/{status}', 'actionPlaceById')->name('admin.places.action');
                Route::post('/add', 'addPlace')->name('admin.places.add');
                Route::get('/add/form', 'addPlaceForm')->name('admin.places.addForm');
                Route::get('/delete/{id}', 'placeDelete')->name('admin.places.destroy');
            });
        });

        Route::controller(ContentController::class)->group(function () {
            Route::prefix('/cms')->group(function () {
                Route::get('/', 'getCMS')->name('admin.cms');
                Route::post('/add', 'addCMS')->name('admin.cms.add');
            });
        });



        Route::controller(AdminController::class)->group(function () {
            Route::post('/logout', 'adminLogout')->name('admin.logout');
        });
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'getDashboard')->name('admin.dashboard');
        });

        Route::controller(StockImageController::class)->group(function () {
            Route::get('/stock/images', 'getStockImages')->name('admin.stock_images');
            Route::post('/stock/images/upload', 'stockImagesUpload')->name('admin.stock_images.upload');
            Route::DELETE('/stock/image/delete/{id}', 'stockImageDelete')->name('admin.stock_image.destroy');
        });
        Route::prefix('/manage_words')->group(function () {

            Route::controller(ManageWordsController::class)->group(function () {
                Route::get('/', 'index')->name('admin.manage_words');
                Route::get('/add/form', 'manageWordAddForm')->name('admin.manage_words.addForm');
                Route::get('/delete/{id}', 'deleteManageWord')->name('admin.delete.manage_words');
                Route::get('/{id}', 'getManageWordById')->name('admin.manage_words.details');
                Route::post('/add', 'addManageWord')->name('admin.manage_words.add');
                Route::post('/update', 'updateManageWord')->name('admin.manage_words.update');
            });
        });

        Route::prefix('/users')->group(function () {

            Route::controller(UserController::class)->group(function () {
                Route::get('/', 'getUsers')->name('admin.users');
                Route::get('/{id}', 'getUserById')->name('admin.users.details');
                Route::get('/action/{id}/{status}', 'actionUserById')->name('admin.users.action');
            });
        });

        Route::prefix('/categories')->group(function () {

            Route::controller(CategoryController::class)->group(function () {
                Route::post('/update', 'updateCategory')->name('admin.category.update');
                Route::get('/', 'getCategories')->name('admin.categories');
                Route::post('/add', 'addCategory')->name('admin.category.add');
                Route::get('/add/form', 'addCategoryForm')->name('admin.category.addForm');
                Route::get('/{id}', 'getCategoryById')->name('admin.categories.details');
                Route::get('/action/{id}/{status}', 'actionCategoryById')->name('admin.categories.action');
            });
        });

        Route::prefix('/upload-files')->group(function () {

            Route::controller(UploadFileController::class)->group(function () {
                Route::post('/update/{id}', 'update')->name('admin.upload-file.update');
                Route::get('/edit/{id}', 'edit')->name('admin.upload-file.edit');
                Route::get('/', 'index')->name('admin.upload-file');
                Route::post('/store', 'store')->name('admin.upload-file.store');
                Route::get('/create', 'create')->name('admin.upload-file.create');
                Route::get('/show/{id}', 'show')->name('admin.upload-file.show');
                Route::get('/delete/{id}', 'destroy')->name('admin.upload-file.destroy');

            });
        });


    });
});


Route::get('unauthorize', function () {
    return response()->json([
        'status' => 0,
        'message' => 'Sorry User is Unauthorize'
    ], 401);
})->name('unauthorize');