<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalEntryController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month');
        $year = $request->get('year');
        $search = $request->get('search');
        $filters = json_decode($request->get('filters'), true) ?? [];
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        $query = DB::table('journal')
            ->where('user_id', $userId);

        if ($month && $year) {
            $query->whereMonth('entry_date', $month)->whereYear('entry_date', $year);
        }

        if ($search) {
            // La búsqueda ahora es más simple y directa sobre las columnas de la vista.
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('debit_account_name', 'like', "%{$search}%")
                    ->orWhere('debit_account_code', 'like', "%{$search}%")
                    ->orWhere('credit_account_name', 'like', "%{$search}%")
                    ->orWhere('credit_account_code', 'like', "%{$search}%");
            });
        }

        $debitAccounts = $filters['debit_accounts'] ?? null;
        $creditAccounts = $filters['credit_accounts'] ?? null;

        // La lógica OR para los filtros de cuenta ahora es mucho más legible.
        if ($debitAccounts || $creditAccounts) {
            $query->where(function ($q) use ($debitAccounts, $creditAccounts) {
                if ($debitAccounts) {
                    $q->whereIn('debit_account_name', $debitAccounts);
                }
                if ($creditAccounts) {
                    // Si ya había un filtro de débito, une este con OR.
                    $q->orWhereIn('credit_account_name', $creditAccounts);
                }
            });
        }

        // --- PASO 3: Calcular Totales (misma lógica, consulta más simple) ---
        $totalsQuery = clone $query;
        // La consulta a la DB es más rápida porque no hay JOINs de Eloquent.
        $allEntriesForTotals = $totalsQuery->get();

        $totalDebit  = 0;
        $totalCredit = 0;

        // Tu lógica de totales es perfecta y la reutilizamos aquí.
        foreach ($allEntriesForTotals as $entry) {
            $totalDebit += $entry->debit;
            $totalCredit += $entry->credit;
        }

        // --- PASO 4: Paginar y Transformar Filas (misma lógica, datos más simples) ---
        $entries = $query->orderBy('entry_date')->orderBy('entry_id')->paginate($limit, ['*'], 'page', $page);
        // dd($entries);

        $rows = $entries->getCollection()->map(function ($entry) use ($debitAccounts, $creditAccounts) {

            return [
                'id'                  => $entry->entry_id,
                'entry_date'          => Carbon::parse($entry->entry_date)->format('d/m/Y'),
                'entry_type_label'    => $entry->entry_type,
                'description'         => $entry->description,
                'debit_account_name'  => $entry->debit_account_name,
                'debit_account_code'  => $entry->debit_account_code,
                'credit_account_name' => $entry->credit_account_name,
                'credit_account_code' => $entry->credit_account_code,
                'debit'               => $entry->debit,
                'credit'              => $entry->credit,
            ];
        });

        // --- PASO 5: Devolver el JSON (sin cambios) ---
        return response()->json([
            'total'  => $entries->total(),
            'data'   => $rows,
            'filters' => $filters,
            'footer' => [
                'description' => 'TOTAL',
                'debit'  => $totalDebit,
                'credit' => $totalCredit,
                'credit_account_code' => "",
                'entry_date' => "",
                'entry_type_label' => "",
                'debit_account_name' => "",
                'debit_account_code' => "",
                'credit_account_name' => "",
            ],
        ]);
    }

    public function filters(Request $request)
    {
        $userId = $request->user()->id;

        $month = $request->get('month');
        $year  = $request->get('year');

        $query = JournalEntry::where('user_id', $userId)
            ->with(['lines.chartOfAccount']);

        // Optional month/year sync with table
        if ($month && $year) {
            $query->whereMonth('entry_date', $month)
                ->whereYear('entry_date', $year);
        }

        $entries = $query->get();

        $debitAccounts  = [];
        $creditAccounts = [];

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                if ($line->debit > 0 && $line->chartOfAccount) {
                    $debitAccounts[] = $line->chartOfAccount->name;
                }

                if ($line->credit > 0 && $line->chartOfAccount) {
                    $creditAccounts[] = $line->chartOfAccount->name;
                }
            }
        }

        return response()->json([
            'debit_accounts'  => collect($debitAccounts)->unique()->sort()->values(),
            'credit_accounts' => collect($creditAccounts)->unique()->sort()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => ['required', 'date'],
            'entry_type' => ['required', 'in:income,expense,opening_balance,opening_balance_credit,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'debit_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'credit_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'description' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            // 'applies_se' => ['nullable', 'boolean'],
            // 'applies_fe' => ['nullable', 'string'],
        ]);
        $userId = $request->user()->id;
        $entry_date = $request->entry_date;
        $entry_type = $request->entry_type;
        $description = $request->description;
        $reference = $request->reference;
        $amount = $request->amount;
        $debit_account_id = $request->debit_account_id;
        $credit_account_id = $request->credit_account_id;


        $month = self::getMonth($entry_date);
        $year = self::getYear($entry_date);

        // self::create($userId, $entry_date, $entry_type, $description, $reference, $amount, $debit_account_id, $credit_account_id);
        $results = self::setOpening($request->debit_account_id, $request->credit_account_id, $month, $year, $userId);
        return response()->json([$results, $month, $year], 201);

        // return redirect()
        //     ->back()
        //     ->with('success', 'Movimiento registrado correctamente');
    }

    public function getMonth(string $date): int
    {
        return Carbon::parse($date)->month;
    }
    public function getYear(string $date): int
    {
        return Carbon::parse($date)->year;
    }

    private static function create($userId, $entry_date, $entry_type, $description, $reference, $amount, $debit_account_id, $credit_account_id)
    {

        DB::transaction(function () use (
            $userId,
            $entry_date,
            $entry_type,
            $description,
            $reference,
            $amount,
            $debit_account_id,
            $credit_account_id
        ) {
            // 1️⃣ Create journal entry
            $entry = JournalEntry::create([
                'user_id' => $userId,
                'entry_date' => $entry_date,
                'entry_type' => $entry_type,
                'description' => $description,
                'reference' => $reference,
            ]);

            if ($entry_type == "opening_balance" || $entry_type == "opening_balance_credit") {
                if ($entry_type == "opening_balance") {
                    // 2️⃣ Create debit line
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $debit_account_id,
                        'debit' => $amount,
                        'credit' => null,
                    ]);
                } else {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $debit_account_id,
                        'debit' => null,
                        'credit' => $amount,
                    ]);
                }
            } else {

                // 2️⃣ Create debit line
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $debit_account_id,
                    'debit' => $amount,
                    'credit' => null,
                ]);

                // 3️⃣ Create credit line
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $credit_account_id,
                    'debit' => null,
                    'credit' => $amount,
                ]);
            }
        });
    }

    private static function update(
        $entry_id,
        $userId,
        $entry_date,
        $entry_type,
        $description,
        $reference,
        $amount,
        $debit_account_id,
        $credit_account_id
    ) {
        DB::transaction(function () use (
            $entry_id,
            $userId,
            $entry_date,
            $entry_type,
            $description,
            $reference,
            $amount,
            $debit_account_id,
            $credit_account_id
        ) {

            // 1️⃣ Fetch entry (and ensure ownership)
            $entry = JournalEntry::where('id', $entry_id)
                ->where('user_id', $userId)
                ->firstOrFail();

            // 2️⃣ Update journal entry header
            $entry->update([
                'entry_date' => $entry_date,
                'entry_type' => $entry_type,
                'description' => $description,
                'reference' => $reference,
            ]);

            // 3️⃣ Remove existing lines (safe & simple)
            JournalEntryLine::where('journal_entry_id', $entry->id)->delete();

            // 4️⃣ Re-create lines (same logic as create)
            if ($entry_type === 'opening_balance' || $entry_type === 'opening_balance_credit') {

                if ($entry_type === 'opening_balance') {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $debit_account_id,
                        'debit' => $amount,
                        'credit' => null,
                    ]);
                } else {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $debit_account_id,
                        'debit' => null,
                        'credit' => $amount,
                    ]);
                }
            } else {

                // Debit line
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $debit_account_id,
                    'debit' => $amount,
                    'credit' => null,
                ]);

                // Credit line
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $credit_account_id,
                    'debit' => null,
                    'credit' => $amount,
                ]);
            }
        });
    }

    public function import(Request $request)
    {
        $request->validate([
            '*.entry_date' => ['required', 'date'],
            '*.entry_type' => ['required', 'in:opening_balance,opening_balance_credit,income,expense,transfer,adjustment,asset_acquisition'],
            '*.amount' => ['required', 'numeric', 'min:0'],
            '*.debit_account_id' => ['required', 'exists:chart_of_accounts,id'],
            '*.credit_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            '*.description' => ['required', 'string'],
            '*.reference' => ['nullable', 'string'],
            '*.applies_se' => ['nullable', 'boolean'],
            '*.applies_fe' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request) {
            $user = $request->user();

            foreach ($request->all() as $row) {
                $month = self::getMonth($row["entry_date"]);
                $year = self::getYear($row["entry_date"]);
                $userId = $user->id;
                Log::debug('ImportEntry', [$userId, $row['entry_date'], $row['entry_type'], $row['description'], $row['reference'], $row['amount'], $row['debit_account_id'], $row['credit_account_id']]);
                self::create($userId, $row['entry_date'], $row['entry_type'], $row['description'], $row['reference'], $row['amount'], $row['debit_account_id'], $row['credit_account_id']);
                self::setOpening($row["debit_account_id"], $row["credit_account_id"], $month, $year, $userId);
                self::setOpening($row["credit_account_id"], $row["debit_account_id"], $month, $year, $userId);
                // self::setOpeningDeficit($month, $year, $userId);
            }
        });

        return response()->json([
            'message' => 'Journal entries imported successfully'
        ], 201);
    }

    public static function setOpening($account_id, $credit_account_id, $month, $year, $userId)
    {
        $trial = TrialBalanceController::getTrialBalance($userId, $month, $year);
        $results = $trial->where('op.account_id', $account_id)->get()->first();
        $total = $results->total;
        $nature = $results->nature;
        $validation = self::validateOpening($userId, $account_id, $credit_account_id, $month, $year, $nature);
        // dd($validation, $results, $total);
        $opening_type = $nature == "debit" ? "opening_balance" : "opening_balance_credit";
        $nextDate = $validation["date"];
        if ($validation["new"] == true) {
            Log::debug('NewOpening', [$userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $credit_account_id]);
            // dd("new", $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 2", $total, $account_id, $credit_account_id);
            self::create($userId, "$nextDate", $opening_type, "Saldo Inicial Nuevo", "Saldo Inicial", $total, $account_id, null);
        } else {
            $entry_id = $validation["entry_id"];
            Log::debug('UpdateOpening', [$entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $credit_account_id]);
            // dd("update", $entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $credit_account_id);
            self::update($entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial Update", "Saldo Inicial", $total, $account_id, null);
        }
        return [$results, $validation];
    }
    // public static function setOpeningDeficit($month, $year, $userId)
    // {
    //     $income = IncomeStatementController::getIncomeStatement($userId, $month, $year);
    //     $total = $income["total"];
    //     $account = DB::table("accounts")->where("user_id", $userId)->where("account_code", "300.2")->get()->first();
    //     $nature = $account->nature;
    //     $account_id = $account->account_id;
    //     $validation = self::validateOpening($userId, $account_id, $account_id, $month, $year, $nature);
    //     dd($validation, $account, $total);
    //     $opening_type = $nature == "debit" ? "opening_balance" : "opening_balance_credit";
    //     $nextDate = $validation["date"];
    //     if ($validation["new"] == true) {
    //         Log::debug('NewOpeningDeficit', [$userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $account_id]);
    //         self::create($userId, "$nextDate", $opening_type, "Saldo Inicial Nuevo", "Saldo Inicial", $total, $account_id, null);
    //     } else {
    //         $entry_id = $validation["entry_id"];
    //         Log::debug('UpdateOpeningDeficit', [$entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $account_id]);
    //         self::update($entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial Update", "Saldo Inicial", $total, $account_id, null);
    //     }
    //     return [$account, $validation];
    // }
    private static function validateOpening($userId, $account_id, $credit_account_id, $month, $year, $nature)
    {
        $nextMonth = self::getNextMonth($month, $year);
        $nextYear = self::getNextYear($month, $year);
        $nextEntryDate = "";
        $query = DB::table('journal')
            ->where('user_id', $userId)
            ->where(function ($q) use ($account_id, $nature) {
                if ($nature == "debit") {
                    $q->where('debit_account_id', $account_id);
                } elseif ($nature == "credit") {
                    $q->where('credit_account_id', $account_id);
                }
                // ->orWhere('credit_account_id', $account_id);
            })
            ->whereMonth('entry_date', $nextMonth)
            ->whereYear('entry_date', $nextYear)
            ->whereIn('entry_type', [
                'opening_balance',
                'opening_balance_credit',
            ]);
        $journal = $query->get();
        $queryRaw = "select * from `journal` where `user_id` = $userId and (`debit_account_id` = $account_id or `credit_account_id` = $account_id) and month(`entry_date`) = $nextMonth and year(`entry_date`) = $nextYear and `entry_type` in ('opening_balance', 'opening_balance_credit')";
        // Log::debug('validateOpening', [$userId, $account_id, $credit_account_id, $month, $year, $nextMonth, $nextYear, $journal->isEmpty(), $queryRaw]);

        if ($journal->isEmpty()) {
            $validation["new"] = true;
            $validation["entry_id"] = null;
            $validation["date"] = sprintf('%04d-%02d-01', $nextYear, $nextMonth);
            return $validation;
        } else {
            $validation["new"] = false;
            $validation["entry_id"] = $journal[0]->entry_id;
            $validation["date"] = sprintf('%04d-%02d-01', $nextYear, $nextMonth);
            return $validation;
        }
    }

    private static function getNextMonth(int $month, int $year): int
    {
        return $month === 12 ? 1 : $month + 1;
    }

    private static function getNextYear(int $month, int $year): int
    {
        return $month === 12 ? $year + 1 : $year;
    }

    public function voucher(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month');
        $year = $request->get('year');
        $search = $request->get('search');
        $filters = json_decode($request->get('filters'), true) ?? [];
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        $query = DB::table('journal_voucher')
            ->where('user_id', $userId);

        if ($month && $year) {
            $query->whereMonth('entry_date', $month)->whereYear('entry_date', $year);
        }

        if ($search) {
            // La búsqueda ahora es más simple y directa sobre las columnas de la vista.
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('debit_account_name', 'like', "%{$search}%")
                    ->orWhere('debit_account_code', 'like', "%{$search}%")
                    ->orWhere('credit_account_name', 'like', "%{$search}%")
                    ->orWhere('credit_account_code', 'like', "%{$search}%");
            });
        }

        $debitAccounts = $filters['debit_accounts'] ?? null;
        $creditAccounts = $filters['credit_accounts'] ?? null;

        // La lógica OR para los filtros de cuenta ahora es mucho más legible.
        if ($debitAccounts || $creditAccounts) {
            $query->where(function ($q) use ($debitAccounts, $creditAccounts) {
                if ($debitAccounts) {
                    $q->whereIn('debit_account_name', $debitAccounts);
                }
                if ($creditAccounts) {
                    // Si ya había un filtro de débito, une este con OR.
                    $q->orWhereIn('credit_account_name', $creditAccounts);
                }
            });
        }

        // --- PASO 3: Calcular Totales (misma lógica, consulta más simple) ---
        $totalsQuery = clone $query;
        // La consulta a la DB es más rápida porque no hay JOINs de Eloquent.
        $allEntriesForTotals = $totalsQuery->get();

        $totalDebit  = 0;
        $totalCredit = 0;

        // Tu lógica de totales es perfecta y la reutilizamos aquí.
        foreach ($allEntriesForTotals as $entry) {
            $totalDebit += $entry->debit;
            $totalCredit += $entry->credit;
        }

        // --- PASO 4: Paginar y Transformar Filas (misma lógica, datos más simples) ---
        $entries = $query->orderBy('entry_date')->orderBy('entry_id')->paginate($limit, ['*'], 'page', $page);
        // dd($entries);

        $rows = $entries->getCollection()->map(function ($entry) use ($debitAccounts, $creditAccounts) {

            return [
                'id'                  => $entry->entry_id,
                'entry_date'          => Carbon::parse($entry->entry_date)->format('d/m/Y'),
                'entry_type_label'    => $entry->entry_type,
                'description'         => $entry->description,
                'debit_account_name'  => $entry->debit_account_name,
                'debit_account_code'  => $entry->debit_account_code,
                'credit_account_name' => $entry->credit_account_name,
                'credit_account_code' => $entry->credit_account_code,
                'debit'               => $entry->debit,
                'credit'              => $entry->credit,
            ];
        });

        // --- PASO 5: Devolver el JSON (sin cambios) ---
        return response()->json([
            'total'  => $entries->total(),
            'data'   => $rows,
            'filters' => $filters,
            'footer' => [
                'description' => 'TOTAL',
                'debit'  => $totalDebit,
                'credit' => $totalCredit,
                'credit_account_code' => "",
                'entry_date' => "",
                'entry_type_label' => "",
                'debit_account_name' => "",
                'debit_account_code' => "",
                'credit_account_name' => "",
            ],
        ]);
    }
}
