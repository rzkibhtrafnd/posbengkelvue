<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Dashboard Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Dashboard Kasir
            </h2>
        </template>

        <section class="mt-6 overflow-hidden border border-slate-200 bg-white p-6 shadow-sm rounded-2xl">
            <h3 class="text-lg font-semibold text-slate-900">Selamat datang di Dashboard Kasir</h3>
            <p class="mt-1 text-sm text-slate-500">Akses kasir untuk melayani transaksi POS Bengkel.</p>
        </section>
    </AuthenticatedLayout>
</template>
