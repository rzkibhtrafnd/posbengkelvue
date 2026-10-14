<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import CategoryForm from '@/Pages/Admin/Categories/CategoryForm.vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Kategori" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Edit Kategori
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Kelola Kategori', url: route('admin.categories.index') },
                { label: 'Edit Kategori' },
            ]"
        />

        <CategoryForm :category="category" />
    </AuthenticatedLayout>
</template>
