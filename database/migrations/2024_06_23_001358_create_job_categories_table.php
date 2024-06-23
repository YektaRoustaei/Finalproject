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
        Schema::create('job_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('job_id');
            $table->integer('category_id');
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('JobPostings')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('Category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_categories');
    }
};
