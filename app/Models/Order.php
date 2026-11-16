<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Order.php
protected $fillable = ['user_id', 'total', 'status', 'phone', 'address'];

public function items() { return $this->hasMany(OrderItem::class); }
public function user()  { return $this->belongsTo(User::class); }
}