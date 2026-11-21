<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesReportController extends Controller
{
    // The page: summary cards + paginated list of sales in the range
    public function index(Request $request): Response
    {
        [$from, $to] = $this->range($request);

        $sales = $this->salesQuery($from, $to)
            ->latest('sales.created_at')
            ->paginate(15)
            ->withQueryString()                       // keep ?from=&to= in the page links
            ->through(fn ($sale) => $this->row($sale));

        return Inertia::render('admin/sales/Index', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => $this->summary($from, $to),
            'sales' => $sales,
        ]);
    }

    // The PDF download for the same range (no pagination: every sale)
    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sales = $this->salesQuery($from, $to)
            ->latest('sales.created_at')
            ->get()
            ->map(fn ($sale) => $this->row($sale));

        // Units and revenue per product (completed sales only)
        $products = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('sale_items.name, SUM(sale_items.quantity) as qty, SUM(sale_items.quantity * sale_items.price) as revenue')
            ->groupBy('sale_items.product_id', 'sale_items.name')
            ->orderByDesc('qty')
            ->get();

        $pdf = Pdf::loadView('pdf.sales', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->summary($from, $to),
            'sales' => $sales,
            'products' => $products,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download("sales-{$from->toDateString()}-to-{$to->toDateString()}.pdf");
    }

    // ---------- helpers ----------

    // Read ?from= and ?to=, defaulting to this month so far
    private function range(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        return [$from, $to];
    }

    // Sales in the range, each with its real cost (the FIFO batches it used)
    private function salesQuery(Carbon $from, Carbon $to)
    {
        return Sale::query()
            ->select('sales.*')
            ->addSelect([
                // A subquery that adds a "cost" column to every sale
                'cost' => DB::table('sale_item_batches')
                    ->join('sale_items', 'sale_items.id', '=', 'sale_item_batches.sale_item_id')
                    ->whereColumn('sale_items.sale_id', 'sales.id')
                    ->selectRaw('COALESCE(SUM(sale_item_batches.quantity * sale_item_batches.unit_cost), 0)'),
            ])
            ->with('user:id,name')
            ->withCount('items')
            ->whereBetween('sales.created_at', [$from, $to]);
    }

    // Turn a sale into a plain array for Vue and the PDF
    private function row(Sale $sale): array
    {
        $cost = (float) $sale->cost;
        $total = (float) $sale->total;

        return [
            'id' => $sale->id,
            'date' => $sale->created_at->format('d M Y, H:i'),   // already in the app timezone
            'cashier' => $sale->user?->name ?? '-',
            'items' => $sale->items_count,
            'payment_method' => $sale->payment_method,
            'status' => $sale->status,
            'total' => $total,
            'cost' => round($cost, 2),
            'profit' => round($total - $cost, 2),
        ];
    }

    // Totals for the range (completed sales only)
    private function summary(Carbon $from, Carbon $to): array
    {
        $completed = fn () => DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to]);

        $count = (int) $completed()->count();
        $revenue = (float) $completed()->sum('total');

        $cost = (float) DB::table('sale_item_batches')
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_batches.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(sale_item_batches.quantity * sale_item_batches.unit_cost), 0) as v')
            ->value('v');

        $profit = $revenue - $cost;

        return [
            'count' => $count,
            'revenue' => round($revenue, 2),
            'cost' => round($cost, 2),
            'profit' => round($profit, 2),
            'margin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0,
            'average' => $count > 0 ? round($revenue / $count, 2) : 0,
        ];
    }
}