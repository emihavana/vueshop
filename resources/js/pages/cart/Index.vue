<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
function changeQuantity(productId: number, newQuantity: number) {
    if (newQuantity < 1) return; // Prevent quantity from being less than 1
    router.patch(`/cart/${productId}`, { quantity: newQuantity }, { preserveScroll: true });
}
function removeItem(productId: number) {
    router.delete(`/cart/${productId}`, { preserveScroll: true });
}

defineProps<{
    items: {
        id: number;
        name: string;
        price: number;
        image_url: string | null;
        quantity: number;
        subtotal: number;
    }[];
    total: number;
}>();
</script>
<template>
    <Head title="Your Cart" />

    <div class="mx-auto max-w-3xl p-6">
        <Link href="/" class="text-blue-500">← Continue shopping</Link>
        <h1 class="mt-4 mb-6 text-2xl font-bold">Your Cart</h1>

        <div v-if="items.length">
            <!-- One card per cart item -->
            <div
                v-for="item in items"
                :key="item.id"
                class="mb-4 flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-100 p-4 dark:border-gray-700 dark:bg-gray-800"
            >
                <img
                    :src="item.image_url"
                    :alt="item.name"
                    class="h-20 w-20 rounded object-cover"
                />

                <!-- Name, price, quantity buttons -->
                <div class="flex-1">
                    <h2 class="font-semibold">{{ item.name }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">KSH {{ item.price }}</p>

                    <div class="mt-2 flex items-center gap-2">
                        <button
                            type="button"
                            :disabled="item.quantity <= 1"
                            @click="changeQuantity(item.id, item.quantity - 1)"
                            class="rounded border px-2 disabled:opacity-40"
                        >−</button>
                        <span>{{ item.quantity }}</span>
                        <button
                            type="button"
                            :disabled="item.quantity >= 10"
                            @click="changeQuantity(item.id, item.quantity + 1)"
                            class="rounded border px-2 disabled:opacity-40"
                        >+</button>
                    </div>
                </div>

                <!-- Subtotal and remove -->
                <div class="text-right">
                    <span class="font-bold">KSH {{ item.subtotal }}</span>
                    <button
                        type="button"
                        @click="removeItem(item.id)"
                        class="mt-2 block text-sm text-red-500 hover:underline"
                    >Remove</button>
                </div>
            </div>
            <!-- end of the v-for card -->

            <div class="mt-6 flex items-center justify-between text-xl font-bold">
                <span>Total</span>
                <span>KSH {{ total }}</span>
            </div>
        </div>

        <p v-else class="text-gray-500">Your cart is empty.</p>
    </div>
</template>