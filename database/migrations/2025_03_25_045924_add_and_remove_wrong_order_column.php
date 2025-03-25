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
            $table->dropColumn('exported_note');
            $table->dropColumn('specified_invoice_exported_note');
            $table->dateTime('exported_at')->nullable();
            $table->dateTime('specified_invoice_exported_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('exported_note')->nullable();
            $table->text('specified_invoice_exported_note')->nullable();
            $table->dropColumn('exported_at');
            $table->dropColumn('specified_invoice_exported_at');
        });
    }
};
