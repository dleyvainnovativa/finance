<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- ¡Importante!

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
        $limit = (int) $request->get('limit', 15);

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
        $openingQuery = DB::table('accounts as a')
            ->select(
                'a.id as account_id',
                'a.nature as nature',
                'a.nature_label as nature_label',
                'a.type_account as type_account',
                'a.type as type',
                'a.code as account_code',
                'a.name as account_name',
                'a.user_id',
                DB::raw('COALESCE(SUM(CASE WHEN j.debit_account_id = a.id THEN j.debit WHEN j.credit_account_id = a.id THEN j.credit ELSE 0 END), 0) as amount')
            )
            ->leftJoin('journal as j', function ($join) use ($month, $year) {
                $join->on(function ($query) {
                    $query->on('a.id', '=', 'j.debit_account_id')->orOn('a.id', '=', 'j.credit_account_id');
                })
                    ->whereIn('j.entry_type', ['opening_balance', 'opening_balance_credit'])
                    ->whereMonth('j.entry_date', $month)
                    ->whereYear('j.entry_date', $year);
            })
            ->groupBy('a.id', 'a.code', 'a.name', 'a.user_id');

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
                DB::raw('COALESCE(mt.credit, 0) as credit')
            )
            ->where('op.user_id', $userId) // Filtro principal por usuario
            ->when($search, function ($q) use ($search) {
                $q->where('op.account_name', 'like', "%{$search}%")
                    ->orWhere('op.account_code', 'like', "%{$search}%");
            })
            ->orderBy('op.account_code');

        $results = $finalQuery->paginate($limit);
        $totalDebit   = 0;
        $totalCredit  = 0;
        $totalOpening = 0;
        $totalSaldo   = 0;

        $rows = $results->getCollection()->map(function ($entry) use (
            &$totalDebit,
            &$totalCredit,
            &$totalOpening,
            &$totalSaldo
        ) {
            $total = 0;

            if ($entry->nature === 'debit') {
                $total = $entry->opening + $entry->debit - $entry->credit;
            } elseif ($entry->nature === 'credit') {
                $total = $entry->opening + $entry->credit - $entry->debit;
            }

            // Accumulate totals
            $totalDebit   += (float) $entry->debit;
            $totalCredit  += (float) $entry->credit;
            $totalOpening += (float) $entry->opening;
            $totalSaldo   += (float) $total;

            return [
                'entry_type_label' => $entry->type_account,
                'account_code'     => $entry->account_code,
                'nature'           => $entry->nature_label,
                'account_name'     => $entry->account_name,
                'opening'          => round($entry->opening, 2),
                'debit'            => round($entry->debit, 2),
                'credit'           => round($entry->credit, 2),
                'total'            => round($total, 2),
            ];
        });

        return response()->json([
            'total' => $results->total(),
            'data'  => $rows,
            'footer' => [
                'entry_type_label' => "",
                'account_code' => "",
                'nature' => "",
                'account_name' => "",
                'opening'  => $totalOpening,
                'debit'  => $totalDebit,
                'credit' => $totalCredit,
                'total' => $totalSaldo,
            ],
        ]);
    }

    private function getNatureByType(string $type): string
    {
        $type = strtoupper($type);
        if (in_array($type, ['ACTIVO', 'ASSET', 'EGRESOS', 'EXPENSE', 'COSTOS'])) {
            return 'DEUDORA';
        }
        if (in_array($type, ['PASIVO', 'LIABILITY', 'PATRIMONIO', 'EQUITY', 'INGRESOS', 'INCOME'])) {
            return 'ACREEDORA';
        }
        return 'INDEFINIDA';
    }
}
