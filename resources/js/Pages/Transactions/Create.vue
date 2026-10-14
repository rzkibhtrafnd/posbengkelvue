<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import TransactionForm from '@/Pages/Transactions/TransactionForm.vue';

defineProps({
    products: { type: Array, required: true },
    categories: { type: Array, required: true },
    vehicles: { type: Array, required: true },
    mechanics: { type: Array, required: true },
    transaction: { type: Object, default: null },
    initialCart: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const indexRoute = computed(() => (
    (user.value.role?.value ?? user.value.role) === 'admin'
        ? 'admin.transactions.index'
        : 'kasir.transactions.index'
));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="transaction ? 'Edit Transaksi' : 'POS Kasir Bengkel'" />
        <template #header>
            <h2 class="font-sans text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                {{ transaction ? 'Edit Transaksi' : 'POS Kasir Bengkel' }}
            </h2>
        </template>
        <Breadcrumb
            :items="[
                { label: 'Riwayat Transaksi', url: route(indexRoute) },
                { label: transaction ? `Edit ${transaction.invoice_number}` : 'Transaksi Baru' },
            ]"
        />
        <TransactionForm
            :products="products"
            :categories="categories"
            :vehicles="vehicles"
            :mechanics="mechanics"
            :transaction="transaction"
            :initial-cart="initialCart"
        />
    </AuthenticatedLayout>
</template>
