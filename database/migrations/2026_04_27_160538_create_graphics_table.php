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

        // когда сотрудник доступен
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('work_start')->nullable(); // null = выходной
            $table->time('work_end')->nullable();
            $table->json('breaks')->nullable(); // [{"start":"13:00","end":"14:00"}]
            $table->unsignedInteger('weekly_hours')->nullable();
            $table->unsignedInteger('daily_hours_target')->nullable();
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
