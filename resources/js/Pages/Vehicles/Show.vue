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
});

const user = computed(() => usePage().props.auth?.user ?? {});
const isAdmin = computed(() => (user.value.role?.value ?? user.value.role) === 'admin');
const indexRoute = computed(() => (
    isAdmin.value ? 'admin.vehicles.index' : 'kasir.vehicles.index'
));
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
                <header class="border-b border-slate-100 pb-5">
                    <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold uppercase text-slate-700">
                        Kendaraan
                    </span>
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                        {{ vehicle.plate_number }}
                    </h3>
                    <p class="mt-0.5 text-xs font-bold text-blue-600">
                        {{ vehicle.brand }} {{ vehicle.model }}
                    </p>
                </header>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <section class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Informasi Kendaraan
                        </h4>
                        <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Nomor Polisi</dt>
                                <dd class="text-right font-mono font-bold uppercase text-slate-900">
                                    {{ vehicle.plate_number }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Merek</dt>
                                <dd class="text-right font-semibold text-slate-800">
                                    {{ vehicle.brand }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Model / Tipe</dt>
                                <dd class="text-right font-semibold text-slate-800">
                                    {{ vehicle.model }}
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

                    <section class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Informasi Pemilik
                        </h4>
                        <dl class="space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Nama</dt>
                                <dd class="text-right font-semibold text-slate-800">
                                    {{ vehicle.customer?.name ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4 text-sm">
                                <dt class="text-slate-500">Telepon</dt>
                                <dd class="text-right font-medium text-slate-800">
                                    {{ vehicle.customer?.phone ?? '—' }}
                                </dd>
                            </div>
                            <div class="flex flex-col gap-1 text-sm">
                                <dt class="text-slate-500">Alamat</dt>
                                <dd class="whitespace-pre-line font-medium text-slate-800">
                                    {{ vehicle.customer?.address ?? '—' }}
                                </dd>
                            </div>
                        </dl>
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
                        :href="route('admin.vehicles.edit', vehicle.id)"
                    >
                        <PrimaryButton type="button">
                            Edit Kendaraan
                        </PrimaryButton>
                    </Link>
                </footer>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
