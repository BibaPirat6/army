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
        Schema::create('position_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('position_type_id')->nullable()->constrained('position_types')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('commissariats', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('specialization', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->decimal('rate', 3, 2);
            $table->timestamps();

            $table->unique(['employee_id', 'position_id']);
        });

        Schema::create('org_links', function (Blueprint $table) {
            $table->id();
            $table->enum('parent_type', ['commissariat', 'department']);
            $table->unsignedBigInteger('parent_id');
            $table->enum('child_type', ['department', 'division', 'employee', 'position']);
            $table->unsignedBigInteger('child_id');
            $table->boolean('is_independent')->default(false);
            $table->timestamps();

            $table->index(['parent_type', 'parent_id']);
            $table->index(['child_type', 'child_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_links');
        Schema::dropIfExists('employee_positions');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('commissariats');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('position_types');
    }
};
