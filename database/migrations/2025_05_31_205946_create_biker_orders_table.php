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
        Schema::create('biker_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_dealer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('biker_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['rejected', 'pending', 'active', 'complete', 'closed'])->default('pending');
            $table->decimal('total_amount', 15, 2);
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biker_orders');
    }
};
