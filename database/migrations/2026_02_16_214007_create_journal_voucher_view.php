<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    DB::statement("
            CREATE OR REPLACE VIEW journal_voucher AS
            with main_table as (
  select 
    `je`.`id` AS `entry_id`, 
    `je`.`user_id` AS `user_id`, 
    `je`.`entry_date` AS `entry_date`, 
    `je`.`entry_type` AS `entry_type`, 
    `je`.`description` AS `description`, 
    
    CASE 
        WHEN je.entry_type = 'income'  THEN da.id
        WHEN je.entry_type = 'expense' THEN ca.id
        WHEN je.entry_type = 'asset_acquisition' THEN ca.id
        ELSE da.id
    END AS debit_account_id,
    CASE 
        WHEN je.entry_type = 'income'  THEN da.name
        WHEN je.entry_type = 'expense' THEN ca.name
        WHEN je.entry_type = 'asset_acquisition' THEN ca.name
        ELSE da.name
    END AS debit_account_name,
    CASE 
        WHEN je.entry_type = 'income'  THEN da.code
        WHEN je.entry_type = 'expense' THEN ca.code
        WHEN je.entry_type = 'asset_acquisition' THEN ca.code
        ELSE da.code
    END AS debit_account_code,
    CASE 
        WHEN je.entry_type = 'income'  THEN ca.id
        WHEN je.entry_type = 'expense' THEN da.id
        WHEN je.entry_type = 'asset_acquisition' THEN da.id
        ELSE ca.id
    END AS credit_account_id,
    CASE 
        WHEN je.entry_type = 'income'  THEN ca.name
        WHEN je.entry_type = 'expense' THEN da.name
        WHEN je.entry_type = 'asset_acquisition' THEN da.name
        ELSE ca.name
    END AS credit_account_name,
    CASE 
        WHEN je.entry_type = 'income'  THEN ca.code
        WHEN je.entry_type = 'expense' THEN da.code
        WHEN je.entry_type = 'asset_acquisition' THEN da.code
        ELSE ca.code
    END AS credit_account_code,
    coalesce(`dl`.`debit`, `cl`.`credit`, 0) AS `debit`, 
    coalesce(`dl`.`debit`, `cl`.`credit`, 0) AS `credit` 
  from 
    (
      (
        (
          (
            `journal_entries` `je` 
            left join `journal_entry_lines` `dl` on(
              `dl`.`journal_entry_id` = `je`.`id` 
              and `dl`.`debit` > 0
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` > 0
        )
      ) 
      left join `chart_of_accounts` `ca` on(
        `ca`.`id` = `cl`.`chart_of_account_id`
      )
    ) 
  where 
    `je`.`entry_type` NOT IN  ('transfer','opening_balance_credit','opening_balance')
    union all 
  select 
    `je`.`id` AS `entry_id`, 
    `je`.`user_id` AS `user_id`, 
    `je`.`entry_date` AS `entry_date`, 
    `je`.`entry_type` AS `entry_type`, 
    `je`.`description` AS `description`, 
        `da`.`id` AS `debit_account_id`, 
    `da`.`name` AS `debit_account_name`, 
    `da`.`code` AS `debit_account_code`, 
        `ca`.`id` AS `credit_account_id`, 
    `ca`.`name` AS `credit_account_name`, 
    `ca`.`code` AS `credit_account_code`, 
    case when `je`.`entry_type` in ('income', 'opening_balance') then coalesce(`dl`.`debit`, `cl`.`credit`, 0) when `je`.`entry_type` = 'transfer' 
    and `da`.`id` is not null then `dl`.`debit` else 0 end AS `debit`, 
    case when `je`.`entry_type` in (
      'expense', 'asset_acquisition', 'opening_balance_credit'
    ) then coalesce(`dl`.`debit`, `cl`.`credit`, 0) else 0 end AS `credit` 
  from 
    (
      (
        (
          (
            `journal_entries` `je` 
            left join `journal_entry_lines` `dl` on(
              `dl`.`journal_entry_id` = `je`.`id` 
              and `dl`.`debit` > 0
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` > 0
        )
      ) 
      left join `chart_of_accounts` `ca` on(
        `ca`.`id` = `cl`.`chart_of_account_id`
      )
    ) 
  where 
    `je`.`entry_type` = 'transfer' 
union all 
  select 
    `je`.`id` AS `entry_id`, 
    `je`.`user_id` AS `user_id`, 
    `je`.`entry_date` AS `entry_date`, 
    `je`.`entry_type` AS `entry_type`, 
    `je`.`description` AS `description`, 
    `da`.`id` AS `debit_account_id`, 
    `da`.`name` AS `debit_account_name`, 
    `da`.`code` AS `debit_account_code`, 
    `ca`.`id` AS `credit_account_id`, 
    `ca`.`name` AS `credit_account_name`, 
    `ca`.`code` AS `credit_account_code`, 
    case when `je`.`entry_type` in ('income', 'opening_balance') then coalesce(`dl`.`debit`, `cl`.`credit`, 0) else 0 end AS `debit`, 
    case when `je`.`entry_type` in (
      'expense', 'asset_acquisition', 'opening_balance_credit'
    ) then coalesce(`dl`.`debit`, `cl`.`credit`, 0) when `je`.`entry_type` = 'transfer' 
    and `ca`.`id` is not null then `cl`.`credit` else 0 end AS `credit` 
  from 
    (
      (
        (
          (
            `journal_entries` `je` 
            left join `journal_entry_lines` `dl` on(
              `dl`.`journal_entry_id` = `je`.`id` 
              and `dl`.`debit` > 0
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` > 0
        )
      ) 
      left join `chart_of_accounts` `ca` on(
        `ca`.`id` = `cl`.`chart_of_account_id`
      )
    ) 
  where 
    `je`.`entry_type` = 'transfer'
) 
select 
-- SUM(debit), SUM(credit)

  `main_table`.`entry_id` AS `entry_id`, 
  `main_table`.`user_id` AS `user_id`, 
  `main_table`.`entry_date` AS `entry_date`, 
  `main_table`.`entry_type` AS `entry_type`, 
  `main_table`.`description` AS `description`, 
  `main_table`.`debit_account_id` AS `debit_account_id`, 
  `main_table`.`debit_account_name` AS `debit_account_name`, 
  `main_table`.`debit_account_code` AS `debit_account_code`, 
  `main_table`.`credit_account_id` AS `credit_account_id`, 
  `main_table`.`credit_account_name` AS `credit_account_name`, 
  `main_table`.`credit_account_code` AS `credit_account_code`, 
  `main_table`.`debit` AS `debit`, 
  `main_table`.`credit` AS `credit` 
from 
  `main_table` 
order by 
  `main_table`.`entry_date`, 
  `main_table`.`entry_id`
        ");
  }

  public function down(): void
  {
    DB::statement('DROP VIEW IF EXISTS journal_voucher');
  }
};
