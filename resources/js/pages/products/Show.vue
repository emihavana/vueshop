<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({ product: Object })
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <Link href="/" class="text-blue-500">← Back to products</Link>

        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-gray-100 shadow-md dark:border-gray-700 dark:bg-gray-800">
            <img
                :src="product.image_url"
                :alt="product.name"
                class="h-72 w-full object-cover"
            />

            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ product.name }}</h1>
                <p class="mt-2 text-gray-700 dark:text-gray-300">{{ product.description }}</p>

                <div class="mt-6 flex items-center justify-between">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">KSH: {{ product.price }}</span>
                    <button type="button" class="rounded-lg bg-blue-400 px-4 py-2 text-white hover:bg-blue-500">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>