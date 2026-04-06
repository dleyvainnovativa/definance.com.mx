<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\TrialBalanceController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashCountController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $data = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        return response()->json([
            'data'  => $data,
        ]);
    }
}
