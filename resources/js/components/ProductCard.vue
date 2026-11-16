<script setup>
import { ShoppingCart } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'

defineProps({
    product: { type: Object, required: true },
})

const emit = defineEmits(['add-to-cart'])
</script>

<template>
    <div class="group flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-gray-100 shadow-md hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600">
        <div class="overflow-hidden">
            <img
                :src="product.image_url"
                :alt="product.name"
                class="h-48 w-full object-cover transition-transform duration-300 ease-in-out group-hover:scale-105"
            />
        </div>

        <div class="flex flex-1 flex-col justify-between p-4">
            <div>
                <Link :href="`/products/${product.id}`">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ product.name }}</h2>
                </Link>
                <p class="mb-4 text-xs text-gray-700 dark:text-gray-300">{{ product.description }}</p>
            </div>

            <div class="flex w-full items-center justify-between">
                <span class="text-xl font-bold text-gray-900 dark:text-white">KSH: {{ product.price }}</span>
                <button
                    type="button"
                    @click="emit('add-to-cart', product)"
                    class="flex items-center gap-2 rounded-lg bg-blue-400 px-4 py-2 text-white hover:bg-blue-500"
                >
                    <ShoppingCart class="h-4 w-4" />
                    Add
                </button>
            </div>
        </div>
    </div>
</template>