<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProductController extends Controller
{

public function index()
{
    return Inertia::render('Welcome', [
      'products' => Product::select('id', 'name', 'price', 'image', 'description')->get(),
    
    ]);
}
//show a single product by id
public function show(Product $product)
{
    return Inertia::render('products/Show', [
        'product' => $product,
    ]);
}}