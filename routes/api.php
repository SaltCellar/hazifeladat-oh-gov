<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('/',function (Request $request) {

    $result = ( new \App\Services\EPK\Pontszamitas( $request->toArray() ) )->szamitas();

    return response()->json(['response' => $result['response_body']],$result['response_code']);
});

Route::post('/simple',function (Request $request) {

    $result = \App\Services\EPK_SF\EPK_SingleFile::getInstance()->handle($request->toArray());

    return response()->json(['response' => $result['message']],$result['error'] ? 400 : 200);
});
