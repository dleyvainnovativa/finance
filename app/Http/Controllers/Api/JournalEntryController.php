<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
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

        $query = JournalEntry::with(['lines.chartOfAccount'])
            ->where('user_id', $userId);

        // ... (Toda tu lógica de filtros es correcta y no necesita cambios)
        if ($month && $year) {
            $query->whereMonth('entry_date', $month)->whereYear('entry_date', $year);
        }
        if ($search) {
            $query->where(function ($qs) use ($search) {
                $qs->where('description', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%")->orWhereHas('lines.chartOfAccount', function ($qa) use ($search) {
                    $qa->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                });
            });
        }
        $debitAccounts = $filters['debit_accounts'] ?? null;
        $creditAccounts = $filters['credit_accounts'] ?? null;
        if ($debitAccounts && $creditAccounts) {
            $query->where(function ($q) use ($debitAccounts, $creditAccounts) {
                $q->whereHas('lines', function ($ql) use ($debitAccounts) {
                    $ql->where('debit', '>', 0)->whereHas('chartOfAccount', function ($qa) use ($debitAccounts) {
                        $qa->whereIn('name', $debitAccounts);
                    });
                })->orWhereHas('lines', function ($ql) use ($creditAccounts) {
                    $ql->where('credit', '>', 0)->whereHas('chartOfAccount', function ($qa) use ($creditAccounts) {
                        $qa->whereIn('name', $creditAccounts);
                    });
                });
            });
        } elseif ($debitAccounts) {
            $query->whereHas('lines', function ($ql) use ($debitAccounts) {
                $ql->where('debit', '>', 0)->whereHas('chartOfAccount', function ($qa) use ($debitAccounts) {
                    $qa->whereIn('name', $debitAccounts);
                });
            });
        } elseif ($creditAccounts) {
            $query->whereHas('lines', function ($ql) use ($creditAccounts) {
                $ql->where('credit', '>', 0)->whereHas('chartOfAccount', function ($qa) use ($creditAccounts) {
                    $qa->whereIn('name', $creditAccounts);
                });
            });
        }


        $totalsQuery = clone $query;
        $entries = $query->orderBy('entry_date')->paginate($limit, ['*'], 'page', $page);
        $allEntriesForTotals = $totalsQuery->with(['lines'])->get();
        $totalDebit  = 0;
        $totalCredit = 0;

        // Tu lógica de totales es excelente y funciona como se espera.
        foreach ($allEntriesForTotals as $entry) {
            $debitLine  = $entry->lines->firstWhere('debit', '>', 0);
            $creditLine = $entry->lines->firstWhere('credit', '>', 0);
            switch ($entry->entry_type) {
                case 'income':
                case 'opening_balance':
                    if ($debitLine) {
                        $totalDebit += $debitLine->debit;
                    }
                    break;
                case 'expense':
                    if ($creditLine) {
                        $totalCredit += $creditLine->credit;
                    }
                    break;
                case 'opening_balance_credit':
                    if ($creditLine) {
                        $totalCredit += $creditLine->credit;
                    }
                    break;
                case 'transfer':
                    if (!empty($debitAccounts) && $debitLine && in_array($debitLine->chartOfAccount?->name, $debitAccounts)) {
                        $totalDebit += $debitLine->debit;
                    } elseif (!empty($creditAccounts) && $creditLine && in_array($creditLine->chartOfAccount?->name, $creditAccounts)) {
                        $totalCredit += $creditLine->credit;
                    }
                    break;
            }
        }

        // --- SECCIÓN CORREGIDA ---
        // Usamos `map` en lugar de `flatMap` para asegurar una fila por asiento.
        $rows = $entries->getCollection()->map(function ($entry) use ($debitAccounts, $creditAccounts) {
            $debitLine  = $entry->lines->firstWhere('debit', '>', 0);
            $creditLine = $entry->lines->firstWhere('credit', '>', 0);
            $amount = $debitLine?->debit ?? $creditLine?->credit ?? 0;
            $debitAmount = 0;
            $creditAmount = 0;

            // Se aplica la misma lógica de los totales a cada fila
            switch ($entry->entry_type) {
                case 'income':
                case 'opening_balance':
                    $debitAmount = $amount;
                    break;
                case 'expense':
                    $creditAmount = $amount;
                    break;
                case 'opening_balance_credit':
                    $creditAmount = $amount;
                    break;
                case 'transfer': // ESTA ES LA LÓGICA QUE FALTABA
                    if (!empty($debitAccounts) && $debitLine && in_array($debitLine->chartOfAccount?->name, $debitAccounts)) {
                        // Si la cuenta filtrada es la que recibió el débito, es un cargo.
                        $debitAmount = $debitLine->debit;
                    } elseif (!empty($creditAccounts) && $creditLine && in_array($creditLine->chartOfAccount?->name, $creditAccounts)) {
                        // Si la cuenta filtrada es la que recibió el crédito, es un abono.
                        $creditAmount = $creditLine->credit;
                    }
                    // Nota: Si no hay filtro, los cargos y abonos para transferencias serán 0, lo cual es correcto
                    // ya que no se tiene la perspectiva de una cuenta específica.
                    break;
            }

            return [
                'id'                  => $entry->id,
                'entry_date'          => $entry->entry_date->format('d/m/Y'),
                'entry_type_label'    => match (strtoupper($entry->entry_type)) {
                    'INCOME'          => 'Ingreso',
                    'EXPENSE'         => 'Egreso',
                    'TRANSFER'        => 'Transferencia',
                    'OPENING_BALANCE' => 'Saldo Inicial',
                    default           => $entry->entry_type,
                },
                'description'         => $entry->description,
                'debit_account_name'  => $debitLine?->chartOfAccount?->name,
                'debit_account_code'  => $debitLine?->chartOfAccount?->code,
                'credit_account_name' => $creditLine?->chartOfAccount?->name,
                'credit_account_code' => $creditLine?->chartOfAccount?->code,
                'debit'               => $debitAmount,
                'credit'              => $creditAmount,
            ];
        });


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
            'entry_type' => ['required', 'in:INGRESO,EGRESO,TRASPASO,SALDO INICIAL,SALDO INICIAL CREDITO'],
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
}
