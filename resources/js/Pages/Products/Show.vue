<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const isAdmin = computed(() => usePage().props.auth.user.role === 'admin');
const indexRoute = computed(() => (
    isAdmin.value ? 'admin.products.index' : 'kasir.products.index'
));
const type = computed(() => props.product.type?.value ?? props.product.type);
const isPhysicalProduct = computed(() => type.value === 'barang');
const formattedCostPrice = computed(() => (
    new Intl.NumberFormat('id-ID').format(Number(props.product.cost_price) || 0)
));
const formattedSellingPrice = computed(() => (
    new Intl.NumberFormat('id-ID').format(Number(props.product.selling_price) || 0)
));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail ${product.name}`" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Detail Produk / Jasa
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Katalog Produk', url: route(indexRoute) },
                { label: `Detail ${product.name}` },
            ]"
        />

        <section class="mx-auto mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6">
                <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span
                            class="rounded-md px-2.5 py-0.5 text-xs font-bold uppercase"
                            :class="isPhysicalProduct
                                ? 'bg-slate-100 text-slate-700'
                                : 'bg-purple-100 text-purple-700'"
                        >
                            {{ type }}
                        </span>
                        <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                            {{ product.name }}
                        </h3>
                        <p class="mt-0.5 font-mono text-xs font-bold text-blue-600">
                            Kode: {{ product.code }}
                        </p>
                    </div>
                </header>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <section class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Informasi Item
                        </h4>
                        <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Kategori</dt>
                                <dd class="text-right font-semibold text-slate-800">
                                    {{ product.category?.name ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Harga Jual</dt>
                                <dd class="text-right font-mono font-bold text-slate-900">
                                    Rp {{ formattedSellingPrice }}
                                </dd>
                            </div>
                            <div v-if="isPhysicalProduct" class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Harga Modal/Beli</dt>
                                <dd class="text-right font-mono text-slate-700">
                                    Rp {{ formattedCostPrice }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Status Inventaris
                        </h4>
                        <dl
                            v-if="isPhysicalProduct"
                            class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Sisa Stok</dt>
                                <dd class="text-right font-mono font-bold text-slate-800">
                                    {{ product.stock }} {{ product.unit }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Batas Minimum Stok</dt>
                                <dd class="text-right font-mono text-slate-700">
                                    {{ product.min_stock }} {{ product.unit }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Satuan</dt>
                                <dd class="text-right font-medium text-slate-800">
                                    {{ product.unit || '—' }}
                                </dd>
                            </div>
                        </dl>
                        <div
                            v-else
                            class="rounded-xl border border-purple-100 bg-purple-50/50 p-4 text-sm text-purple-800"
                        >
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Item ini berjenis <strong>Jasa/Layanan</strong> dan tidak mengikat stok barang inventaris.
                        </div>
                    </section>

                    <section class="space-y-2 md:col-span-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Deskripsi / Catatan
                        </h4>
                        <p class="whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                            {{ product.description || 'Tidak ada deskripsi tambahan.' }}
                        </p>
                    </section>
                </div>

                <footer class="mt-4 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route(indexRoute)">
                        <SecondaryButton type="button">
                            Kembali
                        </SecondaryButton>
                    </Link>
                    <Link
                        v-if="isAdmin"
                        :href="route('admin.products.edit', product.id)"
                    >
                        <PrimaryButton type="button">
                            Edit Produk
                        </PrimaryButton>
                    </Link>
                </footer>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
