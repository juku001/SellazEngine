<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('super_dealer_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('super_dealer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('super_dealer_item_id')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('customer_mobile')->nullable();

            $table->decimal('amount', 15, 2)->default(0);
            $table->date('sales_date');

            $table->timestamps();

            // Foreign keys
            $table->foreign('super_dealer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('super_dealer_item_id')->references('id')->on('super_dealer_items')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
