<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProductForm from '@/components/ProductForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: '/admin/products' },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    product: {
        id: number;
        name: string;
        category_id: number | null;
        description: string | null;
        price: number;
        image_url: string;
    };
    categories: { id: number; name: string }[];
}>();
</script>

<template>
    <Head title="Edit product" />

    <div class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Edit product</h1>
        <ProductForm :product="product" :categories="categories" />
    </div>
</template>