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
                $query->where('op.account_code', 'like', '200.1.%')
                    ->orWhere('op.account_code', 'like', '200.2.%')
                    ->orWhere('op.account_code', 'like', '200.3.%')
                    ->orWhere('op.account_code', 'like', '200.4.%')
                    ->orWhere('op.account_code', 'like', '200.5.%');
            })
            ->get()->toArray();

        $debit_accounts = (clone $trial)
            ->where(function ($query) {
                $query->where('op.account_code', 'like', '100.1.%')
                    ->orWhere('op.account_code', 'like', '100.2.%')
                    ->orWhere('op.account_code', 'like', '100.3.%')
                    ->orWhere('op.account_code', 'like', '100.4.%')
                    ->orWhere('op.account_code', 'like', '100.5.%');
            })
            ->get()->toArray();

        $debitAccountGroups = [
            '100.1.' => 'EFECTIVO',
            '100.2.' => 'BANCOS',
            '100.3.' => 'INVERSIONES',
            '100.4.' => 'CUENTAS DE AHORRO',
            '100.5.' => 'DEUDORES DIVERSOS',
        ];
        $creditAccountGroups = [
            '200.1.' => 'TARJETAS CREDITO',
            '200.2.' => 'PRESTAMOS BANCARIOS',
            '200.3.' => 'CREDITOS AUTOMOTRICES',
            '200.4.' => 'CREDITOS HIPOTECARIOS',
            '200.5.' => 'ACREEDORES DIVERSOS',
        ];

        $all_debit_accounts = [];
        $all_credit_accounts = [];

        foreach ($debitAccountGroups as $prefix => $name) {
            $all_debit_accounts[$prefix] = [
                'name' => $name,
                'accounts' => [],
                'total' => 0,
            ];
        }
        foreach ($debit_accounts as $account) {
            $account = get_object_vars($account);
            foreach ($debitAccountGroups as $prefix => $name) {
                if (str_starts_with($account['account_code'], $prefix)) {
                    $all_debit_accounts[$prefix]['accounts'][] = $account;
                    $all_debit_accounts[$prefix]['total'] += $account['total'];
                    break;
                }
            }
        }
        $all_debit_accounts = array_values($all_debit_accounts);

        foreach ($creditAccountGroups as $prefix => $name) {
            $all_credit_accounts[$prefix] = [
                'name' => $name,
                'accounts' => [],
                'total' => 0,
            ];
        }
        foreach ($credit_accounts as $account) {
            $account = get_object_vars($account);
            foreach ($creditAccountGroups as $prefix => $name) {
                if (str_starts_with($account['account_code'], $prefix)) {
                    $all_credit_accounts[$prefix]['accounts'][] = $account;
                    $all_credit_accounts[$prefix]['total'] += $account['total'];
                    break;
                }
            }
        }
        $all_credit_accounts = array_values($all_credit_accounts);

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
                'debit_accounts'   => $all_debit_accounts,
                'credit_accounts'   => $all_credit_accounts,

                // 'debit_accounts'   => $debit_accounts,
                // 'credit_accounts'   => $credit_accounts,
                // 'total'   => $incomeM["total"],
            ],
        );
    }
}
