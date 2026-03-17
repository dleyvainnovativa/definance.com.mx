<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month =  now()->month;
        $year = now()->year;
        $trial = TrialBalanceController::getTrialBalance($userId, $month, $year);

        $credit_accounts = (clone $trial)
            ->where(function ($query) {
                $query->where('op.account_code', 'like', '200.1.%');
            })
            ->get()->toArray();

        $debit_accounts = (clone $trial)
            ->where(function ($query) {
                $query->where('op.account_code', 'like', '100.1.%')
                    ->orWhere('op.account_code', 'like', '100.2.%');
            })
            ->get()->toArray();

        $accounts = array_merge($debit_accounts, $credit_accounts);

        $journal = JournalEntryController::getJournal($userId, $month, $year, null, [], 10, 1, ["opening_balance_credit", "opening_balance"], "desc");
        $balance = BalanceSheetController::getBalanceSheet($userId, $month, $year)["data"];
        $incomeM = IncomeStatementController::getIncomeStatement($userId, $month, $year)["data"];
        $incomeM = array_slice($incomeM, 0, 3);
        $incomeY = IncomeStatementController::getIncomeStatement($userId, 12, $year, true)["data"];
        $incomeY = array_slice($incomeY, 0, 3);

        return response()->json(
            [
                'income_month'   => $incomeM,
                'income_year'   => $incomeY,
                'balance'   => $balance,
                'journal'   => $journal["data"],
                'accounts'   => $accounts,

                // 'debit_accounts'   => $debit_accounts,
                // 'credit_accounts'   => $credit_accounts,
                // 'total'   => $incomeM["total"],
            ],
        );
    }
}
