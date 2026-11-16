<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\Product;

class CartController extends Controller
{
    public function store (Request $request):RedirectResponse
    {
       $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);
        
        $cart = session('cart', []);
        $id = $data['product_id'];
        $cart[$id] = ($cart[$id] ?? 0) + 1;

        session(['cart' => $cart]);
        

        return redirect()->back()->with('success', 'Product added to cart!');
    }
    public function index(Request $request)
    {
        $cart = session('cart', []);
        $items=Product::whereIn('id', array_keys($cart))->get()
        ->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image_url' => $product->image_url,
            'quantity' => $cart[$product->id],
            'subtotal' => $product->price * $cart[$product->id],
        ])
        ->values();
        return Inertia::render('cart/Index', [
            'items' => $items,
      'total' => $items->sum('subtotal'),
        ]);
}

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = session('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id] = $data['quantity'];
            session(['cart' => $cart]);
        }

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return redirect()->back()->with('success', 'Product removed from cart!');
    }
}