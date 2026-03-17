<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChartOfAccountResource;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $search = $request->get('search');
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        $query = DB::table('accounts')
            ->where('user_id', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $entries = $query->orderBy('code')->paginate($limit, ['*'], 'page', $page);
        $rows = $entries->getCollection()->map(function ($entry) {
            return [
                'id'                  => $entry->id,
                'user_id'                  => $entry->user_id,
                'code'                  => $entry->code,
                'name'                  => $entry->name,
                'type'                  => $entry->type,
                'type_label'                  => $entry->type_account,
                'nature'                  => $entry->nature,
                'nature_label'                  => $entry->nature_label,
                'allows_children'                  => $entry->allows_children,
                'is_editable'                  => $entry->is_editable,
                'is_deletable'                  => $entry->is_deletable,
            ];
        });

        // --- PASO 5: Devolver el JSON (sin cambios) ---
        return response()->json([
            'total'  => $entries->total(),
            'data'   => $rows,
        ]);
    }
    public function all(Request $request)
    {
        $userId = $request->user()->id;

        $query = DB::table('accounts')
            ->where('user_id', $userId);

        $type = $request->get('type', null);
        // if ($type == "create") {
        // $entries = $query->orderBy('code')->get();
        // } else {
        $entries = $query->orderByRaw("
            CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code, '.', 2), '.', -1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)
        ")->get();
        // }
        $rows = $entries->map(function ($entry) {
            return [
                'id'                  => $entry->id,
                'user_id'                  => $entry->user_id,
                'code'                  => $entry->code,
                'name'                  => $entry->name,
                'type'                  => $entry->type,
                'type_label'                  => $entry->type_account,
                'nature'                  => $entry->nature,
                'nature_label'                  => $entry->nature_label,
                'allows_children'                  => $entry->allows_children,
                'is_editable'                  => $entry->is_editable,
                'is_deletable'                  => $entry->is_deletable,
            ];
        });

        // --- PASO 5: Devolver el JSON (sin cambios) ---
        return response()->json([
            // 'total'  => $entries->total(),
            'data'   => $rows,
        ]);
    }
    public function new_entry(Request $request)
    {
        $userId = $request->user()->id;

        $query = DB::table('accounts as a')
            ->where('a.user_id', $userId)
            ->whereNotIn('a.id', function ($q) {
                $q->select('parent_id')
                    ->from('accounts')
                    ->whereNotNull('parent_id');
            });

        $type = $request->get('type', null);
        // if ($type == "create") {
        // $entries = $query->orderBy('code')->get();
        // } else {
        $entries = $query->orderByRaw("
            CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(code, '.', 2), '.', -1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)
        ")->get();
        // }
        $rows = $entries->map(function ($entry) {
            return [
                'id'                  => $entry->id,
                'user_id'                  => $entry->user_id,
                'code'                  => $entry->code,
                'name'                  => $entry->name,
                'type'                  => $entry->type,
                'type_label'                  => $entry->type_account,
                'nature'                  => $entry->nature,
                'nature_label'                  => $entry->nature_label,
                'allows_children'                  => $entry->allows_children,
                'is_editable'                  => $entry->is_editable,
                'is_deletable'                  => $entry->is_deletable,
            ];
        });

        // --- PASO 5: Devolver el JSON (sin cambios) ---
        return response()->json([
            // 'total'  => $entries->total(),
            'data'   => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'parent_id' => 'required|string',
            'type' => 'required|in:asset,liability,equity,income,expense',
        ]);

        $is_editable = true;
        $is_deletable = true;
        $allows_children = false;

        $account_code = $request->code;

        $blocks = explode('.', $account_code);
        $length = count($blocks);
        if ($length == 2) {
            $allows_children = true;
        } elseif ($length == 3) {
            $allows_children = false;
        }

        $account = ChartOfAccount::create([
            'user_id' => $user->id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
            'is_editable' => $is_editable,
            'is_deletable' => $is_deletable,
            'allows_children' => $allows_children,
        ]);

        // `chart_of_accounts`.`is_editable` AS `is_editable`,
        // `chart_of_accounts`.`is_deletable` AS `is_deletable`,
        // `chart_of_accounts`.`allows_children` AS `allows_children`,


        return response()->json([
            'success' => true,
            'code' => $request->code,
            'is_editable' => $is_editable,
            'is_deletable' => $is_deletable,
            'allows_children' => $allows_children,
            'account' => $account
        ], 201);
    }
    public function edit(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'id' => 'required|string',
            'name' => 'required|string'
        ]);
        $account = ChartOfAccount::find($request->id);
        $account->update([
            'name' => $request->name,
        ]);
        return response()->json([
            'success' => true,
            'account' => $account
        ], 201);
    }
    public function delete(Request $request)
    {
        $user = $request->user()->id;
        $request->validate([
            'id' => 'required|string',
        ]);
        $account_id = $request->id;
        $parents = DB::table('accounts')->where("parent_id", $account_id)->where("user_id", $user)->get();
        $account_ids = [];
        foreach ($parents as $key => $parent) {
            array_push($account_ids, $parent->id);
        }
        // dd(json_encode($accounts_ids));
        // $journal_parent = DB::table("journal")->whereIn("")
        $journal = DB::table("journal")
            ->where("user_id", $user)
            ->where(function ($q) use ($account_id) {
                $q->where("debit_account_id", $account_id)
                    ->orWhere("credit_account_id", $account_id);
            })
            ->first();
        if (!$journal && !$account_ids) {
            $account = ChartOfAccount::find($request->id);
            $account->delete();
            return response()->json([
                'success' => true,
                "message" => "Se ha borrado la cuenta",
                "account_ids" => $account_ids
                // 'account' => $account,
            ], 200);
        } else {
            $message = "No es posible borrar este registro";
            if ($journal) {
                $message = "Hay movimientos en esta cuenta, no es posible borrar borrar esta cuenta";
            }
            if ($account_ids) {
                $message = "Hay cuentas asociadas en este cuenta, no es posible borrar esta cuenta";
            }
            return response()->json([
                'message' => "$message",
                'success' => false,
                'journal' => $journal,
                "account_ids" => $account_ids
            ], 400);
        }
    }

    public static function setDefaults($userId)
    {
        // 1️⃣ Load JSON from storage/private
        $jsonPath = 'accounts/default.json';

        if (!Storage::disk('local')->exists($jsonPath)) {
            throw new \Exception("JSON file not found at: storage/app/{$jsonPath}");
        }

        $accounts = json_decode(Storage::disk('local')->get($jsonPath), true);
        $now = Carbon::now();

        DB::transaction(function () use ($accounts, $userId, $now) {

            $codeIdMap = [];

            // 2️⃣ Insert all accounts WITHOUT parent_id first
            foreach ($accounts as $account) {

                $id = DB::table('chart_of_accounts')->insertGetId([
                    'user_id' => $userId,
                    'parent_id' => null, // set later
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_editable' => $account['is_editable'],
                    'is_deletable' => $account['is_deletable'],
                    'allows_children' => $account['allows_children'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $codeIdMap[$account['code']] = $id;
            }

            foreach ($accounts as $account) {

                if (!empty($account['parent_code'])) {

                    $childId = $codeIdMap[$account['code']];
                    $parentId = $codeIdMap[$account['parent_code']] ?? null;

                    if ($parentId) {
                        DB::table('chart_of_accounts')
                            ->where('id', $childId)
                            ->update(['parent_id' => $parentId]);
                    }
                }
            }
        });
    }
}
