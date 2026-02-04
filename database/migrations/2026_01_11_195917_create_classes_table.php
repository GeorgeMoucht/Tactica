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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();

            $table->string('title', 180);
            $table->text('description')->nullable();

            $table->string('type', 20)->default('weekly'); // weekly|workshop
            $table->boolean('active')->default(true);

            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->time('starts_time')->nullable();
            $table->time('ends_time')->nullable();

            $table->unsignedInteger('capacity')->nullable();

            // Techer is a user (role teacher/admin)
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['teacher_id']);
            $table->index(['day_of_week']);
            $table->index(['type', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
