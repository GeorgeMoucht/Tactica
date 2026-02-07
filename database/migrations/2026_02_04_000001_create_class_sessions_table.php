<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->time('starts_time');
            $table->time('ends_time');
            $table->timestamps();

            $table->index('date');
            $table->unique(['class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
