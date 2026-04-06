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
        if ($month == "total") {
            $results = self::getJournalClose($userId, $year, $search, $filters, $limit, $page);
        } else {
            $results = self::getJournal($userId, $month, $year, $search, $filters, $limit, $page);
        }
        return response()->json([
            'total'  => $results["total"],
            'data'   => $results["data"],
            'filters' => $results["filters"],
            'footer' => [
                'description' => 'TOTAL',
                'debit'  => $results["debit"],
                'credit' => $results["credit"],
                'credit_account_code' => "",
                'checkbox' => "",
                'entry_date' => "",
                'entry_type_label' => "",
                'debit_account_name' => "",
                'debit_account_code' => "",
                'credit_account_name' => "",
            ],
        ]);
    }

    public static function getJournal($userId, $month, $year, $search = null, $filters = [], $limit = 10, $page = 1, $typeNotIn = [], $order = "asc")
    {
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
        $typesFilter = $filters['types'] ?? null;
        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;

        // Date filter
        if ($start_date && !$end_date) {
            $query->whereDate('entry_date', $start_date);
        } elseif (!$start_date && $end_date) {
            $query->whereDate('entry_date', '<=', $end_date);
        } elseif ($start_date && $end_date) {
            $query->whereBetween('entry_date', [$start_date, $end_date]);
        }

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
        if ($typesFilter) {
            $query->where(function ($q) use ($typesFilter) {
                if ($typesFilter) {
                    $q->whereIn('entry_type', $typesFilter);
                }
            });
        }

        if (!empty($typeNotIn)) {
            $query->where(function ($q) use ($typeNotIn) {
                $q->whereNotIn('entry_type', $typeNotIn);
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
        $entries = $query->orderBy('entry_date', $order)->orderBy('entry_id', $order)->paginate($limit, ['*'], 'page', $page);
        // dd($entries);

        $rows = $entries->getCollection()->map(function ($entry) use ($debitAccounts, $creditAccounts) {

            return [
                'id'                  => $entry->entry_id,
                'entry_date'          => Carbon::parse($entry->entry_date)->format('d/m/Y'),
                'entry_type'          => $entry->entry_type,
                'entry_type_label'    => self::translateJournalType($entry->entry_type),
                'description'         => $entry->description,
                'reference'         => $entry->reference,
                'debit_account_id'  => $entry->debit_account_id,
                'debit_account_name'  => $entry->debit_account_name,
                'debit_account_code'  => $entry->debit_account_code,
                'credit_account_id' => $entry->credit_account_id,
                'credit_account_name' => $entry->credit_account_name,
                'credit_account_code' => $entry->credit_account_code,
                'debit'               => $entry->debit,
                'credit'              => $entry->credit,
            ];
        });

        $returned["total"] = $entries->total();
        $returned["data"] = $rows;
        $returned["filters"] = $filters;
        $returned["debit"] = $totalDebit;
        $returned["credit"] = $totalCredit;
        return $returned;
    }

    public static function getJournalClose($userId, $year, $search = null, $filters = [], $limit = 10, $page = 1)
    {
        $rows = TrialBalanceController::getTrialBalanceSummary($userId, 12, $year);
        $prefixes = ['400.', '500.', '600.', '700.', '800.', '900.'];

        $data = $rows->where(function ($q) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                $q->orWhere('op.account_code', 'like', $prefix . '%');
            }
        });

        $entries = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($data->get() as $key => $entry) {
            $totalDebit += $entry->credit;
            $totalCredit +=  $entry->debit;
        }
        $results = $data->paginate($limit);
        $entries = $results->getCollection()->map(function ($entry) use ($year) {
            if ($entry->nature == "debit") {
                $type = "Egreso";
            } else {
                $type = "Ingreso";
            }
            return [
                'id'                   => null,
                'entry_date'           => "01/12/$year",
                'entry_type_label'     => $type,
                'description'          => "POLIZA CIERRE",
                'debit_account_id'  => $entry->debit_account_id,
                'debit_account_name'   => $entry->account_name,
                'debit_account_code'   => $entry->account_code,
                'credit_account_id' => $entry->credit_account_id,
                'credit_account_name'  => "REMANENTE O DEFICIT EJERCICIO $year",
                'credit_account_code'  => "300.1",
                'debit'                => round($entry->credit, 2),
                'credit'               => round($entry->debit, 2),
            ];
        });
        // dd($entries->get());
        $returned["total"] = $results->total();
        $returned["data"] = $entries;
        $returned["filters"] = [];
        $returned["debit"] = $totalDebit;
        $returned["credit"] = $totalCredit;
        return $returned;
    }

    public static function getTypeName($type)
    {
        $name = "";
        switch ($type) {
            case 'value':
                # code...
                break;

            default:
                # code...
                break;
        }
        return $name;
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
            'entry_type' => ['required', 'in:income,expense,opening_balance,asset_acquisition,opening_balance_credit,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'debit_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'credit_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'description' => ['required', 'string'],
            // 'applies_se' => ['nullable', 'boolean'],
            // 'applies_fe' => ['nullable', 'string'],
        ]);
        $userId = $request->user()->id;
        $entry_date = $request->entry_date;
        $entry_type = $request->entry_type;
        $description = $request->description;
        $reference = "manual";
        $amount = $request->amount;
        $debit_account_id = $request->debit_account_id;
        $credit_account_id = $request->credit_account_id;


        $month = self::getMonth($entry_date);
        $year = self::getYear($entry_date);

        self::create($userId, $entry_date, $entry_type, $description, $reference, $amount, $debit_account_id, $credit_account_id);
        self::recalculateForward($debit_account_id, $month, $year, $userId);
        self::recalculateForward($credit_account_id, $month, $year, $userId);


        self::setCloseAccounts($year, $userId);
        self::setOpeningDeficit($year, $userId);

        $results = [];
        return response()->json([$results, $month, $year], 201);
    }
    public function change(Request $request)
    {
        $request->validate([
            'entry_id' => ['required', 'string'],
            'credit_account_id' => ['required', 'string'],
            'debit_account_id' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
            'entry_type' => ['required', 'in:income,expense,opening_balance,opening_balance_credit,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string'],
        ]);
        $userId = $request->user()->id;
        $entry_id = $request->entry_id;
        $new_debit = $request->debit_account_id;
        $new_credit = $request->credit_account_id;
        $entry_date = $request->entry_date;
        $entry_type = $request->entry_type;
        $description = $request->description;
        $reference = "manual";
        $amount = $request->amount;


        $month = self::getMonth($entry_date);
        $year = self::getYear($entry_date);
        $results = [];

        $results = DB::table('journal')->where("entry_id", $entry_id)->get()->first();
        $debit_account_id = $results->debit_account_id;
        $credit_account_id = $results->credit_account_id;

        $current_entry_date = $results->entry_date;
        $current_month = self::getMonth($current_entry_date);
        $current_year = self::getYear($current_entry_date);

        // dd($debit_account_id, $new_debit, $credit_account_id, $new_credit);
        self::update($entry_id, $userId, $entry_date, $entry_type, $description, $reference, $amount, $new_debit, $new_credit);

        self::recalculateForward($new_debit, $month, $year, $userId);
        self::recalculateForward($new_credit, $month, $year, $userId);

        self::setOpeningDeficit($year, $userId);

        if ($new_debit != $debit_account_id) {
            self::recalculateForward($debit_account_id, $current_month, $current_year, $userId);
        }
        if ($new_credit != $credit_account_id) {
            self::recalculateForward($credit_account_id, $current_month, $current_year, $userId);
        }
        self::setOpeningDeficit($current_year, $userId);

        // // $results = [];
        return response()->json([$results], 201);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'entry_id' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
        ]);
        $userId = $request->user()->id;
        $entry_id = $request->entry_id;
        $entry_date = $request->entry_date;


        $month = self::getMonth($entry_date);
        $year = self::getYear($entry_date);

        $results = DB::table('journal')->where("entry_id", $entry_id)->get()->first();
        $debit_account_id = $results->debit_account_id;
        $credit_account_id = $results->credit_account_id;

        // dd($results);
        self::remove($entry_id, $userId);
        self::recalculateForward($debit_account_id, $month, $year, $userId);
        self::recalculateForward($credit_account_id, $month, $year, $userId);
        self::setOpeningDeficit($year, $userId);

        // $results = [];
        return response()->json([$results, $debit_account_id, $credit_account_id], 201);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.entry_id' => ['required', 'string'],
            'entries.*.entry_date' => ['required', 'date'],
        ]);

        $userId = $request->user()->id;
        foreach ($request->entries as $entry) {
            $entry_id = $entry['entry_id'];
            $entry_date = $entry['entry_date'];
            $month = self::getMonth($entry_date);
            $year = self::getYear($entry_date);
            $result = DB::table('journal')->where("entry_id", $entry_id)->first();
            if (!$result) continue;
            $debit_account_id = $result->debit_account_id;
            $credit_account_id = $result->credit_account_id;
            self::remove($entry_id, $userId);
            self::recalculateForward($debit_account_id, $month, $year, $userId);
            self::recalculateForward($credit_account_id, $month, $year, $userId);
            self::setOpeningDeficit($year, $userId);
        }

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function getMonth(string $date): int
    {
        return Carbon::parse($date)->month;
    }
    public function getYear(string $date): int
    {
        return Carbon::parse($date)->year;
    }

    private static function remove($entry_id, $userId)
    {

        DB::transaction(function () use (
            $entry_id,
            $userId,
        ) {
            // 1️⃣ Create journal entry
            $entry = JournalEntry::destroy([
                'id' => $entry_id
            ]);
        });
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
                self::setOpeningDeficit($year, $userId);
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
        $total = $results->total ?? 0;
        $nature = $results->nature;
        $account_code = $results->account_code;
        $validation = self::validateOpening($userId, $account_id, $credit_account_id, $month, $year, $nature);
        $prefixes = collect(['400.', '500.', '600.', '700.', '800.', '900.']);
        $opening_type = $nature == "debit" ? "opening_balance" : "opening_balance_credit";
        $nextDate = $validation["date"];
        if ($month == 12 && $prefixes->contains(fn($p) => str_starts_with($account_code, $p))) {
            $total = 0;
        }
        if ($validation["new"] == true) {
            Log::debug('NewOpening', [$userId, "$account_code", "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $credit_account_id]);
            self::create($userId, "$nextDate", $opening_type, "Saldo Inicial Nuevo", "automatic", $total, $account_id, null);
        } else {
            $entry_id = $validation["entry_id"];
            Log::debug('UpdateOpening', [$entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $total, $account_id, $credit_account_id]);
            self::update($entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial Update", "automatic", $total, $account_id, null);
        }
        return [$results, $validation];
    }
    public static function setOpeningDeficit($year, $userId)
    {
        $income = IncomeStatementController::getIncomeStatement($userId, 12, $year, true);
        $total = $income["total"];
        $account = DB::table("accounts")->where("user_id", $userId)->where("code", "300.2")->get()->first();
        $nature = $account->nature;
        $account_id = $account->id;
        $validation = self::validateOpening($userId, $account_id, $account_id, 12, $year, $nature);

        $lastAmount = (float) TrialBalanceController::getTrialBalance($userId, 12, $year)->where("op.account_code", "300.2")->get()->first()->total;
        $totalAmount = $lastAmount + $total;

        // dd($validation, $account, $total, $lastAmount, $lastAmount + $total);
        $opening_type = $nature == "debit" ? "opening_balance" : "opening_balance_credit";
        $nextDate = $validation["date"];
        if ($validation["new"] == true) {
            Log::debug('NewOpeningDeficit', [$userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $totalAmount, $account_id, $account_id]);
            self::create($userId, "$nextDate", $opening_type, "Saldo Inicial Nuevo", "automatic", $totalAmount, $account_id, null);
        } else {
            $entry_id = $validation["entry_id"];
            Log::debug('UpdateOpeningDeficit', [$entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial", "Saldo Inicial 4", $totalAmount, $account_id, $account_id]);
            self::update($entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial Update", "automatic", $totalAmount, $account_id, null);
        }
        return [$account, $validation];
    }
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
        if ($month == "total") {
            $results = self::getJournalVoucherClose($userId, $year, $search, $filters, $limit, $page);
        } else {
            $results = self::getJournalVoucher($userId, $month, $year, $search, $filters, $limit, $page);
        }


        return response()->json([
            'total'  => $results["total"],
            'data'   => $results["data"],
            'filters' => $results["filters"],
            'footer' => [
                'description' => 'TOTAL',
                'debit'  => $results["debit"],
                'credit' => $results["credit"],
                'credit_account_code' => "",
                'entry_date' => "",
                'entry_type_label' => "",
                'debit_account_name' => "",
                'debit_account_code' => "",
                'credit_account_name' => "",
            ],
        ]);
    }
    public static function getJournalVoucher($userId, $month, $year, $search = null, $filters = [], $limit = 10, $page = 1)
    {
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
        $typesFilter = $filters['types'] ?? null;
        $datesFilter = $filters['dates'] ?? null;

        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;

        // Date filter
        if ($start_date && !$end_date) {
            $query->whereDate('entry_date', $start_date);
        } elseif (!$start_date && $end_date) {
            $query->whereDate('entry_date', '<=', $end_date);
        } elseif ($start_date && $end_date) {
            $query->whereBetween('entry_date', [$start_date, $end_date]);
        }


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

        if ($typesFilter) {
            $query->where(function ($q) use ($typesFilter) {
                if ($typesFilter) {
                    $q->whereIn('entry_type', $typesFilter);
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
                'entry_type'          => $entry->entry_type,
                'entry_type_label'    => self::translateJournalType($entry->entry_type),
                'description'         => $entry->description,
                'debit_account_name'  => $entry->debit_account_name,
                'debit_account_code'  => $entry->debit_account_code,
                'credit_account_name' => $entry->credit_account_name,
                'credit_account_code' => $entry->credit_account_code,
                'debit'               => $entry->debit,
                'credit'              => $entry->credit,
            ];
        });
        $returned["total"] = $entries->total();
        $returned["data"] = $rows;
        $returned["filters"] = $filters;
        $returned["debit"] = $totalDebit;
        $returned["credit"] = $totalCredit;
        return $returned;

        // --- PASO 5: Devolver el JSON (sin cambios) ---
    }
    public static function getJournalVoucherClose($userId, $year, $search = null, $filters = [], $limit = 10, $page = 1)
    {
        $rows = TrialBalanceController::getTrialBalanceSummary($userId, 12, $year);
        $prefixes = ['400.', '500.', '600.', '700.', '800.', '900.'];

        $data = $rows->where(function ($q) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                $q->orWhere('op.account_code', 'like', $prefix . '%');
            }
        });
        // $results = $data->paginate($limit);
        // $allEntries = $data->get();
        $entries = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($data->get() as $key => $entry) {
            $totalDebit += $entry->credit + $entry->debit;
            $totalCredit += $entry->credit + $entry->debit;
        }
        $results = $data->paginate($limit);
        $entries = $results->getCollection()->map(function ($entry) use ($year) {
            if ($entry->nature == "debit") {
                $type = "Egreso";
            } else {
                $type = "Ingreso";
            }
            return [
                'id'                   => null,
                'entry_date'           => "01/12/$year",
                'entry_type_label'     => $type,
                'description'          => "POLIZA CIERRE",
                'debit_account_name'   => $entry->account_name,
                'debit_account_code'   => $entry->account_code,
                'credit_account_name'  => "REMANENTE O DEFICIT EJERCICIO $year",
                'credit_account_code'  => "300.1",
                'debit'                => round($entry->credit + $entry->debit, 2),
                'credit'               => round($entry->credit + $entry->debit, 2),
            ];
        });

        $returned["total"] = $results->total();
        $returned["data"] = $entries;
        $returned["filters"] = [];
        $returned["debit"] = $totalDebit;
        $returned["credit"] = $totalCredit;
        return $returned;
    }
    public static function translateJournalType(string $type): string
    {
        $map = [
            'opening_balance' => 'Saldo Inicial Cuenta Deudora',
            'opening_balance_credit' => 'Saldo Inicial Cuenta Acreedora',
            'income' => 'Ingreso',
            'expense' => 'Gasto',
            'transfer' => 'Traspaso',
            'asset_acquisition' => 'Adquisición de Activo',
            'adjustment' => 'Ajuste',
        ];

        return $map[$type] ?? 'Desconocido';
    }

    public static function setCloseAccounts($year, $userId)
    {
        $prefixes = ['400.', '500.', '600.', '700.', '800.', '900.'];
        $accounts = DB::table("accounts")
            ->where("user_id", $userId)
            ->where(function ($query) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $query->orWhere('code', 'like', $prefix . '%');
                }
            })
            ->get();
        $nextYear = self::getNextYear(12, $year);
        foreach ($accounts as $key => $account) {
            $account_id = $account->id;
            $nature = $account->nature;
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
                ->whereMonth('entry_date', 1)
                ->whereYear('entry_date', $nextYear)
                ->whereIn('entry_type', [
                    'opening_balance',
                    'opening_balance_credit',
                ]);
            $journal = $query->get();
            $nextDate = sprintf('%04d-%02d-01', $nextYear, 1);
            $opening_type = $nature == "debit" ? "opening_balance" : "opening_balance_credit";
            if ($journal->isEmpty()) {
                // $validation["new"] = true;
                // $validation["entry_id"] = null;
                // $validation["userId"] = $userId;
                // $validation["nextDate"] = $nextDate;
                // $validation["opening_type"] = $opening_type;
                // $validation["description"] = "Saldo Inicial Nuevo";
                // $validation["reference"] = "automatic";
                self::create($userId, "$nextDate", $opening_type, "Saldo Inicial Nuevo", "automatic", 0, $account_id, null);
            } else {
                // $validation["new"] = false;
                // $validation["entry_id"] = $journal[0]->entry_id;
                // $validation["userId"] = $userId;
                // $validation["nextDate"] = $nextDate;
                // $validation["opening_type"] = $opening_type;
                // $validation["description"] = "Saldo Inicial Update";
                // $validation["reference"] = "automatic";
                if ($journal[0]->reference == "automatic") {
                    self::update($journal[0]->entry_id, $userId, "$nextDate", $opening_type, "Saldo Inicial Update", "automatic", 0, $account_id, null);
                }
            }
        }
    }
    public static function recalculateForward($account_id, $startMonth, $year, $userId)
    {
        $currentMonth = $startMonth;
        $currentYear = $year;

        // 🔥 Get last REAL movement date (not automatic)
        $lastDate = self::getLastMovementDate($account_id, $userId);

        if (!$lastDate) return;

        $currentDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
        $endDate = \Carbon\Carbon::parse($lastDate)->startOfMonth();

        $maxIterations = 60; // 🔥 safety (5 years max)
        $count = 0;
        Log::debug([
            "validate recalculate",
            $lastDate,
            $account_id,
            $currentDate->toString(),
            $endDate->toString(),
            $currentDate <= $endDate
        ]);
        while ($currentDate <= $endDate) {

            if ($count++ > $maxIterations) break;

            $month = $currentDate->month;
            $year = $currentDate->year;

            $trial = TrialBalanceController::getTrialBalance($userId, $month, $year);
            $result = $trial->where('op.account_id', $account_id)->first();

            // If no result, still continue (carry forward)
            $total = $result->total ?? 0;
            $nature = $result->nature ?? 'debit';
            $account_code = $result->account_code ?? null;

            $nextDateData = self::validateOpening(
                $userId,
                $account_id,
                null,
                $month,
                $year,
                $nature
            );

            $nextDate = $nextDateData["date"];
            $opening_type = $nature == "debit"
                ? "opening_balance"
                : "opening_balance_credit";

            // 🔥 Debug AFTER computing month
            Log::debug([
                "recalculate",
                $month,
                $year,
                $account_id,
                $nextDate,
                $total
            ]);

            if ($nextDateData["new"]) {
                self::create(
                    $userId,
                    $nextDate,
                    $opening_type,
                    "Saldo Inicial Auto",
                    "automatic",
                    $total,
                    $account_id,
                    null
                );
            } else {
                self::update(
                    $nextDateData["entry_id"],
                    $userId,
                    $nextDate,
                    $opening_type,
                    "Saldo Inicial Update",
                    "automatic",
                    $total,
                    $account_id,
                    null
                );
            }

            // 👉 Move to next month
            $currentDate->addMonth();
        }
    }

    public static function getLastMovementDate($account_id, $userId)
    {

        return DB::table('journal')
            ->where('user_id', $userId)
            ->where(function ($query) use ($account_id) {
                $query->where('debit_account_id', $account_id)
                    ->orWhere('credit_account_id', $account_id);
            })
            ->where(function ($query) {
                $query->whereNull('reference')
                    ->orWhere('reference', '!=', 'automatic');
            })
            ->max('entry_date');
    }

    public static function hasMovementsOrOpening($account_id, $month, $year, $userId)
    {

        return DB::table('journal')
            ->where('user_id', $userId)
            ->where(function ($query) use ($account_id) {
                $query->where('debit_account_id', $account_id)
                    ->orWhere('credit_account_id', $account_id);
            })
            ->whereMonth('entry_date', $month)
            ->whereYear('entry_date', $year)
            ->where('reference', '!=', 'automatic')
            ->exists();
    }
}
