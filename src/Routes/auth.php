<?php

use Illuminate\Support\Facades\Route;
use NickKlein\News\Controllers\NewsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(['prefix' => 'news'], function () {
        Route::get('/', [NewsController::class, 'index'])->name('news.index');
        Route::get('/edit', [NewsController::class, 'edit'])->name('news.edit');
        Route::post('/settings/update-source', [NewsController::class, 'updateSource'])->name('news.edit.update-source');

        Route::post('/settings/add-tag', [NewsController::class, 'addTag'])->name('news.edit.add-tag');
        Route::post('/settings/remove-tag', [NewsController::class, 'removeTag'])->name('news.edit.remove-tag');
        Route::get('/favourites', [NewsController::class, 'showFavourites'])->name('news.favourite');
        Route::post('/favourite/save', [NewsController::class, 'toggleFavourites'])->name('news.toggle-favourite');
    });
});
