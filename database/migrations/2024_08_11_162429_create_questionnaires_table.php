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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->onDelete('cascade'); // Defines foreign key
            $table->string('question');
            $table->enum('answer_type', ['string', 'int']); // Defines allowed answer types
            $table->integer('min_value'); // Makes min_value mandatory
            $table->integer('max_value')->nullable(); // Max value is optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
