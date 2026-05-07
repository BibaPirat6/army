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
        // Schema::create('work_schedule_templates', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->json('pattern');
        //     // Пример: {"monday":{"work_start":"09:00","work_end":"18:00","breaks":[{"start":"13:00","end":"14:00"}]}, ...}
        //     $table->boolean('is_default')->default(false);
        //     $table->timestamps();
        // });

        // когда сотрудник доступен
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('work_start')->nullable(); // null = выходной
            $table->time('work_end')->nullable();
            $table->json('breaks')->nullable(); // [{"start":"13:00","end":"14:00"}]
            $table->unsignedInteger('weekly_hours')->nullable();
            $table->enum('type', ['рабочий_день', 'выходной'])->default('рабочий_день');
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_files');
    }
};
