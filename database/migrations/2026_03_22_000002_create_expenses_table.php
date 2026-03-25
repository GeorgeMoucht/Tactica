<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')
                  ->nullable()
                  ->constrained('expense_categories')
                  ->nullOnDelete();
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
