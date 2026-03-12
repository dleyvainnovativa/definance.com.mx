<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AverageController;
use App\Http\Controllers\Api\BalanceSheetController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\BudgetMonthlyController;
use App\Http\Controllers\Api\CashFlowController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\IncomeStatementController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\ManagedCashFlowController;
use App\Http\Controllers\Api\TrialBalanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/',)->name('api');


Route::middleware('firebase.jwt')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
    Route::get('/accounts', [AccountController::class, 'index'])->name('api.accounts');
    Route::get('/accounts/all', [AccountController::class, 'all'])->name('api.accounts.all');
    Route::get('/journal', [JournalEntryController::class, 'index'])->name('api.journal');
    Route::get('/voucher', [JournalEntryController::class, 'voucher'])->name('api.voucher');
    Route::get('/journal/filters', [JournalEntryController::class, 'filters'])->name('api.journal.filters');
    Route::get('/trial_balance', [TrialBalanceController::class, 'index'])->name('api.trial-balance');
    Route::get('/income_statement', [IncomeStatementController::class, 'index'])->name('api.income-statement');
    Route::get('/cash_flow', [CashFlowController::class, 'index'])->name('api.cash-flow');
    Route::get('/managed_cash_flow', [ManagedCashFlowController::class, 'index'])->name('api.managed-cash-flow');
    Route::post('/managed_cash_flow/save', [ManagedCashFlowController::class, 'save'])->name('api.managed-cash-flow.save');
    Route::get('/budget', [BudgetController::class, 'index'])->name('api.budget');
    Route::get('/budget_monthly', [BudgetMonthlyController::class, 'index'])->name('api.budget-monthly');
    Route::post('/budget/save', [BudgetController::class, 'save'])->name('api.budget.save');
    Route::get('/average', [AverageController::class, 'index'])->name('api.average');
    Route::get('/balance_sheet', [BalanceSheetController::class, 'index'])->name('api.balance-sheet');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.add');
    Route::post('/entries', [JournalEntryController::class, 'store'])->name('entries.add');
    Route::put('/entries', [JournalEntryController::class, 'change'])->name('entries.update');
    Route::delete('/entries', [JournalEntryController::class, 'delete'])->name('entries.delete');
    Route::post('/entries/import', [JournalEntryController::class, 'import'])->name('entries.import');
});
