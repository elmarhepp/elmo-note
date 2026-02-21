<?php

use Illuminate\Support\Facades\Route;

// Serve Vue SPA for all non-API routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('spa/index.html'));
})->where('any', '.*');
