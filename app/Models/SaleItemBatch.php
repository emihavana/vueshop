<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemBatch extends Model
{
// SaleItemBatch.php
protected $fillable = ['sale_item_id', 'stock_batch_id', 'quantity', 'unit_cost'];

public function stockBatch() { return $this->belongsTo(StockBatch::class); }
}