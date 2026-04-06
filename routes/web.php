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
    Route::get('/voucher', function () {
        return view('pages.voucher');
    })->name("voucher");
    Route::get('/trial-balance', function () {
        return view('pages.trial_balance');
    })->name("trial_balance");
    Route::get('/income-statement', function () {
        return view('pages.income_statement');
    })->name("income_statement");
    Route::get('/cash-flow', function () {
        return view('pages.cash_flow');
    })->name("cash_flow");
    Route::get('/managed-cash-flow', function () {
        return view('pages.managed_cash_flow');
    })->name("managed_cash_flow");
    Route::get('/balance-sheet', function () {
        return view('pages.balance_sheet');
    })->name("balance_sheet");
    Route::get('/average', function () {
        return view('pages.average');
    })->name("average");
    Route::get('/budget', function () {
        return view('pages.budget');
    })->name("budget");
    Route::get('/budget_monthly', function () {
        return view('pages.budget_monthly');
    })->name("budget_monthly");
    Route::get('/cash_count', function () {
        return view('pages.cash_count');
    })->name("cash_count");
    Route::get('/projections', function () {
        return view('pages.projections');
    })->name("projections");
});
