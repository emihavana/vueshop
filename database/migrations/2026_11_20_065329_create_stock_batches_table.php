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
        Schema::create('stock_batches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained();
    $table->unsignedInteger('quantity_received');
    $table->unsignedInteger('quantity_remaining');   // goes down as we sell
    $table->decimal('unit_cost', 10, 2);             // what you paid per unit
    $table->date('received_at');                     // FIFO order
    $table->date('expires_at')->nullable();
    $table->timestamps();

    $table->index(['product_id', 'received_at']);    // fast "oldest first" lookups
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};