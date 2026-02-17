<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($request) {
            $user = $request->user();

            // 1️⃣ Create journal entry
            $entry = JournalEntry::create([
                'user_id' => $user->id,
                'entry_date' => $request->entry_date,
                'entry_type' => $request->entry_type,
                'description' => $request->description,
                'reference' => $request->reference,
            ]);

            $amount = $request->amount;

            // 2️⃣ Create debit line
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'chart_of_account_id' => $request->debit_account_id,
                'debit' => $amount,
                'credit' => 0,
            ]);

            // 3️⃣ Create credit line
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'chart_of_account_id' => $request->credit_account_id,
                'debit' => 0,
                'credit' => $amount,
            ]);
        });
        return response()->json($request, 201);

        // return redirect()
        //     ->back()
        //     ->with('success', 'Movimiento registrado correctamente');
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

                $entry = JournalEntry::create([
                    'user_id' => $user->id,
                    'entry_date' => $row['entry_date'],
                    'entry_type' => $row['entry_type'],
                    'description' => $row['description'],
                    'reference' => $row['reference'] ?? null,
                ]);

                $amount = $row['amount'];

                // 🟢 Debit line
                if ($row['entry_type'] == "opening_balance_credit") {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $row['debit_account_id'],
                        'debit' => 0,
                        'credit' => $amount,
                    ]);
                } else {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $row['debit_account_id'],
                        'debit' => $amount,
                        'credit' => 0,
                    ]);
                }

                // 🔵 Credit line (skip if null, like opening balance)
                if (!empty($row['credit_account_id'])) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'chart_of_account_id' => $row['credit_account_id'],
                        'debit' => 0,
                        'credit' => $amount,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Journal entries imported successfully'
        ], 201);
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
