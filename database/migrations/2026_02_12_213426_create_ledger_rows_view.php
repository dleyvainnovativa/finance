<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW ledger_rows AS
            WITH main_table AS (
SELECT
    je.id AS entry_id,
    je.user_id,
    je.entry_date,
    je.entry_type,
    je.description,
    da.name AS debit_account_name,
    da.code AS debit_account_code,
    ca.name AS credit_account_name,
    ca.code AS credit_account_code,
    CASE
        WHEN je.entry_type IN ('income', 'opening_balance') THEN
            COALESCE(dl.debit, cl.credit, 0)
        ELSE 0
    END AS debit,
    CASE
        WHEN je.entry_type IN ('expense', 'opening_balance_credit') THEN
            COALESCE(dl.debit, cl.credit, 0)
        ELSE 0
    END AS credit
FROM journal_entries je
LEFT JOIN journal_entry_lines dl
    ON dl.journal_entry_id = je.id
    AND dl.debit > 0
LEFT JOIN chart_of_accounts da
    ON da.id = dl.chart_of_account_id
LEFT JOIN journal_entry_lines cl
    ON cl.journal_entry_id = je.id
    AND cl.credit > 0
LEFT JOIN chart_of_accounts ca
    ON ca.id = cl.chart_of_account_id
WHERE entry_type NOT IN ('transfer')
UNION ALL
SELECT
    je.id AS entry_id,
    je.user_id,
    je.entry_date,
    je.entry_type,
    je.description,
    da.name AS debit_account_name,
    da.code AS debit_account_code,
    ca.name AS credit_account_name,
    ca.code AS credit_account_code,
    CASE
        WHEN je.entry_type IN ('income', 'opening_balance') THEN
            COALESCE(dl.debit, cl.credit, 0)
 WHEN je.entry_type = 'transfer'
             AND da.id IS NOT NULL THEN
            dl.debit
        ELSE 0
    END AS debit,
    CASE
        WHEN je.entry_type IN ('expense', 'opening_balance_credit') THEN
            COALESCE(dl.debit, cl.credit, 0)
        ELSE 0
    END AS credit
FROM journal_entries je
LEFT JOIN journal_entry_lines dl
    ON dl.journal_entry_id = je.id
    AND dl.debit > 0
LEFT JOIN chart_of_accounts da
    ON da.id = dl.chart_of_account_id
LEFT JOIN journal_entry_lines cl
    ON cl.journal_entry_id = je.id
    AND cl.credit > 0
LEFT JOIN chart_of_accounts ca
    ON ca.id = cl.chart_of_account_id
WHERE entry_type  IN ('transfer')
UNION ALL
SELECT
    je.id AS entry_id,
    je.user_id,
    je.entry_date,
    je.entry_type,
    je.description,
    ca.name AS debit_account_name,
    ca.code AS debit_account_code,
    da.name AS credit_account_name,
    da.code AS credit_account_code,
    CASE
        WHEN je.entry_type IN ('income', 'opening_balance') THEN
            COALESCE(dl.debit, cl.credit, 0)
        ELSE 0
    END AS debit,
    CASE
        WHEN je.entry_type IN ('expense', 'opening_balance_credit') THEN
            COALESCE(dl.debit, cl.credit, 0)
		WHEN je.entry_type = 'transfer'
             AND ca.id IS NOT NULL THEN
            cl.credit
        ELSE 0
    END AS credit
FROM journal_entries je
LEFT JOIN journal_entry_lines dl
    ON dl.journal_entry_id = je.id
    AND dl.debit > 0
LEFT JOIN chart_of_accounts da
    ON da.id = dl.chart_of_account_id
LEFT JOIN journal_entry_lines cl
    ON cl.journal_entry_id = je.id
    AND cl.credit > 0
LEFT JOIN chart_of_accounts ca
    ON ca.id = cl.chart_of_account_id
WHERE entry_type  IN ('transfer')
)
SELECT * from main_table
ORDER BY entry_date, entry_id;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS ledger_rows');
    }
};
