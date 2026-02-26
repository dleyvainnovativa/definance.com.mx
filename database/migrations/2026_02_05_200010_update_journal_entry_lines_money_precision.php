<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->decimal('debit', 15, 2)->nullable()->default(null)->change();
            $table->decimal('credit', 15, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->numeric('debit')->nullable()->default(null)->change();
            $table->numeric('credit')->nullable()->default(null)->change();
        });
    }
};
