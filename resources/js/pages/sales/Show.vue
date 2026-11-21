<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Receipt', href: '#' }] },
});

defineProps<{
    sale: {
        id: number;
        total: number;
        payment_method: string;
        created_at: string;
        user: { name: string };
        items: { id: number; name: string; price: number; quantity: number }[];
    };
}>();

function printReceipt() {
    window.print();
}
</script>

<template>
    <Head :title="`Receipt #${sale.id}`" />

    <div class="mx-auto max-w-md p-6">
        <div class="rounded-lg border border-gray-200 p-6 dark:border-gray-700">
            <h1 class="text-center text-xl font-bold">Receipt #{{ sale.id }}</h1>
            <p class="text-center text-sm text-gray-500">
                {{ new Date(sale.created_at).toLocaleString() }} · {{ sale.user.name }}
            </p>

            <div class="my-4 border-t border-dashed pt-4">
                <div v-for="item in sale.items" :key="item.id" class="mb-2 flex justify-between text-sm">
                    <span>{{ item.quantity }} × {{ item.name }}</span>
                    <span>KSH {{ item.price * item.quantity }}</span>
                </div>
            </div>

            <div class="space-y-1 border-t border-dashed pt-4">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span>KSH {{ sale.total }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Paid by</span>
                    <span class="capitalize">{{ sale.payment_method }}</span>
                </div>
            </div>
        </div>

        <div class="mt-4 flex gap-3 print:hidden">
            <Link href="/pos" class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-center text-white hover:bg-green-700">
                New sale
            </Link>
            <button type="button" class="rounded-lg border px-4 py-2" @click="printReceipt">Print</button>
        </div>
    </div>
</template>