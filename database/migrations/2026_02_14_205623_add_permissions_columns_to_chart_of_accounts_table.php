<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->boolean('is_editable')->default(false)->after('type');
            $table->boolean('is_deletable')->default(false)->after('is_editable');
            $table->boolean('allows_children')->default(false)->after('is_deletable');
        });
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'is_editable',
                'is_deletable',
                'allows_children'
            ]);
        });
    }
};
