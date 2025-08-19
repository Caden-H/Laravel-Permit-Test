<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 👇 add this line
Route::get('ping', function () {
    return ['ok' => true];
});


use App\Http\Controllers\PermitController;

Route::apiResource('permits', PermitController::class);
Route::post('permits/{permit}/approve', [PermitController::class, 'approve']);
