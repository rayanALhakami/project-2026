<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['category_id']);

            $table->dropIndex(['type']);
            $table->dropIndex(['date']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['user_id', 'name']);
            $table->unique(['user_id', 'name', 'type']);

            $table->dropIndex(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['type']);

            $table->dropUnique(['user_id', 'name', 'type']);
            $table->dropIndex(['user_id', 'name']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['type']);
            $table->index(['date']);

            $table->dropIndex(['category_id']);
            $table->dropIndex(['user_id', 'type', 'date']);
            $table->dropIndex(['user_id', 'date']);
        });
    }
};
