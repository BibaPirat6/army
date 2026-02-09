<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

            $table->unique(['name', 'position_type_id']);
        });

        Schema::create('commissariats', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
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
        });


        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('commissariat_id')
                ->constrained('commissariats')
                ->cascadeOnDelete();
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->cascadeOnDelete();
            $table->boolean('is_independent')->default(false);

            $table->enum('rate', [
                '0.25',
                '0.50',
                '0.75',
                '1.00',
                '1.25',
                '1.50',
                '1.75',
                '2.00'
            ])->default('1.00');


            $table->timestamps();

            $table->unique(
                ['employee_id', 'position_id', 'commissariat_id', 'department_id', "division_id"],
                'employee_pos_comm_dept_divis_unique'
            );


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('commissariats');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('position_types');
    }
};
