<?php

use App\Http\Controllers\Books\BooksController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::post('/auth/token', [LoginController::class, 'authenticate']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('livros', BooksController::class)->only('index', 'store');
        Route::post('/livros/{book}/importar-indices-xml', [BooksController::class, 'importarIndicesXml']);
    });
});
