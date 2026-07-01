<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/logout', 'AuthController@logout');
    Route::post('/refresh', 'AuthController@refresh');
    Route::get('/me', 'AuthController@me');

});


// Route accessible only by Admins
Route::get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome to the Secret Admin Panel!']);
})->middleware(['auth:api', 'role:admin']);

// Route accessible by both Admins and normal Users
Route::get('/user/profile', function () {
    return response()->json(['message' => 'Welcome to your Profile page!']);
})->middleware(['auth:api', 'role:user,admin']);

Route::post('/file/upload', 'FileUploadController@upload');
Route::post('/file/download-link', 'FileUploadController@getDownloadUrl');

// Route::post('/file/upload', 'FileUploadController@upload')->middleware(['auth:api', 'role:user,admin']);
// Group routes that require a logged-in JWT user
// Route::middleware(['auth:api'])->group(function () {
    
    // Any authenticated user can request a secure file download link
    // Route::post('/file/download-link', 'FileUploadController@getDownloadUrl');
    
    // Only users with 'admin' role can actually upload files to S3
    // Route::post('/file/upload', 'FileUploadController@upload')->middleware('role:user');
    
// });