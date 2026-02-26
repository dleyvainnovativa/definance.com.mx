<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BudgetMonthlyController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $userId = $request->user()->id;
        $year = $request->get('year', now()->year);
        $data = self::getBudgetMonthly($userId, $month, $year);
        return response()->json(
            [
                'data'   => $data["data"],
                'results'   => $data["budget"],
            ],
        );
    }

    public static function getBudgetMonthly($userId, $month, $year)
    {
        $incomes = IncomeStatementController::getIncomeStatement($userId, $month, $year);
        $incomesData = &$incomes["data"];
        $budget = BudgetController::getBudget($userId, ($year - 1))["results"];
        foreach ($incomesData as $key => &$group) {
            $total_budget = 0;
            if ($group["type"] != "total") {
                foreach ($group["data"] as $key => &$entry) {
                    foreach ($budget as $key => $budget_entry) {
                        if ($budget_entry->account_id == $entry->account_id) {
                            $entry->amount_budget = $budget_entry->monthly;
                            $entry->amount_difference = $budget_entry->monthly - $entry->amount;
                            $total_budget += $budget_entry->monthly;
                        }
                    }
                }
            }
            $group["total_budget"] = $total_budget;
        }
        $returned["data"] = $incomesData;
        $returned["budget"] = $budget;
        return $returned;
    }
}
