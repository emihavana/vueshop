<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('pos/Index', [
            'products' => Product::query()
                ->select('id', 'name', 'price', 'image', 'category_id')
                ->withSum('batches as stock_qty', 'quantity_remaining')
                ->orderBy('name')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'image_url' => $p->image_url,
                    'category_id' => $p->category_id,
                    'stock' => (int) ($p->stock_qty ?? 0),
                ]),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }
}