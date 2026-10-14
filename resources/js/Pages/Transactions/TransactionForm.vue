<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    products: { type: Array, required: true },
    categories: { type: Array, required: true },
    vehicles: { type: Array, required: true },
    mechanics: { type: Array, required: true },
    transaction: { type: Object, default: null },
    initialCart: { type: Array, default: () => [] },
});

const isEditing = computed(() => props.transaction !== null);
const page = usePage();
const isAdmin = computed(() => (page.props.auth?.user?.role?.value ?? page.props.auth?.user?.role) === 'admin');
const backRoute = computed(() => isAdmin.value ? 'admin.transactions.index' : 'kasir.transactions.index');
const categoryId = ref('');
const productSearch = ref('');
const paymentModalOpen = ref(false);

const form = useForm({
    vehicle_id: props.transaction?.vehicle_id ?? '',
    mechanic_id: props.transaction?.mechanic_id ?? '',
    payment_method: props.transaction?.payment_method?.value ?? props.transaction?.payment_method ?? 'cash',
    pay_amount: props.transaction?.pay_amount ?? '',
    discount: props.transaction?.discount ?? 0,
    subtotal: props.transaction?.subtotal ?? 0,
    grand_total: props.transaction?.grand_total ?? 0,
    change_amount: props.transaction?.change_amount ?? 0,
    items: props.initialCart.map((item) => ({
        product_id: item.id ?? item.product_id,
        qty: Number(item.qty),
        selling_price: Number(item.selling_price),
        subtotal: Number(item.selling_price) * Number(item.qty),
        maxStock: item.maxStock,
        type: item.type?.value ?? item.type,
        name: item.name,
    })),
});

watch(() => form.vehicle_id, (vehicleId) => {
    if (!vehicleId) form.mechanic_id = '';
});

watch(() => form.payment_method, (method) => {
    if (method !== 'cash') paymentModalOpen.value = false;
});

const filteredProducts = computed(() => props.products.filter((product) => {
    const matchesCategory = !categoryId.value || String(product.category?.name) === String(categoryId.value);
    const query = productSearch.value.trim().toLowerCase();
    const matchesSearch = !query
        || String(product.name ?? '').toLowerCase().includes(query)
        || String(product.code ?? '').toLowerCase().includes(query);

    return matchesCategory && matchesSearch;
}));

const subtotal = computed(() => form.items.reduce((sum, item) => sum + Number(item.selling_price) * Number(item.qty), 0));
const discount = computed(() => Math.min(Math.max(Number(form.discount) || 0, 0), subtotal.value));
const grandTotal = computed(() => Math.max(subtotal.value - discount.value, 0));
const changeAmount = computed(() => form.payment_method === 'cash'
    ? (Number(form.pay_amount) || 0) - grandTotal.value
    : 0);
const cashShortfall = computed(() => form.payment_method === 'cash' && changeAmount.value < 0);

function productType(product) {
    return product.type?.value ?? product.type;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function addProduct(product) {
    const type = productType(product);
    const existingItem = form.items.find((item) => Number(item.product_id) === Number(product.id));
    const stockLimit = existingItem?.maxStock ?? Number(product.stock ?? 0);

    if (type === 'barang' && (stockLimit <= 0 || (existingItem && existingItem.qty >= stockLimit))) return;

    if (existingItem) {
        existingItem.qty += 1;
        existingItem.subtotal = existingItem.qty * existingItem.selling_price;
    } else {
        form.items.push({
            product_id: product.id,
            name: product.name,
            qty: 1,
            selling_price: Number(product.selling_price),
            subtotal: Number(product.selling_price),
            maxStock: Number(product.stock ?? 0),
            type,
        });
    }
}

function cartQuantity(productId) {
    return form.items.find((item) => Number(item.product_id) === Number(productId))?.qty ?? 0;
}

function isAtStockLimit(product) {
    if (productType(product) !== 'barang') return false;
    const item = form.items.find((cartItem) => Number(cartItem.product_id) === Number(product.id));
    const limit = Number(item?.maxStock ?? product.stock ?? 0);
    return limit <= 0 || Number(item?.qty ?? 0) >= limit;
}

function removeItem(productId) {
    form.items = form.items.filter((item) => Number(item.product_id) !== Number(productId));
}

function decreaseQuantity(item) {
    if (item.qty <= 1) {
        removeItem(item.product_id);
        return;
    }
    item.qty -= 1;
    item.subtotal = Number(item.selling_price) * item.qty;
}

function increaseQuantity(item) {
    if (item.type === 'barang' && item.qty >= Number(item.maxStock ?? 0)) return;
    item.qty += 1;
    item.subtotal = Number(item.selling_price) * item.qty;
}

function resetCart() {
    form.items = [];
}

function submit() {
    form.subtotal = subtotal.value;
    form.discount = discount.value;
    form.grand_total = grandTotal.value;
    form.change_amount = changeAmount.value;
    form.pay_amount = form.payment_method === 'cash'
        ? Number(form.pay_amount) || 0
        : grandTotal.value;
    form.items.forEach((item) => {
        item.subtotal = Number(item.selling_price) * Number(item.qty);
    });

    if (isEditing.value) {
        form.put(route('admin.transactions.update', props.transaction.id));
        return;
    }

    form.post(route(`${isAdmin.value ? 'admin' : 'kasir'}.transactions.store`));
}
</script>

<template>
    <form class="mt-4 grid grid-cols-1 items-start gap-6 font-sans lg:grid-cols-12" @submit.prevent="submit">
        
        <!-- KOLOM KIRI: Katalog Barang & Select Unit (8/12) -->
        <div class="space-y-4 lg:col-span-7 xl:col-span-8">
            
            <!-- Unit & Montir Picker Card -->
            <div class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2">
                <div>
                    <label for="vehicle_id" class="mb-1 block text-xs font-bold uppercase text-slate-600">
                        Kendaraan (Opsional)
                    </label>
                    <select
                        id="vehicle_id"
                        v-model="form.vehicle_id"
                        class="w-full rounded-xl border-slate-200 text-xs font-semibold shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Tanpa kendaraan (pembelian produk)</option>
                        <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                            {{ vehicle.plate_number }} - {{ vehicle.brand }} {{ vehicle.model }} ({{ vehicle.customer?.name ?? 'Tanpa nama' }})
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.vehicle_id" />
                </div>

                <div>
                    <label for="mechanic_id" class="mb-1 block text-xs font-bold uppercase text-slate-600">
                        Montir yang Mengerjakan
                    </label>
                    <select
                        id="mechanic_id"
                        v-model="form.mechanic_id"
                        :disabled="!form.vehicle_id"
                        class="w-full rounded-xl border-slate-200 text-xs font-semibold shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">{{ form.vehicle_id ? 'Tanpa montir' : 'Pilih kendaraan untuk memilih montir' }}</option>
                        <option v-for="mechanic in mechanics" :key="mechanic.id" :value="mechanic.id">
                            {{ mechanic.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.mechanic_id" />
                </div>
            </div>

            <!-- Katalog & Filter Area -->
            <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3">
                    <!-- Search Input -->
                    <div class="w-full">
                        <input
                            v-model="productSearch"
                            type="text"
                            placeholder="Scan barcode / cari nama sparepart atau jasa..."
                            class="w-full rounded-xl border-slate-200 px-3 py-2 text-xs font-semibold shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Category Pills -->
                    <div role="group" aria-label="Filter kategori produk" class="flex w-full flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="whitespace-nowrap rounded-xl px-3 py-1.5 text-xs font-bold transition"
                            :class="categoryId === '' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            @click="categoryId = ''"
                        >
                            Semua
                        </button>
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="whitespace-nowrap rounded-xl px-3 py-1.5 text-xs font-bold transition"
                            :class="categoryId === category.name ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            @click="categoryId = category.name"
                        >
                            {{ category.name }}
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid max-h-[500px] grid-cols-2 gap-3 overflow-y-auto p-1 sm:grid-cols-3 xl:grid-cols-4">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        type="button"
                        :disabled="isAtStockLimit(product)"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-slate-50 p-3 text-left transition-all hover:border-blue-500 hover:bg-blue-50/50 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="addProduct(product)"
                    >
                        <span
                            class="absolute right-2 top-2 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase"
                            :class="productType(product) === 'jasa' ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-700'"
                        >
                            {{ productType(product) }}
                        </span>
                        
                        <div>
                            <span class="block font-mono text-[10px] font-bold text-blue-600">{{ product.code }}</span>
                            <h4 class="mt-0.5 line-clamp-2 text-xs font-bold text-slate-800 group-hover:text-blue-600">
                                {{ product.name }}
                            </h4>
                        </div>

                        <div class="mt-3 flex w-full items-center justify-between border-t border-slate-200/60 pt-2">
                            <span class="font-mono text-xs font-extrabold text-slate-900">Rp {{ formatCurrency(product.selling_price) }}</span>
                            <span
                                v-if="productType(product) === 'barang'"
                                class="text-[10px] font-bold"
                                :class="Number(product.stock) <= Number(product.min_stock) ? 'text-red-500' : 'text-emerald-600'"
                            >
                                Stok: {{ product.stock }}
                            </span>
                        </div>
                        
                        <!-- Optional Floating Cart Quantity Indicator -->
                        <span v-if="cartQuantity(product.id)" class="absolute bottom-[44px] right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white shadow">
                            {{ cartQuantity(product.id) }}
                        </span>
                    </button>
                    <div v-if="filteredProducts.length === 0" class="col-span-full rounded-xl border border-dashed border-slate-200 py-12 text-center text-sm text-slate-400">
                        Produk atau jasa tidak ditemukan.
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.items" />
            </div>
        </div>

        <!-- KOLOM KANAN: Billing & Payment Sticky Panel (4/12) -->
        <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-5 xl:col-span-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">
                    Keranjang Belanja
                </h3>
                <SecondaryButton
                    v-if="form.items.length > 0"
                    type="button"
                    class="px-3 py-1.5 normal-case tracking-normal text-red-600 hover:text-red-700"
                    @click="resetCart"
                >
                    Kosongkan
                </SecondaryButton>
            </div>

            <!-- Items List -->
            <div class="my-4 max-h-[260px] space-y-2 overflow-y-auto pr-1">
                <div v-if="form.items.length === 0" class="py-8 text-center text-slate-400">
                    <p class="text-xs">Belum ada item dipilih.</p>
                    <p class="mt-1 text-[11px]">Pilih produk atau jasa dari katalog.</p>
                </div>
                
                <div
                    v-for="item in form.items"
                    :key="item.product_id"
                    class="flex items-center justify-between rounded-xl border border-slate-200/60 bg-slate-50 p-2.5 text-xs"
                >
                    <div class="flex-1 pr-2">
                        <h5 class="font-bold text-slate-800">{{ item.name }}</h5>
                        <span class="font-mono text-[10px] text-slate-500">@ Rp {{ formatCurrency(item.selling_price) }}</span>
                    </div>
                    
                    <div class="flex shrink-0 items-center gap-1.5">
                        <button type="button" class="flex h-6 w-6 items-center justify-center rounded-lg border border-slate-300 bg-white font-bold hover:bg-slate-100" @click="decreaseQuantity(item)">-</button>
                        <span class="w-6 text-center font-mono font-bold">{{ item.qty }}</span>
                        <button
                            type="button"
                            :disabled="item.type === 'barang' && item.qty >= Number(item.maxStock ?? 0)"
                            class="flex h-6 w-6 items-center justify-center rounded-lg border border-slate-300 bg-white font-bold hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="increaseQuantity(item)"
                        >
                            +
                        </button>
                        <button type="button" aria-label="Hapus item" class="ml-1 flex h-7 w-7 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700" @click="removeItem(item.product_id)">
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <InputError :message="form.errors['items.0.product_id']" />
            <InputError class="mt-1" :message="form.errors['items.0.qty']" />

            <!-- Calculation Summary -->
            <div class="space-y-2 rounded-xl border border-slate-200/80 bg-slate-50 p-3 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-mono font-bold">Rp {{ formatCurrency(subtotal) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 text-slate-600">
                    <label for="transaction-discount" class="font-semibold">Diskon (Rp)</label>
                    <CurrencyInput
                        id="transaction-discount"
                        v-model="form.discount"
                        min="0"
                        :max="subtotal"
                        placeholder="0"
                        class="w-32 rounded-lg border-slate-200 px-2 py-1.5 text-right font-mono text-xs font-bold focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.discount" />
                
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-extrabold text-slate-900">
                    <span>TOTAL BAYAR</span>
                    <span class="font-mono text-blue-600">Rp {{ formatCurrency(grandTotal) }}</span>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-600">Metode Pembayaran</label>
                    <select
                        v-model="form.payment_method"
                        required
                        class="w-full rounded-xl border-slate-200 text-xs font-semibold focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">QRIS / Digital</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.payment_method" />
                </div>

                <div v-if="form.payment_method === 'cash'">
                    <SecondaryButton type="button" class="w-full justify-center" @click="paymentModalOpen = true">
                        Atur pembayaran tunai
                    </SecondaryButton>
                    <p v-if="cashShortfall" class="mt-1 text-center text-xs font-medium text-amber-700">Pembayaran tunai belum cukup.</p>
                </div>

                <PrimaryButton
                    type="submit"
                    class="w-full"
                    :disabled="form.processing || form.items.length === 0 || cashShortfall"
                >
                    {{ form.processing ? 'MEMPROSES...' : (isEditing ? 'PERBARUI TRANSAKSI' : 'PROSES TRANSAKSI') }}
                </PrimaryButton>
                <Link :href="route(backRoute)" class="block">
                    <SecondaryButton type="button" class="w-full justify-center">
                        Batal Transaksi
                    </SecondaryButton>
                </Link>
            </div>
        </div>

        <div
            v-if="paymentModalOpen && form.payment_method === 'cash'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            role="presentation"
            @click.self="paymentModalOpen = false"
            @keydown.esc="paymentModalOpen = false"
        >
            <section
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cash-payment-title"
            >
                <header class="border-b border-slate-100 px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="cash-payment-title" class="text-base font-bold text-slate-900">Pembayaran Tunai</h3>
                            <p class="mt-1 text-xs text-slate-500">Masukkan uang yang diterima untuk menghitung kembalian.</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg px-2 py-1 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tutup dialog pembayaran"
                            @click="paymentModalOpen = false"
                        >
                            &times;
                        </button>
                    </div>
                </header>

                <div class="space-y-4 p-5">
                    <div class="flex items-center justify-between rounded-xl bg-blue-50 px-4 py-3">
                        <span class="text-sm font-semibold text-blue-900">Total yang harus dibayar</span>
                        <span class="font-mono text-base font-extrabold text-blue-700">Rp {{ formatCurrency(grandTotal) }}</span>
                    </div>

                    <div>
                        <label for="pay_amount" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Uang diterima (Rp)</label>
                        <CurrencyInput
                            id="pay_amount"
                            v-model="form.pay_amount"
                            min="0"
                            step="1"
                            autofocus
                            @keydown.enter.prevent
                            class="w-full rounded-xl border-slate-300 py-3 text-right font-mono text-lg font-bold focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Contoh: 100.000"
                        />
                        <InputError class="mt-1" :message="form.errors.pay_amount" />
                        <SecondaryButton
                            type="button"
                            class="mt-2 px-3 py-1.5 normal-case tracking-normal text-blue-700"
                            @click="form.pay_amount = grandTotal"
                        >
                            Isi uang pas
                        </SecondaryButton>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-xl px-4 py-3"
                        :class="cashShortfall ? 'bg-red-50 text-red-800' : 'bg-emerald-50 text-emerald-800'"
                    >
                        <span class="text-sm font-bold">{{ cashShortfall ? 'Kekurangan pembayaran' : 'Kembalian' }}</span>
                        <span class="font-mono text-base font-extrabold">Rp {{ formatCurrency(cashShortfall ? Math.abs(changeAmount) : changeAmount) }}</span>
                    </div>
                </div>

                <footer class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                    <SecondaryButton type="button" @click="paymentModalOpen = false">Tutup</SecondaryButton>
                    <PrimaryButton type="button" :disabled="cashShortfall" @click="paymentModalOpen = false">
                        Simpan pembayaran
                    </PrimaryButton>
                </footer>
            </section>
        </div>
    </form>
</template>
