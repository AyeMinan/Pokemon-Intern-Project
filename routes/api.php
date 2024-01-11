<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("register", [AuthController::class,"register"]);
Route::post("login", [AuthController::class,"login"]);
Route::get("login", [AuthController::class, "show"])->name('api.login');



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('cards', [CardController::class,'index']);
    Route::post('logout', [AuthController::class,'logout']);

    Route::post('cards', [CardController::class,'store']);
    Route::get('cards/{id}', [CardController::class,'show']);
    Route::get('/cards/{id}/edit', [CardController::class,'edit']);
    Route::post('cards/{id}/edit', [CardController::class,'update']);
    Route::delete('cards/{id}/delete', [CardController::class,'destroy']);
    Route::post('cards/{id}/activate', [CardController::class,'activateCard']);
    Route::post('cards/{id}/deactivate', [CardController::class,'deactivateCard']);


    Route::get('/shopping-cart', [CardController::class, 'cardCart'])->name('shopping.cart');
    Route::get('/shopping-cart/purchase', [CardController::class,'showCards']);
    Route::post('/shopping-cart/{id}/updateAmount', [CardController::class, 'updateAmount']);
    Route::post('/shopping-cart/calculatePrice', [CardController::class, 'calculatePrice']);
    Route::post('/shopping-cart/purchase', [CardController::class, 'purchase']);
    Route::delete('/shopping-cart/clearall', [CardController::class,'removeCardsFromCart']);
    Route::post('/cards/{id}', [CardController::class,'addCardtoCart']);

});



// Routes for unauthenticated users
Route::get('unauthenticated', function () {
    return 'You are not authenticated!';
})->name('unauthenticated')->middleware('guest');

