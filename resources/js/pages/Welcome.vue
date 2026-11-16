<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShoppingCart } from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';
import ProductCard from '@/components/ProductCard.vue';


const page = usePage();
const cartCount = computed(() => Number(page.props.cartCount ?? 0));

defineProps({
    products: Array,
});

function addToCart(product: { id: number }) {
    router.post('/cart', { product_id: product.id }, { preserveScroll: true });
}
</script>
<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <!-- Top bar -->
        <header class="w-full border-b border-black/10 dark:border-white/10">
            <nav
                class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4"
            >
                <Link href="/" class="text-lg font-semibold">My Shop</Link>

                <div class="flex items-center gap-3 text-sm">
    <Link
        v-if="$page.props.auth.user"
        :href="dashboard()"
        class="rounded-sm border border-[#19140035] px-5 py-1.5 hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
    >
        Dashboard
    </Link>

    <template v-else>
        <Link
            :href="login()"
            class="rounded-sm border border-transparent px-5 py-1.5 hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
        >
            Log in
        </Link>
        <Link
            :href="register()"
            class="rounded-sm border border-[#19140035] px-5 py-1.5 hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
        >
            Register
        </Link>
    </template>

    <!-- Cart: visible to everyone -->
    <Link href="/cart" class="relative">
        <ShoppingCart class="h-6 w-6" />
        <span
            v-if="cartCount > 0"
            class="absolute -right-2 -top-2 rounded-full bg-red-500 px-1.5 text-xs text-white"
        >
            {{ cartCount }}
        </span>
    </Link>
</div>
            </nav>
        </header>

        <!-- Main content -->
        <main>
            <!-- rendering the  products using card-->
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
        </main>
    </div>
</template>
