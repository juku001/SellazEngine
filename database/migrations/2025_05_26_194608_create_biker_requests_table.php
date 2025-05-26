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
        Schema::create('biker_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('biker_id');
            $table->unsignedBigInteger('super_dealer_id');
            $table->date('requested_at');
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned', 'completed'])->default('pending');
            $table->timestamps();


            
            $table->foreign('biker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('super_dealer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biker_requests');
    }
};
