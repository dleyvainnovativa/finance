<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\TrialBalanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/',)->name('api');


Route::middleware('firebase.jwt')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/journal', [JournalEntryController::class, 'index'])->name('api.journal');
    Route::get('/journal/filters', [JournalEntryController::class, 'filters'])->name('api.journal.filters');
    Route::get('/trial_balance', [TrialBalanceController::class, 'index'])->name('api.trial-balance');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.add');

    Route::post('/entries', [JournalEntryController::class, 'store'])->name('entries.add');
    Route::post('/entries/import', [JournalEntryController::class, 'import'])->name('entries.import');
});
