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
        Schema::create('person_columns', function (Blueprint $table) {
            $table->id();
            $table->string('column_name')->unique();
            $table->string('type');
            $table->string('default')->nullable();
            $table->string('comment')->nullable();
            $table->boolean('nullable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_columns');
    }
};
