<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    
protected $fillable = ['sale_id', 'product_id', 'name', 'price', 'quantity'];

public function sale()    { return $this->belongsTo(Sale::class); }
public function batches() { return $this->hasMany(SaleItemBatch::class); }
}