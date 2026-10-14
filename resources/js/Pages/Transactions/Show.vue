<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
});

const user = computed(() => usePage().props.auth?.user ?? {});
const isAdmin = computed(() => (user.value.role?.value ?? user.value.role) === 'admin');
const routePrefix = computed(() => isAdmin.value ? 'admin' : 'kasir');
const indexRoute = computed(() => `${routePrefix.value}.transactions.index`);
const invoiceRoute = computed(() => `${routePrefix.value}.transactions.invoice`);

function enumValue(value) {
    return value?.value ?? value;
}

function paymentLabel(method) {
    return ({ cash: 'Tunai (Cash)', qris: 'QRIS / Digital', transfer: 'Transfer Bank' })[enumValue(method)] ?? '—';
}

function statusLabel(status) {
    return enumValue(status) === 'completed' ? 'Lunas' : (enumValue(status) || '—');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function formatDate(value) {
    if (!value) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Faktur ${transaction.invoice_number}`" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Detail Faktur Penjualan
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Riwayat Transaksi', url: route(indexRoute) },
                { label: transaction.invoice_number },
            ]"
        />

        <section class="mx-auto mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6">
                <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-black tracking-wider text-slate-900">POS BENGKEL MANAGEMENT</h3>
                        <p class="mt-1 text-xs text-slate-500">Faktur Penjualan &amp; Layanan Servis</p>
                    </div>
                    <div class="sm:text-right">
                        <span class="inline-block font-mono text-xs font-bold text-blue-700">
                            {{ transaction.invoice_number }}
                        </span>
                        <p class="mt-1 font-mono text-xs text-slate-500">{{ formatDate(transaction.transaction_date) }} WIB</p>
                        <p class="mt-1 text-xs font-semibold text-blue-700">{{ paymentLabel(transaction.payment_method) }}</p>
                        <p class="mt-1 text-xs font-semibold text-green-700">{{ statusLabel(transaction.status) }}</p>
                    </div>
                </header>

                <div class="my-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4 text-xs sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Pelanggan</span>
                        <span class="font-bold text-slate-800">{{ transaction.customer_name }}</span>
                        <span class="block font-mono text-slate-500">{{ transaction.customer_phone || '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Kendaraan</span>
                        <span class="font-mono font-bold text-slate-800">{{ transaction.vehicle_plate_number || 'Tanpa kendaraan' }}</span>
                        <span v-if="transaction.vehicle_brand_model" class="block text-slate-500">{{ transaction.vehicle_brand_model }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Montir</span>
                        <span class="font-semibold text-slate-800">{{ transaction.mechanic_name || 'Tanpa Montir' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Kasir / Status</span>
                        <span class="font-semibold text-slate-800">{{ transaction.cashier_name }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] divide-y divide-slate-200 text-xs">
                        <thead>
                            <tr class="text-left font-bold uppercase text-slate-500">
                                <th scope="col" class="py-2">Item / Jasa</th>
                                <th scope="col" class="py-2 text-center">Qty</th>
                                <th scope="col" class="py-2 text-right">Harga</th>
                                <th scope="col" class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="item in transaction.items" :key="item.id">
                                <td class="py-2.5 font-semibold text-slate-900">
                                    {{ item.product_name }}
                                    <span class="block font-mono text-[10px] text-slate-400">
                                        {{ item.product_code }} · {{ item.category_name || 'Umum' }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-center font-mono font-bold">{{ item.qty }}</td>
                                <td class="py-2.5 text-right font-mono">Rp {{ formatCurrency(item.selling_price) }}</td>
                                <td class="py-2.5 text-right font-mono font-bold">Rp {{ formatCurrency(item.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="ml-auto mt-5 max-w-md space-y-2 border-t border-slate-200 pt-4 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-mono font-semibold">Rp {{ formatCurrency(transaction.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Diskon</span>
                        <span class="font-mono text-red-500">- Rp {{ formatCurrency(transaction.discount) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 text-sm font-bold text-slate-900">
                        <span>TOTAL TAGIHAN</span>
                        <span class="font-mono text-blue-600">Rp {{ formatCurrency(transaction.grand_total) }}</span>
                    </div>
                    <template v-if="enumValue(transaction.payment_method) === 'cash'">
                        <div class="flex justify-between">
                            <span>Uang diterima</span>
                            <span class="font-mono">Rp {{ formatCurrency(transaction.pay_amount) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-emerald-700">
                            <span>Kembalian</span>
                            <span class="font-mono">Rp {{ formatCurrency(transaction.change_amount) }}</span>
                        </div>
                    </template>
                </div>

                <p v-if="transaction.notes" class="mt-5 whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-400">Catatan</span>
                    {{ transaction.notes }}
                </p>

                <footer class="mt-8 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <Link :href="route(indexRoute)">
                        <SecondaryButton type="button">Kembali</SecondaryButton>
                    </Link>
                    <Link :href="route(invoiceRoute, transaction.id)" target="_blank" class="inline-flex">
                        <PrimaryButton type="button">Cetak Invoice</PrimaryButton>
                    </Link>
                </footer>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
