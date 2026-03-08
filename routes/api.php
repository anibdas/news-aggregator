<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/articles', [ArticleController::class , 'index']);
Route::get('/metadata', [ArticleController::class , 'metadata']);
