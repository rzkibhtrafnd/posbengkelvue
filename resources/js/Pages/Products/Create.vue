<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ProductForm from '@/Pages/Products/ProductForm.vue';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Produk" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Tambah Produk
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Katalog Produk', url: route('admin.products.index') },
                { label: 'Tambah Produk' },
            ]"
        />

        <ProductForm :categories="categories" />
    </AuthenticatedLayout>
</template>
