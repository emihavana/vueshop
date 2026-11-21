<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // Default range: first day of this month until today
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        // ---------- Totals ----------
        $revenue = (float) $this->saleLines($from, $to)
            ->selectRaw('COALESCE(SUM(sale_items.quantity * sale_items.price), 0) as v')
            ->value('v');

        $salesCount = (int) DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Cost of goods sold: the REAL batch costs recorded by FIFO checkout
        $cost = (float) $this->costLines($from, $to)
            ->selectRaw('COALESCE(SUM(sale_item_batches.quantity * sale_item_batches.unit_cost), 0) as v')
            ->value('v');

        $profit = $revenue - $cost;

        // ---------- Per day (revenue and profit) ----------
        $revenueByDay = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->pluck('revenue', 'day');

        $costByDay = $this->costLines($from, $to)
            ->selectRaw('DATE(sales.created_at) as day, SUM(sale_item_batches.quantity * sale_item_batches.unit_cost) as cost')
            ->groupByRaw('DATE(sales.created_at)')
            ->pluck('cost', 'day');

        // One entry per day, including days with no sales (as zero)
        $daily = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()))
            ->map(function ($date) use ($revenueByDay, $costByDay) {
                $key = $date->toDateString();
                $dayRevenue = (float) ($revenueByDay[$key] ?? 0);
                $dayCost = (float) ($costByDay[$key] ?? 0);

                return [
                    'date' => $key,
                    'label' => $date->format('d M'),
                    'revenue' => round($dayRevenue, 2),
                    'profit' => round($dayRevenue - $dayCost, 2),
                ];
            })
            ->values();

        // ---------- Best sellers ----------
        $topProducts = $this->saleLines($from, $to)
            ->selectRaw('sale_items.product_id, sale_items.name, SUM(sale_items.quantity) as qty, SUM(sale_items.quantity * sale_items.price) as revenue')
            ->groupBy('sale_items.product_id', 'sale_items.name')
            ->orderByDesc('qty')
            ->limit(8)
            ->get()
            ->map(fn ($r) => [
                'product_id' => $r->product_id,
                'name' => $r->name,
                'qty' => (int) $r->qty,
                'revenue' => (float) $r->revenue,
            ]);

        // ---------- Sales by category ----------
        $categories = $this->saleLines($from, $to)
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category, SUM(sale_items.quantity * sale_items.price) as revenue")
            ->groupByRaw("COALESCE(categories.name, 'Uncategorized')")
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => ['category' => $r->category, 'revenue' => (float) $r->revenue]);

        // ---------- Current stock (not tied to the date range) ----------
        $stockCost = (float) DB::table('stock_batches')
            ->selectRaw('COALESCE(SUM(quantity_remaining * unit_cost), 0) as v')
            ->value('v');

        $stockRetail = (float) DB::table('stock_batches')
            ->join('products', 'products.id', '=', 'stock_batches.product_id')
            ->selectRaw('COALESCE(SUM(stock_batches.quantity_remaining * products.price), 0) as v')
            ->value('v');

        $stockLevels = Product::query()
            ->select('id', 'name')
            ->withSum('batches as stock_qty', 'quantity_remaining')
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => (int) ($p->stock_qty ?? 0)]);

        $lowThreshold = 5;
        $lowStock = $stockLevels
            ->filter(fn ($p) => $p['stock'] <= $lowThreshold)
            ->sortBy('stock')
            ->values();

        return Inertia::render('admin/dashboard/Index', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'kpis' => [
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0,
                'sales_count' => $salesCount,
                'average_sale' => $salesCount > 0 ? round($revenue / $salesCount, 2) : 0,
            ],
            'stock' => [
                'cost_value' => round($stockCost, 2),
                'retail_value' => round($stockRetail, 2),
                'low_count' => $lowStock->where('stock', '>', 0)->count(),
                'out_count' => $lowStock->where('stock', 0)->count(),
            ],
            'daily' => $daily,
            'top_products' => $topProducts,
            'categories' => $categories,
            'low_stock' => $lowStock->take(10)->values(),
        ]);
    }

    // Sale lines of completed sales inside the range
    private function saleLines(Carbon $from, Carbon $to)
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to]);
    }

    // FIFO trail rows (batch, quantity, cost) of completed sales inside the range
    private function costLines(Carbon $from, Carbon $to)
    {
        return DB::table('sale_item_batches')
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_batches.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to]);
    }
}