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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('имя');
            $table->string('фамилия');
            $table->string('отчество')->nullable();
            // новые
            $table->boolean('участие_в_боевых_действиях')->default(false);
            $table->integer('дата_рождения')->nullable();
            $table->boolean('наличие_среднего_образования')->default(false);
            $table->boolean('наличие_высшего_образования')->default(false);
            $table->timestamps();
        });

        // дети для персон
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')
                ->constrained('persons')
                ->onDelete('cascade');
            $table->string('имя');
            $table->string('фамилия');  
            $table->string('отчество')->nullable();
            $table->date('дата_рождения')->nullable();
            $table->enum('пол', ['мужской', 'женский'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
