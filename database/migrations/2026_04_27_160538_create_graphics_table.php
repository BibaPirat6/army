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
        // шаблон для назначения сотруднику для быстроты
        // Назначение: Хранит повторно используемые шаблоны графиков (5/2, 2/2, сутки/трое и т.д.). При назначении шаблона сотруднику генерируются записи в work_days на год (или произвольный период). Изменение шаблона не меняет уже сгенерированные дни – их можно корректировать вручную.
        Schema::create('work_schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('pattern');
            // Пример: {"monday":{"work_start":"09:00","work_end":"18:00","breaks":[{"start":"13:00","end":"14:00"}]}, ...}
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // график работы сотрудника на день
        // Назначение: Физический календарь сотрудника. Каждая строка — один день. Позволяет задать точные интервалы работы и произвольные перерывы (обед, технологические перерывы). Менеджер может корректировать любой день (больничный, отпуск, изменение смены). Именно эта таблица используется алгоритмом расчёта загрузки, чтобы знать доступные 5‑минутные слоты.
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('work_start')->nullable(); // null = выходной
            $table->time('work_end')->nullable();
            $table->json('breaks')->nullable(); // [{"start":"13:00","end":"14:00"}]
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });

        // Назначение: Центральная таблица задач. Содержит описание, цвет, общую квоту для подразделения и привязку к организационной единице (комиссариат, отдел, отделение). Диапазон дат определяет, на какие дни будут автоматически сгенерированы экземпляры (task_instances).
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3788d8');

            // Квота – минимальное число выполнений для всего подразделения
            $table->unsignedInteger('quota')->nullable();

            // К какому подразделению относится задача (строго одна из трёх связей)
            $table->foreignId('commissariat_id')->nullable()->constrained('commissariats')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();

            // Кто создал
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Период действия задачи (диапазон дат)
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null – задача на один день

            $table->timestamps();
        });

        // храним файлы для tasks
         Schema::create('task_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('original_name');      
            $table->string('path');               
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        // Назначение: Декомпозиция задачи на подзадачи. Каждая хранит три временные оценки (оптимистичную, среднюю, пессимистичную) в минутах. Эти данные нужны алгоритму расчёта загрузки: они суммируются в модели Task (total_min_time, total_avg_time, total_max_time) и используются как стоимость одной итерации выполнения задачи. На основании квоты и этих чисел вычисляется общее требуемое время.
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('min_time_minutes');
            $table->unsignedInteger('avg_time_minutes');
            $table->unsignedInteger('max_time_minutes');
            $table->timestamps();
        });

        // Назначение: Ручное распределение задач начальником. Начальник выбирает задачу (уже привязанную к его подразделению), сотрудника из этого же подразделения, задаёт персональную квоту, приоритет и сроки. При сохранении система проверяет, что сумма квот всех назначений по задаче не превышает общую квоту tasks.quota. На основе этих записей симулятор (WorkloadSimulator) строит прогноз загрузки сотрудника.
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            // Привязка к родительской задаче (чтобы знать контекст и подразделение)
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            // Какой сотрудник назначен
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Сколько всего итераций должен выполнить именно этот сотрудник
            $table->unsignedInteger('quota')->default(1);
            // Приоритет: чем меньше число, тем выше приоритет (1 – наивысший)
            $table->unsignedInteger('priority')->default(1);

            // Период, в который сотрудник должен выполнить свою квоту
            $table->date('start_date');
            $table->date('end_date');

            // Статус и счётчик фактического выполнения (денормализация)
            $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned');
            $table->unsignedInteger('completed_count')->default(0);

            $table->timestamps();
        });

        // для аналитики, надо ппц
        // Зачем: При отметке выполнения одной итерации создаётся запись, а completed_count в task_assignments увеличивается через Observer. Даёт аудит и фактические данные
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->constrained('task_assignments')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->unsignedInteger('duration_minutes')->nullable(); // реально потраченное время
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('task_files');
         Schema::dropIfExists('task_files');
         Schema::dropIfExists('task_files');
         Schema::dropIfExists('task_files');
         Schema::dropIfExists('task_files');
        Schema::dropIfExists('graphics');
    }
};
