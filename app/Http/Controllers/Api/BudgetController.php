<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        // $month = 1;
        $userId = $request->user()->id;
        $year = $request->get('year', now()->year);
        $data = self::getBudget($userId, $year);
        return response()->json(
            [
                'total_incomes'   => $data["total_incomes"],
                'data'   => $data["data"],
                'total'   => $data["total"],
                'results'   => $data["results"],
            ],
        );
    }

    public static function getBudget($userId, $year)
    {
        $month = 12;
        $fileName = "pr/{$userId}/pr_{$year}.json";
        if (!Storage::exists($fileName)) {
            $saveFile = [];
        } else {
            $content = Storage::get($fileName);
            $saveFile = json_decode($content, true) ?? [];
        }
        $total_incomes = 0;
        $results = [];
        $incomeStatement = IncomeStatementController::getIncomeStatement($userId, $month, $year, true);
        $data = $incomeStatement["data"];
        foreach ($data as $key => &$group) {
            $total_pl = 0;
            $total_month = 0;
            if ($group["type"] != "total") {
                foreach ($group["data"] as $key => &$entry) {
                    $pr = 100;
                    foreach ($saveFile as $key => $fileEntry) {
                        if ($fileEntry["account_id"] == $entry->account_id) {
                            $pr = $fileEntry["percent"];
                            break;
                        }
                    }
                    $entry_total = $entry->amount * ($pr / 100);
                    $entry->pr = $pr;
                    $entry->annual = $entry_total;
                    $entry->monthly = $entry_total / 12;
                    $total_pl += $entry_total;
                    $total_month += ($entry_total / 12);
                    if ($group["key"] == "incomes") {
                        $total_incomes += ($entry_total);
                    }
                }
                $group["total_pl"] = $total_pl;
                $group["total_month"] = $total_month;
                foreach ($group["data"] as $key => &$entry) {
                    $pr = 100;
                    if ($total_pl == 0) {
                        $entry_pr_percent = 0;
                    } else {
                        $entry_pr_percent = round(($entry->annual / $total_incomes) * 100, 2);
                    }
                    $entry->pl = $entry_pr_percent;
                    array_push($results, $entry);
                }
            }
        }
        $returned["total_incomes"] = $total_incomes;
        $returned["data"] = $data;
        $returned["total"] = $incomeStatement["total"];
        $returned["results"] = $results;
        return $returned;
    }

    public function save(Request $request)
    {
        $userId = $request->user()->id;
        $year = $request->input('year', now()->year);
        $saveFile = $request->input('data', []);

        // Optional validation
        if (empty($saveFile)) {
            return response()->json([
                'message' => 'No PR data received'
            ], 422);
        }

        // Build filename
        $fileName = "pr/{$userId}/pr_{$year}.json";

        // Save file
        Storage::put($fileName, json_encode($saveFile, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => 'File saved',
            'path' => $fileName
        ]);
    }
}
