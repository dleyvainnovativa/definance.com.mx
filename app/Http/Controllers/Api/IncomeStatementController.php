<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        // $month = 1;
        $year = $request->get('year', now()->year);
        if ($month == "total") {
            $incomeSt = self::getIncomeStatement($userId, 12, $year, true);
        } else {
            $incomeSt = self::getIncomeStatement($userId, $month, $year);
        }

        return response()->json(
            [
                'data'   => $incomeSt["data"],
                'total'   => $incomeSt["total"],
            ],
        );
    }
    public static function getIncomeStatement($userId, $month, $year, $summary = null)
    {
        // $year = 2026;
        $results = [];
        if ($summary) {
            $results = TrialBalanceController::getTrialBalanceSummary($userId, $month, $year)->get();
        } else {
            $results = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        }

        $prefixMap = [
            [
                'key' => 'incomes',
                'data' => [],
                'icon' => 'fa-arrow-trend-up',
                'type'   => 'credit',
                'title' => 'Ingresos',
                'codes' => ['400.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'expenses',
                'data' => [],
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Gastos',
                'codes' => ['500.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'total',
                'data' => [],
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Utilidad / Pérdida',
                'codes' => ['400.', '500.'],
                'calculate' => [
                    "plus" => ['incomes'],
                    "minus" => ['expenses']
                ],
                'display' => 'total',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_incomes',
                'data' => [],
                'icon' => 'fa-arrow-trend-up',
                'type'   => 'credit',
                'title' => 'Otros Productos Financieros',
                'codes' => ['700.', '900.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_expenses',
                'data' => [],
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Otros Gastos Financieros',
                'codes' => ['600.', '800.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_total',
                'data' => [],
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Utilidad / Pérdida',
                'codes' => ['700.', '900.', '600.', '800.'],
                'calculate' => [
                    "plus" => ['other_incomes', 'incomes'],
                    "minus" => ['other_expenses', 'expenses']
                ],
                'display' => 'total',
                'total' => 0,
                'total_sum' => 0,
                'percent' => 0,
            ],
        ];

        $data = [];

        $rows = $results->map(function ($entry) use (&$prefixMap) {

            foreach ($prefixMap as &$group) {

                // Skip total rows for now
                if ($group['display'] !== 'operation') {
                    continue;
                }
                foreach ($group['codes'] as $code) {
                    if (!str_starts_with($entry->account_code, $code)) {
                        continue;
                    }
                    $amount = 0;
                    $debit  = $entry->debit  ?? 0;
                    $credit = $entry->credit ?? 0;
                    if ($group['type'] === 'credit') {
                        $amount = $credit;
                        $group['total'] += $credit;
                    }
                    if ($group['type'] === 'debit') {
                        $amount = $debit;
                        $group['total'] += $debit;
                    }
                    $group['total_sum'] += $entry->total;
                    $entry->amount = $amount;
                    $group['data'][] = $entry;
                    break 2;
                }
            }
            return $entry;
        });

        $groupIndex = collect($prefixMap)->keyBy('key');
        $totalIncomes = $groupIndex['incomes']['total'] ?? 0;

        foreach ($prefixMap as &$group) {
            if ($group['display'] !== 'total') {
                continue;
            }
            $total_sum = 0;
            $total = 0;
            foreach ($group['calculate']['plus'] as $key) {
                $total += $groupIndex[$key]['total'] ?? 0;
                $total_sum += $groupIndex[$key]['total_sum'] ?? 0;
            }

            foreach ($group['calculate']['minus'] as $key) {
                $total -= $groupIndex[$key]['total'] ?? 0;
                $total_sum -= $groupIndex[$key]['total_sum'] ?? 0;
            }

            $group['total'] = round($total, 2);
            $group['total_sum'] = round($total_sum, 2);
        }

        foreach ($prefixMap as &$group) {

            if ($totalIncomes > 0) {
                $group['percent'] = round(
                    ($group['total'] / $totalIncomes) * 100,
                    2
                );
            } else {
                $group['percent'] = 0;
            }
        }

        foreach ($prefixMap as &$group) {

            if (!isset($group['data']) || empty($group['data'])) {
                continue;
            }

            foreach ($group['data'] as &$row) {

                if ($totalIncomes > 0) {
                    $row->percent = round(
                        ($row->amount / $totalIncomes) * 100,
                        2
                    );
                } else {
                    $row->percent = 0;
                }
            }
        }

        $groupIndex = collect($prefixMap)->keyBy('key');
        $totalUtility = $groupIndex['other_total']['total'] ?? 0;

        $returned["data"] = $prefixMap;
        $returned["total"] = $totalUtility;
        $returned["results"] = $results;
        return $returned;
    }
}
