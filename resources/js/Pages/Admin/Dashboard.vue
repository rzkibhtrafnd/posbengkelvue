<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RevenueBarChart from '@/Components/RevenueBarChart.vue';

const props = defineProps({
    dashboard: { type: Object, required: true },
    filters: {
        type: Object,
        default: () => ({ date_from: null, date_to: null }),
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const filterForm = useForm({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});
const hasDateFilter = computed(() => Boolean(props.filters.date_from && props.filters.date_to));
const dateRangeLabel = computed(() => {
    if (!hasDateFilter.value) return '';

    const formatter = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' });

    return `${formatter.format(new Date(`${props.filters.date_from}T00:00:00`))} – ${formatter.format(new Date(`${props.filters.date_to}T00:00:00`))}`;
});
const periodLabel = computed(() => dateRangeLabel.value || `Tahun ${new Date().getFullYear()}`);
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

const statCards = computed(() => [
    {
        label: hasDateFilter.value ? 'Omzet periode' : 'Omzet hari ini',
        value: `Rp ${formatCurrency(props.dashboard.stats.periodRevenue)}`,
        note: hasDateFilter.value ? dateRangeLabel.value : `${props.dashboard.stats.periodTransactions} transaksi`,
        icon: 'fa-sack-dollar',
        tone: 'bg-blue-50 text-blue-600',
    },
    {
        label: hasDateFilter.value ? 'Transaksi periode' : 'Transaksi hari ini',
        value: props.dashboard.stats.periodTransactions,
        note: hasDateFilter.value ? dateRangeLabel.value : 'transaksi berhasil hari ini',
        icon: 'fa-receipt',
        tone: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Total transaksi',
        value: props.dashboard.stats.totalTransactions,
        note: 'seluruh transaksi tercatat',
        icon: 'fa-chart-simple',
        tone: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Produk perlu restock',
        value: props.dashboard.stats.lowStockCount,
        note: `dari ${props.dashboard.stats.products} produk`,
        icon: 'fa-box-open',
        tone: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Pelanggan terdaftar',
        value: props.dashboard.stats.customers,
        note: `${props.dashboard.stats.vehicles} kendaraan tercatat`,
        icon: 'fa-users',
        tone: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Kendaraan terdaftar',
        value: props.dashboard.stats.vehicles,
        note: 'kendaraan pelanggan',
        icon: 'fa-car',
        tone: 'bg-blue-50 text-blue-600',
    },
]);

function applyDateFilter() {
    filterForm.get(route('admin.dashboard'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearDateFilter() {
    filterForm.date_from = '';
    filterForm.date_to = '';
    filterForm.clearErrors();
    applyDateFilter();
}
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">Dashboard Admin</h2>
        </template>

        <div class="space-y-6">
            <section class="relative overflow-hidden rounded-2xl bg-blue-700 px-5 py-6 text-white shadow-lg sm:px-6">
                <div class="relative flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-blue-100/90">Ringkasan aktivitas Anda</p>
                        <p class="text-sm font-semibold text-blue-200">{{ todayLabel }}</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                            Selamat datang, {{ user.name || 'Admin' }}!
                        </h1>
                        <p class="mt-2 max-w-xl text-sm text-blue-100">
                            Pantau performa bengkel, transaksi, dan persediaan dari satu tempat.
                        </p>
                    </div>
                    <form
                        class="flex w-full flex-wrap items-center justify-start gap-3 rounded-xl border border-white/20 bg-white/10 p-3 sm:w-auto sm:justify-end"
                        @submit.prevent="applyDateFilter"
                    >
                        <div class="flex items-center gap-2">
                            <label for="dashboard-date-from" class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-blue-50">
                                Dari
                            </label>
                            <input
                                id="dashboard-date-from"
                                v-model="filterForm.date_from"
                                type="date"
                                class="h-9 rounded-lg border-white/20 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-300 focus:ring-blue-300"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <label for="dashboard-date-to" class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-blue-50">
                                Sampai
                            </label>
                            <input
                                id="dashboard-date-to"
                                v-model="filterForm.date_to"
                                type="date"
                                class="h-9 rounded-lg border-white/20 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-300 focus:ring-blue-300"
                            />
                        </div>
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-white px-3 text-xs font-bold text-blue-800 shadow-sm transition hover:bg-blue-50 disabled:opacity-60"
                            :disabled="filterForm.processing"
                        >
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[10px]" aria-hidden="true"></i>
                            Cari
                        </button>
                        <button
                            v-if="hasDateFilter || filterForm.date_from || filterForm.date_to"
                            type="button"
                            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg border border-white/25 bg-white/10 px-3 text-xs font-semibold text-white transition hover:bg-white/20"
                            @click="clearDateFilter"
                        >
                            <i class="fa-solid fa-rotate-left mr-2 text-blue-100" aria-hidden="true"></i>
                            Reset
                        </button>
                        <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-white/20 bg-blue-950/20 px-3 py-1.5 text-xs font-bold tracking-wide text-white">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            {{ periodLabel }}
                        </span>
                        <p
                            v-if="filterForm.errors.date_from || filterForm.errors.date_to"
                            class="w-full text-xs font-semibold text-rose-100"
                        >
                            {{ filterForm.errors.date_from || filterForm.errors.date_to }}
                        </p>
                    </form>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                <article
                    v-for="card in statCards"
                    :key="card.label"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ card.label }}</p>
                            <p class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ card.value }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ card.note }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" :class="card.tone">
                            <i class="fa-solid text-lg" :class="card.icon"></i>
                        </span>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Tren bulanan</p>
                            <h2 class="mt-1 text-lg font-extrabold text-slate-900">Tren transaksi & omzet bulanan</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ hasDateFilter ? dateRangeLabel : `Seluruh bulan tahun ${new Date().getFullYear()}` }}
                            </p>
                        </div>
                    </div>
                    <RevenueBarChart :data="dashboard.monthlyTrend" type="line" />
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Tren harian</p>
                            <h2 class="mt-1 text-lg font-extrabold text-slate-900">Tren transaksi & omzet harian</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ hasDateFilter ? `${dateRangeLabel} · akumulasi per hari` : 'Akumulasi per hari selama tahun berjalan' }}
                            </p>
                        </div>
                    </div>
                    <RevenueBarChart :data="dashboard.dailyTrend" show-transactions />
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div>
                            <h2 class="font-bold text-slate-900">Transaksi terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">Aktivitas penjualan terkini</p>
                        </div>
                        <Link :href="route('admin.transactions.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Lihat semua <i class="fa-solid fa-arrow-right ml-1"></i>
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Invoice</th>
                                    <th class="px-5 py-3">Pelanggan</th>
                                    <th class="px-5 py-3">Pembayaran</th>
                                    <th class="px-5 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="transaction in dashboard.recentTransactions" :key="transaction.id" class="hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <Link :href="route('admin.transactions.show', transaction.id)" class="font-mono text-xs font-bold text-blue-600 hover:text-blue-800">
                                            {{ transaction.invoice_number }}
                                        </Link>
                                        <p class="mt-1 text-xs text-slate-400">{{ formatDate(transaction.transaction_date) }}</p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-800">{{ transaction.customer_name }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ transaction.vehicle_plate_number || 'Tanpa kendaraan' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ paymentLabel(transaction.payment_method) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-bold text-slate-800">
                                        Rp {{ formatCurrency(transaction.grand_total) }}
                                    </td>
                                </tr>
                                <tr v-if="!dashboard.recentTransactions.length">
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada transaksi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <div class="space-y-6">
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                            <div>
                                <h2 class="font-bold text-slate-900">Stok menipis</h2>
                                <p class="mt-1 text-xs text-slate-500">{{ dashboard.stats.lowStockCount }} produk perlu perhatian</p>
                            </div>
                            <i class="fa-solid fa-triangle-exclamation text-blue-600"></i>
                        </div>
                        <div v-if="dashboard.lowStockProducts.length" class="divide-y divide-slate-100">
                            <div v-for="product in dashboard.lowStockProducts" :key="product.id" class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ product.name }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ product.code }}</p>
                                </div>
                                <span class="shrink-0 rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-700">
                                    {{ product.stock }} / {{ product.min_stock }} {{ product.unit || '' }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="px-5 py-8 text-center text-sm text-slate-400">Semua stok dalam kondisi aman.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-bold text-slate-900">Produk terlaris bulan ini</h2>
                        <div v-if="dashboard.topProducts.length" class="mt-4 space-y-4">
                            <div v-for="(product, index) in dashboard.topProducts" :key="product.product_name" class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs font-black text-blue-700">
                                    {{ index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ product.product_name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ product.quantity }} terjual</p>
                                </div>
                                <p class="whitespace-nowrap text-xs font-bold text-slate-600">Rp {{ formatCurrency(product.revenue) }}</p>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-slate-400">Belum ada penjualan bulan ini.</p>
                    </article>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
