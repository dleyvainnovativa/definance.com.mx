<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        // $month = 1;
        $year = $request->get('year', now()->year);
        $balanceSheet = self::getBalanceSheet($userId, $month, $year);
        return response()->json(
            [
                'data'   => $balanceSheet["data"],
                'total'   => $balanceSheet["total"],
                'flagRemanent'   => $balanceSheet["flagRemanent"],
            ],
        );
    }
    public static function getBalanceSheet($userId, $month, $year)
    {
        $results = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        $prefixMap = [
            [
                'key' => 'assets',
                'data' => [],
                'icon' => 'fa-coins',                 // Assets = resources
                'type'   => 'debit',
                'title' => 'Activos',
                'description' => 'Total de Activos',
                'codes' => ['100.'],
                'display' => 'operation',
                'total' => 0,
                'divided' => "total_assets",
                'percent' => 100,
                'percent_group' => 0,
            ],
            [
                'key' => 'fixed_assets',
                'data' => [],
                'icon' => 'fa-building',              // Fixed assets = property / equipment
                'type'   => 'debit',
                'title' => 'Activos Fijos',
                'description' => 'Total de Activos Fijos',
                'codes' => ['110.'],
                'display' => 'operation',
                'total' => 0,
                'divided' => "total_assets",
                'percent' => 100,
                'percent_group' => 0,
            ],
            [
                'key' => 'total_assets',
                'data' => [],
                'icon' => 'fa-landmark',               // Total assets = balance sheet strength
                'type'   => 'total',
                'title' => 'TOTAL ACTIVOS FIJOS',
                'description' => 'Activos + Activos Fijos',
                'codes' => ['100.', '110.'],
                'calculate' => [
                    "plus" => ['assets', 'fixed_assets'],
                    "minus" => []
                ],
                'display' => 'total',
                'total' => 0,
                'divided' => "",
                'percent' => 100,
                'percent_group' => 0,
            ],
            [
                'key' => 'liabilities',
                'data' => [],
                'icon' => 'fa-file-invoice-dollar',    // Liabilities = obligations
                'type'   => 'debit',
                'title' => 'Pasivos',
                'description' => 'Total de Pasivos',
                'codes' => ['200.'],
                'display' => 'operation',
                'total' => 0,
                'divided' => "total",
                'percent' => 100,
                'percent_group' => 0,
            ],
            [
                'key' => 'equity',
                'data' => [],
                'icon' => 'fa-hand-holding-dollar',    // Equity = ownership value
                'type'   => 'debit',
                'title' => 'Patrimonio',
                'description' => 'Total de Patrimonio',
                'codes' => ['300.2', '300.1'],
                'display' => 'operation',
                'total' => 0,
                'divided' => "total",
                'percent' => 100,
                'percent_group' => 0,
            ],
            [
                'key' => 'total',
                'data' => [],
                'icon' => 'fa-scale-balanced',          // Assets = Liabilities + Equity
                'type'   => 'total',
                'title' => 'TOTAL PASIVO + CAPITAL',
                'description' => 'Pasivo + Capital',
                'codes' => ['200.', '300.1', '300.2'],
                'calculate' => [
                    "plus" => ['liabilities', 'equity'],
                    "minus" => []
                ],
                'display' => 'total',
                'total' => 0,
                'divided' => "total_assets",
                'percent' => 100,
                'percent_group' => 0,
            ],
        ];
        $flagRemanent = 0;


        // Create a lookup map for account names for efficient access
        $accountNameMap = collect($results)->keyBy('account_code');

        foreach ($prefixMap as &$group) {
            if ($group['display'] !== 'operation') {
                continue;
            }

            $parentAccounts = [];

            foreach ($results as $entry) {
                // Skip parent accounts from being processed as children
                if (substr_count($entry->account_code, '.') < 1) {
                    continue;
                }

                foreach ($group['codes'] as $code) {
                    if ($code == "300.1") {
                        break;
                    }

                    if ($code == "300.2") {
                        $flagRemanent = (float) $entry->total;

                        if ($flagRemanent == 0) {
                            break; // 🔴 This stops the entire foreach
                        }

                        // continue;
                    }

                    if (str_starts_with($entry->account_code, $code)) {
                        $parts = explode('.', $entry->account_code);
                        $parentCode = $parts[0] . '.' . $parts[1];

                        if (!isset($parentAccounts[$parentCode])) {
                            // Look for the parent account in our map to get its real name
                            $parentEntry = $accountNameMap->get($parentCode);
                            $parentName = $parentEntry ? $parentEntry->account_name : 'Total ' . $parentCode;

                            $parentAccounts[$parentCode] = (object)[
                                'account_code' => $parentCode,
                                'account_name' => $parentName, // Use the real name here
                                'amount' => 0,
                                'percent' => 0
                            ];
                        }

                        $amount = $entry->total ?? 0;
                        $parentAccounts[$parentCode]->amount += $amount;
                        $group['total'] += $amount;
                        break; // Move to the next entry
                    }
                }
            }
            $group['data'] = array_values($parentAccounts);

            // Replace the group data with the aggregated parent accounts
            $extraParentAccounts = [];
            foreach ($group['codes'] as $code) {
                if ($code == "300.1") {
                    $amount = 0;
                    for ($i = 1; $i <= $month; $i++) {
                        $amount += IncomeStatementController::getIncomeStatement($userId, $i, $year)["total"];
                    }
                    $extraParentAccounts[0] = (object)[
                        'account_code' => $code,
                        'account_name' => "DEFICIT O REMANENTE DE EJERCICIO", // Use the real name here
                        'amount' => $amount,
                        'percent' => 0
                    ];
                    $group['total'] += $amount;
                    $parentAccounts = array_merge($parentAccounts, $extraParentAccounts);

                    break;
                }
            }
            // foreach ($group['codes'] as $code) {
            //     if ($code == "300.2" && $flagRemanent == 0) {
            //         $amount = 0;
            //         for ($i = 1; $i <= 12; $i++) {
            //             $amount += IncomeStatementController::getIncomeStatement($userId, $i, $year - 1)["total"];
            //         }
            //         if ($amount != 0) {

            //             $extraParentAccounts[0] = (object)[
            //                 'account_code' => $code,
            //                 'account_name' => "DEFICIT O REMANENTE DEL EJERCICIO ANTERIORES2", // Use the real name here
            //                 'amount' => $amount,
            //                 'percent' => 0
            //             ];
            //             $group['total'] += $amount;
            //             $parentAccounts = array_merge($parentAccounts, $extraParentAccounts);
            //         }

            //         break;
            //     }
            // }
            $group['data'] = array_values($parentAccounts);
        }
        unset($group);


        $groupIndex = collect($prefixMap)->keyBy('key');


        // The rest of your calculation logic remains the same...
        foreach ($prefixMap as &$group) {
            if ($group['display'] !== 'total') {
                continue;
            }
            $total = 0;
            foreach ($group['calculate']['plus'] as $key) {
                $total += $groupIndex[$key]['total'] ?? 0;
            }
            foreach ($group['calculate']['minus'] as $key) {
                $total -= $groupIndex[$key]['total'] ?? 0;
            }
            $group['total'] = round($total, 2);
        }
        unset($group);

        $groupIndex = collect($prefixMap)->keyBy('key');

        $totalAssets = $groupIndex['total_assets']['total'] ?? 0;
        $totalLE = $groupIndex['total']['total'] ?? 0;

        foreach ($prefixMap as $key => &$group) {
            $totalGroup = $group["total"];
            $dividedKey = $group["divided"];

            foreach ($group["data"] as $key => &$entry) {
                if ($entry->amount > 0 && $totalGroup > 0) {
                    $entry->percent = round(($entry->amount / $totalGroup) * 100, 2);
                } else {
                    $entry->percent = 0;
                }
                if (!empty($dividedKey)) {
                    $groupedCalculated = $groupIndex[$dividedKey]['total'];
                    if ($entry->amount > 0 && $groupedCalculated > 0) {
                        $entry->percent_group = round(($entry->amount / $groupedCalculated) * 100, 2);
                    } else {
                        $entry->percent_group = 0;
                    }
                }
            }
            if (!empty($dividedKey)) {
                if ($totalGroup > 0 &&  $groupIndex[$dividedKey]['total'] > 0) {
                    $group["percent_group"] =
                        round(($totalGroup / $groupIndex[$dividedKey]['total']) * 100, 2);
                }
            }
        }
        $data["data"] = $prefixMap;
        $data["total"] = 0;
        $data["flagRemanent"] = $flagRemanent;
        return $data;
    }
    private static function getLastMonth(int $month, int $year): int
    {
        return $month === 1 ? 12 : $month - 1;
    }
    private static function getLastYear(int $month, int $year): int
    {
        return $month === 1 ? $year - 1 : $year;
    }
}
