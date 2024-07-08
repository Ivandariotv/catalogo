<?php

use App\Models\Client;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AddressesController;
use App\Http\Controllers\ApplicationSettingsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayUController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\GeolocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserCollection;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('recoverPassword', [AuthController::class, 'recoverPassword']);
Route::post('validateVerificationCode', [AuthController::class, 'validateVerificationCode']);
Route::get('applicationSettings', [ApplicationSettingsController::class, 'index']);

Route::get('Banners', [BannerController::class, 'index']);
Route::get('Categories', [CategoryController::class, 'index']);
Route::get('Subcategories/{idCategory}', [CategoryController::class, 'showSubcategory']);
Route::get('Products/Category/{idCategory}', [ProductController::class, 'indexByCategory']);
Route::get('Products/Search/{keyword}', [ProductController::class, 'indexByKeyword']);
Route::get('RecommendProduct/{idProduct}', [ProductController::class, 'recommendProduct']);
Route::get('RecommendProducts', [ProductController::class, 'recommendProducts']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('logout', [AuthController::class, 'logout']);

    Route::get('verifyToken', function () {
        return response()->json(["message" => "authenticated."]);
    });

    Route::post('newPin', [AuthController::class, 'newPin']);

    Route::get('Profile', [ProfileController::class, 'show']);
    Route::put('Profile/Update', [ProfileController::class, 'update']);
    Route::put('Profile/Update/Pin', [ProfileController::class, 'updatePin']);
    Route::put('Profile/Update/Image', [ProfileController::class, 'updateImage']);
    Route::delete('Profile', [AuthController::class, 'deleteUser']);
    Route::get('shoppingCart', [ShoppingCartController::class, 'index']);
    Route::put('shoppingCart/Update/Product/{idProduct}', [ShoppingCartController::class, 'UpdateProducts']);
    Route::get('consultOrders', [ShoppingCartController::class, 'consultOrders']);
    Route::get('banksList', [PayUController::class, 'banckList']);
    Route::post('queryByTransactionId', [PayUController::class, 'queryByTransactionId']);
    Route::post('paymentRequestPSE', [PayUController::class, 'paymentRequestPSE']);
    Route::post('paymentRequestCreditCard', [PayUController::class, 'paymentRequestCreditCard']);
    Route::post('paymentRequestOnDelivery', [PayUController::class, 'paymentRequestOnDelivery']);

    Route::get('addresses', [AddressesController::class, 'show']);
    Route::post('addresses', [AddressesController::class, 'store']);
    Route::put('addresses/{id}', [AddressesController::class, 'update']);
    Route::delete('addresses/{id}', [AddressesController::class, 'delete']);

    Route::post('geolocation/calculateDistance', [GeolocationController::class, 'calculateDistance']);
});
