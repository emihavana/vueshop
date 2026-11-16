<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Products', href: '/admin/products' }],
    },
});

defineProps<{
    products: {
        data: { id: number; name: string; price: number }[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

function deleteProduct(id: number) {
    if (confirm('Delete this product?')) {
        router.delete(`/admin/products/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Products" />

    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Products</h1>
            <Link
                href="/admin/products/create"
                class="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
            >
                New product
            </Link>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="border-b">
                <tr>
                    <th class="py-2">Name</th>
                    <th class="py-2">Price</th>
                    <th class="py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products.data" :key="product.id" class="border-b">
                    <td class="py-2">{{ product.name }}</td>
                    <td class="py-2">KSH {{ product.price }}</td>
                    <td class="py-2 text-right">
                        <Link :href="`/admin/products/${product.id}/edit`" class="mr-4 text-blue-500">
                            Edit
                        </Link>
                        <button type="button" class="text-red-500" @click="deleteProduct(product.id)">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-6 flex gap-2">
            <template v-for="link in products.links" :key="link.label">
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