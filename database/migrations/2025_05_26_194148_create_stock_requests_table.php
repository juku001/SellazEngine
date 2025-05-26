<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * the records of the super dealer when request from the company. 
     */
    public function up(): void
    {
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('super_dealer_id'); // user_id
            $table->date('requested_at');
            $table->date('cheque_due_at'); // 7 days later
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'unpaid'])->default('pending');
            $table->timestamps();


             $table->foreign('super_dealer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};
