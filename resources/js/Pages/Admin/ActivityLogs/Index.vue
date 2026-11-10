<script setup>
import { computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    activityLogs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    action: props.filters.action ?? '',
    actor_role: props.filters.actor_role ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

const hasFilters = computed(() => Boolean(
    filterForm.search
    || filterForm.action
    || filterForm.actor_role
    || filterForm.date_from
    || filterForm.date_to
));

function applyFilters() {
    router.get(route('admin.activity-logs.index'), {
        search: filterForm.search,
        action: filterForm.action,
        actor_role: filterForm.actor_role,
        date_from: filterForm.date_from,
        date_to: filterForm.date_to,
        per_page: props.activityLogs.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.search = '';
    filterForm.action = '';
    filterForm.actor_role = '';
    filterForm.date_from = '';
    filterForm.date_to = '';
    router.get(route('admin.activity-logs.index'), {
        per_page: props.activityLogs.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function actionLabel(action) {
    return {
        created: 'Dibuat',
        updated: 'Diperbarui',
        deleted: 'Dihapus',
    }[action] ?? action;
}

function subjectLabel(type) {
    return {
        User: 'Pengguna',
        Category: 'Kategori',
        Customer: 'Pelanggan',
        Mechanic: 'Montir',
        Product: 'Produk',
        Setting: 'Pengaturan',
        Transaction: 'Transaksi',
        TransactionItem: 'Item Transaksi',
        Vehicle: 'Kendaraan',
    }[type] ?? type;
}

function fieldLabel(field) {
    const labels = {
        address: 'Alamat',
        brand: 'Merek',
        cashier_name: 'Kasir',
        category_id: 'Kategori',
        change_amount: 'Kembalian',
        color: 'Warna',
        cost_price: 'Harga Modal',
        customer_name: 'Pelanggan',
        customer_phone: 'Telepon Pelanggan',
        date: 'Tanggal',
        description: 'Deskripsi',
        discount: 'Diskon',
        email: 'Email',
        grand_total: 'Total Transaksi',
        mechanic_name: 'Montir',
        min_stock: 'Stok Minimum',
        model: 'Model',
        name: 'Nama',
        notes: 'Catatan',
        pay_amount: 'Jumlah Bayar',
        payment_method: 'Metode Pembayaran',
        phone: 'Nomor Telepon',
        plate_number: 'Nomor Polisi',
        product_name: 'Nama Produk',
        product_type: 'Jenis',
        qty: 'Jumlah',
        selling_price: 'Harga Jual',
        status: 'Status',
        stock: 'Stok',
        subtotal: 'Subtotal',
        tax_percentage: 'Persentase Pajak',
        type: 'Jenis',
        unit: 'Satuan',
        vehicle_plate_number: 'Nomor Polisi',
        year: 'Tahun',
    };

    return labels[field] ?? field.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function displayValue(value, field = '') {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    const normalized = String(value);
    if (['subtotal', 'discount', 'grand_total', 'pay_amount', 'change_amount', 'selling_price', 'cost_price'].includes(field)) {
        return `Rp ${new Intl.NumberFormat('id-ID').format(Number(value) || 0)}`;
    }

    if (field === 'payment_method') {
        return ({ cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' })[normalized] ?? normalized;
    }

    if (field === 'product_type') {
        return ({ barang: 'Barang', jasa: 'Jasa' })[normalized] ?? normalized;
    }

    if (field === 'status') {
        return ({ completed: 'Selesai', pending: 'Menunggu', cancelled: 'Dibatalkan' })[normalized] ?? normalized;
    }

    return normalized;
}

function regularChanges(changes) {
    return Object.entries(changes ?? {})
        .filter(([key]) => !['id', 'created_at', 'updated_at', 'transaction'].includes(key))
        .map(([key, value]) => ({
            key,
            label: fieldLabel(key),
            isUpdate: value && typeof value === 'object' && ('old' in value || 'new' in value),
            oldValue: value && typeof value === 'object' && ('old' in value || 'new' in value) ? value.old : null,
            newValue: value && typeof value === 'object' && ('old' in value || 'new' in value) ? value.new : value,
        }));
}

function transactionVersions(log) {
    const transaction = log.changes?.transaction;
    if (!transaction) return [];

    if (log.action === 'updated' && transaction.old && transaction.new) {
        return [
            { label: 'Sebelum', snapshot: transaction.old },
            { label: 'Sesudah', snapshot: transaction.new },
        ];
    }

    return [{
        label: log.action === 'deleted' ? 'Data sebelum dihapus' : 'Rincian transaksi',
        snapshot: transaction,
    }];
}

function transactionItemCount(log) {
    return transactionVersions(log).reduce((count, version) => count + (version.snapshot.items?.length ?? 0), 0);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Log Aktivitas" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Log Aktivitas
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Log Aktivitas' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="border-b border-slate-100 pb-5">
                <h3 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                    Riwayat Perubahan Sistem
                </h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    Catatan perubahan data oleh seluruh pengguna beserta waktu dan detailnya.
                </p>
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
                        placeholder="Pengguna atau data..."
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold shadow-sm placeholder:text-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </label>
                <label class="flex min-w-[150px] items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Aktivitas</span>
                    <select
                        v-model="filterForm.action"
                        name="action"
                        class="w-full cursor-pointer rounded-xl border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Semua</option>
                        <option value="created">Dibuat</option>
                        <option value="updated">Diperbarui</option>
                        <option value="deleted">Dihapus</option>
                    </select>
                </label>
                <label class="flex min-w-[130px] items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Role</span>
                    <select
                        v-model="filterForm.actor_role"
                        name="actor_role"
                        class="w-full cursor-pointer rounded-xl border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Semua</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                    </select>
                </label>
                <label class="flex min-w-[165px] items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Dari</span>
                    <input
                        v-model="filterForm.date_from"
                        type="date"
                        name="date_from"
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </label>
                <label class="flex min-w-[165px] items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Sampai</span>
                    <input
                        v-model="filterForm.date_to"
                        type="date"
                        name="date_to"
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
                            <th class="whitespace-nowrap px-3 py-2" scope="col">Waktu</th>
                            <th class="whitespace-nowrap px-3 py-2" scope="col">Pengguna</th>
                            <th class="whitespace-nowrap px-3 py-2" scope="col">Aktivitas</th>
                            <th class="whitespace-nowrap px-3 py-2" scope="col">Data</th>
                            <th class="min-w-72 px-3 py-2" scope="col">Detail Perubahan</th>
                            <th class="whitespace-nowrap px-3 py-2" scope="col">IP Address</th>
                            <th class="min-w-64 px-3 py-2" scope="col">User Agent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr
                            v-for="log in activityLogs.data"
                            :key="log.id"
                            class="align-top transition-colors hover:bg-slate-50/60"
                        >
                            <td class="whitespace-nowrap px-3 py-3 text-xs text-slate-500">
                                {{ new Date(log.created_at).toLocaleString('id-ID') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                <p class="font-semibold text-slate-900">{{ log.actor_name || 'Sistem' }}</p>
                                <span
                                    v-if="log.actor_role"
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                >
                                    {{ log.actor_role }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                <span
                                    class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-bold"
                                    :class="{
                                        'border-emerald-200 bg-emerald-50 text-emerald-700': log.action === 'created',
                                        'border-blue-200 bg-blue-50 text-blue-700': log.action === 'updated',
                                        'border-red-200 bg-red-50 text-red-700': log.action === 'deleted',
                                    }"
                                >
                                    {{ actionLabel(log.action) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-slate-900">{{ subjectLabel(log.subject_type) }}</p>
                                <p class="text-xs text-slate-500">{{ log.subject_name }}</p>
                            </td>
                            <td class="min-w-[320px] px-3 py-3 text-xs text-slate-600">
                                <div v-if="log.changes?.transaction" class="space-y-3">
                                    <div
                                        class="grid gap-2"
                                        :class="log.action === 'updated' ? 'sm:grid-cols-2' : 'grid-cols-1'"
                                    >
                                        <div
                                            v-for="version in transactionVersions(log)"
                                            :key="version.label"
                                            class="rounded-lg border border-slate-200 bg-slate-50 p-2.5"
                                        >
                                            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                {{ version.label }}
                                            </p>
                                            <p class="font-semibold text-slate-800">
                                                {{ version.snapshot.customer_name || 'Pelanggan Umum' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500">
                                                {{ version.snapshot.vehicle_plate_number || 'Tanpa kendaraan' }}
                                                · {{ version.snapshot.items?.length ?? 0 }} item
                                            </p>
                                            <p class="mt-1 font-mono font-bold text-blue-700">
                                                Rp {{ new Intl.NumberFormat('id-ID').format(Number(version.snapshot.grand_total) || 0) }}
                                            </p>
                                        </div>
                                    </div>

                                    <details class="group rounded-lg border border-slate-200">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2 font-semibold text-blue-700 hover:bg-blue-50 [&::-webkit-details-marker]:hidden">
                                            <span>
                                                <i class="fa-solid fa-list-check mr-1.5"></i>
                                                Lihat rincian ({{ transactionItemCount(log) }} item)
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform group-open:rotate-180"></i>
                                        </summary>
                                        <div class="space-y-3 border-t border-slate-100 p-3">
                                            <div
                                                v-for="version in transactionVersions(log)"
                                                :key="version.label"
                                                class="overflow-hidden rounded-lg border border-slate-100"
                                            >
                                                <p class="bg-slate-50 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                                    {{ version.label }} · {{ version.snapshot.invoice_number }}
                                                </p>
                                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 px-3 py-2 text-[11px] sm:grid-cols-3">
                                                    <div>
                                                        <span class="block text-slate-400">Pembayaran</span>
                                                        <span class="font-semibold text-slate-700">{{ displayValue(version.snapshot.payment_method, 'payment_method') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-slate-400">Subtotal</span>
                                                        <span class="font-semibold text-slate-700">{{ displayValue(version.snapshot.subtotal, 'subtotal') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-slate-400">Diskon</span>
                                                        <span class="font-semibold text-slate-700">{{ displayValue(version.snapshot.discount, 'discount') }}</span>
                                                    </div>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="w-full min-w-[380px] text-[11px]">
                                                        <thead class="bg-slate-50 text-left text-slate-400">
                                                            <tr>
                                                                <th class="px-3 py-1.5 font-semibold">Produk / Jasa</th>
                                                                <th class="px-2 py-1.5 text-center font-semibold">Qty</th>
                                                                <th class="px-3 py-1.5 text-right font-semibold">Harga</th>
                                                                <th class="px-3 py-1.5 text-right font-semibold">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            <tr v-for="(item, index) in version.snapshot.items" :key="`${version.label}-${index}`">
                                                                <td class="px-3 py-2 font-semibold text-slate-700">
                                                                    {{ item.product_name }}
                                                                    <span class="block font-normal text-slate-400">{{ displayValue(item.product_type, 'product_type') }}</span>
                                                                </td>
                                                                <td class="px-2 py-2 text-center font-mono">{{ item.qty }}</td>
                                                                <td class="px-3 py-2 text-right font-mono">{{ displayValue(item.selling_price, 'selling_price') }}</td>
                                                                <td class="px-3 py-2 text-right font-mono font-semibold">{{ displayValue(item.subtotal, 'subtotal') }}</td>
                                                            </tr>
                                                            <tr v-if="!version.snapshot.items?.length">
                                                                <td colspan="4" class="px-3 py-3 text-center text-slate-400">Tidak ada item transaksi.</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class="flex justify-between border-t border-slate-100 px-3 py-2 text-xs font-bold text-slate-800">
                                                    <span>Total transaksi</span>
                                                    <span>{{ displayValue(version.snapshot.grand_total, 'grand_total') }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </details>
                                </div>

                                <div v-else-if="regularChanges(log.changes).length" class="space-y-2">
                                    <div
                                        v-for="change in regularChanges(log.changes)"
                                        :key="change.key"
                                        class="rounded-lg border border-slate-100 bg-slate-50/70 px-3 py-2"
                                    >
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                            {{ change.label }}
                                        </p>
                                        <div v-if="change.isUpdate" class="mt-1 flex flex-wrap items-center gap-1.5">
                                            <span class="rounded bg-red-50 px-1.5 py-0.5 text-red-700">
                                                {{ displayValue(change.oldValue, change.key) }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-[9px] text-slate-400"></i>
                                            <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-semibold text-emerald-700">
                                                {{ displayValue(change.newValue, change.key) }}
                                            </span>
                                        </div>
                                        <p v-else class="mt-1 break-words font-medium text-slate-700">
                                            {{ displayValue(change.newValue, change.key) }}
                                        </p>
                                    </div>
                                </div>
                                <span v-else class="text-slate-400">Tidak ada detail perubahan.</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-xs text-slate-600">
                                {{ log.ip_address || '—' }}
                            </td>
                            <td class="max-w-sm px-3 py-3 text-xs text-slate-600">
                                <span v-if="log.user_agent" class="block break-words" :title="log.user_agent">
                                    {{ log.user_agent }}
                                </span>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                        </tr>
                        <tr v-if="activityLogs.data.length === 0">
                            <td class="px-3 py-8 text-center text-slate-400" colspan="7">
                                Tidak ada log aktivitas yang cocok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls
                :paginator="activityLogs"
                :filters="filters"
                route-name="admin.activity-logs.index"
            />
        </section>
    </AuthenticatedLayout>
</template>
