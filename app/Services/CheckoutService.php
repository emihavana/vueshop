<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{product_id:int, quantity:int}>  $items
     */
    public function process(User $user, array $items, string $paymentMethod = 'cash'): Sale
    {
        // TRANSACTION: everything inside succeeds together, or nothing is saved.
        return DB::transaction(function () use ($user, $items, $paymentMethod) {

            // Merge duplicate lines and sort by product id (keeps row locking in a stable order)
            $wanted = collect($items)
                ->groupBy('product_id')
                ->map(fn ($rows) => (int) $rows->sum('quantity'))
                ->sortKeys();

            // Real prices come from the database, never from the browser
            $products = Product::whereIn('id', $wanted->keys())->get()->keyBy('id');

            $total = 0;
            foreach ($wanted as $productId => $qty) {
                $total += $products[$productId]->price * $qty;
            }

            $sale = Sale::create([
                'user_id' => $user->id,
                'total' => round($total, 2),
                'payment_method' => $paymentMethod,
                'status' => 'completed',
            ]);

            foreach ($wanted as $productId => $qty) {
                $product = $products[$productId];

                // FIFO: oldest delivery first.
                // lockForUpdate() makes other cashiers wait, so the same last unit
                // can't be sold twice at the same moment.
                $batches = StockBatch::where('product_id', $productId)
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('received_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $available = (int) $batches->sum('quantity_remaining');

                if ($available < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for {$product->name}. Only {$available} left.",
                    ]);
                }

                // Copy name and price so old receipts never change
                $item = $sale->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                ]);

                // Take from the oldest batch, then the next, until we have enough
                $needed = $qty;
                foreach ($batches as $batch) {
                    if ($needed === 0) {
                        break;
                    }

                    $take = min($needed, $batch->quantity_remaining);

                    $batch->decrement('quantity_remaining', $take);

                    // FIFO trail: which batch, how many units, at what cost
                    $item->batches()->create([
                        'stock_batch_id' => $batch->id,
                        'quantity' => $take,
                        'unit_cost' => $batch->unit_cost,
                    ]);

                    $needed -= $take;
                }
            }

            return $sale;
        });
    }
}