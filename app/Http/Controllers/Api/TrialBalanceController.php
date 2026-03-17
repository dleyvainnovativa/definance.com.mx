<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Carbon\Carbon;


class TrialBalanceController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $search = $request->get('search');
        $details = $request->boolean('details');
        $limit = (int) $request->get('limit', 10);

        // if ($month == "total") {
        //     $finalQuery = self::getTrialBalanceSummary($userId, 12, $year)->when($search, function ($q) use ($search) {
        //         $q->where('op.account_name', 'like', "%{$search}%")
        //             ->orWhere('op.account_code', 'like', "%{$search}%");
        //     });
        // } else {

        if ($month == "total") {
            $finalQuery = self::getTrialBalanceTotal($userId, 1, $year + 1)->when($search, function ($q) use ($search) {
                $q->where('op.account_name', 'like', "%{$search}%")
                    ->orWhere('op.account_code', 'like', "%{$search}%");
            });
        } else {

            $finalQuery = self::getTrialBalance($userId, $month, $year)->when($search, function ($q) use ($search) {
                $q->where('op.account_name', 'like', "%{$search}%")
                    ->orWhere('op.account_code', 'like', "%{$search}%");
            });
            if (!$details) {
                $finalQuery->having(function ($q) {
                    $q->having('debit', '!=', 0)
                        ->orHaving('credit', '!=', 0)
                        ->orHaving('opening', '!=', 0);
                });
            }
        }
        // }

        $totals = clone $finalQuery;
        $allEntries = $totals->get();
        $totalDebit = 0;
        $totalCredit = 0;
        $totalOpening = 0;
        $totalSaldo = 0;

        $baseTotals = [
            "title"   => "",
            "type"    => "calculate",
            "opening" => 0,
            "debit"   => 0,
            "credit"  => 0,
            "total"   => 0,
        ];

        $headers = [
            "asset" => array_merge($baseTotals, [
                "key"   => "asset",
                "title" => "Activos",
                'icon' => 'fa-coins',
                "type" => "calculate"
            ]),

            "liability" => array_merge($baseTotals, [
                "key"   => "liability",
                "title" => "Pasivos",
                'icon' => 'fa-file-invoice-dollar',
                "type" => "calculate"
            ]),

            "equity" => array_merge($baseTotals, [
                "key"   => "equity",
                "title" => "Capital",
                'icon' => 'fa-hand-holding-dollar',
                "type" => "calculate"
            ]),


            "income" => array_merge($baseTotals, [
                "key"   => "income",
                "title" => "Ingresos",
                'icon' => 'fa-scale-balanced',
                "type"  => "calculate",
            ]),
            "expense" => array_merge($baseTotals, [
                "key"   => "expense",
                "title" => "Egresos",
                'icon' => 'fa-scale-balanced',
                "type"  => "calculate",
            ]),

            "result" => array_merge($baseTotals, [
                "key"   => "result",
                "title" => "Resultado",
                "type"  => "total",
            ]),
            "remain" => array_merge($baseTotals, [
                "key"   => "remain",
                "title" => "Remanente o Utilidad",
                'icon' => 'fa-scale-balanced',
                "type"  => "calculate",
            ]),
        ];

        // $realOpening = 0;

        // Tu lógica de totales es perfecta y la reutilizamos aquí.
        foreach ($allEntries as $entry) {
            $totalDebit += $entry->debit;
            $totalCredit += $entry->credit;
            $totalOpening += $entry->opening;
            $totalSaldo += $entry->total;
            if (
                isset($headers[$entry->type]) &&
                $headers[$entry->type]["type"] === "calculate"
            ) {
                $headers[$entry->type]["opening"] += $entry->opening;
                $headers[$entry->type]["debit"]   += $entry->debit;
                $headers[$entry->type]["credit"]  += $entry->credit;
                $headers[$entry->type]["total"]   += $entry->total;
            }
        }


        $headers["remain"]["opening"] =
            $headers["asset"]["opening"]
            - $headers["liability"]["opening"]
            - $headers["equity"]["opening"];

        $headers["remain"]["credit"] =
            $headers["asset"]["credit"]
            - $headers["liability"]["credit"]
            - $headers["equity"]["credit"];

        $headers["remain"]["debit"] =
            $headers["asset"]["debit"]
            - $headers["liability"]["debit"]
            - $headers["equity"]["debit"];

        $headers["remain"]["total"] =
            $headers["asset"]["total"]
            - $headers["liability"]["total"]
            - $headers["equity"]["total"];

        $headers["result"]["opening"] =
            $headers["asset"]["opening"]
            - $headers["liability"]["opening"]
            - $headers["equity"]["opening"]
            - $headers["remain"]["opening"];

        $headers["result"]["credit"] =
            $headers["asset"]["credit"]
            - $headers["liability"]["credit"]
            - $headers["equity"]["credit"]
            - $headers["remain"]["credit"];

        $headers["result"]["debit"] =
            $headers["asset"]["debit"]
            - $headers["liability"]["debit"]
            - $headers["equity"]["debit"]
            - $headers["remain"]["debit"];

        $headers["result"]["total"] =
            $headers["asset"]["total"]
            - $headers["liability"]["total"]
            - $headers["equity"]["total"]
            - $headers["remain"]["total"];

        $results = $finalQuery->paginate($limit);

        $rows = $results->getCollection()->map(function ($entry) {

            return [
                'entry_type' => $entry->type,
                'entry_type_label' => $entry->type_account,
                'account_code'     => $entry->account_code,
                'nature'           => $entry->nature_label,
                'account_name'     => $entry->account_name,
                'opening'          => round($entry->opening, 2),
                'debit'            => round($entry->debit, 2),
                'credit'           => round($entry->credit, 2),
                'total'            => round($entry->total, 2),
            ];
        });

        return response()->json([
            'total' => $results->total(),
            'data'  => $rows,
            'headers'  => array_values($headers),
            'allEntries'  => $allEntries,
            'footer' => [
                'entry_type_label' => "",
                'account_code' => "",
                'nature' => "",
                'account_name' => "",
                // 'opening'  => $totalOpening,
                // 'total' => $totalSaldo,
                'opening'  => $headers["result"]["opening"],
                // 'debit'  => $headers["result"]["debit"],
                'debit'  => $totalDebit,
                'credit' => $totalCredit,
                // 'credit' => $headers["result"]["credit"],
                'total' => $headers["result"]["total"],
            ],
        ]);
    }

    public static function getTrialBalance($userId, $month, $year)
    {
        // --- Subconsulta para 'debit' CTE ---
        $debitQuery = DB::table('journal_voucher')
            ->select('user_id', 'debit_account_id as account_id', 'debit_account_code as account_code', 'debit_account_name as account_name', DB::raw('SUM(debit) as debit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'debit_account_id', 'debit_account_code', 'debit_account_name');

        // --- Subconsulta para 'credit' CTE ---
        $creditQuery = DB::table('journal_voucher')
            ->select('user_id', 'credit_account_id as account_id', 'credit_account_code as account_code', 'credit_account_name as account_name', DB::raw('SUM(credit) as credit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'credit_account_id', 'credit_account_code', 'credit_account_name');

        // --- Subconsulta para 'opening' CTE ---
        // $openingQuery = DB::table('accounts as a')
        //     ->select(
        //         'a.id as account_id',
        //         'a.nature as nature',
        //         'a.nature_label as nature_label',
        //         'a.type_account as type_account',
        //         'a.type as type',
        //         'a.code as account_code',
        //         'a.name as account_name',
        //         'a.user_id',
        //         DB::raw('COALESCE(SUM(CASE WHEN j.debit_account_id = a.id THEN j.debit WHEN j.credit_account_id = a.id THEN j.credit ELSE 0 END), 0) as amount')
        //     )
        //     ->leftJoin('journal as j', function ($join) use ($month, $year) {
        //         $join->on(function ($query) {
        //             $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
        //         })
        //             ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
        //             ->whereMonth('j.entry_date', $month)
        //             ->whereYear('j.entry_date', $year);
        //     })
        //     ->groupBy('a.id', 'a.code', 'a.name', 'a.user_id');

        $lastDay = Carbon::create($year, $month, 1)->endOfMonth();
        $firstDay = Carbon::create($year, 1, 1);

        $openingJournalQuery = DB::table('accounts as a')
            ->select(
                'a.id as account_id',
                'a.code as account_code',
                'a.name as account_name',
                'a.nature as nature',
                'a.nature_label as nature_label',
                'a.type as type',
                'a.type_account as type_account',
                'a.user_id',
                'j.entry_date',
                'j.entry_id',
                DB::raw('CASE
                  WHEN j.debit_account_id = a.id THEN j.debit
                  WHEN j.credit_account_id = a.id THEN j.credit
                  ELSE 0
                END AS amount'),
                DB::raw('ROW_NUMBER() OVER (
                  PARTITION BY a.id
                  ORDER BY j.entry_date DESC, j.entry_id DESC
                ) AS rn')

            )
            // ->leftJoin('journal as j', function ($join) use ($lastDay) {            
            ->leftJoin('journal as j', function ($join) use ($firstDay, $lastDay) {

                $join->on(function ($query) {
                    $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
                })
                    ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
                    ->whereDate('j.entry_date', '<=', $lastDay);
                // ->whereBetween('j.entry_date', [$firstDay, $lastDay]);
            });

        $openingQuery = DB::query()
            ->fromSub($openingJournalQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->select(
                'op.account_id',
                'op.account_code',
                'op.account_name',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.user_id',
                DB::raw('COALESCE(amount, 0) AS amount')
            )
            ->where('op.rn', 1)
            ->orderBy("op.account_code");

        // --- Subconsulta para 'main_table' CTE (La parte del UNION ALL) ---
        // Se construye como una variable de PHP para usarla en el JOIN final.
        $mainTableQuery = DB::table(DB::raw("({$debitQuery->toSql()}) as d"))
            ->mergeBindings($debitQuery) // ¡Importante! Pasar los bindings de la subconsulta
            ->select('d.user_id', 'd.account_id', 'd.account_code', 'd.account_name', 'd.debit', DB::raw('COALESCE(c.credit, 0) as credit'))
            ->leftJoinSub($creditQuery, 'c', function ($join) {
                $join->on('d.account_id', '=', 'c.account_id');
            })
            ->unionAll(
                DB::table(DB::raw("({$creditQuery->toSql()}) as c"))
                    ->mergeBindings($creditQuery)
                    ->select('c.user_id', 'c.account_id', 'c.account_code', 'c.account_name', DB::raw('0'), 'c.credit')
                    ->leftJoinSub($debitQuery, 'd', function ($join) {
                        $join->on('c.account_id', '=', 'd.account_id');
                    })
                    ->whereNull('d.account_id')
            );

        // --- Consulta Final Uniendo Todo ---
        // Esta es la parte que finalmente se ejecuta.
        $finalQuery = DB::query()
            ->fromSub($openingQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->leftJoinSub($mainTableQuery, 'mt', function ($join) {
                $join->on('op.account_id', '=', 'mt.account_id');
            })
            ->select(
                'op.user_id',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.account_id',
                'op.account_code',
                'op.account_name',
                DB::raw('COALESCE(op.amount, 0) as opening'),
                DB::raw('COALESCE(mt.debit, 0) as debit'),
                DB::raw('COALESCE(mt.credit, 0) as credit'),
                DB::raw("
                    CASE 
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'debit'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.debit, 0) 
                                - COALESCE(mt.credit, 0)
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'credit'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.credit, 0) 
                                - COALESCE(mt.debit, 0)
                        ELSE 0
                    END AS total
                ")
            )
            ->where('op.user_id', $userId)
            // ->orderBy("op.account_code");
            ->orderByRaw("
    CAST(SUBSTRING_INDEX(op.account_code, '.', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(op.account_code, '.', 2), '.', -1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(op.account_code, '.', -1) AS UNSIGNED)
");

        // $results = $finalQuery->get();

        return $finalQuery;
    }
    public static function getTrialBalanceFree($userId, $month, $year)
    {
        // --- Subconsulta para 'debit' CTE ---
        $debitQuery = DB::table('journal_voucher')
            ->select('user_id', 'debit_account_id as account_id', 'debit_account_code as account_code', 'debit_account_name as account_name', DB::raw('SUM(debit) as debit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'debit_account_id', 'debit_account_code', 'debit_account_name');

        // --- Subconsulta para 'credit' CTE ---
        $creditQuery = DB::table('journal_voucher')
            ->select('user_id', 'credit_account_id as account_id', 'credit_account_code as account_code', 'credit_account_name as account_name', DB::raw('SUM(credit) as credit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'credit_account_id', 'credit_account_code', 'credit_account_name');

        $lastDay = Carbon::create($year, $month, 1)->endOfMonth();
        $firstDay = Carbon::create($year, 1, 1);

        $openingJournalQuery = DB::table('accounts as a')
            ->select(
                'a.id as account_id',
                'a.code as account_code',
                'a.name as account_name',
                'a.nature as nature',
                'a.nature_label as nature_label',
                'a.type as type',
                'a.type_account as type_account',
                'a.user_id',
                'j.entry_date',
                'j.entry_id',
                DB::raw('CASE
                  WHEN j.debit_account_id = a.id THEN j.debit
                  WHEN j.credit_account_id = a.id THEN j.credit
                  ELSE 0
                END AS amount'),
                DB::raw('ROW_NUMBER() OVER (
                  PARTITION BY a.id
                  ORDER BY j.entry_date DESC, j.entry_id DESC
                ) AS rn')

            )
            // ->leftJoin('journal as j', function ($join) use ($lastDay) {            
            ->leftJoin('journal as j', function ($join) use ($firstDay, $lastDay) {

                $join->on(function ($query) {
                    $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
                })
                    ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
                    ->whereDate('j.entry_date', '<=', $lastDay);
                // ->whereBetween('j.entry_date', [$firstDay, $lastDay]);
            });

        $openingQuery = DB::query()
            ->fromSub($openingJournalQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->select(
                'op.account_id',
                'op.account_code',
                'op.account_name',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.user_id',
                DB::raw('COALESCE(amount, 0) AS amount')
            )
            ->where('op.rn', 1)
            ->orderBy("op.account_code");

        // --- Subconsulta para 'main_table' CTE (La parte del UNION ALL) ---
        // Se construye como una variable de PHP para usarla en el JOIN final.
        $mainTableQuery = DB::table(DB::raw("({$debitQuery->toSql()}) as d"))
            ->mergeBindings($debitQuery) // ¡Importante! Pasar los bindings de la subconsulta
            ->select('d.user_id', 'd.account_id', 'd.account_code', 'd.account_name', 'd.debit', DB::raw('COALESCE(c.credit, 0) as credit'))
            ->leftJoinSub($creditQuery, 'c', function ($join) {
                $join->on('d.account_id', '=', 'c.account_id');
            })
            ->unionAll(
                DB::table(DB::raw("({$creditQuery->toSql()}) as c"))
                    ->mergeBindings($creditQuery)
                    ->select('c.user_id', 'c.account_id', 'c.account_code', 'c.account_name', DB::raw('0'), 'c.credit')
                    ->leftJoinSub($debitQuery, 'd', function ($join) {
                        $join->on('c.account_id', '=', 'd.account_id');
                    })
                    ->whereNull('d.account_id')
            );

        // --- Consulta Final Uniendo Todo ---
        // Esta es la parte que finalmente se ejecuta.
        $finalQuery = DB::query()
            ->fromSub($openingQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->leftJoinSub($mainTableQuery, 'mt', function ($join) {
                $join->on('op.account_id', '=', 'mt.account_id');
            })
            ->select(
                'op.user_id',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.account_id',
                'op.account_code',
                'op.account_name',
                DB::raw('COALESCE(op.amount, 0) as opening'),
                DB::raw('COALESCE(mt.debit, 0) as debit'),
                DB::raw('COALESCE(mt.credit, 0) as credit'),
                DB::raw("
                    CASE 
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'debit'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.debit, 0) 
                                - COALESCE(mt.credit, 0)
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'credit'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.credit, 0) 
                                - COALESCE(mt.debit, 0)
                        ELSE 0
                    END AS total
                ")
            )
            ->where('op.user_id', $userId)
            // ->orderBy("op.account_code");
            ->orderByRaw("
    CAST(SUBSTRING_INDEX(op.account_code, '.', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(op.account_code, '.', 2), '.', -1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(op.account_code, '.', -1) AS UNSIGNED)
");
        return $finalQuery;
    }
    public static function getTrialBalanceSummary($userId, $month, $year)
    {
        // --- Subconsulta para 'debit' CTE ---
        $debitQuery = DB::table('journal_voucher')
            ->select('user_id', 'debit_account_id as account_id', 'debit_account_code as account_code', 'debit_account_name as account_name', DB::raw('SUM(debit) as debit'))
            ->whereRaw('MONTH(entry_date) <= ?', [$month])
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'debit_account_id', 'debit_account_code', 'debit_account_name');

        // --- Subconsulta para 'credit' CTE ---
        $creditQuery = DB::table('journal_voucher')
            ->select('user_id', 'credit_account_id as account_id', 'credit_account_code as account_code', 'credit_account_name as account_name', DB::raw('SUM(credit) as credit'))
            ->whereRaw('MONTH(entry_date) <= ?', [$month])
            ->whereYear('entry_date', $year)
            ->groupBy('user_id', 'credit_account_id', 'credit_account_code', 'credit_account_name');

        $lastDay = Carbon::create($year, 1, 1)->endOfMonth();
        $firstDay = Carbon::create($year, 1, 1);

        $openingJournalQuery = DB::table('accounts as a')
            ->select(
                'a.id as account_id',
                'a.code as account_code',
                'a.name as account_name',
                'a.nature as nature',
                'a.nature_label as nature_label',
                'a.type as type',
                'a.type_account as type_account',
                'a.user_id',
                'j.entry_date',
                'j.entry_id',
                DB::raw('CASE
                  WHEN j.debit_account_id = a.id THEN j.debit
                  WHEN j.credit_account_id = a.id THEN j.credit
                  ELSE 0
                END AS amount'),
                DB::raw('ROW_NUMBER() OVER (
                  PARTITION BY a.id
                  ORDER BY j.entry_date DESC, j.entry_id DESC
                ) AS rn')

            )
            ->leftJoin('journal as j', function ($join) use ($firstDay, $lastDay) {
                $join->on(function ($query) {
                    $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
                })
                    ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
                    // ->whereDate('j.entry_date', '<=', $lastDay);
                    ->whereBetween('j.entry_date', [$firstDay, $lastDay]);
            });

        $openingQuery = DB::query()
            ->fromSub($openingJournalQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->select(
                'op.account_id',
                'op.account_code',
                'op.account_name',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.user_id',
                DB::raw('COALESCE(SUM(amount), 0) AS amount')
            )
            ->groupBy("account_id", "account_name", "account_code", "nature", "nature_label", "type", "type_account", "user_id")

            ->orderBy("op.account_code");

        // --- Subconsulta para 'main_table' CTE (La parte del UNION ALL) ---
        // Se construye como una variable de PHP para usarla en el JOIN final.
        $mainTableQuery = DB::table(DB::raw("({$debitQuery->toSql()}) as d"))
            ->mergeBindings($debitQuery) // ¡Importante! Pasar los bindings de la subconsulta
            ->select('d.user_id', 'd.account_id', 'd.account_code', 'd.account_name', 'd.debit', DB::raw('COALESCE(c.credit, 0) as credit'))
            ->leftJoinSub($creditQuery, 'c', function ($join) {
                $join->on('d.account_id', '=', 'c.account_id');
            })
            ->unionAll(
                DB::table(DB::raw("({$creditQuery->toSql()}) as c"))
                    ->mergeBindings($creditQuery)
                    ->select('c.user_id', 'c.account_id', 'c.account_code', 'c.account_name', DB::raw('0'), 'c.credit')
                    ->leftJoinSub($debitQuery, 'd', function ($join) {
                        $join->on('c.account_id', '=', 'd.account_id');
                    })
                    ->whereNull('d.account_id')
            );

        // --- Consulta Final Uniendo Todo ---
        // Esta es la parte que finalmente se ejecuta.
        $finalQuery = DB::query()
            ->fromSub($openingQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->leftJoinSub($mainTableQuery, 'mt', function ($join) {
                $join->on('op.account_id', '=', 'mt.account_id');
            })
            ->select(
                'op.user_id',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.account_id',
                'op.account_code',
                'op.account_name',
                DB::raw('COALESCE(op.amount, 0) as opening'),
                DB::raw('COALESCE(mt.debit, 0) as debit'),
                DB::raw('COALESCE(mt.credit, 0) as credit'),
                DB::raw("
                    CASE 
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'debit'
                            THEN (COALESCE(op.amount, 0) 
                                + COALESCE(mt.debit, 0) 
                                - COALESCE(mt.credit, 0)) / $month
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'credit'
                            THEN (COALESCE(op.amount, 0) 
                                + COALESCE(mt.credit, 0) 
                                - COALESCE(mt.debit, 0)) / $month
                        ELSE 0
                    END AS total
                ")
            )
            ->where('op.user_id', $userId)
            // ->orderBy("op.account_code");
            ->orderByRaw("
    CAST(SUBSTRING_INDEX(op.account_code, '.', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(op.account_code, '.', 2), '.', -1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(op.account_code, '.', -1) AS UNSIGNED)");

        // $results = $finalQuery->get();

        return $finalQuery;
    }
    public static function getTrialBalanceTotal($userId, $month, $year)
    { // --- Subconsulta para 'debit' CTE ---
        $debitQuery = DB::table('journal_voucher')
            ->select('user_id', 'debit_account_id as account_id', 'debit_account_code as account_code', 'debit_account_name as account_name', DB::raw('SUM(debit) as debit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->whereRaw("1 = 0")
            ->groupBy('user_id', 'debit_account_id', 'debit_account_code', 'debit_account_name');

        // --- Subconsulta para 'credit' CTE ---
        $creditQuery = DB::table('journal_voucher')
            ->select('user_id', 'credit_account_id as account_id', 'credit_account_code as account_code', 'credit_account_name as account_name', DB::raw('SUM(credit) as credit'))
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->whereRaw("1 = 0")
            ->groupBy('user_id', 'credit_account_id', 'credit_account_code', 'credit_account_name');

        // --- Subconsulta para 'opening' CTE ---
        // $openingQuery = DB::table('accounts as a')
        //     ->select(
        //         'a.id as account_id',
        //         'a.nature as nature',
        //         'a.nature_label as nature_label',
        //         'a.type_account as type_account',
        //         'a.type as type',
        //         'a.code as account_code',
        //         'a.name as account_name',
        //         'a.user_id',
        //         DB::raw('COALESCE(SUM(CASE WHEN j.debit_account_id = a.id THEN j.debit WHEN j.credit_account_id = a.id THEN j.credit ELSE 0 END), 0) as amount')
        //     )
        //     ->leftJoin('journal as j', function ($join) use ($month, $year) {
        //         $join->on(function ($query) {
        //             $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
        //         })
        //             ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
        //             ->whereMonth('j.entry_date', $month)
        //             ->whereYear('j.entry_date', $year);
        //     })
        //     ->groupBy('a.id', 'a.code', 'a.name', 'a.user_id');

        $lastDay = Carbon::create($year, $month, 1)->endOfMonth();
        $firstDay = Carbon::create($year, 1, 1);

        $openingJournalQuery = DB::table('accounts as a')
            ->select(
                'a.id as account_id',
                'a.code as account_code',
                'a.name as account_name',
                'a.nature as nature',
                'a.nature_label as nature_label',
                'a.type as type',
                'a.type_account as type_account',
                'a.user_id',
                'j.entry_date',
                'j.entry_id',
                DB::raw('CASE
                  WHEN j.debit_account_id = a.id THEN j.debit
                  WHEN j.credit_account_id = a.id THEN j.credit
                  ELSE 0
                END AS amount'),
                DB::raw('ROW_NUMBER() OVER (
                  PARTITION BY a.id
                  ORDER BY j.entry_date DESC, j.entry_id DESC
                ) AS rn')

            )
            // ->leftJoin('journal as j', function ($join) use ($lastDay) {            
            ->leftJoin('journal as j', function ($join) use ($firstDay, $lastDay) {

                $join->on(function ($query) {
                    $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
                })
                    ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
                    ->whereDate('j.entry_date', '<=', $lastDay);
                // ->whereBetween('j.entry_date', [$firstDay, $lastDay]);
            });

        $openingQuery = DB::query()
            ->fromSub($openingJournalQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->select(
                'op.account_id',
                'op.account_code',
                'op.account_name',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.user_id',
                DB::raw('COALESCE(amount, 0) AS amount')
            )
            ->where('op.rn', 1)
            ->orderBy("op.account_code");

        // --- Subconsulta para 'main_table' CTE (La parte del UNION ALL) ---
        // Se construye como una variable de PHP para usarla en el JOIN final.
        $mainTableQuery = DB::table(DB::raw("({$debitQuery->toSql()}) as d"))
            ->mergeBindings($debitQuery) // ¡Importante! Pasar los bindings de la subconsulta
            ->select('d.user_id', 'd.account_id', 'd.account_code', 'd.account_name', 'd.debit', DB::raw('COALESCE(c.credit, 0) as credit'))
            ->leftJoinSub($creditQuery, 'c', function ($join) {
                $join->on('d.account_id', '=', 'c.account_id');
            })
            ->unionAll(
                DB::table(DB::raw("({$creditQuery->toSql()}) as c"))
                    ->mergeBindings($creditQuery)
                    ->select('c.user_id', 'c.account_id', 'c.account_code', 'c.account_name', DB::raw('0'), 'c.credit')
                    ->leftJoinSub($debitQuery, 'd', function ($join) {
                        $join->on('c.account_id', '=', 'd.account_id');
                    })
                    ->whereNull('d.account_id')
            );

        // --- Consulta Final Uniendo Todo ---
        // Esta es la parte que finalmente se ejecuta.
        $finalQuery = DB::query()
            ->fromSub($openingQuery, 'op') // Usa la subconsulta 'opening' como tabla base 'op'
            ->leftJoinSub($mainTableQuery, 'mt', function ($join) {
                $join->on('op.account_id', '=', 'mt.account_id');
            })
            ->select(
                'op.user_id',
                'op.nature',
                'op.nature_label',
                'op.type',
                'op.type_account',
                'op.account_id',
                'op.account_code',
                'op.account_name',
                DB::raw('COALESCE(op.amount, 0) as opening'),
                DB::raw("CASE 
                    WHEN op.nature COLLATE utf8mb4_unicode_ci = 'credit'
                        AND op.account_code REGEXP '^(300|400|500|600|700|800|900)\\.'
                    THEN COALESCE(op.amount, 0)
                    ELSE 0
                END as debit"),
                DB::raw("CASE 
                    WHEN op.nature COLLATE utf8mb4_unicode_ci = 'debit'
                        AND op.account_code REGEXP '^(400|500|600|700|800|900)\\.'
                    THEN COALESCE(op.amount, 0)
                    ELSE 0
                END as credit"),
                // DB::raw('COALESCE(mt.credit, 0) as credit'),
                DB::raw("
                    CASE 
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'debit' AND op.account_code REGEXP '^(400|500|600|700|800|900)\\.'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.debit, 0) 
                                - COALESCE(op.amount, 0)
                        WHEN op.nature COLLATE utf8mb4_unicode_ci = 'credit' AND op.account_code REGEXP '^(400|500|600|700|800|900)\\.'
                            THEN COALESCE(op.amount, 0) 
                                + COALESCE(mt.credit, 0) 
                                - COALESCE(op.amount, 0)
                        ELSE op.amount
                    END AS total
                ")
            )
            ->where('op.user_id', $userId)
            // ->orderBy("op.account_code");
            ->orderByRaw("
    CAST(SUBSTRING_INDEX(op.account_code, '.', 1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(op.account_code, '.', 2), '.', -1) AS UNSIGNED),
    CAST(SUBSTRING_INDEX(op.account_code, '.', -1) AS UNSIGNED)
");

        // $results = $finalQuery->get();

        return $finalQuery;
    }
}
