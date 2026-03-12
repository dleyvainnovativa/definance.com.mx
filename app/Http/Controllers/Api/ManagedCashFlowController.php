<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManagedCashFlowController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $cash_flow = self::getCashFlow($userId, $month, $year);

        // $fileName = "fea/{$userId}/fea_{$month}_{$year}.json";
        // if (!Storage::exists($fileName)) {
        //     $saveFile = [];
        // } else {
        //     $content = Storage::get($fileName);
        //     $saveFile = json_decode($content, true) ?? [];
        // }
        // $last_cash_flow = self::getCashFlowTotal($userId, self::getLastMonth($month, $year), self::getLastYear($month, $year));
        // $month_variation = $cash_flow["total"] - $last_cash_flow;
        // $cash_flow["total_map"][3]["total"] = $month_variation;

        return response()->json(
            [
                'save'   => $cash_flow["save"],
                'data'   => $cash_flow["data"],
                'cash_accounts'   => $cash_flow["cash_accounts"],

                // 'total' => $month_variation
            ],
        );
    }
    public static function getCashFlow($userId, $month, $year, $summary = false)
    {
        $savedMonth = $month - 1;
        $fileName = "fea/{$userId}/fea_{$savedMonth}_{$year}.json";
        $currentFileName = "fea/{$userId}/fea_{$month}_{$year}.json";
        // $month = 12;

        if (!Storage::exists($fileName)) {
            $saveFile = [];
        } else {
            $content = Storage::get($fileName);
            $saveFile = json_decode($content, true) ?? [];
        }

        if (!Storage::exists($currentFileName)) {
            $saveCurrentFile = [];
        } else {
            $content = Storage::get($currentFileName);
            $saveCurrentFile = json_decode($content, true) ?? [];
        }
        // $year = 2026;
        $results = [];
        $results = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        $budget = BudgetController::getBudget($userId, $year - 1);
        $cash_accounts = DB::table('accounts')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('code', 'like', '100.1.%')
                    ->orWhere('code', 'like', '100.2.%');
            })
            ->get();
        $budgetResults = $budget["data"];
        $incomeBudget = $budgetResults[0];
        $incomeBudget["budget"] = true;
        $incomeBudget["manual"] = false;
        $incomeBudget["projection_manual"] = true;
        $incomeBudget["total_projection"] = 0;
        $incomeBudget["title"] = "Ingresos Presupuestados";
        $incomeBudget["total_attribute"] = "monthly";
        $incomeBudget["type"] = "debit";
        $incomeBudget["total_sum"] = $incomeBudget["total_month"];
        $incomeBudget["description"] = "Total de Ingresos Presupuestados";
        // dd($incomeBudget);


        $projectionAmount = 0;
        foreach ($incomeBudget["data"] as $key => &$incomeData) {
            $incomeData->projection_entry = 0;
            $incomeData->percent_projection = 0;
            foreach ($saveCurrentFile as $key => $save) {
                if ($save["id"] == $incomeData->account_id) {
                    $incomeData->projection_entry = $save["projection"];
                    $projectionAmount = $save["projection"];
                    break;
                }
            }
            foreach ($saveFile as $key => $save) {
                $typeSaved = $incomeBudget["type"];
                if ($save["cash_account"] == $incomeData->account_id) {
                    if ($typeSaved === 'credit') {
                        $incomeData->projection_entry -= $save["projection"];
                        $projectionAmount -= $save["projection"];
                    }
                    if ($typeSaved === 'debit') {
                        $incomeData->projection_entry += $save["projection"];
                        $projectionAmount += $save["projection"];
                    }
                }
            }
            $incomeBudget['total_projection'] += $projectionAmount;
        }

        $prefixMap = [
            [
                'key' => 'cash_available',
                'data' => [],
                'projection_manual' => false,
                'total_attribute' => "total",
                'manual' => false,
                'icon' => 'fa-arrow-trend-up',
                'type'   => 'debit',
                'title' => 'Efectivo Disponible',
                'description' => "Total de Efectivo Disponible",
                'codes' => ['100.1.', '100.2.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            $incomeBudget,
            [
                'key' => 'miscellaneous_debtors_income',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "opening",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Ingresos Deudores Diversos',
                'description' => "Total de Ingresos Deudores Diversos",
                'codes' => ['100.5.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_incomes',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => true,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Otros Ingresos',
                'description' => "Total de Otros Ingresos",
                'codes' => [],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'total',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => false,
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Ingresos',
                'description' => "Total de Ingresos",
                'codes' => ['100.1.', '100.2.', '400.', '100.5.'],
                'calculate' => [
                    "plus" => ['incomes', 'cash_available', 'miscellaneous_debtors_income', 'other_incomes'],
                    "minus" => []
                ],
                'display' => 'total',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'monthly_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => true,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Gastos del mes',
                'description' => "Total de Gastos del mes",
                'codes' => [],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'credit_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Pagos a T. de Crédito',
                'description' => "Total de Egresos por Pagos a T. de Crédito",
                'codes' => ['200.1.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'bank_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "opening",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Pagos de Créditos Bancarios',
                'description' => "Total de Egresos por Pagos de Créditos Bancarios",
                'codes' => ['200.2.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'car_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "opening",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Pagos de Créditos Automotrices',
                'description' => "Total de Egresos por Pagos de Créditos Automotrices",
                'codes' => ['200.3.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'house_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "opening",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Pagos de Créditos Hipotecarios',
                'description' => "Total de Egresos por Pagos de Créditos Hipotecarios",
                'codes' => ['200.4.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'payments_miscellaneous_creditors',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "opening",
                'manual' => false,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Pagos a Acreedores Diversos',
                'description' => "Total de Egresos por Pagos a Acreedores Diversos",
                'codes' => ['200.5.'],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'investment_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => true,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Egresos por Traspasos a Cuentas de Inversión',
                'description' => "Total de Egresos por Traspasos a Cuentas de Inversión",
                'codes' => [],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => true,
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'credit',
                'title' => 'Otros Egresos',
                'description' => "Total de Otros Egresos",
                'codes' => [],
                'display' => 'operation',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'total_expenses',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => false,
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Total de Egresos',
                'description' => "Resultado del Ejercicio",
                'codes' => [],
                'calculate' => [
                    "plus" => [
                        'monthly_expenses',
                        'credit_expenses',
                        'bank_expenses',
                        'car_expenses',
                        'house_expenses',
                        'payments_miscellaneous_creditors',
                        'investment_expenses',
                        'other_expenses'
                    ],
                    "minus" => []
                ],
                'display' => 'total',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'summary',
                'data' => [],
                'projection_manual' => true,
                'total_attribute' => "total",
                'manual' => false,
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Saldo Final',
                'description' => "Resultado del Ejercicio",
                'codes' => [],
                'calculate' => [
                    "plus" => [
                        'incomes',
                        'cash_available',
                        'miscellaneous_debtors_income',
                        'other_incomes'
                    ],
                    "minus" => [
                        'monthly_expenses',
                        'credit_expenses',
                        'bank_expenses',
                        'car_expenses',
                        'house_expenses',
                        'payments_miscellaneous_creditors',
                        'investment_expenses',
                        'other_expenses'
                    ]
                ],
                'display' => 'total',
                'total' => 0,
                'total_sum' => 0,
                'total_projection' => 0,
                'percent' => 0,
            ],
        ];

        // dd($incomeBudget);

        $data = [];



        $rows = $results->map(function ($entry) use (&$prefixMap, $saveFile, $saveCurrentFile) {
            foreach ($prefixMap as &$group) {
                if (!isset($group["budget"])) {
                    $group["budget"] = false;
                }
                if (!$group["budget"]) {
                    // Skip total rows for now
                    if ($group['display'] !== 'operation') {
                        continue;
                    }
                    $entry->projection_entry = 0;
                    if ($group["projection_manual"] == false) {
                        $entry->projection_entry = $entry->{$group['total_attribute']};
                    }
                    foreach ($group['codes'] as $code) {
                        if (!str_starts_with($entry->account_code, $code)) {
                            continue;
                        }
                        $amount = 0;
                        $total  = $entry->total  ?? 0;
                        $debit  = $entry->debit  ?? 0;
                        $credit = $entry->credit ?? 0;
                        $projectionAmount = 0;
                        if ($group["projection_manual"] == false) {
                            $projectionAmount = $entry->{$group['total_attribute']};
                        }
                        // $projectionAmount = $entry->{$group['total_attribute']};
                        foreach ($saveCurrentFile as $key => $save) {
                            if ($save["id"] == $entry->account_id) {
                                $entry->projection_entry = $save["projection"];
                                $projectionAmount = $save["projection"];
                                break;
                            }
                        }

                        foreach ($saveFile as $key => $save) {
                            $groupIndex = collect($prefixMap)->keyBy('key');
                            $typeSaved = $groupIndex[$save["key"]]["type"];

                            if ($save["cash_account"] == $entry->account_id) {
                                if ($typeSaved === 'credit') {
                                    // $group['total'] -= $save["projection"];
                                    // $amount -= $save["projection"];
                                    // $total -= $save["projection"];
                                    $entry->projection_entry -= $save["projection"];
                                    $projectionAmount -= $save["projection"];

                                    // $entry->{$group['total_attribute']} -= $save["projection"];
                                }
                                if ($typeSaved === 'debit') {
                                    // $group['total'] += $save["projection"];
                                    // $amount += $save["projection"];
                                    // $total += $save["projection"];
                                    $entry->projection_entry += $save["projection"];
                                    $projectionAmount += $save["projection"];
                                    // $entry->{$group['total_attribute']} += $save["projection"];
                                }
                            }
                        }
                        if ($group['type'] === 'credit') {
                            $amount = $credit;
                            $group['total'] += $credit;
                        }
                        if ($group['type'] === 'debit') {
                            $amount = $debit;
                            $group['total'] += $debit;
                        }
                        $group['total_projection'] += $projectionAmount;
                        $group['total_sum'] += $total;
                        $entry->amount = $amount;
                        $group['data'][] = $entry;
                        break 2;
                    }
                }
            }
            return $entry;
        });


        // dd($incomeBudget);

        $groupIndex = collect($prefixMap)->keyBy('key');
        // $totalIncomes = $groupIndex['incomes']['total'] ?? 0;
        $totalIncomes = $groupIndex['incomes']['total_sum'] + $groupIndex['cash_available']['total_sum'] + $groupIndex['miscellaneous_debtors_income']['total_sum'] + $groupIndex['other_incomes']['total_sum'];
        $totalExpenses =
            $groupIndex['monthly_expenses']['total_sum'] +
            $groupIndex['credit_expenses']['total_sum'] +
            $groupIndex['bank_expenses']['total_sum'] +
            $groupIndex['car_expenses']['total_sum'] +
            $groupIndex['house_expenses']['total_sum'] +
            $groupIndex['payments_miscellaneous_creditors']['total_sum'] +
            $groupIndex['investment_expenses']['total_sum'] +
            $groupIndex['other_expenses']['total_sum'];

        $totalIncomesProjections = $groupIndex['incomes']['total_projection']
            + $groupIndex['cash_available']['total_projection']
            + $groupIndex['miscellaneous_debtors_income']['total_projection']
            + $groupIndex['other_incomes']['total_projection'];
        $totalExpensesProjections =
            $groupIndex['monthly_expenses']['total_projection'] +
            $groupIndex['credit_expenses']['total_projection'] +
            $groupIndex['bank_expenses']['total_projection'] +
            $groupIndex['car_expenses']['total_projection'] +
            $groupIndex['house_expenses']['total_projection'] +
            $groupIndex['payments_miscellaneous_creditors']['total_projection'] +
            $groupIndex['investment_expenses']['total_projection'] +
            $groupIndex['other_expenses']['total_projection'];


        $groupIndex = [];
        foreach ($prefixMap as &$group) {
            $groupIndex[$group['key']] = &$group;
        }
        unset($group);
        foreach ($saveCurrentFile as $key => $save) {
            if ($save["id"] == null) {
                $typeSaved = $groupIndex[$save["key"]]["type"];
                $totalAttribute = $groupIndex[$save["key"]]["total_attribute"];

                if ($typeSaved === 'credit') {
                    $totalExpensesProjections += $save["projection"];
                    $groupIndex[$save["key"]]["total_projection"] += $save["projection"];
                    $groupIndex[$save["key"]]["$totalAttribute"] += $save["total"];
                    $groupIndex[$save["key"]]["total_sum"] += $save["total"];
                }

                if ($typeSaved === 'debit') {
                    $totalIncomesProjections += $save["projection"];
                    $groupIndex[$save["key"]]["total_projection"] += $save["projection"];
                    $groupIndex[$save["key"]]["$totalAttribute"] += $save["total"];
                    $groupIndex[$save["key"]]["total_sum"] += $save["total"];
                }
            }
        }
        // dd($totalIncomesProjections);


        // dd(
        //     $totalExpensesProjections,
        //     $totalIncomesProjections
        // );
        foreach ($saveCurrentFile as $key => &$save) {
            if ($save["id"] == null) {
                $typeSaved = $groupIndex[$save["key"]]["type"];
                $totalAttribute = $groupIndex[$save["key"]]["total_attribute"];

                if ($typeSaved === 'credit') {
                    $save["percent"] = ($save["total"] / $totalExpenses) * 100;
                    $save["percent_projection"] = ($save["projection"] / $totalExpensesProjections) * 100;
                }

                if ($typeSaved === 'debit') {
                    $save["percent_projection"] = ($save["projection"] / $totalIncomesProjections) * 100;
                    $save["percent"] = ($save["total"] / $totalIncomes) * 100;
                }
            }
        }

        foreach ($prefixMap as &$group) {
            if ($group['display'] !== 'total') {
                continue;
            }
            $total_sum = 0;
            $total = 0;
            $total_projection = 0;
            foreach ($group['calculate']['plus'] as $key) {
                $total += $groupIndex[$key]['total'] ?? 0;
                $total_sum += $groupIndex[$key]['total_sum'] ?? 0;
                $total_projection += $groupIndex[$key]['total_projection'] ?? 0;
            }

            foreach ($group['calculate']['minus'] as $key) {
                $total -= $groupIndex[$key]['total'] ?? 0;
                $total_sum -= $groupIndex[$key]['total_sum'] ?? 0;
                $total_projection -= $groupIndex[$key]['total_projection'] ?? 0;
            }

            $group['total'] = round($total, 2);
            $group['total_sum'] = round($total_sum, 2);
            $group['total_projection'] = round($total_projection, 2);
        }

        // dd($totalIncomesProjections);

        foreach ($prefixMap as &$group) {

            if ($totalIncomes > 0) {
                $group['percent'] = round(
                    ($group['total'] / $totalIncomes) * 100,
                    2
                );
            } else {
                $group['percent'] = 0;
            }
            if ($totalIncomesProjections > 0) {
                $group['percent_projection'] = round(
                    ($group['total_projection'] / $totalIncomesProjections) * 100,
                    2
                );
            } else {
                $group['percent_projection'] = 0;
            }
        }

        foreach ($prefixMap as &$group) {

            if (!isset($group['data']) || empty($group['data'])) {
                continue;
            }

            foreach ($group['data'] as &$row) {
                if ($group["type"] == "debit") {

                    if ($totalIncomes > 0) {
                        $row->percent = round(
                            ($row->{$group['total_attribute']} / $totalIncomes) * 100,
                            2
                        );
                    } else {
                        $row->percent = 0;
                    }
                    if ($totalIncomesProjections > 0) {
                        $row->percent_projection =
                            round(
                                ($row->projection_entry / $totalIncomesProjections) * 100,
                                2
                            );
                    } else {
                        $row->percent_projection = 0;
                    }
                } elseif ($group["type"] == "credit") {
                    if ($totalExpenses > 0) {
                        $row->percent = round(
                            ($row->{$group['total_attribute']} / $totalExpenses) * 100,
                            2
                        );
                    } else {
                        $row->percent = 0;
                    }
                    if ($totalExpensesProjections > 0) {
                        $row->percent_projection = round(
                            ($row->projection_entry / $totalExpensesProjections) * 100,
                            2
                        );
                    } else {
                        $row->percent_projection = 0;
                    }
                }
            }
        }

        $groupIndex = collect($prefixMap)->keyBy('key');
        $totalUtility = $groupIndex['other_total']['total'] ?? 0;

        $returned["save"] = $saveCurrentFile;
        $returned["data"] = $prefixMap;
        $returned["total"] = $totalUtility;
        $returned["results"] = $results;
        $returned["cash_accounts"] = $cash_accounts;
        return $returned;
    }

    private static function getLastMonth(int $month, int $year): int
    {
        return $month === 1 ? 12 : $month - 1;
    }
    private static function getLastYear(int $month, int $year): int
    {
        return $month === 1 ? $year - 1 : $year;
    }

    public function save(Request $request)
    {
        $userId = $request->user()->id;
        $year = $request->input('year', now()->year);
        $month = $request->input('month');
        $saveFile = $request->input('data', []);

        // Optional validation
        if (empty($saveFile)) {
            return response()->json([
                'message' => 'No FEA data received'
            ], 422);
        }

        // Build filename
        $fileName = "fea/{$userId}/fea_{$month}_{$year}.json";

        // Save file
        Storage::put($fileName, json_encode($saveFile, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => 'File saved',
            'path' => $fileName
        ]);
    }
}
