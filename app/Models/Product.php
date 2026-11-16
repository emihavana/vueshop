<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
public $fillable = [
        'name',
        'description',
        'price',
        'image',
    ];
  

protected $appends = ['image_url'];

protected function imageUrl(): Attribute
{
    return Attribute::get(function () {
        if (! $this->image) {
            return '/placeholder.png';
        }

        if (str_starts_with($this->image, 'images/')) {
            return '/'.$this->image;
        }

        return '/storage/'.$this->image;
    });
}
}