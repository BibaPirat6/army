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

        Schema::create('    ', function (Blueprint $table) {
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
            $table->string('color');
            // занимает ли статус ставку
            $table->boolean('occupies_rate')->default(true);
            $table->timestamps();
        });

        // штатные должности в каждом комиссариате
        Schema::create('commissariat_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commissariat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate_total', 4, 2)->default(1.00);
            $table->boolean('is_independent')->default(false);
            $table->timestamps();
            // добавить сервис для вычисления статуса - например вакантна, занята, сотрудник в отпуске свободна до 03.03,
            // сотрудник в декрете свободна до 03.03
        });

        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commissariat_position_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 4, 2)->default(1.00);
            $table->foreignId('employee_position_status_id')->default(1)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
        Schema::dropIfExists('commissariat_positions');
        Schema::dropIfExists('employee_position_statuses');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('commissariats');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('chief_types');
        Schema::dropIfExists('position_types');
    }
};
