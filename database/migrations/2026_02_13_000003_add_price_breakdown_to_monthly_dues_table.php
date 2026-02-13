<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_dues', function (Blueprint $table) {
            $table->decimal('base_price', 8, 2)->nullable()->after('amount');
            $table->decimal('discount_applied', 8, 2)->nullable()->after('base_price');
            $table->boolean('price_override')->default(false)->after('discount_applied');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_dues', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'discount_applied', 'price_override']);
        });
    }
};
