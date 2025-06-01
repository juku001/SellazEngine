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
        Schema::create('super_dealer_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('super_dealer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'rejected', 'approved', 'fulfilled'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('date_to_pay')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_dealer_orders');
    }
};
