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
        Schema::create('biker_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('biker_order_items');
            $table->integer('quantity_returned');
            $table->string('reason')->nullable();
            $table->timestamp('returned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biker_returns');
    }
};
