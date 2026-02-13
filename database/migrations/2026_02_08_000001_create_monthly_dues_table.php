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
        Schema::create('monthly_dues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->foreignId('enrollment_id')
                ->nullable()
                ->constrained('class_enrollments')
                ->nullOnDelete();

            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');

            $table->decimal('amount', 8, 2);

            $table->enum('status', ['pending', 'paid', 'waived', 'cancelled'])
                ->default('pending');

            $table->foreignId('student_purchase_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Unique constraint: one due per student/class/period
            $table->unique(
                ['student_id', 'class_id', 'period_year', 'period_month'],
                'monthly_dues_unique'
            );

            // Index for outstanding balance queries
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_dues');
    }
};
