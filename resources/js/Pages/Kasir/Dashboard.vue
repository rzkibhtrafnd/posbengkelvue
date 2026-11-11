<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RevenueBarChart from '@/Components/RevenueBarChart.vue';

defineProps({
    dashboard: { type: Object, required: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const todayLabel = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
}).format(new Date());

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function formatDate(value) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function paymentLabel(method) {
    const value = method?.value ?? method;
    return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' })[value] ?? '—';
}
</script>

<template>
    <Head title="Dashboard Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">Dashboard Kasir</h2>
        </template>

        <div class="space-y-6">
            <section class="relative overflow-hidden rounded-3xl bg-blue-700 p-6 text-white shadow-lg sm:p-8">
                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-100">{{ todayLabel }}</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                            Halo, {{ user.name || 'Kasir' }}!
                        </h1>
                        <p class="mt-2 text-sm text-blue-50">Semoga pelayanan hari ini lancar. Siap melayani pelanggan?</p>
                    </div>
                    <Link
                        :href="route('kasir.transactions.create')"
                        class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50 sm:self-auto"
                    >
                        <i class="fa-solid fa-cash-register"></i>
                        Mulai transaksi
                    </Link>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-500">Omzet hari ini</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-black tracking-tight text-slate-900">Rp {{ formatCurrency(dashboard.stats.todayRevenue) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ dashboard.stats.todayTransactions }} transaksi berhasil</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-500">Transaksi hari ini</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fa-solid fa-receipt"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-black tracking-tight text-slate-900">{{ dashboard.stats.todayTransactions }}</p>
                    <p class="mt-1 text-xs text-slate-400">Tercatat atas akun Anda</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-500">Omzet bulan ini</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fa-solid fa-chart-column"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-black tracking-tight text-slate-900">Rp {{ formatCurrency(dashboard.stats.monthRevenue) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ dashboard.stats.monthTransactions }} transaksi bulan ini</p>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1fr_1.2fr]">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-6">
                        <h2 class="font-bold text-slate-900">Omzet Anda</h2>
                        <p class="mt-1 text-sm text-slate-500">Performa 7 hari terakhir</p>
                    </div>
                    <RevenueBarChart :data="dashboard.salesTrend" />
                </article>

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div>
                            <h2 class="font-bold text-slate-900">Transaksi terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">Riwayat transaksi akun Anda</p>
                        </div>
                        <Link :href="route('kasir.transactions.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Lihat semua <i class="fa-solid fa-arrow-right ml-1"></i>
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <Link
                            v-for="transaction in dashboard.recentTransactions"
                            :key="transaction.id"
                            :href="route('kasir.transactions.show', transaction.id)"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-mono text-xs font-bold text-blue-600">{{ transaction.invoice_number }}</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-800">{{ transaction.customer_name }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ formatDate(transaction.transaction_date) }} · {{ paymentLabel(transaction.payment_method) }}
                                </p>
                            </div>
                            <span class="shrink-0 text-sm font-bold text-slate-900">Rp {{ formatCurrency(transaction.grand_total) }}</span>
                        </Link>
                        <p v-if="!dashboard.recentTransactions.length" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada transaksi. Transaksi baru akan muncul di sini.
                        </p>
                    </div>
                </article>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
