<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // 1. Subconsultas explícitas para cada cálculo
        // Este método es el más confiable para evitar errores de correlación.
        $openingDebitSubquery = \App\Models\JournalEntryLine::selectRaw('SUM(debit)')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereColumn('journal_entry_lines.chart_of_account_id', 'chart_of_accounts.id')
            ->where('journal_entries.entry_type', 'opening_balance')
            ->where('journal_entries.user_id', $userId)
            ->whereBetween('journal_entries.entry_date', [$periodStart, $periodEnd]);

        $openingCreditSubquery = \App\Models\JournalEntryLine::selectRaw('SUM(credit)')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereColumn('journal_entry_lines.chart_of_account_id', 'chart_of_accounts.id')
            ->where('journal_entries.entry_type', 'opening_balance')
            ->where('journal_entries.user_id', $userId)
            ->whereBetween('journal_entries.entry_date', [$periodStart, $periodEnd]);

        $periodDebitSubquery = \App\Models\JournalEntryLine::selectRaw('SUM(debit)')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereColumn('journal_entry_lines.chart_of_account_id', 'chart_of_accounts.id')
            ->where('journal_entries.entry_type', '!=', 'opening_balance')
            ->where('journal_entries.user_id', $userId)
            ->whereBetween('journal_entries.entry_date', [$periodStart, $periodEnd]);

        $periodCreditSubquery = \App\Models\JournalEntryLine::selectRaw('SUM(credit)')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereColumn('journal_entry_lines.chart_of_account_id', 'chart_of_accounts.id')
            ->where('journal_entries.entry_type', '!=', 'opening_balance')
            ->where('journal_entries.user_id', $userId)
            ->whereBetween('journal_entries.entry_date', [$periodStart, $periodEnd]);

        // 2. Consulta principal que aplica las subconsultas
        $accountsQuery = ChartOfAccount::where('user_id', $userId)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            })
            ->with(['parent'])
            ->addSelect([
                'opening_debit_sum' => $openingDebitSubquery,
                'opening_credit_sum' => $openingCreditSubquery,
                'period_debit_sum' => $periodDebitSubquery,
                'period_credit_sum' => $periodCreditSubquery,
            ]);

        $accounts = $accountsQuery->paginate($limit, ['*'], 'page', $page);

        $totals = ['opening_balance' => 0, 'debit' => 0, 'credit' => 0, 'final_balance' => 0];

        $data = $accounts->getCollection()->map(function ($account) use (&$totals) {
            $nature = $this->getNatureByType($account->type);

            $opening_balance = ($nature === 'DEUDORA')
                ? ($account->opening_debit_sum ?? 0) - ($account->opening_credit_sum ?? 0)
                : ($account->opening_credit_sum ?? 0) - ($account->opening_debit_sum ?? 0);

            $period_debit = $account->period_debit_sum ?? 0;
            $period_credit = $account->period_credit_sum ?? 0;
            $final_balance = $opening_balance + $period_debit - $period_credit;

            $totals['opening_balance'] += $opening_balance;
            $totals['debit'] += $period_debit;
            $totals['credit'] += $period_credit;
            $totals['final_balance'] += $final_balance;

            return [
                'account_code'    => $account->code,
                'nature'          => $nature,
                'type'            => strtoupper($account->type),
                'account_name'    => $account->name,
                'category'        => $account->parent ? $account->parent->name : 'N/A',
                'opening_balance' => number_format($opening_balance, 2),
                'debit'           => number_format($period_debit, 2),
                'credit'          => number_format($period_credit, 2),
                'final_balance'   => number_format($final_balance, 2),
            ];
        });

        return response()->json([
            'total' => $accounts->total(),
            'data'  => $data,
            'footer' => ['account_name' => 'TOTALES', 'opening_balance' => number_format($totals['opening_balance'], 2), 'debit' => number_format($totals['debit'], 2), 'credit' => number_format($totals['credit'], 2), 'final_balance' => number_format($totals['final_balance'], 2)],
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
