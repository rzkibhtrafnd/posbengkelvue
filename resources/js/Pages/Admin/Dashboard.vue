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
            <section class="relative overflow-hidden rounded-3xl bg-blue-700 p-6 text-white shadow-lg sm:p-8">
                <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-200">{{ todayLabel }}</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                            Selamat datang, {{ user.name || 'Admin' }}!
                        </h1>
                        <p class="mt-2 max-w-xl text-sm text-blue-100">
                            Pantau performa bengkel, transaksi, dan persediaan dari satu tempat.
                        </p>
                    </div>
                    <Link
                        :href="route('admin.transactions.create')"
                        class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-800 shadow-sm transition hover:bg-blue-50 sm:self-auto"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Buat transaksi
                    </Link>
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

            <form
                class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end"
                @submit.prevent="applyDateFilter"
            >
                <div class="flex-1">
                    <label for="dashboard-date-from" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Dari tanggal
                    </label>
                    <input
                        id="dashboard-date-from"
                        v-model="filterForm.date_from"
                        type="date"
                        class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="filterForm.errors.date_from" class="mt-1 text-xs text-red-600">{{ filterForm.errors.date_from }}</p>
                </div>
                <div class="flex-1">
                    <label for="dashboard-date-to" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Sampai tanggal
                    </label>
                    <input
                        id="dashboard-date-to"
                        v-model="filterForm.date_to"
                        type="date"
                        class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="filterForm.errors.date_to" class="mt-1 text-xs text-red-600">{{ filterForm.errors.date_to }}</p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-800 disabled:opacity-60"
                        :disabled="filterForm.processing"
                    >
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>
                    <button
                        v-if="hasDateFilter || filterForm.date_from || filterForm.date_to"
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50"
                        @click="clearDateFilter"
                    >
                        Reset
                    </button>
                </div>
            </form>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-slate-900">Tren transaksi & omzet bulanan</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ hasDateFilter ? dateRangeLabel : `Seluruh bulan tahun ${new Date().getFullYear()}` }}
                            </p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                            {{ hasDateFilter ? 'Periode filter' : 'Januari–Desember' }}
                        </span>
                    </div>
                    <RevenueBarChart :data="dashboard.monthlyTrend" type="line" />
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-slate-900">Tren transaksi & omzet harian</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ hasDateFilter ? dateRangeLabel : 'Minggu berjalan, dimulai Senin' }}
                            </p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                            {{ hasDateFilter ? `${dashboard.dailyTrend.length} hari` : 'Senin–Minggu' }}
                        </span>
                    </div>
                    <RevenueBarChart :data="dashboard.dailyTrend" />
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
