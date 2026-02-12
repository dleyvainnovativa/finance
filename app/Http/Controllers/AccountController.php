<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;

class AccountController extends Controller
{
    public function index()
    {
        // $accounts = ChartOfAccount::where('user_id', session('user_id'))
        //     ->orderBy('code')
        //     ->get();
        // dd($accounts);
        return view('pages.accounts');
    }
}
