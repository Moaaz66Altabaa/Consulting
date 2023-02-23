<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Client;
use App\Http\Controllers\Expert;
use App\Http\Controllers\JointController;
use Illuminate\Support\Facades\Http;
use Google\Cloud\Firestore\FirestoreClient;

//--------------------PUBLIC--ROUTES-----------------------------------
Route::post('register' , [Auth\AuthController::class , 'register']);
Route::post('login' , [Auth\AuthController::class , 'login']);
Route::post('indexPublic' , [Client\CategoryController::class , 'indexPublic']);
Route::post('sectionsPublic/{id}' , [Client\SectionController::class , 'sectionsPublic']);
//-------------------LOG-OUT------------------------------------------
Route::post('logout' , [Auth\AuthController::class , 'logout'])->middleware(['auth:sanctum']);


//--------------------CLIENT--ROUTES-----------------------------------
Route::group( ['prefix' => '/client' , 'middleware' => ['auth:sanctum' , 'isClient']] , function() {
    Route::post('/index' , [Client\CategoryController::class , 'index']);
    Route::post('/showCategory/{id}' , [Client\CategoryController::class , 'showCategory']);
    Route::post('/showSection/{id}' , [Client\SectionController::class , 'showSection']);
    Route::post('/favourite' , [Client\FavouriteController::class , 'favourite']);
    Route::post('/search' , [Client\SearchController::class , 'search']);
    Route::post('/showExpert/{id}' , [Client\ExpertsController::class , 'showExpert']);
    Route::post('/rateExpert/{id}' , [Client\RateController::class , 'rateExpert']);
    Route::post('/setFavourite/{id}' , [Client\FavouriteController::class , 'setFavourite']);
    Route::post('/showAppointments/{id}' , [Client\AppointmentController::class , 'showAppointments']);
    Route::post('/addAppointment/{id}' , [Client\AppointmentController::class , 'addAppointment']);
    Route::post('/updateProfile' , [Client\ProfileController::class , 'updateProfile']);

});

//-------------------ROUTES-NEED-TOKENS-ONLY-------------------------
Route::group(['middleware' => ['auth:sanctum']] , function (){
    Route::post('/showProfile/{id}' , [JointController::class , 'showProfile']);
    Route::post('/sendMessage/{id}' , [MessagesController::class , 'sendMessage']);
    Route::post('/showMessages/{id}' , [MessagesController::class , 'showMessages']);
    Route::post('/showWallet' , [JointController::class , 'showWallet']);
    Route::post('/setLocal' , [JointController::class , 'setLocal']);
});


//--------------------EXPERT--ROUTES-----------------------------------
Route::group( ['prefix' => '/expert' , 'middleware' => ['auth:sanctum' , 'isExpert']] , function() {
    Route::post('/showAppointments' , [Expert\AppointmentController::class , 'showAppointments']);
    Route::post('/showProfile' , [Expert\ProfileController::class , 'showProfile']);
    Route::post('/updateProfile' , [Expert\ProfileController::class , 'updateProfile']);

});

//------testing---------------------

//Route::post('send' , function (){
//
//    $db = new FirestoreClient([
//        'projectId' => 'consulting-984a5'
//    ]);
//
//    $documentId = 1;
//    $messages = $db->collection('messages')->documents();
//    foreach ($messages as $message){
//        return $message['body'];
//    }
//
//
//});


