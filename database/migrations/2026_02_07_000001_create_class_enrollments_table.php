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
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->enum('status', ['active', 'withdrawn'])->default('active');

            $table->date('enrolled_at');
            $table->date('withdrawn_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index for enrollment history lookups (allows multiple enrollments per student/class)
            $table->index(['student_id', 'class_id', 'status']);

            // Indexes for common queries
            $table->index(['class_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
