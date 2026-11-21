<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: '/admin/dashboard' }] },
});

const props = defineProps<{
    filters: { from: string; to: string };
    kpis: {
        revenue: number;
        cost: number;
        profit: number;
        margin: number;
        sales_count: number;
        average_sale: number;
    };
    stock: { cost_value: number; retail_value: number; low_count: number; out_count: number };
    daily: { date: string; label: string; revenue: number; profit: number }[];
    top_products: { product_id: number; name: string; qty: number; revenue: number }[];
    categories: { category: string; revenue: number }[];
    low_stock: { id: number; name: string; stock: number }[];
}>();

// ---------- Date range ----------
const from = ref(props.filters.from);
const to = ref(props.filters.to);

function apply() {
    router.get(
        '/admin/dashboard',
        { from: from.value, to: to.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

// Local YYYY-MM-DD (toISOString would shift the day because of the timezone)
function fmt(d: Date) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function preset(name: 'today' | '7days' | 'month' | 'lastmonth') {
    const now = new Date();
    if (name === 'today') {
        from.value = to.value = fmt(now);
    } else if (name === '7days') {
        const start = new Date(now);
        start.setDate(now.getDate() - 6);
        from.value = fmt(start);
        to.value = fmt(now);
    } else if (name === 'month') {
        from.value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        to.value = fmt(now);
    } else {
        from.value = fmt(new Date(now.getFullYear(), now.getMonth() - 1, 1));
        to.value = fmt(new Date(now.getFullYear(), now.getMonth(), 0)); // day 0 = last day of previous month
    }
    apply();
}

const money = (n: number) => 'KSH ' + n.toLocaleString(undefined, { maximumFractionDigits: 2 });

// Short numbers for chart axes: 1200 becomes 1.2k
function short(n: number) {
    const abs = Math.abs(n);
    if (abs >= 1_000_000) return (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (abs >= 1_000) return (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'k';
    return String(Math.round(n));
}

// Round a number up to a "nice" axis maximum (1, 2, 5, 10 times a power of ten)
function niceMax(v: number) {
    if (v <= 0) return 10;
    const p = Math.pow(10, Math.floor(Math.log10(v)));
    const n = v / p;
    const step = n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10;
    return step * p;
}

// ---------- Line chart (revenue and profit per day), drawn with SVG ----------
const W = 800;
const H = 300;
const padL = 56;
const padR = 20;
const padT = 20;
const padB = 36;
const innerW = W - padL - padR;
const innerH = H - padT - padB;

const yMax = computed(() => {
    const m = Math.max(0, ...props.daily.flatMap((d) => [d.revenue, d.profit]));
    return niceMax(m);
});

// Profit can be negative, so the axis may need to go below zero
const yMin = computed(() => {
    const m = Math.min(0, ...props.daily.map((d) => d.profit));
    return m < 0 ? -niceMax(-m) : 0;
});

const xAt = (i: number) =>
    props.daily.length <= 1 ? padL + innerW / 2 : padL + (i * innerW) / (props.daily.length - 1);

const yAt = (v: number) => padT + ((yMax.value - v) / (yMax.value - yMin.value)) * innerH;

const revenueLine = computed(() => props.daily.map((d, i) => `${xAt(i)},${yAt(d.revenue)}`).join(' '));
const profitLine = computed(() => props.daily.map((d, i) => `${xAt(i)},${yAt(d.profit)}`).join(' '));

// Shaded area under the revenue line
const revenueArea = computed(() => {
    if (!props.daily.length) return '';
    const last = props.daily.length - 1;
    return `${revenueLine.value} ${xAt(last)},${yAt(0)} ${xAt(0)},${yAt(0)}`;
});

// 5 horizontal grid lines with value labels
const yTicks = computed(() =>
    [0, 1, 2, 3, 4].map((i) => {
        const value = yMin.value + ((yMax.value - yMin.value) * i) / 4;
        return { value, y: yAt(value) };
    }),
);

// At most about 8 date labels so they don't overlap
const labelStep = computed(() => Math.max(1, Math.ceil(props.daily.length / 8)));

const showDots = computed(() => props.daily.length <= 45);

// ---------- Best sellers (horizontal bars made of divs) ----------
const topMax = computed(() => Math.max(1, ...props.top_products.map((p) => p.qty)));

// ---------- Category donut ----------
const palette = ['#3b82f6', '#16a34a', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#ec4899', '#64748b'];
const R = 60;
const CIRC = 2 * Math.PI * R;

const categoryTotal = computed(() => props.categories.reduce((s, c) => s + c.revenue, 0));

const segments = computed(() => {
    let offset = 0;
    return props.categories.map((c, i) => {
        const len = categoryTotal.value > 0 ? (c.revenue / categoryTotal.value) * CIRC : 0;
        const seg = {
            category: c.category,
            revenue: c.revenue,
            percent: categoryTotal.value > 0 ? (c.revenue / categoryTotal.value) * 100 : 0,
            color: palette[i % palette.length],
            dash: `${len} ${CIRC - len}`,
            offset: -offset,
        };
        offset += len;
        return seg;
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6 p-6">
        <!-- Header and date range -->
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-2xl font-bold">Dashboard</h1>

            <div class="flex flex-wrap items-end gap-3">
                <div class="flex gap-2 text-sm">
                    <button type="button" class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-800" @click="preset('today')">Today</button>
                    <button type="button" class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-800" @click="preset('7days')">Last 7 days</button>
                    <button type="button" class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-800" @click="preset('month')">This month</button>
                    <button type="button" class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-800" @click="preset('lastmonth')">Last month</button>
                </div>

                <div>
                    <label class="block text-xs text-gray-500">From</label>
                    <input v-model="from" type="date" class="rounded border border-gray-300 px-2 py-1 dark:border-gray-700 dark:bg-gray-800" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500">To</label>
                    <input v-model="to" type="date" class="rounded border border-gray-300 px-2 py-1 dark:border-gray-700 dark:bg-gray-800" />
                </div>
                <button type="button" class="rounded-lg bg-blue-500 px-4 py-1.5 text-white hover:bg-blue-600" @click="apply">
                    Apply
                </button>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Revenue</p>
                <p class="mt-1 text-2xl font-bold">{{ money(kpis.revenue) }}</p>
                <p class="text-xs text-gray-500">{{ kpis.sales_count }} sales · avg {{ money(kpis.average_sale) }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Profit</p>
                <p class="mt-1 text-2xl font-bold" :class="kpis.profit < 0 ? 'text-red-500' : 'text-green-600'">
                    {{ money(kpis.profit) }}
                </p>
                <p class="text-xs text-gray-500">{{ kpis.margin }}% margin · cost {{ money(kpis.cost) }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Stock value (at cost)</p>
                <p class="mt-1 text-2xl font-bold">{{ money(stock.cost_value) }}</p>
                <p class="text-xs text-gray-500">Retail value {{ money(stock.retail_value) }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Stock alerts</p>
                <p class="mt-1 text-2xl font-bold">{{ stock.low_count + stock.out_count }}</p>
                <p class="text-xs text-gray-500">{{ stock.low_count }} low · {{ stock.out_count }} out of stock</p>
            </div>
        </div>

        <!-- Revenue and profit per day -->
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Revenue and profit per day</h2>
                <div class="flex gap-4 text-xs">
                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-blue-500"></span> Revenue</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-green-600"></span> Profit</span>
                </div>
            </div>

            <svg v-if="daily.length" :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full">
                <!-- grid lines and value labels -->
                <g v-for="t in yTicks" :key="t.y">
                    <line :x1="padL" :x2="W - padR" :y1="t.y" :y2="t.y" class="stroke-gray-200 dark:stroke-gray-700" stroke-width="1" />
                    <text :x="padL - 8" :y="t.y + 4" text-anchor="end" font-size="11" class="fill-gray-500">{{ short(t.value) }}</text>
                </g>

                <!-- zero line when profit can be negative -->
                <line v-if="yMin < 0" :x1="padL" :x2="W - padR" :y1="yAt(0)" :y2="yAt(0)" class="stroke-gray-400" stroke-width="1" stroke-dasharray="4 3" />

                <!-- revenue area, then the two lines -->
                <polygon :points="revenueArea" fill="#3b82f6" fill-opacity="0.12" />
                <polyline :points="revenueLine" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linejoin="round" />
                <polyline :points="profitLine" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linejoin="round" />

                <!-- dots (hover shows the exact figures) -->
                <g v-if="showDots">
                    <circle v-for="(d, i) in daily" :key="'r' + d.date" :cx="xAt(i)" :cy="yAt(d.revenue)" r="4" fill="#3b82f6">
                        <title>{{ d.label }}: revenue {{ money(d.revenue) }}</title>
                    </circle>
                    <circle v-for="(d, i) in daily" :key="'p' + d.date" :cx="xAt(i)" :cy="yAt(d.profit)" r="4" fill="#16a34a">
                        <title>{{ d.label }}: profit {{ money(d.profit) }}</title>
                    </circle>
                </g>

                <!-- date labels -->
                <g v-for="(d, i) in daily" :key="'x' + d.date">
                    <text v-if="i % labelStep === 0" :x="xAt(i)" :y="H - 12" text-anchor="middle" font-size="11" class="fill-gray-500">
                        {{ d.label }}
                    </text>
                </g>
            </svg>
            <p v-else class="text-gray-500">No data in this range.</p>
        </div>

        <!-- Best sellers and categories -->
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h2 class="mb-4 font-semibold">Best selling products (units)</h2>

                <div v-if="top_products.length" class="space-y-3">
                    <div v-for="p in top_products" :key="p.product_id">
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="truncate pr-2">{{ p.name }}</span>
                            <span class="font-semibold">{{ p.qty }}</span>
                        </div>
                        <div class="h-3 rounded bg-gray-100 dark:bg-gray-800">
                            <div class="h-3 rounded bg-blue-500" :style="{ width: (p.qty / topMax) * 100 + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-500">No sales in this range.</p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h2 class="mb-4 font-semibold">Sales by category</h2>

                <div v-if="categories.length" class="flex flex-wrap items-center gap-6">
                    <svg viewBox="0 0 160 160" class="h-44 w-44 shrink-0">
                        <g transform="rotate(-90 80 80)">
                            <circle
                                v-for="s in segments"
                                :key="s.category"
                                cx="80"
                                cy="80"
                                :r="R"
                                fill="none"
                                :stroke="s.color"
                                stroke-width="24"
                                :stroke-dasharray="s.dash"
                                :stroke-dashoffset="s.offset"
                            >
                                <title>{{ s.category }}: {{ money(s.revenue) }}</title>
                            </circle>
                        </g>
                        <text x="80" y="76" text-anchor="middle" font-size="11" class="fill-gray-500">Total</text>
                        <text x="80" y="94" text-anchor="middle" font-size="14" font-weight="bold" class="fill-current">
                            {{ short(categoryTotal) }}
                        </text>
                    </svg>

                    <ul class="min-w-40 flex-1 space-y-2 text-sm">
                        <li v-for="s in segments" :key="s.category" class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full" :style="{ background: s.color }"></span>
                                {{ s.category }}
                            </span>
                            <span class="text-gray-500">{{ s.percent.toFixed(0) }}% · {{ money(s.revenue) }}</span>
                        </li>
                    </ul>
                </div>
                <p v-else class="text-gray-500">No sales in this range.</p>
            </div>
        </div>

        <!-- Tables -->
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h2 class="mb-3 font-semibold">Best sellers</h2>
                <table v-if="top_products.length" class="w-full text-left text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="py-2">Product</th>
                            <th class="py-2 text-right">Units</th>
                            <th class="py-2 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in top_products" :key="p.product_id" class="border-b">
                            <td class="py-2">{{ p.name }}</td>
                            <td class="py-2 text-right">{{ p.qty }}</td>
                            <td class="py-2 text-right">{{ money(p.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-gray-500">No sales in this range.</p>
            </div>

            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h2 class="mb-3 font-semibold">Low stock</h2>
                <ul v-if="low_stock.length" class="text-sm">
                    <li v-for="p in low_stock" :key="p.id" class="flex justify-between border-b py-2">
                        <span>{{ p.name }}</span>
                        <span :class="p.stock === 0 ? 'font-semibold text-red-500' : 'text-amber-500'">
                            {{ p.stock === 0 ? 'Out of stock' : `${p.stock} left` }}
                        </span>
                    </li>
                </ul>
                <p v-else class="text-gray-500">Everything is well stocked.</p>
            </div>
        </div>
    </div>
</template>