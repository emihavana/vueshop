<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Sales', href: '/admin/sales' }] },
});

const props = defineProps<{
    filters: { from: string; to: string };
    summary: { count: number; revenue: number; cost: number; profit: number; margin: number; average: number };
    sales: {
        data: {
            id: number;
            date: string;
            cashier: string;
            items: number;
            payment_method: string;
            status: string;
            total: number;
            cost: number;
            profit: number;
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

// ---------- Date range ----------
const from = ref(props.filters.from);
const to = ref(props.filters.to);

function apply() {
    router.get('/admin/sales', { from: from.value, to: to.value }, { preserveState: true, replace: true });
}

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
        to.value = fmt(new Date(now.getFullYear(), now.getMonth(), 0));
    }
    apply();
}

const money = (n: number) => 'KSH ' + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
</script>

<template>
    <Head title="Sales history" />

    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-2xl font-bold">Sales history</h1>

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

                <!-- A plain link, not Inertia's Link: the response is a file download -->
                <a
                    :href="`/admin/sales/pdf?from=${filters.from}&to=${filters.to}`"
                    class="rounded-lg bg-green-600 px-4 py-1.5 text-white hover:bg-green-700"
                >
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Revenue</p>
                <p class="mt-1 text-2xl font-bold">{{ money(summary.revenue) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Cost of goods</p>
                <p class="mt-1 text-2xl font-bold">{{ money(summary.cost) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Profit</p>
                <p class="mt-1 text-2xl font-bold" :class="summary.profit < 0 ? 'text-red-500' : 'text-green-600'">
                    {{ money(summary.profit) }}
                </p>
                <p class="text-xs text-gray-500">{{ summary.margin }}% margin</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-sm text-gray-500">Sales</p>
                <p class="mt-1 text-2xl font-bold">{{ summary.count }}</p>
                <p class="text-xs text-gray-500">average {{ money(summary.average) }}</p>
            </div>
        </div>

        <!-- Sales table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Cashier</th>
                        <th class="p-3 text-right">Items</th>
                        <th class="p-3">Payment</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3 text-right">Cost</th>
                        <th class="p-3 text-right">Profit</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in sales.data" :key="s.id" class="border-b" :class="s.status !== 'completed' ? 'opacity-50' : ''">
                        <td class="p-3">{{ s.id }}</td>
                        <td class="p-3 whitespace-nowrap">{{ s.date }}</td>
                        <td class="p-3">{{ s.cashier }}</td>
                        <td class="p-3 text-right">{{ s.items }}</td>
                        <td class="p-3 capitalize">
                            {{ s.payment_method }}
                            <span v-if="s.status !== 'completed'" class="ml-1 rounded bg-gray-200 px-1.5 py-0.5 text-xs dark:bg-gray-700">{{ s.status }}</span>
                        </td>
                        <td class="p-3 text-right">{{ money(s.total) }}</td>
                        <td class="p-3 text-right">{{ money(s.cost) }}</td>
                        <td class="p-3 text-right" :class="s.profit < 0 ? 'text-red-500' : 'text-green-600'">{{ money(s.profit) }}</td>
                        <td class="p-3 text-right">
                            <Link :href="`/sales/${s.id}`" class="text-blue-500 hover:underline">Receipt</Link>
                        </td>
                    </tr>
                    <tr v-if="!sales.data.length">
                        <td colspan="9" class="p-6 text-center text-gray-500">No sales in this range.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-wrap gap-2">
            <template v-for="link in sales.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded border px-3 py-1"
                    :class="{ 'bg-blue-500 text-white': link.active }"
                    v-html="link.label"
                />
                <span v-else class="rounded border px-3 py-1 opacity-40" v-html="link.label" />
            </template>
        </div>
    </div>
</template>