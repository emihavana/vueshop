<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Categories', href: '/admin/categories' }] },
});

type Category = { id: number; name: string; products_count: number };

defineProps<{ categories: Category[] }>();

// ---- Create ----
const form = useForm({ name: '' });

function create() {
    form.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

// ---- Rename (inline) ----
const editingId = ref<number | null>(null);
const editName = ref('');

function startEdit(category: Category) {
    editingId.value = category.id;
    editName.value = category.name;
}

function saveEdit(id: number) {
    router.put(
        `/admin/categories/${id}`,
        { name: editName.value },
        { preserveScroll: true, onSuccess: () => (editingId.value = null) },
    );
}

// ---- Delete ----
function remove(category: Category) {
    if (confirm(`Delete "${category.name}"? Its products will become uncategorized.`)) {
        router.delete(`/admin/categories/${category.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Categories" />

    <div class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Categories</h1>

        <!-- Create -->
        <form @submit.prevent="create" class="mb-2 flex max-w-xl gap-3">
            <input
                v-model="form.name"
                type="text"
                placeholder="New category name"
                class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
            />
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 disabled:opacity-50"
            >
                Add
            </button>
        </form>
        <p v-if="form.errors.name" class="mb-4 text-sm text-red-500">{{ form.errors.name }}</p>

        <!-- Error from renaming -->
        <p v-if="$page.props.errors.name && editingId" class="mb-4 text-sm text-red-500">
            {{ $page.props.errors.name }}
        </p>

        <!-- List -->
        <table class="mt-4 w-full max-w-xl text-left text-sm">
            <thead class="border-b">
                <tr>
                    <th class="py-2">Name</th>
                    <th class="py-2">Products</th>
                    <th class="py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="category in categories" :key="category.id" class="border-b">
                    <td class="py-2">
                        <input
                            v-if="editingId === category.id"
                            v-model="editName"
                            type="text"
                            class="w-full rounded border border-gray-300 px-2 py-1 dark:border-gray-700 dark:bg-gray-800"
                            @keydown.enter.prevent="saveEdit(category.id)"
                        />
                        <span v-else>{{ category.name }}</span>
                    </td>
                    <td class="py-2">{{ category.products_count }}</td>
                    <td class="py-2 text-right">
                        <template v-if="editingId === category.id">
                            <button type="button" class="mr-3 text-blue-500" @click="saveEdit(category.id)">Save</button>
                            <button type="button" class="text-gray-500" @click="editingId = null">Cancel</button>
                        </template>
                        <template v-else>
                            <button type="button" class="mr-3 text-blue-500" @click="startEdit(category)">Rename</button>
                            <button type="button" class="text-red-500" @click="remove(category)">Delete</button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>

        <p v-if="!categories.length" class="mt-4 text-gray-500">No categories yet.</p>
    </div>
</template>