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
        $month =  $request->get('month');
        $userId = $request->user()->id;
        $year = $request->get('year', now()->year);
        $summary = $request->boolean('summary');


        if ($month == "total") {
            $data = self::getBudgetMonthly($userId, 12, $year, true, true);
        } else {
            if ($summary) {
                $data = self::getBudgetMonthly($userId, $month, $year, $summary, false);
            } else {
                $data = self::getBudgetMonthly($userId, $month, $year);
            }
        }
        return response()->json(
            [
                'data'   => $data["data"],
                'results'   => $data["budget"],
            ],
        );
    }

    public static function getBudgetMonthly($userId, $month, $year, $summary = false, $summary_total = false)
    {
        if ($summary) {
            $incomes = IncomeStatementController::getIncomeStatement($userId, $month, $year, true);
        } else {
            $incomes = IncomeStatementController::getIncomeStatement($userId, $month, $year);
        }
        $budget = BudgetController::getBudget($userId, ($year - 1))["results"];
        $incomesData = &$incomes["data"];
        foreach ($incomesData as $key => &$group) {
            $total_budget = 0;
            if ($group["type"] != "total") {
                foreach ($group["data"] as $key => &$entry) {
                    foreach ($budget as $key => $budget_entry) {
                        if ($budget_entry->account_id == $entry->account_id) {
                            if ($summary && $summary_total) {
                                $entry->amount_budget = $budget_entry->annual;
                                $entry->amount_difference = $budget_entry->annual - $entry->amount;
                                $total_budget += $budget_entry->annual;
                            } else if (!$summary_total && $summary) {
                                $budget_amount = $budget_entry->monthly * $month;
                                $entry->amount_budget = $budget_amount;
                                $entry->amount_difference = $budget_amount - $entry->amount;
                                $total_budget += $budget_amount;
                            } else {
                                $entry->amount_budget = $budget_entry->monthly;
                                $entry->amount_difference = $budget_entry->monthly - $entry->amount;
                                $total_budget += $budget_entry->monthly;
                            }
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

    //  public static function getBudgetMonthly($userId, $month, $year, $summary = false)
    // {
    //     // 🔹 Get incomes
    //     if ($summary) {
    //         $incomes = IncomeStatementController::getIncomeStatement($userId, $month, $year, true);
    //     } else {
    //         $incomes = IncomeStatementController::getIncomeStatement($userId, $month, $year);
    //         $incomesSummary = IncomeStatementController::getIncomeStatement($userId, $month, $year, true);
    //     }

    //     // 🔹 Get budget (previous year)
    //     $budget = BudgetController::getBudget($userId, ($year - 1))["results"];

    //     // 🔹 Build budget map (performance optimization)
    //     $budgetMap = [];
    //     foreach ($budget as $b) {
    //         $budgetMap[$b->account_id] = $b;
    //     }

    //     // 🔹 Build summary map (only if needed)
    //     $summaryMap = [];
    //     if (!$summary) {
    //         foreach ($incomesSummary["data"] as $group) {
    //             if ($group["type"] != "total") {
    //                 foreach ($group["data"] as $entry) {
    //                     $summaryMap[$entry->account_id] = $entry;
    //                 }
    //             }
    //         }
    //     }

    //     $incomesData = &$incomes["data"];

    //     // 🔹 Main loop
    //     foreach ($incomesData as &$group) {
    //         $total_budget = 0;
    //         $total_summary = 0;
    //         if ($group["type"] != "total") {
    //             foreach ($group["data"] as &$entry) {

    //                 // 🔸 Get budget for this account
    //                 if (isset($budgetMap[$entry->account_id])) {
    //                     $budget_entry = $budgetMap[$entry->account_id];

    //                     if ($summary) {
    //                         $entry->amount_budget = $budget_entry->annual;
    //                         $entry->amount_difference = $budget_entry->annual - $entry->amount;
    //                         $total_budget += $budget_entry->annual;
    //                     } else {
    //                         $entry->amount_budget = $budget_entry->monthly;
    //                         $entry->amount_difference = $budget_entry->monthly - $entry->amount;
    //                         $total_budget += $budget_entry->monthly;
    //                     }
    //                 }

    //                 // 🔸 Add summary fields (ONLY when not in summary mode)
    //                 if (!$summary && isset($summaryMap[$entry->account_id])) {
    //                     $summaryEntry = $summaryMap[$entry->account_id];

    //                     $entry->amount_summary = $summaryEntry->amount;
    //                     $total_summary += $summaryEntry->amount;

    //                     if (isset($budgetMap[$entry->account_id])) {
    //                         $budget_entry = $budgetMap[$entry->account_id];

    //                         $entry->amount_budget_summary = $budget_entry->annual;
    //                         $entry->amount_difference_summary =
    //                             $budget_entry->annual - $summaryEntry->amount;
    //                     }
    //                 }
    //             }
    //         }

    //         $group["total_summary"] = $total_summary;
    //         $group["total_budget"] = $total_budget;
    //     }

    //     return [
    //         "data" => $incomesData,
    //         "budget" => $budget
    //     ];
    // }
}
