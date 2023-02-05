<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Client;
use App\Http\Controllers\Expert;

//--------------------PUBLIC--ROUTES-----------------------------------
Route::post('register' , [AuthController::class , 'register']);
Route::post('login' , [AuthController::class , 'login']);
Route::post('/index' , [Client\ClientController::class , 'index']);

//-------------------LOG-OUT------------------------------------------
Route::post('logout' , [AuthController::class , 'logout'])->middleware(['auth:api']);


//--------------------CLIENT--ROUTES-----------------------------------
Route::group( ['prefix' => '/client' , 'middleware' => ['auth:api' , 'isClient']] , function() {
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
Route::post('/showProfile/{id}' , [Client\ClientController::class , 'showProfile'])->middleware('auth:api');
Route::post('/sendMessage/{id}' , [MessagesController::class , 'sendMessage'])->middleware('auth:api');
Route::post('/showMessages/{id}' , [MessagesController::class , 'showMessages'])->middleware('auth:api');
Route::post('/showWallet' , [Client\ClientController::class , 'showWallet'])->middleware('auth:api');
Route::post('/setLocal' , [Client\ClientController::class , 'setLocal'])->middleware('auth:api');


//--------------------EXPERT--ROUTES-----------------------------------
Route::group( ['prefix' => '/expert' , 'middleware' => ['auth:api' , 'isExpert']] , function() {
    Route::post('/showAppointments' , [Expert\ExpertController::class , 'showAppointments']);
    Route::post('/showProfile' , [Expert\ExpertController::class , 'showProfile']);
    Route::post('/updateProfile' , [Expert\ExpertController::class , 'updateProfile']);

});

