<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import CategoryForm from '@/Pages/Admin/Categories/CategoryForm.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Kategori" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Tambah Kategori
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Kelola Kategori', url: route('admin.categories.index') },
                { label: 'Tambah Kategori' },
            ]"
        />

        <CategoryForm />
    </AuthenticatedLayout>
</template>
