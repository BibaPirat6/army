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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3788d8');

            // Квота – минимальное число выполнений для всего подразделения
            $table->unsignedInteger('quota')->nullable();

            // К какому подразделению относится задача (строго одна из трёх связей)
            $table->foreignId('employee_position_id')
                ->nullable()
                ->constrained('employee_positions')
                ->nullOnDelete();

            // Период действия задачи (диапазон дат)
            $table->date('start_date');
            $table->date('end_date');

             $table->json('files')->nullable();

            $table->timestamps();
        });

        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('min_time_minutes');
            $table->unsignedInteger('avg_time_minutes');
            $table->unsignedInteger('max_time_minutes');
            $table->timestamps();
        });


        // какой сотрудник делает какую задачу
        // и в каком объёме
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            // Привязка к родительской задаче (чтобы знать контекст и подразделение)
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            // Какой сотрудник назначен
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Сколько всего итераций должен выполнить именно этот сотрудник
            $table->unsignedInteger('quota')->default(0);
            // Приоритет: чем меньше число, тем выше приоритет (1 – наивысший)
            $table->unsignedInteger('priority')->default(1);

            $table->unsignedInteger('completed_count')->default(0);

            $table->timestamps();

            $table->unique(['task_id', 'employee_id']);
        });

        // когда задача существует по дням
        Schema::create('task_instances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();

            $table->date('date');

            // сколько нужно выполнить в этот день
            $table->unsignedInteger('daily_quota')->default(0);

            $table->timestamps();

            $table->unique(['task_id', 'date']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
