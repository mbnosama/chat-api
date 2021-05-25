<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/*....................for users   ................*/
Route::post('register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);
Route::post('logout',[UserController::class,'logout'])->middleware('auth:api');
//for any user by id
Route::get('allConversation/{id}',[MessageController::class,'getAllConversation']);




Route::middleware('auth:api')->group(function (){
    Route::get('fetchUsers',[MessageController::class,'fetchUsers'] );
    Route::get('fetchMessages/{id}',[MessageController::class,'fetchMessages'] );
    Route::post('sendMessage',[MessageController::class,'store'] );
    Route::get('allConversationsForCurrentUser',[MessageController::class,'allConversationForDetectUser'] );
    Route::get('privateMessage/{user}/{friend}', [MessageController::class,'privateMessage']);

});
