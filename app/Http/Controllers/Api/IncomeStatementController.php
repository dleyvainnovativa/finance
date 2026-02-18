<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        // $month = 1;
        $year = $request->get('year', now()->year);
        // $year = 2026;
        $results = self::getTrialBalance($userId, $month, $year);

        $prefixMap = [
            [
                'key' => 'incomes',
                'data' => [],
                'icon' => 'fa-arrow-trend-up',
                'type'   => 'credit',
                'title' => 'Ingresos',
                'codes' => ['400.'],
                'display' => 'operation',
                'total' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'expenses',
                'data' => [],
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Gastos',
                'codes' => ['500.'],
                'display' => 'operation',
                'total' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'total',
                'data' => [],
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Utilidad / Pérdida',
                'codes' => ['400.', '500.'],
                'calculate' => [
                    "plus" => ['incomes'],
                    "minus" => ['expenses']
                ],
                'display' => 'total',
                'total' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_incomes',
                'data' => [],
                'icon' => 'fa-arrow-trend-up',
                'type'   => 'credit',
                'title' => 'Otros Productos Financieros',
                'codes' => ['700.', '900.'],
                'display' => 'operation',
                'total' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_expenses',
                'data' => [],
                'icon' => 'fa-arrow-trend-down',
                'type'   => 'debit',
                'title' => 'Otros Gastos Financieros',
                'codes' => ['600.', '800.'],
                'display' => 'operation',
                'total' => 0,
                'percent' => 0,
            ],
            [
                'key' => 'other_total',
                'data' => [],
                'icon' => 'fa-chart-line',
                'type'   => 'total',
                'title' => 'Utilidad / Pérdida',
                'codes' => ['700.', '900.', '600.', '800.'],
                'calculate' => [
                    "plus" => ['other_incomes', 'incomes'],
                    "minus" => ['other_expenses', 'expenses']
                ],
                'display' => 'total',
                'total' => 0,
                'percent' => 0,
            ],
        ];

        $data = [];

        $rows = $results->map(function ($entry) use (&$prefixMap) {

            foreach ($prefixMap as &$group) {

                // Skip total rows for now
                if ($group['display'] !== 'operation') {
                    continue;
                }
                foreach ($group['codes'] as $code) {
                    if (!str_starts_with($entry->account_code, $code)) {
                        continue;
                    }
                    $amount = 0;
                    $debit  = $entry->debit  ?? 0;
                    $credit = $entry->credit ?? 0;
                    if ($group['type'] === 'credit') {
                        $amount = $credit;
                        $group['total'] += $credit;
                    }
                    if ($group['type'] === 'debit') {
                        $amount = $debit;
                        $group['total'] += $debit;
                    }
                    $entry->amount = $amount;
                    $group['data'][] = $entry;
                    break 2;
                }
            }
            return $entry;
        });

        $groupIndex = collect($prefixMap)->keyBy('key');
        $totalIncomes = $groupIndex['incomes']['total'] ?? 0;

        foreach ($prefixMap as &$group) {
            if ($group['display'] !== 'total') {
                continue;
            }
            $total = 0;
            foreach ($group['calculate']['plus'] as $key) {
                $total += $groupIndex[$key]['total'] ?? 0;
            }

            foreach ($group['calculate']['minus'] as $key) {
                $total -= $groupIndex[$key]['total'] ?? 0;
            }

            $group['total'] = round($total, 2);
        }

        foreach ($prefixMap as &$group) {

            if ($totalIncomes > 0) {
                $group['percent'] = round(
                    ($group['total'] / $totalIncomes) * 100,
                    2
                );
            } else {
                $group['percent'] = 0;
            }
        }

        foreach ($prefixMap as &$group) {

            if (!isset($group['data']) || empty($group['data'])) {
                continue;
            }

            foreach ($group['data'] as &$row) {

                if ($totalIncomes > 0) {
                    $row->percent = round(
                        ($row->amount / $totalIncomes) * 100,
                        2
                    );
                } else {
                    $row->percent = 0;
                }
            }
        }





        return response()->json(
            [
                'data'   => $prefixMap,
            ],
        );
    }
    public static function getTrialBalance($userId, $month, $year)
    {
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
            ->where('op.user_id', $userId)
            ->orderBy('op.account_code');

        $results = $finalQuery->get();

        return $results;
    }
}
