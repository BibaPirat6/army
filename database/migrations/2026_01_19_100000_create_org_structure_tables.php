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
        // position tables
        Schema::create('position_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        Schema::create('chief_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->foreignId('position_type_id')->nullable()->constrained('position_types')->nullOnDelete();
            $table->foreignId('chief_type_id')->nullable()->constrained('chief_types')->nullOnDelete();
            $table->timestamps();
        });

        // structure tables

        Schema::create('commissariats', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();

            $table->integer('longitude')->nullable();
            $table->integer('latitude')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);

            $table->foreignId('commissariat_id')
                ->constrained('commissariats')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['name', 'commissariat_id']);
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);

            $table->foreignId('commissariat_id')
                ->constrained('commissariats')
                ->cascadeOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['name', 'commissariat_id']);
        });


        // Employee Positions tables
        Schema::create('employee_position_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('employee_position_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 3, 2)->unique();
            $table->timestamps();
        });

        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->foreignId('commissariat_id')->constrained()->cascadeOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('division_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('position_id')->constrained()->cascadeOnDelete();

            $table->foreignId('employee_position_rate_id')->default(4)->constrained()->cascadeOnDelete();

            $table->foreignId('employee_position_status_id')
                ->default(1)
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_independent')->default(false);

            $table->timestamps();

            $table->unique([
                'employee_id',
                'commissariat_id',
                'department_id',
                'division_id',
                'position_id',
            ],
                'employee_position_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
        Schema::dropIfExists('employee_position_rates');
        Schema::dropIfExists('employee_position_statuses');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('commissariats');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('chief_types');
        Schema::dropIfExists('position_types');
    }
};
