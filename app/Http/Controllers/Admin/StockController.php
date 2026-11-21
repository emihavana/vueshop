<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    // List of deliveries, newest first
    public function index(): Response
    {
        return Inertia::render('admin/stock/Index', [
            'batches' => StockBatch::with('product:id,name')   // load each batch's product name
                ->latest('received_at')
                ->paginate(15),
        ]);
    }

    // Form to record a new delivery
    public function create(): Response
    {
        return Inertia::render('admin/stock/Create', [
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // Save the delivery as a new batch
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'received_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:received_at'],
        ]);

        StockBatch::create([
            'product_id' => $data['product_id'],
            'quantity_received' => $data['quantity'],
            'quantity_remaining' => $data['quantity'],   // nothing sold yet
            'unit_cost' => $data['unit_cost'],
            'received_at' => $data['received_at'],
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.stock.index');
    }
}