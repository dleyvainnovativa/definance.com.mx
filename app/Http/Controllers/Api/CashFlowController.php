<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $cash_flow = self::getCashFlow($userId, $month, $year);
        $last_cash_flow = self::getCashFlowTotal($userId, self::getLastMonth($month, $year), self::getLastYear($month, $year));
        $month_variation = $cash_flow["total"] - $last_cash_flow;
        $cash_flow["total_map"][3]["total"] = $month_variation;

        return response()->json(
            [
                "map" => $cash_flow["total_map"],
                'data'   => $cash_flow["data"],
                'total' => $month_variation
            ],
        );
    }
    public static function getCashFlow($userId, $month, $year)
    {
        $results = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        $total = 0;
        $totalOpening = 0;
        $totalMap = 0;
        $totalFinal = 0;
        foreach ($results as $entry) {
            if (
                str_starts_with($entry->account_code, '100.1') ||
                str_starts_with($entry->account_code, '100.2')
            ) {
                $total += $entry->total;
                $totalOpening += $entry->opening;
            }
        }

        $prefixMap = [

            // ===============================
            // OPERACIÓN
            // ===============================
            [
                'icon'  => 'fa-arrow-trend-up',
                'title' => 'FLUJO DE EFECTIVO DE LAS ACTIVIDADES DE OPERACIÓN',
                'total' => 0,
                'data'  => [
                    [
                        'title' => '+ INGRESOS RECIBIDOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '400',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ INGRESOS RECIBIDOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '400',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS POR GASTOS DIARIOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '500',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS POR GASTOS DIARIOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '500',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                ],
            ],

            // ===============================
            // INVERSIÓN Y AHORRO
            // ===============================
            [
                'icon'  => 'fa-piggy-bank',
                'title' => 'FLUJO DE EFECTIVO DE LAS ACTIVIDADES DE INVERSIÓN Y AHORRO',
                'total' => 0,
                'data'  => [

                    // Activos fijos
                    [
                        'title' => '- COMPRAS DE ACTIVOS FIJOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '110',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- COMPRAS DE ACTIVOS FIJOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '110',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ VENTAS DE ACTIVOS FIJOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '110',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ VENTAS DE ACTIVOS FIJOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '110',
                        'type' => 'debit',
                        'total' => 0,
                    ],

                    // Inversiones (acciones / criptos / bonos)
                    [
                        'title' => '- COMPRAS DE ACCIONES / CRIPTOS / BONOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.3',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- COMPRAS DE ACCIONES / CRIPTOS / BONOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.3',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ VENTAS DE ACCIONES / CRIPTOS / BONOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.3',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ VENTAS DE ACCIONES / CRIPTOS / BONOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.3',
                        'type' => 'debit',
                        'total' => 0,
                    ],

                    // Ahorro
                    [
                        'title' => '+ ENTRADAS POR RETIRO DE CUENTAS DE AHORRO (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.4',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ ENTRADAS POR RETIRO DE CUENTAS DE AHORRO (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.4',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- SALIDAS POR TRASPASOS A CUENTAS DE AHORRO (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.4',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- SALIDAS POR TRASPASOS A CUENTAS DE AHORRO (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.4',
                        'type' => 'credit',
                        'total' => 0,
                    ],

                    // Intereses
                    [
                        'title' => '+ INTERESES GANADOS EN INVERSIONES (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '700.1',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ INTERESES GANADOS EN INVERSIONES (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '700.1',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                ],
            ],

            // ===============================
            // FINANCIAMIENTO
            // ===============================
            [
                'icon'  => 'fa-hand-holding-dollar',
                'title' => 'FLUJO DE EFECTIVO DE LAS ACTIVIDADES DE FINANCIAMIENTO',
                'total' => 0,
                'data'  => [
                    [
                        'title' => '- PRESTAMOS OTORGADOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.5',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PRESTAMOS OTORGADOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.5',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ COBROS A PRESTAMOS OTORGADOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '100.5',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ COBROS A PRESTAMOS OTORGADOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '100.5',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS A TARJETAS DE CREDITO',
                        'code_target' => '100.2',
                        'code_target_end' => '200.1',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS A CREDITOS AUTOMOTRICES RECIBIDOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '200.3',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS A CREDITOS HIPOTECARIOS RECIBIDOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '200.4',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ ENTRADAS DE EFECTIVO PRESTAMOS RECIBIDOS',
                        'code_target' => '100.1',
                        'code_target_end' => '200.5',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '+ ENTRADAS DE EFECTIVO PRESTAMOS RECIBIDOS (BANCOS)',
                        'code_target' => '100.2',
                        'code_target_end' => '200.5',
                        'type' => 'debit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS A PRESTAMOS RECIBIDOS (EFECTIVO)',
                        'code_target' => '100.1',
                        'code_target_end' => '200.5',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- PAGOS A PRESTAMOS RECIBIDOS (Bancos)',
                        'code_target' => '100.2',
                        'code_target_end' => '200.5',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                    [
                        'title' => '- GASTOS DE FINANCIACIÓN (Bancos)',
                        'code_target' => '100.2',
                        'code_target_end' => '600',
                        'type' => 'credit',
                        'total' => 0,
                    ],
                ],
            ],
        ];
        $journal = DB::table('journal')
            ->where('user_id', $userId)
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->get();

        foreach ($journal as $entry) {

            foreach ($prefixMap as &$section) {

                foreach ($section['data'] as &$item) {

                    if (
                        str_starts_with($entry->debit_account_code, $item['code_target']) &&
                        str_starts_with($entry->credit_account_code, $item['code_target_end'])
                    ) {
                        if ($item['type'] === 'debit') {
                            $item['total'] += $entry->debit;
                        } elseif ($item['type'] === 'credit') {
                            $item['total'] -= $entry->credit;
                        }
                    }
                }

                unset($item); // 🔥 VERY IMPORTANT
            }

            unset($section); // 🔥 VERY IMPORTANT
        }
        $totalMap = 0;

        foreach ($prefixMap as &$section) {
            $section['total'] = 0;

            foreach ($section['data'] as $item) {
                $section['total'] += $item['total'];
            }

            $totalMap += $section['total'];
        }
        unset($section);
        $totalMap = round($totalMap, 2);
        $totalFinal = round($totalOpening + $totalMap, 2);
        $totalMapped = [
            [
                "title" => "SALDO INICIAL DE EFECTIVO",
                "icon"  => "fa-wallet",
                "total" => $totalOpening
            ],
            [
                "title" => "SALDO AL FINAL DEL PERIODO",
                "icon"  => "fa-cash-register",
                "total" => $totalFinal
            ],
            [
                "title" => "SALDO EN B. DE COMP",
                "icon"  => "fa-scale-balanced",
                "total" => $total
            ],
            [
                "title" => "VARIACIÓN MENSUAL",
                "icon"  => "fa-arrow-trend-up", // or fa-arrow-trend-down dynamically
                "total" => $totalFinal - $total
            ],
        ];
        $data["total_map"] = $totalMapped;
        $data["data"] = $prefixMap;
        $data["total"] = $total;
        return $data;
    }

    public static function getCashFlowTotal($userId, $month, $year)
    {
        $results = TrialBalanceController::getTrialBalance($userId, $month, $year)->get();
        $total = 0;
        foreach ($results as $entry) {
            if (
                str_starts_with($entry->account_code, '100.1') ||
                str_starts_with($entry->account_code, '100.2')
            ) {
                $total += $entry->total;
            }
        }
        return $total;
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
