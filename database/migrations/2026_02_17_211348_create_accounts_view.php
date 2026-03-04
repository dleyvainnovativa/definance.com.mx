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
            CREATE OR REPLACE VIEW accounts AS
            SELECT
    id,
    user_id,
    parent_id,
    code,
    name,
    type,
    is_editable,
    is_deletable,
    allows_children,
    CASE
        WHEN type IN ('asset', 'expense') THEN 'debit'
        WHEN type IN ('liability', 'equity', 'income') THEN 'credit'
        ELSE NULL
    END AS nature,
    CASE
        WHEN type IN ('asset', 'expense') THEN 'Deudora'
        WHEN type IN ('liability', 'equity', 'income') THEN 'Acreedora'
        ELSE NULL
    END AS nature_label,
    CASE
        WHEN type = 'asset'     THEN 'Activo'
        WHEN type = 'liability' THEN 'Pasivo'
        WHEN type = 'equity'    THEN 'Patrimonio'
        WHEN type = 'income'    THEN 'Ingresos'
        WHEN type = 'expense'   THEN 'Gastos'
        ELSE 'Otro'
    END AS type_account,
    created_at,
    updated_at
FROM chart_of_accounts;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS accounts');
    }
};
