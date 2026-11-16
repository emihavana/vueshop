<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';
import ProductCard from '@/components/ProductCard.vue';
import NavMain from '@/components/NavMain.vue';
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />


    <div class="flex flex-col gap-6">

            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
           <div v-if="products.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    @add-to-cart="addToCart"
                />
            </div>
            <div v-else class="text-center text-gray-500 dark:text-gray-400">
                No products available.
            </div>
        <div
            class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </div>
</template>
