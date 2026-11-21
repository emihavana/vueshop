<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Boxes, LayoutGrid, Package, Receipt, ShoppingCart, Tags } from '@lucide/vue';
import { computed, onUnmounted } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();

const isAdmin = computed(() => Boolean((page.props.auth.user as { is_admin?: boolean } | null)?.is_admin));

const mainNavItems = computed<NavItem[]>(() => [
    ...(isAdmin.value ? [{ title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid }] : []),
    { title: 'POS', href: '/pos', icon: ShoppingCart },
    ...(isAdmin.value
        ? [
              { title: 'Products', href: '/admin/products', icon: Package },
              { title: 'Categories', href: '/admin/categories', icon: Tags },
              { title: 'Stock', href: '/admin/stock', icon: Boxes },
              { title: 'SalesReports', href: '/admin/sales', icon: Receipt },
          ]
        : []),
]);

const footerNavItems: NavItem[] = [];

// ---- Close the phone menu as soon as a link is tapped ----
const { setOpenMobile } = useSidebar();

// router.on returns a function that removes the listener again
const stopListening = router.on('start', () => setOpenMobile(false));
onUnmounted(stopListening);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/pos">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>