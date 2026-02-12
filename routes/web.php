<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/')->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin']);
Route::get('/logout', [AuthController::class, 'logout'])->name("logout");

Route::middleware('firebase.auth')->group(function () {
    Route::get('/', function () {
        return view('pages.dashboard');
    })->name("home");
    Route::get('/accounts', function () {
        return view('pages.accounts');
    })->name("accounts");
    Route::get('/entry', function () {
        return view('pages.entry');
    })->name("entry");
    Route::get('/import', function () {
        return view('pages.import');
    })->name("import");
    Route::get('/journal', function () {
        return view('pages.journal');
    })->name("journal");
    Route::get('/trial_balance', function () {
        return view('pages.trial_balance');
    })->name("trial_balance");
});
