<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class JournalEntryImportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'attachments.*' => 'file|max:5120', // 5MB each
        ]);


        $user = $request->user();
        $userId = $user->id;

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('journal-attachments', 'public');



                // Detect delimiter (CSV or TSV)
                $firstLine = fgets($file);
                $delimiter = str_contains($firstLine, "\t") ? "\t" : ",";
                rewind($file);

                $header = fgetcsv($file, 0, $delimiter);

                DB::transaction(function () use ($file, $delimiter, $userId) {

                    while (($row = fgetcsv($file, 0, $delimiter)) !== false) {

                        [
                            $fecha,
                            $aplica_se,
                            $aplica_fe,
                            $tipo,
                            $forma_pago,
                            $debit_code,
                            $debit_n1,
                            $debit_n2,
                            $credit_account_name,
                            $credit_code,
                            $credit_n1,
                            $credit_n2,
                            $concepto,
                            $cargo,
                            $abono,
                            $saldo
                        ] = array_pad($row, 16, null);

                        $cargo = (float) $cargo;
                        $abono = (float) $abono;

                        if ($cargo == 0 && $abono == 0) {
                            continue; // ignore empty rows
                        }

                        $entry = JournalEntry::create([
                            'user_id' => $userId,
                            'entry_date' => $this->parseDate($fecha),
                            'description' => $concepto,
                            'reference' => $tipo,
                        ]);

                        if ($cargo > 0) {
                            $debitAccount = $this->findAccount($userId, $debit_code);
                            $creditAccount = $this->findAccountByName($userId, $credit_account_name);

                            $this->createLines($entry, $debitAccount, $creditAccount, $cargo);
                        }

                        if ($abono > 0) {
                            $debitAccount = $this->findAccountByName($userId, $credit_account_name);
                            $creditAccount = $this->findAccount($userId, $credit_code);

                            $this->createLines($entry, $debitAccount, $creditAccount, $abono);
                        }
                    }
                });

                fclose($file);
            }
        }

        return back()->with('success', 'Movimientos importados correctamente');
    }

    private function createLines($entry, $debitAccount, $creditAccount, $amount)
    {
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $debitAccount->id,
            'debit' => $amount,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $creditAccount->id,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    private function findAccount($userId, $code)
    {
        return ChartOfAccount::where('user_id', $userId)
            ->where('code', trim($code))
            ->firstOrFail();
    }

    private function findAccountByName($userId, $name)
    {
        return ChartOfAccount::where('user_id', $userId)
            ->where('name', trim($name))
            ->firstOrFail();
    }

    private function parseDate($value)
    {
        return \Carbon\Carbon::createFromFormat('n/j/y', trim($value));
    }
}
