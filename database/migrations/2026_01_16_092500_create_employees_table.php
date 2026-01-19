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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('person_id')->unique()->nullable()->constrained('persons')->onDelete('cascade');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->enum('work_status', ['vacant', 'fired', 'active'])->default('active');
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
