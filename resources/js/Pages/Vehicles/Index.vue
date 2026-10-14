<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    vehicles: { type: Object, required: true },
    brands: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const isAdmin = computed(() => (page.props.auth?.user?.role?.value ?? page.props.auth?.user?.role) === 'admin');
const indexRoute = computed(() => isAdmin.value ? 'admin.vehicles.index' : 'kasir.vehicles.index');
const showRoute = computed(() => isAdmin.value ? 'admin.vehicles.show' : 'kasir.vehicles.show');
const filterForm = useForm({
    search: props.filters.search ?? '',
    brand: props.filters.brand ?? '',
});
const paginationFilters = computed(() => ({
    search: props.filters.search ?? '',
    brand: props.filters.brand ?? '',
}));
const hasFilters = computed(() => Boolean(filterForm.search || filterForm.brand));

function applyFilters() {
    router.get(
        route(indexRoute.value),
        {
            search: filterForm.search,
            brand: filterForm.brand,
            per_page: props.vehicles.per_page,
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
    filterForm.brand = '';
    router.get(
        route(indexRoute.value),
        { per_page: props.vehicles.per_page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function rowNumber(index) {
    return (props.vehicles.current_page - 1) * props.vehicles.per_page + index + 1;
}

function removeVehicle(vehicle) {
    if (!window.confirm(`Hapus kendaraan ${vehicle.plate_number}?`)) return;
    router.delete(route('admin.vehicles.destroy', vehicle.id), { preserveScroll: true });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Data Kendaraan" />
        <template #header><h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Data Kendaraan</h2></template>
        <Breadcrumb :items="[{ label: 'Data Kendaraan' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Daftar Kendaraan</h3>
                    <p class="mt-1 text-sm text-slate-500">Cari kendaraan berdasarkan nomor polisi, pemilik, atau merek.</p>
                </div>
                <Link v-if="isAdmin" :href="route('admin.vehicles.create')" class="shrink-0">
                    <PrimaryButton type="button"><i class="fa-solid fa-plus mr-2"></i>Tambah Kendaraan</PrimaryButton>
                </Link>
            </header>

            <form
                class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200/60 bg-slate-50/70 px-4 py-3"
                @submit.prevent="applyFilters"
            >
                <div class="flex min-w-[220px] flex-1 items-center gap-2">
                    <span class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400">Cari</span>
                    <input
                        v-model="filterForm.search"
                        type="search"
                        name="search"
                        placeholder="Plat, nama pemilik, atau telepon..."
                        class="w-full rounded-xl border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold shadow-sm placeholder:text-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <div class="flex min-w-[180px] items-center gap-2">
                    <select
                        v-model="filterForm.brand"
                        name="brand"
                        class="w-full cursor-pointer rounded-xl border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">Semua Merek</option>
                        <option v-for="brand in brands" :key="brand.brand" :value="brand.brand">{{ brand.brand }}</option>
                    </select>
                </div>

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
                            <th scope="col" class="px-3 py-2">Kendaraan</th>
                            <th scope="col" class="px-3 py-2">Pemilik</th>
                            <th scope="col" class="px-3 py-2">Telepon</th>
                            <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr v-for="(vehicle, index) in vehicles.data" :key="vehicle.id" class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-mono text-slate-400">{{ rowNumber(index) }}</td>
                            <td class="px-3 py-3">
                                <p class="font-bold uppercase text-slate-900">{{ vehicle.plate_number }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ vehicle.brand }} {{ vehicle.model }}<span v-if="vehicle.year"> · {{ vehicle.year }}</span></p>
                            </td>
                            <td class="px-3 py-3 font-medium text-slate-800">{{ vehicle.customer?.name || '—' }}</td>
                            <td class="px-3 py-3">{{ vehicle.customer?.phone || '—' }}</td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route(showRoute, vehicle.id)"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        title="Detail"
                                    >
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </Link>
                                    <template v-if="isAdmin">
                                        <Link
                                            :href="route('admin.vehicles.edit', vehicle.id)"
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
                                            @click="removeVehicle(vehicle)"
                                        >
                                            <i class="fa-solid fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="vehicles.data.length === 0">
                            <td colspan="5" class="px-3 py-8 text-center text-slate-400">Tidak ada kendaraan yang cocok dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationControls :paginator="vehicles" :filters="paginationFilters" :route-name="indexRoute" />
        </section>
    </AuthenticatedLayout>
</template>
