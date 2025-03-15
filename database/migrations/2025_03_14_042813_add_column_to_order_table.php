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
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('order_date')->nullable()->change();
            $table->string('customer_name')->nullable()->change();
            $table->string('sales_representative')->nullable()->change();
            $table->string('project_name')->nullable()->change();
            $table->string('order_no')->nullable()->change();
            $table->dateTime('delivery_date')->nullable()->change();
            $table->dateTime('expected_inspection_month')->nullable()->change();
            $table->string('delivery_destination')->nullable()->change();
            $table->string('delivery_destination_phone')->nullable()->change();
            $table->string('delivery_destination_zip_code')->nullable()->change();
            $table->string('delivery_destination_address')->nullable()->change();
            $table->string('receiver_person_in_charge')->nullable()->change();
            $table->string('receiver_phone_number')->nullable()->change();
            $table->decimal('total', 20, 2)->nullable()->change();
            $table->string('exported_note')->nullable();
            $table->dateTime('obic_registered_at')->nullable();
            $table->dateTime('shipment_arranged_at')->nullable();
            $table->string('specified_invoice_exported_note')->nullable();
            $table->dropColumn('status');
            $table->enum('status', [
                'draft',
                'confirmed',
                'exported',
                'obic_registered',
                'shipment_arranged',
                'specified_invoice_exported',
            ])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('order_date')->nullable(false)->change();
            $table->string('customer_name')->nullable(false)->change();
            $table->string('sales_representative')->nullable(false)->change();
            $table->string('project_name')->nullable(false)->change();
            $table->string('order_no')->nullable(false)->change();
            $table->dateTime('delivery_date')->nullable(false)->change();
            $table->dateTime('expected_inspection_month')->nullable(false)->change();
            $table->string('delivery_destination')->nullable(false)->change();
            $table->string('delivery_destination_phone')->nullable(false)->change();
            $table->string('delivery_destination_zip_code')->nullable(false)->change();
            $table->string('delivery_destination_address')->nullable(false)->change();
            $table->string('receiver_person_in_charge')->nullable(false)->change();
            $table->string('receiver_phone_number')->nullable(false)->change();
            $table->decimal('total', 20, 2)->nullable(false)->change();
            $table->dropColumn('exported_note');
            $table->dropColumn('obic_registered_at');
            $table->dropColumn('shipment_arranged_at');
            $table->dropColumn('specified_invoice_exported_note');
            $table->dropColumn('status');
            $table->enum('status', [
                'draft',
                'confirmed',
                'exported',
                'obic_registered',
                'shipment_arranged',
                'specified_invoice_exported',
            ])->default('draft');
        });
    }
};
