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
        Schema::create('cash_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('biker_id');
            $table->unsignedBigInteger('super_dealer_id');
            $table->decimal('amount', 10, 2);
            $table->date('return_date');
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
        Schema::dropIfExists('cash_returns');
    }
};
