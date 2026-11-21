<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ShoppingCart, X } from '@lucide/vue';
import { computed, ref } from 'vue';

defineOptions({
    layout: { breadcrumbs: [{ title: 'POS', href: '/pos' }] },
});

type Product = {
    id: number;
    name: string;
    price: number;
    image_url: string;
    category_id: number | null;
    stock: number;
};
type Line = { id: number; name: string; price: number; quantity: number; stock: number };

const props = defineProps<{
    products: Product[];
    categories: { id: number; name: string }[];
}>();

// ---- State ----
const sale = ref<Line[]>([]);
const search = ref('');
const selectedCategory = ref<number | null>(null);   // null = all products
const showCart = ref(true);
const processing = ref(false);
const error = ref('');

// ---- Computed ----
const filtered = computed(() =>
    props.products.filter(
        (p) =>
            p.name.toLowerCase().includes(search.value.toLowerCase()) &&
            (selectedCategory.value === null || p.category_id === selectedCategory.value),
    ),
);
const total = computed(() => sale.value.reduce((sum, l) => sum + l.price * l.quantity, 0));
const itemCount = computed(() => sale.value.reduce((sum, l) => sum + l.quantity, 0));
const canCharge = computed(() => sale.value.length > 0 && !processing.value);

// ---- Actions ----
function addProduct(p: Product) {
    const line = sale.value.find((l) => l.id === p.id);
    if (line) {
        if (line.quantity < p.stock) line.quantity++;
        return;
    }
    if (p.stock > 0) {
        sale.value.push({ id: p.id, name: p.name, price: p.price, quantity: 1, stock: p.stock });
    }
}

function changeQty(line: Line, delta: number) {
    const next = line.quantity + delta;
    if (next < 1 || next > line.stock) return;
    line.quantity = next;
}

function removeLine(id: number) {
    sale.value = sale.value.filter((l) => l.id !== id);
}

function clearSale() {
    sale.value = [];
    error.value = '';
}

function charge() {
    if (!canCharge.value) return;
    error.value = '';

    router.post(
        '/pos/checkout',
        {
            items: sale.value.map((l) => ({ product_id: l.id, quantity: l.quantity })),
        },
        {
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onError: (errors) => {
                error.value = String(Object.values(errors)[0]);
            },
        },
    );
}

function onImgError(event: Event) {
    const img = event.target as HTMLImageElement;
    if (!img.dataset.fallback) {
        img.dataset.fallback = '1';
        img.src = '/placeholder.svg';
    }
}
</script>

<template>
    <Head title="POS" />

    <div class="flex">
        <!-- LEFT: search, categories, product cards -->
        <div class="min-w-0 flex-1 p-6">
            <div class="mb-4 flex items-center gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search products..."
                    class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                />

                <button
                    type="button"
                    class="relative shrink-0 rounded-lg border border-gray-300 p-2 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                    @click="showCart = !showCart"
                >
                    <ShoppingCart class="h-6 w-6" />
                    <span
                        v-if="itemCount > 0"
                        class="absolute -right-2 -top-2 rounded-full bg-red-500 px-1.5 text-xs text-white"
                    >
                        {{ itemCount }}
                    </span>
                </button>
            </div>

            <!-- Category buttons -->
            <div v-if="categories.length" class="mb-4 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full border px-4 py-1 text-sm"
                    :class="selectedCategory === null ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 dark:border-gray-700'"
                    @click="selectedCategory = null"
                >
                    All
                </button>
                <button
                    v-for="c in categories"
                    :key="c.id"
                    type="button"
                    class="rounded-full border px-4 py-1 text-sm"
                    :class="selectedCategory === c.id ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 dark:border-gray-700'"
                    @click="selectedCategory = c.id"
                >
                    {{ c.name }}
                </button>
            </div>

            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(11rem, 1fr))">
                <button
                    v-for="p in filtered"
                    :key="p.id"
                    type="button"
                    :disabled="p.stock === 0"
                    @click="addProduct(p)"
                    class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-gray-100 text-left shadow-sm transition hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                >
                    <img :src="p.image_url" :alt="p.name" class="h-32 w-full object-cover" @error="onImgError" />
                    <div class="flex flex-1 flex-col justify-between p-3">
                        <p class="truncate font-semibold">{{ p.name }}</p>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="font-bold">KSH {{ p.price }}</span>
                            <span class="text-xs" :class="p.stock <= 5 ? 'text-red-500' : 'text-gray-500'">
                                {{ p.stock === 0 ? 'Out of stock' : `${p.stock} left` }}
                            </span>
                        </div>
                    </div>
                </button>
            </div>

            <p v-if="!filtered.length" class="mt-6 text-gray-500">No products match.</p>
        </div>

        <!-- RIGHT: current sale -->
        <aside
            v-if="showCart"
            class="fixed inset-y-0 right-0 z-40 w-full max-w-sm overflow-y-auto border-l border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-900 lg:static lg:z-auto lg:max-h-screen lg:w-96 lg:max-w-none lg:shrink-0 lg:self-start lg:shadow-none"
        >
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold">Current sale</h2>
                <div class="flex items-center gap-3">
                    <button v-if="sale.length" type="button" class="text-sm text-red-500" @click="clearSale">
                        Clear
                    </button>
                    <button type="button" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-gray-800" @click="showCart = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <p v-if="!sale.length" class="text-gray-500">Tap a product to add it.</p>

            <div v-for="line in sale" :key="line.id" class="mb-3 border-b border-gray-200 pb-3 dark:border-gray-700">
                <div class="flex justify-between">
                    <span class="font-medium">{{ line.name }}</span>
                    <span class="font-bold">KSH {{ line.price * line.quantity }}</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded border px-2" @click="changeQty(line, -1)">−</button>
                        <span>{{ line.quantity }}</span>
                        <button type="button" class="rounded border px-2" @click="changeQty(line, 1)">+</button>
                        <span class="text-gray-500">× KSH {{ line.price }}</span>
                    </div>
                    <button type="button" class="text-red-500" @click="removeLine(line.id)">Remove</button>
                </div>
            </div>

            <div class="mt-4 flex justify-between text-xl font-bold">
                <span>Total</span>
                <span>KSH {{ total }}</span>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-500">{{ error }}</p>

            <button
                type="button"
                :disabled="!canCharge"
                @click="charge"
                class="mt-4 w-full rounded-lg bg-green-600 px-4 py-3 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ processing ? 'Processing...' : `Charge KSH ${total}` }}
            </button>
        </aside>
    </div>
</template>