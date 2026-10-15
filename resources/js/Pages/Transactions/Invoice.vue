<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
    workshop: {
        type: Object,
        default: () => ({}),
    },
});

const paymentMethod = computed(() => {
    const value = props.transaction.payment_method?.value ?? props.transaction.payment_method;
    return ({ cash: 'Tunai (Cash)', qris: 'QRIS / Digital', transfer: 'Transfer Bank' })[value] ?? '—';
});
const status = computed(() => {
    const value = props.transaction.status?.value ?? props.transaction.status;
    return value === 'completed' ? 'Lunas' : (value || '—');
});
const printedAt = computed(() => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date()));

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

function printInvoice() {
    window.print();
}

onMounted(() => window.print());
</script>

<template>
    <Head :title="`Struk ${transaction.invoice_number}`" />

    <div class="receipt-page">
        <div class="receipt-toolbar">
            <button type="button" class="print-button" @click="printInvoice">Cetak struk 58 mm</button>
        </div>

        <main class="receipt-paper">
            <header class="receipt-header">
                <p class="store-name">{{ workshop.name || 'POS BENGKEL' }}</p>
                <p v-if="workshop.email">{{ workshop.email }}</p>
                <p v-if="workshop.phone">{{ workshop.phone }}</p>
                <p v-if="workshop.address" class="store-address">{{ workshop.address }}</p>
                <div class="divider"></div>
                <p class="receipt-title">BUKTI TRANSAKSI</p>
                <p class="invoice-number">{{ transaction.invoice_number }}</p>
                <p>{{ formatDate(transaction.transaction_date) }} WIB</p>
            </header>

            <section class="receipt-meta mt-3">
                <div class="meta-row"><span>Pelanggan</span><strong>{{ transaction.customer_name || 'Pelanggan Umum' }}</strong></div>
                <div v-if="transaction.customer_phone" class="meta-row"><span>Telepon</span><strong>{{ transaction.customer_phone }}</strong></div>
                <div class="meta-row"><span>Kendaraan</span><strong>{{ transaction.vehicle_plate_number || 'Tanpa kendaraan' }}</strong></div>
                <div v-if="transaction.vehicle_brand_model" class="meta-row"><span>Model</span><strong>{{ transaction.vehicle_brand_model }}</strong></div>
                <div class="meta-row"><span>Kasir</span><strong>{{ transaction.cashier_name }}</strong></div>
            </section>

            <div class="divider"></div>

            <section class="receipt-items" aria-label="Daftar item transaksi">
                <article v-for="item in transaction.items" :key="item.id" class="receipt-item">
                    <p class="item-name">{{ item.product_name }}</p>
                    <div class="item-detail">
                        <span>{{ item.qty }} x {{ formatCurrency(item.selling_price) }}</span>
                        <strong>{{ formatCurrency(item.subtotal) }}</strong>
                    </div>
                </article>
            </section>

            <div class="divider"></div>

            <section class="receipt-totals">
                <div class="total-row"><span>Subtotal</span><strong>{{ formatCurrency(transaction.subtotal) }}</strong></div>
                <div class="total-row"><span>Diskon</span><strong>-{{ formatCurrency(transaction.discount) }}</strong></div>
                <div class="total-row grand-total"><span>TOTAL</span><strong>{{ formatCurrency(transaction.grand_total) }}</strong></div>
                <div class="total-row"><span>Pembayaran</span><strong>{{ paymentMethod }}</strong></div>
                <template v-if="(transaction.payment_method?.value ?? transaction.payment_method) === 'cash'">
                    <div class="total-row"><span>Uang diterima</span><strong>{{ formatCurrency(transaction.pay_amount) }}</strong></div>
                    <div class="total-row"><span>Kembalian</span><strong>{{ formatCurrency(transaction.change_amount) }}</strong></div>
                </template>
            </section>

            <section v-if="transaction.notes" class="receipt-notes">
                <p class="section-label">Catatan</p>
                <p>{{ transaction.notes }}</p>
            </section>

            <footer class="receipt-footer">
                <p>Terima kasih telah bertransaksi.</p>
                <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
                <p class="printed-at">Dicetak {{ printedAt }}</p>
            </footer>
        </main>
    </div>
</template>

<style scoped>
@page {
    size: 58mm auto;
    margin: 0;
}

.receipt-page {
    min-height: 100vh;
    padding: 24px 12px;
    background: #e2e8f0;
    color: #0f172a;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    line-height: 1.45;
}

.receipt-toolbar {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.print-button {
    padding: 10px 16px;
    border: 0;
    border-radius: 9px;
    background: #1d4ed8;
    color: white;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgb(30 64 175 / 20%);
}

.receipt-paper {
    box-sizing: border-box;
    width: 58mm;
    min-height: 100mm;
    margin: 0 auto;
    padding: 4mm 3mm;
    background: white;
    box-shadow: 0 8px 28px rgb(15 23 42 / 14%);
    overflow-wrap: anywhere;
}

.receipt-header {
    text-align: center;
}

.store-name {
    margin: 0;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 1.2px;
}

.store-caption,
.receipt-header p {
    margin-top: 2px;
}

.store-caption {
    color: #475569;
}

.store-address {
    white-space: pre-line;
}

.receipt-title {
    margin-bottom: 0;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1px;
}

.invoice-number {
    font-family: "Courier New", monospace;
    font-size: 11px;
    font-weight: 700;
}

.receipt-status {
    display: inline-block;
    margin: 5px 0 0 !important;
    padding: 2px 8px;
    border: 1px solid #0f172a;
    border-radius: 99px;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.7px;
}

.divider {
    margin: 10px 0;
    border-top: 1px dashed #64748b;
}

.receipt-meta,
.receipt-totals {
    display: grid;
    gap: 4px;
}

.meta-row,
.total-row,
.item-detail {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.meta-row span,
.total-row span,
.item-detail span {
    color: #475569;
}

.meta-row strong,
.total-row strong {
    text-align: right;
}

.receipt-item {
    padding: 5px 0;
    break-inside: avoid;
}

.item-name {
    margin: 0 0 2px;
    font-weight: 700;
}

.item-detail {
    font-family: "Courier New", monospace;
    font-size: 9px;
}

.grand-total {
    margin: 3px 0;
    padding: 6px 0;
    border-top: 1px solid #0f172a;
    border-bottom: 1px solid #0f172a;
    font-size: 12px;
    font-weight: 900;
}

.grand-total span,
.grand-total strong {
    color: #0f172a;
}

.receipt-notes {
    margin-top: 10px;
    padding-top: 7px;
    border-top: 1px dashed #94a3b8;
    white-space: pre-line;
}

.receipt-notes p {
    margin: 0;
}

.section-label {
    margin-bottom: 3px !important;
    font-weight: 700;
}

.receipt-footer {
    margin-top: 14px;
    padding-top: 8px;
    border-top: 1px dashed #64748b;
    text-align: center;
}

.receipt-footer p {
    margin: 2px 0;
}

.printed-at {
    margin-top: 8px !important;
    color: #64748b;
    font-size: 8px;
}

@media print {
    :global(html),
    :global(body) {
        width: 58mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .receipt-page {
        width: 58mm;
        min-height: 0;
        margin: 0;
        padding: 0;
        background: white;
        font-size: 9px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .receipt-toolbar {
        display: none;
    }

    .receipt-paper {
        width: 58mm;
        min-height: 0;
        margin: 0;
        padding: 3mm;
        box-shadow: none;
    }
}
</style>
