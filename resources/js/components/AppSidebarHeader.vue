<script setup lang="ts">
import { Menu, PanelLeft } from '@lucide/vue';
import { useSidebar } from '@/components/ui/sidebar';

const { toggleSidebar } = useSidebar();

</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
        
            <button
    type="button"
    class="-ml-1 inline-flex h-9 w-9 items-center justify-center rounded-md hover:bg-accent"
    aria-label="Toggle menu"
    @click="toggleSidebar"
>
    <Menu class="h-5 w-5 md:hidden" />
    <PanelLeft class="hidden h-4 w-4 md:block" />
</button>
        </div>
    </header>
</template>
