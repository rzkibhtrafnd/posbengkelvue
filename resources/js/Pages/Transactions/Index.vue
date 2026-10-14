<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    transactions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const isAdmin = computed(() => (page.props.auth?.user?.role?.value ?? page.props.auth?.user?.role) === 'admin');
const routePrefix = computed(() => isAdmin.value ? 'admin' : 'kasir');
const indexRoute = computed(() => `${routePrefix.value}.transactions.index`);
const filterForm = useForm({
    search: props.filters.search ?? '',
    date: props.filters.date ?? '',
});
const paginationFilters = computed(() => ({
    search: props.filters.search ?? '',
    date: props.filters.date ?? '',
}));
const hasFilters = computed(() => Boolean(filterForm.search || filterForm.date));

function applyFilters() {
    router.get(route(indexRoute.value), {
        search: filterForm.search,
        date: filterForm.date,
        per_page: props.transactions.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.date = '';
    router.get(route(indexRoute.value), { per_page: props.transactions.per_page }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function transactionNumber(index) {
    return (props.transactions.current_page - 1) * props.transactions.per_page + index + 1;
}

function formatDate(value) {
    if (!value) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function enumValue(value) {
    return value?.value ?? value;
}

function statusLabel(status) {
    return enumValue(status) === 'completed' ? 'Lunas' : (enumValue(status) || '—');
}

function paymentLabel(method) {
    return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' })[enumValue(method)] ?? '—';
}

function deleteTransaction(transaction) {
    if (!window.confirm(`Hapus transaksi ${transaction.invoice_number}?`)) return;
    router.delete(route('admin.transactions.destroy', transaction.id), { preserveScroll: true });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Riwayat Transaksi" />
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Riwayat Transaksi
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Riwayat Transaksi' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">Daftar Transaksi</h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Cari invoice berdasarkan nomor, pelanggan, kendaraan, atau tanggal.</p>
                </div>
                <Link :href="route(`${routePrefix}.transactions.create`)" class="shrink-0">
                    <PrimaryButton type="button">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Transaksi Baru
                    </PrimaryButton>
                </Link>
            </header>

            <form
                class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200/60 bg-slate-50/70 px-4 py-3"
                @submit.prevent="applyFilters"
            >
                <label class="flex min-w-[220px] flex-1 items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Cari</span>
                    <input
                        v-model="filterForm.search"
                        type="search"
                        name="search"
                        placeholder="No. invoice, pelanggan, atau plat..."
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold shadow-sm placeholder:text-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </label>
                <label class="flex min-w-[180px] items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Tanggal</span>
                    <input
                        v-model="filterForm.date"
                        type="date"
                        name="date"
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </label>
                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <PrimaryButton type="submit">
                        <i class="fa-solid fa-magnifying-glass mr-2 text-[10px]"></i>
                        Cari
                    </PrimaryButton>
                    <SecondaryButton v-if="hasFilters" type="button" @click="resetFilters">
                        <i class="fa-solid fa-rotate-left mr-2 text-slate-400"></i>
                        Reset
                    </SecondaryButton>
                </div>
            </form>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-sm font-medium text-slate-600">
                            <th scope="col" class="px-3 py-2">No.</th>
                            <th scope="col" class="px-3 py-2">Invoice / Tanggal</th>
                            <th scope="col" class="px-3 py-2">Pelanggan / Kendaraan</th>
                            <th scope="col" class="px-3 py-2">Pembayaran</th>
                            <th scope="col" class="px-3 py-2 text-right">Total</th>
                            <th scope="col" class="px-3 py-2 text-center">Status</th>
                            <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr
                            v-for="(transaction, index) in transactions.data"
                            :key="transaction.id"
                            class="transition-colors hover:bg-slate-50/60"
                        >
                            <td class="px-3 py-3 font-mono text-slate-400">{{ transactionNumber(index) }}</td>
                            <td class="px-3 py-3">
                                <p class="font-mono text-xs font-bold text-blue-600">{{ transaction.invoice_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ formatDate(transaction.transaction_date) }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-slate-900">{{ transaction.customer_name }}</p>
                                <p class="mt-0.5 font-mono text-xs text-slate-500">{{ transaction.vehicle_plate_number || 'Tanpa kendaraan' }}</p>
                            </td>
                            <td class="px-3 py-3 text-slate-600">{{ paymentLabel(transaction.payment_method) }}</td>
                            <td class="px-3 py-3 text-right font-mono font-bold text-slate-900">Rp {{ formatCurrency(transaction.grand_total) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-block rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                    {{ statusLabel(transaction.status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route(`${routePrefix}.transactions.show`, transaction.id)"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        title="Detail"
                                    >
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </Link>
                                    <template v-if="isAdmin">
                                        <Link
                                            :href="route('admin.transactions.edit', transaction.id)"
                                            class="inline-flex items-center rounded border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            title="Edit"
                                        >
                                            <i class="fa-solid fa-pen mr-1"></i>
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            title="Hapus"
                                            @click="deleteTransaction(transaction)"
                                        >
                                            <i class="fa-solid fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="transactions.data.length === 0">
                            <td colspan="7" class="px-3 py-6 text-center text-slate-400">
                                {{ hasFilters ? 'Transaksi tidak ditemukan berdasarkan filter.' : 'Belum ada transaksi.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls
                :paginator="transactions"
                :filters="paginationFilters"
                :route-name="indexRoute"
            />
        </section>
    </AuthenticatedLayout>
</template>
