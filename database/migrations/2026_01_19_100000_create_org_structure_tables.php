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

        Schema::create('commissariats', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->foreignId('chief_employee_id')->nullable()->constrained('employees')->nullOnDelete();

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

            $table->foreignId('chief_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

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

            $table->foreignId('chief_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['name', 'commissariat_id']);
        });

        Schema::create('commissariats_positions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commissariat_id')
                ->constrained('commissariats')
                ->cascadeOnDelete();

            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();

            // вместимость должности (например 1.00 или 2.00)
            $table->decimal('rate_total', 3, 2)->default(1.00);

            $table->timestamps();

            // уникальность должности в рамках структуры
            $table->unique([
                'commissariat_id',
                'position_id',
            ], 'unique_position_structure');
        });

        Schema::create('employee_position_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('commissariat_position_id')
                ->constrained('commissariats_positions')
                ->cascadeOnDelete();

            // доля ставки
            $table->decimal('rate', 3, 2)->default(1.00);

            $table->boolean('is_independent')->default(false);

            $table->foreignId('employee_position_status_id')
                ->nullable()
                ->constrained('employee_position_statuses')
                ->nullOnDelete();

            $table->timestamps();

            // запрет дублирования одного сотрудника на одну должность
            $table->unique([
                'employee_id',
                'commissariat_position_id',
            ], 'employee_position_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
        Schema::dropIfExists('employee_position_statuses');
        Schema::dropIfExists('commissariats_positions');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('commissariats');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('chief_types');
        Schema::dropIfExists('position_types');
    }
};
