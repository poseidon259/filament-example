<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', [
                \App\Enums\OrderStatus::Draft->value,
                \App\Enums\OrderStatus::Confirmed->value,
                \App\Enums\OrderStatus::PreparingToShip->value,
                \App\Enums\OrderStatus::Shipped->value,
                \App\Enums\OrderStatus::Unpaid->value,
                \App\Enums\OrderStatus::PaymentCompleted->value,
            ]);
            $table->dateTime('order_date');
            $table->string('customer_name');
            $table->string('sales_representative');
            $table->string('project_name');
            $table->string('order_no');
            $table->dateTime('delivery_date');
            $table->dateTime('expected_inspection_month');
            $table->string('delivery_destination');
            $table->string('delivery_destination_phone')->nullable();
            $table->string('delivery_destination_zip_code');
            $table->string('delivery_destination_address');
            $table->string('receiver_person_in_charge');
            $table->string('receiver_phone_number');
            $table->decimal('total', 20, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
