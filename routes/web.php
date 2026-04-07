<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.index')->name('frontend.index');

Auth::routes(['verify' => true]);

Route::get('/home', [HomeController::class, 'index'])
    ->middleware('verified')
    ->name('home');
