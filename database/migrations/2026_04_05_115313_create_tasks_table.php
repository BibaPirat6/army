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

            // Кто создал
            // $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

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

        // храним файлы для tasks
        // Schema::create('task_files', function (Blueprint $table) {
        //     $table->id();

        //     $table->foreignId('task_id')
        //         ->nullable()
        //         ->constrained('tasks')
        //         ->nullOnDelete();

        //     $table->string('original_name');
        //     $table->string('path');
        //     $table->string('mime_type')->nullable();
        //     $table->unsignedBigInteger('size')->default(0);
        //     $table->timestamps();

        //     $table->index('task_id');
        // });

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

            // рамки (не календарь!)
            // $table->date('start_date')->nullable();
            // $table->date('end_date')->nullable();

            // Статус и счётчик фактического выполнения (денормализация)
            // $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned');
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

        // для аналитики, надо ппц
        // Зачем: При отметке выполнения одной итерации создаётся запись, а completed_count в task_assignments увеличивается через Observer. Даёт аудит и фактические данные
        // Schema::create('task_completions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('task_assignment_id')->constrained('task_assignments')->cascadeOnDelete();
        //     $table->timestamp('completed_at');
        //     $table->unsignedInteger('duration_minutes')->nullable(); // реально потраченное время
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
