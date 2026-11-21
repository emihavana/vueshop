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
       Schema::create('sale_item_batches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_item_id')->constrained();
    $table->foreignId('stock_batch_id')->constrained();
    $table->unsignedInteger('quantity');
    $table->decimal('unit_cost', 10, 2);          // cost of that batch, for profit reports
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_batches');
    }
};