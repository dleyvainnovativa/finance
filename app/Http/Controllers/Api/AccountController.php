<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChartOfAccountResource;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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

        $entries = $query->orderBy('code')->get();
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
        ]);
    }
}
