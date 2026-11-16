<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    product?: {
        id: number;
        name: string;
        description: string | null;
        price: number;
        image_url: string;
    };
}>();

const isEdit = !!props.product;

const form = useForm({
    _method: isEdit ? 'put' : 'post',
    name: props.product?.name ?? '',
    description: props.product?.description ?? '',
    price: props.product?.price ?? '',
    image: null as File | null,
});

function submit() {
    const url = isEdit ? `/admin/products/${props.product!.id}` : '/admin/products';
    form.post(url, { forceFormData: true });
}

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.image = input.files?.[0] ?? null;
}
</script>

<template>
    <form @submit.prevent="submit" class="max-w-xl space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium">Name</label>
            <input v-model="form.name" type="text" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Description</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded border px-3 py-2 dark:bg-gray-800"></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Price (KSH)</label>
            <input v-model="form.price" type="number" step="0.01" min="0" class="w-full rounded border px-3 py-2 dark:bg-gray-800" />
            <p v-if="form.errors.price" class="mt-1 text-sm text-red-500">{{ form.errors.price }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Image</label>
            <img v-if="product" :src="product.image_url" alt="Current image" class="mb-2 h-24 w-24 rounded object-cover" />
            <input type="file" accept="image/*" @change="onFileChange" />
            <p v-if="form.errors.image" class="mt-1 text-sm text-red-500">{{ form.errors.image }}</p>
        </div>

        <div class="flex items-center gap-4">
            <Button type="submit" :disabled="form.processing">
    {{ isEdit ? 'Save changes' : 'Create product' }}
</Button>
            <Link href="/admin/products" class="text-gray-600 dark:text-gray-300">Cancel</Link>
        </div>
    </form>
</template>