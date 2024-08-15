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
        Schema::table('applied_jobs', function (Blueprint $table) {
            // Alter the status column to include 'next step' and 'final step'
            $table->enum('status', ['accepted', 'hold', 'rejected', 'next_step', 'final_step'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applied_jobs', function (Blueprint $table) {
            // Revert the status column to its original values
            $table->enum('status', ['accepted', 'hold', 'rejected'])->change();
        });
    }
};
