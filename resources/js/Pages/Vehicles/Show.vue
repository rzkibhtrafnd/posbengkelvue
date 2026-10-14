<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    vehicle: {
        type: Object,
        required: true,
    },
    serviceHistory: {
        type: Object,
        required: true,
    },
});

const user = computed(() => usePage().props.auth?.user ?? {});
const isAdmin = computed(() => (user.value.role?.value ?? user.value.role) === 'admin');
const indexRoute = computed(() => (
    isAdmin.value ? 'admin.vehicles.index' : 'kasir.vehicles.index'
));
const transactionRoutePrefix = computed(() => isAdmin.value ? 'admin' : 'kasir');

function formatDate(value) {
    if (!value) return '—';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).format(new Date(value));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function canViewTransaction(transaction) {
    return isAdmin.value || Number(transaction.user_id) === Number(user.value.id);
}

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Kendaraan ${vehicle.plate_number}`" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Detail Kendaraan
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Data Kendaraan', url: route(indexRoute) },
                { label: `Detail ${vehicle.plate_number}` },
            ]"
        />

        <section class="mx-auto mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6">
                <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="inline-block rounded-md bg-slate-900 px-3 py-1 font-mono text-sm font-bold tracking-wider text-white">
                            {{ vehicle.plate_number }}
                        </span>
                        <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                            {{ vehicle.brand }} {{ vehicle.model }}
                        </h3>
                    </div>
                </header>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <section class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Kendaraan</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Merk &amp; Model</dt>
                                <dd class="text-right font-semibold text-slate-800">
                                    {{ vehicle.brand }} {{ vehicle.model }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Tahun Pembuatan</dt>
                                <dd class="text-right font-mono text-slate-700">
                                    {{ vehicle.year || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Warna</dt>
                                <dd class="text-right font-medium text-slate-800">
                                    {{ vehicle.color || '—' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Pemilik</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Nama Pemilik</dt>
                                <dd class="text-right font-bold text-slate-900">
                                    {{ vehicle.customer?.name ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Kode Pelanggan</dt>
                                <dd class="text-right font-mono font-bold text-blue-600">
                                    {{ vehicle.customer?.code ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">No. Telepon/WA</dt>
                                <dd class="text-right font-mono text-slate-800">
                                    {{ vehicle.customer?.phone ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Alamat</dt>
                                <dd class="whitespace-pre-line text-right text-slate-700">
                                    {{ vehicle.customer?.address ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <section class="mt-8 border-t border-slate-100 pt-6">
                    <header class="mb-4">
                        <div>
                            <h4 class="text-base font-bold text-slate-900">Riwayat Servis</h4>
                            <p class="mt-1 text-xs text-slate-500">Daftar transaksi dan pekerjaan servis untuk kendaraan ini.</p>
                        </div>
                    </header>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th scope="col" class="px-3 py-2">Tanggal / Faktur</th>
                                    <th scope="col" class="px-3 py-2">Rincian Servis</th>
                                    <th scope="col" class="px-3 py-2">Montir</th>
                                    <th scope="col" class="px-3 py-2 text-right">Total</th>
                                    <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr
                                    v-for="transaction in serviceHistory.data"
                                    :key="transaction.id"
                                    class="align-top transition-colors hover:bg-slate-50"
                                >
                                    <td class="px-3 py-3">
                                        <span class="block font-mono text-xs text-slate-500">{{ formatDate(transaction.transaction_date) }}</span>
                                        <span class="mt-1 block font-mono text-xs font-bold text-blue-600">{{ transaction.invoice_number }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <ul v-if="transaction.items?.length" class="space-y-1">
                                            <li v-for="item in transaction.items" :key="item.id" class="text-xs">
                                                <span class="font-medium text-slate-800">{{ item.product_name }}</span>
                                                <span class="text-slate-500">× {{ item.qty }}</span>
                                            </li>
                                        </ul>
                                        <span v-else class="text-xs text-slate-400">Tidak ada rincian item</span>
                                    </td>
                                    <td class="px-3 py-3 text-xs">{{ transaction.mechanic_name || 'Tanpa Montir' }}</td>
                                    <td class="px-3 py-3 text-right font-mono text-xs font-bold text-slate-900">
                                        Rp {{ formatCurrency(transaction.grand_total) }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <Link
                                            v-if="canViewTransaction(transaction)"
                                            :href="route(`${transactionRoutePrefix}.transactions.show`, transaction.id)"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                                        >
                                            <i class="fa-solid fa-eye mr-1" aria-hidden="true"></i>
                                            Detail
                                        </Link>
                                        <span v-else class="text-xs text-slate-300">—</span>
                                    </td>
                                </tr>
                                <tr v-if="serviceHistory.data.length === 0">
                                    <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-400">
                                        Belum ada riwayat servis untuk kendaraan ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav
                        v-if="serviceHistory.last_page > 1"
                        class="mt-5 flex flex-wrap items-center justify-end gap-1 border-t border-slate-100 pt-4"
                        aria-label="Halaman riwayat servis"
                    >
                        <template v-for="(link, index) in serviceHistory.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="inline-flex min-h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="link.active
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700'"
                                :aria-current="link.active ? 'page' : undefined"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-3 text-xs font-medium text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </section>

                <footer class="mt-4 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route(indexRoute)">
                        <SecondaryButton type="button">
                            Kembali
                        </SecondaryButton>
                    </Link>
                    <Link
                        v-if="isAdmin"
                        :href="route('admin.vehicles.edit', vehicle.id)"
                    >
                        <PrimaryButton type="button">
                            Edit Data
                        </PrimaryButton>
                    </Link>
                </footer>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
