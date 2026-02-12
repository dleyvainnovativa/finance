<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['user_id', 'entry_date']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index(['chart_of_account_id']);
            $table->index(['journal_entry_id']);
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'entry_date']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex(['chart_of_account_id']);
            $table->dropIndex(['journal_entry_id']);
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type']);
        });
    }
};
