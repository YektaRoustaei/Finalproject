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
        Schema::create('synonyms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('synonym1')->nullable();
            $table->string('synonym2')->nullable();
            $table->string('synonym3')->nullable();
            $table->string('synonym4')->nullable();
            $table->string('synonym5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('synonyms');
    }
};