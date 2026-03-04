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
        if ($type == "create") {
            $entries = $query->orderBy('code')->where("allows_children", true)->get();
        } else {
            $entries = $query->orderBy('code')->get();
        }
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

        $account = ChartOfAccount::create([
            'user_id' => $user->id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
        ]);


        return response()->json([
            'success' => true,
            'account' => $account
        ], 201);
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
