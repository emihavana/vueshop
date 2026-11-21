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
    {Schema::create('sales', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();                 // the cashier
    $table->decimal('total', 10, 2);
    $table->string('payment_method')->default('cash');           // cash now, mpesa later
    $table->string('status')->default('completed');              // completed, pending, failed
    $table->decimal('amount_tendered', 10, 2)->nullable();       // cash given
    $table->decimal('change_given', 10, 2)->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};