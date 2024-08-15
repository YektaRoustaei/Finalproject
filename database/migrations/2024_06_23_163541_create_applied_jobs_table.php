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
        Schema::create('applied_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->onDelete('cascade');
            $table->foreignId('seeker_id')->constrained('seekers')->onDelete('cascade');
            $table->foreignId('curriculum_vitae_id')->nullable()->constrained('curriculum_vitaes')->onDelete('set null');
            $table->foreignId('cover_letter_id')->nullable()->constrained('cover_letters')->onDelete('set null');
            $table->enum('status', ['accepted', 'hold', 'rejected', 'next_step', 'final_step']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_jobs');
    }
};
