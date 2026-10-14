<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const isAdmin = computed(() => usePage().props.auth.user.role === 'admin');
const indexRoute = computed(() => (
    isAdmin.value ? 'admin.products.index' : 'kasir.products.index'
));
const filterForm = useForm({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
});
const paginationFilters = computed(() => ({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
}));
const hasFilters = computed(() => Boolean(filterForm.search || filterForm.category_id));

function applyFilters() {
    router.get(
        route(indexRoute.value),
        {
            search: filterForm.search,
            category_id: filterForm.category_id,
            per_page: props.products.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters() {
    filterForm.search = '';
    filterForm.category_id = '';
    router.get(
        route(indexRoute.value),
        { per_page: props.products.per_page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function deleteProduct(product) {
    if (!window.confirm(`Apakah Anda yakin ingin menghapus produk "${product.name}"?`)) {
        return;
    }

    router.delete(route('admin.products.destroy', product.id), {
        preserveScroll: true,
    });
}

function productNumber(index) {
    return (props.products.current_page - 1) * props.products.per_page + index + 1;
}

function productType(product) {
    return product.type?.value ?? product.type;
}

function formatPrice(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Katalog Produk & Jasa" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Katalog Produk &amp; Jasa
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Katalog Produk' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                        Daftar Barang &amp; Layanan
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Daftar katalog sparepart, oli, peralatan, serta biaya jasa servis bengkel.
                    </p>
                </div>

                <Link
                    v-if="isAdmin"
                    :href="route('admin.products.create')"
                    class="shrink-0"
                >
                    <PrimaryButton type="button">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Produk
                    </PrimaryButton>
                </Link>
            </header>

            <form
                class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200/60 bg-slate-50/70 px-4 py-3"
                @submit.prevent="applyFilters"
            >
                <div class="flex min-w-[220px] flex-1 items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">
                        Cari
                    </span>
                    <input
                        v-model="filterForm.search"
                        type="search"
                        name="search"
                        placeholder="Nama produk atau kode/SKU..."
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold shadow-sm placeholder:text-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <!-- Modifikasi Penambahan pr-8 pada select -->
                <div class="flex min-w-[180px] items-center gap-2">
                    <select
                        v-model="filterForm.category_id"
                        name="category_id"
                        class="w-full cursor-pointer rounded-xl border-slate-200 bg-white pl-3 pr-8 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <PrimaryButton type="submit">
                        <i class="fa-solid fa-magnifying-glass mr-2 text-[10px]"></i>
                        Cari
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="hasFilters"
                        type="button"
                        @click="resetFilters"
                    >
                        <i class="fa-solid fa-rotate-left mr-2 text-slate-400"></i>
                        Reset
                    </SecondaryButton>
                </div>
            </form>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-sm font-medium text-slate-600">
                            <th class="px-3 py-2" scope="col">No.</th>
                            <th class="px-3 py-2" scope="col">Kode/SKU</th>
                            <th class="px-3 py-2" scope="col">Nama Produk</th>
                            <th class="px-3 py-2" scope="col">Kategori</th>
                            <th class="px-3 py-2 text-right" scope="col">Harga Jual</th>
                            <th class="px-3 py-2 text-center" scope="col">Stok</th>
                            <th class="px-3 py-2 text-center" scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr
                            v-for="(product, index) in products.data"
                            :key="product.id"
                            class="transition-colors hover:bg-slate-50/60"
                        >
                            <td class="px-3 py-3 font-mono text-slate-400">
                                {{ productNumber(index) }}
                            </td>
                            <td class="px-3 py-3 font-mono text-xs font-bold text-blue-600">
                                {{ product.code }}
                            </td>
                            <td class="px-3 py-3 font-semibold text-slate-900">
                                {{ product.name }}
                                <span
                                    class="ml-1 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase"
                                    :class="productType(product) === 'jasa'
                                        ? 'bg-purple-100 text-purple-700'
                                        : 'bg-slate-100 text-slate-600'"
                                >
                                    {{ productType(product) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ product.category?.name ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-right font-mono font-semibold text-slate-900">
                                Rp {{ formatPrice(product.selling_price) }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    v-if="productType(product) === 'jasa'"
                                    class="text-xs italic text-slate-400"
                                >
                                    —
                                </span>
                                <span
                                    v-else
                                    class="inline-block rounded-xl border px-2.5 py-0.5 text-xs font-bold"
                                    :class="product.stock <= product.min_stock
                                        ? 'border-red-200 bg-red-50 text-red-700'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                                >
                                    {{ product.stock }} {{ product.unit }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route(`${indexRoute.split('.')[0]}.products.show`, product.id)"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        title="Detail"
                                    >
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </Link>
                                    <template v-if="isAdmin">
                                        <Link
                                            :href="route('admin.products.edit', product.id)"
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
                                            @click="deleteProduct(product)"
                                        >
                                            <i class="fa-solid fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td class="px-3 py-6 text-center text-slate-400" colspan="7">
                                {{ hasFilters
                                    ? 'Produk tidak ditemukan berdasarkan filter/pencarian.'
                                    : 'Belum ada produk atau jasa terdaftar.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls
                :paginator="products"
                :filters="paginationFilters"
                :route-name="indexRoute"
            />
        </section>
    </AuthenticatedLayout>
</template>