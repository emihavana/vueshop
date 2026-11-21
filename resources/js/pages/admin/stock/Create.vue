<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Stock', href: '/admin/stock' },
            { title: 'Receive', href: '#' },
        ],
    },
});

defineProps<{
    products: { id: number; name: string }[];
}>();

const form = useForm({
    product_id: '',
    quantity: '',
    unit_cost: '',
    received_at: new Date().toISOString().slice(0, 10),   // today
    expires_at: '',
});

function submit() {
    form.post('/admin/stock');
}
</script>

<template>
    <Head title="Receive stock" />

    <div class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Receive stock</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">Product</label>
                <select v-model="form.product_id" class="w-full rounded border px-3 py-2 dark:bg-gray-800">
                    <option value="" disabled>Select a product</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-500">{{ form.errors.product_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Quantity received</label>
                <input v-model="form.quantity" type="number" min="1" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
                <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-500">{{ form.errors.quantity }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Cost per unit (KSH)</label>
                <input v-model="form.unit_cost" type="number" step="0.01" min="0" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
                <p v-if="form.errors.unit_cost" class="mt-1 text-sm text-red-500">{{ form.errors.unit_cost }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Date received</label>
                <input v-model="form.received_at" type="date" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
                <p v-if="form.errors.received_at" class="mt-1 text-sm text-red-500">{{ form.errors.received_at }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Expiry date (optional)</label>
                <input v-model="form.expires_at" type="date" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
                <p v-if="form.errors.expires_at" class="mt-1 text-sm text-red-500">{{ form.errors.expires_at }}</p>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 disabled:opacity-50"
                >
                    Save batch
                </button>
                <Link href="/admin/stock" class="text-gray-600 dark:text-gray-300">Cancel</Link>
            </div>
        </form>
    </div>
</template>