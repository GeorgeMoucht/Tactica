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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email', 180)->nullable()->index();
            $table->string('phone', 60)->nullable();
            $table->json('address')->nullable(); // {street,city,zip}
            $table->string('preferred_contact', 20)->nullable(); // email|sms|phone
            $table->text('notes')->nullable();
            $table->boolean('newsletter_consent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
