<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Stock', href: '/admin/stock' }] },
});

defineProps<{
    batches: {
        data: {
            id: number;
            quantity_received: number;
            quantity_remaining: number;
            unit_cost: number;
            received_at: string;
            product: { id: number; name: string };
        }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();
</script>

<template>
    <Head title="Stock" />

    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Stock batches</h1>
            <Link href="/admin/stock/create" class="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                Receive stock
            </Link>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="border-b">
                <tr>
                    <th class="py-2">Received</th>
                    <th class="py-2">Product</th>
                    <th class="py-2">Remaining</th>
                    <th class="py-2">Received qty</th>
                    <th class="py-2">Unit cost</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="batch in batches.data" :key="batch.id" class="border-b">
                    <td class="py-2">{{ batch.received_at.slice(0, 10) }}</td>
                    <td class="py-2">{{ batch.product.name }}</td>
                    <td class="py-2">{{ batch.quantity_remaining }}</td>
                    <td class="py-2">{{ batch.quantity_received }}</td>
                    <td class="py-2">KSH {{ batch.unit_cost }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-6 flex gap-2">
            <template v-for="link in batches.links" :key="link.label">
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