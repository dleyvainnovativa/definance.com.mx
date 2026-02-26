<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AverageController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        // $month = 1;
        $year = $request->get('year', now()->year);
        // $balanceSheet = TrialBalanceController::getTrialBalance($userId, $month, $year);
        $incomeStatement = IncomeStatementController::getIncomeStatement($userId, $month, $year, true);
        return response()->json(
            [
                'data'   => $incomeStatement["data"],
                'total'   => $incomeStatement["total"],
                'results'   => $incomeStatement["results"],
            ],
        );
    }
}
