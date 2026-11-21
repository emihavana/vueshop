<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    // Called when the cashier clicks Charge
    public function store(Request $request, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $sale = $checkout->process($request->user(), $data['items']);

        return redirect()->route('sales.show', $sale);
    }

    // The receipt
    public function show(Request $request, Sale $sale): Response
    {
        // A cashier sees only their own sales. An admin sees all.
        abort_unless(
            $sale->user_id === $request->user()->id || $request->user()->is_admin,
            403,
        );

        $sale->load(['items', 'user:id,name']);

        return Inertia::render('sales/Show', ['sale' => $sale]);
    }
}