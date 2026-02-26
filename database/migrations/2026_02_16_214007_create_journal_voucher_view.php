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
    case when `je`.`entry_type` = 'income' then `da`.`id` when `je`.`entry_type` = 'expense' then `ca`.`id` when `je`.`entry_type` = 'asset_acquisition' then `ca`.`id` else `da`.`id` end AS `debit_account_id`, 
    case when `je`.`entry_type` = 'income' then `da`.`name` when `je`.`entry_type` = 'expense' then `ca`.`name` when `je`.`entry_type` = 'asset_acquisition' then `ca`.`name` else `da`.`name` end AS `debit_account_name`, 
    case when `je`.`entry_type` = 'income' then `da`.`code` when `je`.`entry_type` = 'expense' then `ca`.`code` when `je`.`entry_type` = 'asset_acquisition' then `ca`.`code` else `da`.`code` end AS `debit_account_code`, 
    case when `je`.`entry_type` = 'income' then `ca`.`id` when `je`.`entry_type` = 'expense' then `da`.`id` when `je`.`entry_type` = 'asset_acquisition' then `da`.`id` else `ca`.`id` end AS `credit_account_id`, 
    case when `je`.`entry_type` = 'income' then `ca`.`name` when `je`.`entry_type` = 'expense' then `da`.`name` when `je`.`entry_type` = 'asset_acquisition' then `da`.`name` else `ca`.`name` end AS `credit_account_name`, 
    case when `je`.`entry_type` = 'income' then `ca`.`code` when `je`.`entry_type` = 'expense' then `da`.`code` when `je`.`entry_type` = 'asset_acquisition' then `da`.`code` else `ca`.`code` end AS `credit_account_code`, 
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
              and `dl`.`debit` is not null
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` is not null
        )
      ) 
      left join `chart_of_accounts` `ca` on(
        `ca`.`id` = `cl`.`chart_of_account_id`
      )
    ) 
  where 
    `je`.`entry_type` not in (
      'transfer', 'opening_balance_credit', 
      'opening_balance'
    ) 
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
              and `dl`.`debit` is not null
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` is not null
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
              and `dl`.`debit` is not null
            )
          ) 
          left join `chart_of_accounts` `da` on(
            `da`.`id` = `dl`.`chart_of_account_id`
          )
        ) 
        left join `journal_entry_lines` `cl` on(
          `cl`.`journal_entry_id` = `je`.`id` 
          and `cl`.`credit` is not null
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
