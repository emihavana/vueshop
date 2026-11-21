<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sales report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        h2 {
            font-size: 12px;
            margin: 20px 0 6px;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 5px 4px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .r {
            text-align: right;
        }

        .summary td {
            border: 1px solid #ddd;
            padding: 8px;
            width: 25%;
        }

        .summary .label {
            font-size: 9px;
            color: #666;
        }

        .summary .value {
            font-size: 14px;
            font-weight: bold;
        }

        .neg {
            color: #dc2626;
        }

        .pos {
            color: #16a34a;
        }

        .void {
            color: #999;
        }
    </style>
</head>

<body>
    <h1>{{ config('app.name') }}: Sales report</h1>
    <p class="muted">
        {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
        · generated {{ $generatedAt->format('d M Y, H:i') }}
    </p>

    <h2>Summary</h2>
    <table class="summary">
        <tr>
            <td>
                <div class="label">Revenue</div>
                <div class="value">KSH {{ number_format($summary['revenue'], 2) }}</div>
                <div class="label">{{ $summary['count'] }} sales · avg {{ number_format($summary['average'], 2) }}</div>
            </td>
            <td>
                <div class="label">Cost of goods</div>
                <div class="value">KSH {{ number_format($summary['cost'], 2) }}</div>
            </td>
            <td>
                <div class="label">Profit</div>
                <div class="value {{ $summary['profit'] < 0 ? 'neg' : 'pos' }}">KSH {{ number_format($summary['profit'],
                    2) }}</div>
                <div class="label">{{ $summary['margin'] }}% margin</div>
            </td>
            <td>
                <div class="label">Sales</div>
                <div class="value">{{ $summary['count'] }}</div>
            </td>
        </tr>
    </table>

    <h2>Products sold</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="r">Units</th>
                <th class="r">Revenue (KSH)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td class="r">{{ $p->qty }}</td>
                <td class="r">{{ number_format($p->revenue, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="muted">No sales in this range.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>All sales</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Cashier</th>
                <th class="r">Items</th>
                <th>Payment</th>
                <th class="r">Total</th>
                <th class="r">Cost</th>
                <th class="r">Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $s)
            <tr class="{{ $s['status'] !== 'completed' ? 'void' : '' }}">
                <td>{{ $s['id'] }}</td>
                <td>{{ $s['date'] }}</td>
                <td>{{ $s['cashier'] }}</td>
                <td class="r">{{ $s['items'] }}</td>
                <td>{{ ucfirst($s['payment_method']) }}@if ($s['status'] !== 'completed') ({{ $s['status'] }})@endif
                </td>
                <td class="r">{{ number_format($s['total'], 2) }}</td>
                <td class="r">{{ number_format($s['cost'], 2) }}</td>
                <td class="r">{{ number_format($s['profit'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="muted">No sales in this range.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>