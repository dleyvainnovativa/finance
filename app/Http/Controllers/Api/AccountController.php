<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChartOfAccountResource;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;

class AccountController extends Controller
{

    // public function index()
    // {
    //     $accounts = ChartOfAccount::where('user_id', session('user_id'))
    //         ->orderBy('code')
    //         ->get();
    // }
    public function index(Request $request)
    {
        // 1. Start the query for the ChartOfAccount model
        $query = ChartOfAccount::query();
        $user = $request->user();


        // 2. CRITICAL: Add the condition to fetch accounts ONLY for the logged-in user
        $query->where('user_id', $user->id);

        // 3. Keep your existing search functionality (optional)
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            // It's good practice to search in both name and code
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('code', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // 4. Get ALL results using get() instead of paginate() and order them
        $accounts = $query->get();

        // 5. Return the data in the format your frontend expects
        return [
            'data' => ChartOfAccountResource::collection($accounts)->resolve(),
            'user' => $user->id,
            // Use count() on the resulting collection to get the total
            'total' => $accounts->count()
        ];
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
