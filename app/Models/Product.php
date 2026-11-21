<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
protected $fillable = ['name', 'description', 'price', 'image', 'category_id'];

public function category()
{
    return $this->belongsTo(Category::class);
}
    public function batches() { return $this->hasMany(StockBatch::class); }

// Current stock = total remaining across all batches
public function getStockAttribute(): int
{
    return (int) $this->batches()->sum('quantity_remaining');
}
  

protected $appends = ['image_url'];

protected function imageUrl(): Attribute
{
    return Attribute::get(function () {
        if (! $this->image) {
            return '/placeholder.svg';
        }

        if (str_starts_with($this->image, 'images/')) {
            return '/'.$this->image;
        }

        return '/storage/'.$this->image;
    });
}
}