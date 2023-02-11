<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Client;
use App\Http\Controllers\Expert;

//--------------------PUBLIC--ROUTES-----------------------------------
Route::post('register' , [Auth\AuthController::class , 'register']);
Route::post('login' , [Auth\AuthController::class , 'login']);

//-------------------LOG-OUT------------------------------------------
Route::post('logout' , [Auth\AuthController::class , 'logout'])->middleware(['auth:sanctum']);


//--------------------CLIENT--ROUTES-----------------------------------
Route::group( ['prefix' => '/client' , 'middleware' => ['auth:sanctum' , 'isClient']] , function() {
    Route::post('/index' , [Client\ClientController::class , 'index']);
    Route::post('/showCategory/{id}' , [Client\ClientController::class , 'showCategory']);
    Route::post('/favourite' , [Client\ClientController::class , 'favourite']);
    Route::post('/search' , [Client\ClientController::class , 'search']);
    Route::post('/showExpert/{id}' , [Client\ClientController::class , 'showExpert']);
    Route::post('/rateExpert/{id}' , [Client\ClientController::class , 'rateExpert']);
    Route::post('/setFavourite/{id}' , [Client\ClientController::class , 'setFavourite']);
    Route::post('/showAppointments/{id}' , [Client\ClientController::class , 'showAppointments']);
    Route::post('/addAppointment/{id}' , [Client\ClientController::class , 'addAppointment']);
    Route::post('/updateProfile' , [Client\ClientController::class , 'updateProfile']);

});

//-------------------ROUTES-NEED-TOKENS-ONLY-------------------------
Route::group(['middleware' => ['auth:sanctum']] , function (){
    Route::post('/showProfile/{id}' , [Client\ClientController::class , 'showProfile']);
    Route::post('/sendMessage/{id}' , [MessagesController::class , 'sendMessage']);
    Route::post('/showMessages/{id}' , [MessagesController::class , 'showMessages']);
    Route::post('/showWallet' , [Client\ClientController::class , 'showWallet']);
    Route::post('/setLocal' , [Client\ClientController::class , 'setLocal']);
});


//--------------------EXPERT--ROUTES-----------------------------------
Route::group( ['prefix' => '/expert' , 'middleware' => ['auth:sanctum' , 'isExpert']] , function() {
    Route::post('/showAppointments' , [Expert\ExpertController::class , 'showAppointments']);
    Route::post('/showProfile' , [Expert\ExpertController::class , 'showProfile']);
    Route::post('/updateProfile' , [Expert\ExpertController::class , 'updateProfile']);

});

