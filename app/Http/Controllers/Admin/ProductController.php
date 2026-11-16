<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    // List products (10 per page)
    public function index(): Response
    {
        return Inertia::render('admin/products/Index', [
            'products' => Product::latest()->paginate(10),
        ]);
    }

    // Show the empty form
    public function create(): Response
    {
        return Inertia::render('admin/products/Create');
    }

    // Save a new product
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],   // max 2MB
        ]);

        if ($request->hasFile('image')) {
            // Saves the file in storage/app/public/products and returns "products/abc123.jpg"
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index');
    }

    // Show the form filled with an existing product
    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/Edit', [
            'product' => $product,
        ]);
    }

    // Save changes to an existing product
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // Remove the old uploaded file (the exists() check protects your old public/images files)
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']);   // no new file: keep the current image
        }

        $product->update($data);

        return redirect()->route('admin.products.index');
    }

    // Delete a product
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index');
    }
}