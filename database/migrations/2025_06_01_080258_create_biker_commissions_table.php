<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('biker_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('biker_orders')->onDelete('cascade');
            $table->foreignId('biker_id')->constrained('users')->onDelete('cascade');
            $table->decimal('sales_amount', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biker_commissions');
    }
};
