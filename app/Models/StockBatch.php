<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{

protected $fillable = ['product_id', 'quantity_received', 'quantity_remaining', 'unit_cost', 'received_at', 'expires_at'];

public function product() { return $this->belongsTo(Product::class); } 
//
}