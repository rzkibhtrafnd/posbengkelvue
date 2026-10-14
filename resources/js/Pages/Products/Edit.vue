<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ProductForm from '@/Pages/Products/ProductForm.vue';

defineProps({
    product: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Produk" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Edit Produk
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Katalog Produk', url: route('admin.products.index') },
                { label: 'Edit Produk' },
            ]"
        />

        <ProductForm :product="product" :categories="categories" />
    </AuthenticatedLayout>
</template>
