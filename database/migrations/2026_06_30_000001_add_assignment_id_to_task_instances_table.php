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
        if (! Schema::hasColumn('task_instances', 'assignment_id')) {
            Schema::table('task_instances', function (Blueprint $table) {
                $table->foreignId('assignment_id')
                    ->nullable()
                    ->after('task_id')
                    ->constrained('task_assignments')
                    ->nullOnDelete();
            });
        }

        Schema::table('task_instances', function (Blueprint $table) {
            $table->unique(['assignment_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_instances', function (Blueprint $table) {
            $table->dropUnique(['assignment_id', 'date']);
        });

        if (Schema::hasColumn('task_instances', 'assignment_id')) {
            Schema::table('task_instances', function (Blueprint $table) {
                $table->dropForeign(['assignment_id']);
                $table->dropColumn('assignment_id');
            });
        }
    }
};
