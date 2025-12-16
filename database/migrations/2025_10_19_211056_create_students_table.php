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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->date('birthdate');
            $table->string('email', 180)->nullable()->index();
            $table->string('phone', 60)->nullable();
            $table->enum('preferred_contact', ['email', 'sms', 'phone'])->nullable();
            $table->text('contact_notes')->nullable();
            $table->json('address')->nullable();
            $table->string('level', 20)->nullable(); // beginner|intermediate|advanced
            $table->json('interests')->nullable(); // ["painting", "ceramics", "drawing"]
            $table->text('notes')->nullable();
            $table->text('medical_note')->nullable();
            $table->boolean('consent_media')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
