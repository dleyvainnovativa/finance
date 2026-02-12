<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->enum('entry_type', [
                'opening_balance',
                'opening_balance_credit',
                'income',
                'expense',
                'transfer',
                'asset_acquisition',
                'adjustment'
            ])->default('income')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn('entry_type');
        });
    }
};
