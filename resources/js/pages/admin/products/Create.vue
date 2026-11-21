<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProductForm from '@/components/ProductForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: '/admin/products' },
            { title: 'Create', href: '#' },
        ],
    },
});

defineProps<{
    categories: { id: number; name: string }[];
}>();
</script>

<template>
    <Head title="New product" />

    <div class="p-6">
        <h1 class="mb-6 text-2xl font-bold">New product</h1>
        <ProductForm :categories="categories" />
    </div>
</template>