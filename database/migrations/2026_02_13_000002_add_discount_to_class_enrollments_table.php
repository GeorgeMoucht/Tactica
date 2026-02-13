<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0.00)->after('notes');
            $table->decimal('discount_amount', 8, 2)->default(0.00)->after('discount_percent');
            $table->string('discount_note', 255)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'discount_note']);
        });
    }
};
