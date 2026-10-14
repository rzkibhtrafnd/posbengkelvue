<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    mechanics: { type: Object, required: true },
});

function removeMechanic(mechanic) {
    if (!window.confirm(`Hapus data montir "${mechanic.name}"?`)) return;
    router.delete(route('admin.mechanics.destroy', mechanic.id), { preserveScroll: true });
}

function rowNumber(index) {
    return (props.mechanics.current_page - 1) * props.mechanics.per_page + index + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kelola Montir" />
        <template #header>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Kelola Montir</h2>
        </template>

        <Breadcrumb :items="[{ label: 'Kelola Montir' }]" />
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Daftar Montir</h3>
                    <p class="mt-1 text-sm text-slate-500">Kelola data dan status montir bengkel.</p>
                </div>
                <Link :href="route('admin.mechanics.create')" class="shrink-0">
                    <PrimaryButton type="button"><i class="fa-solid fa-plus mr-2"></i>Tambah Montir</PrimaryButton>
                </Link>
            </header>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-sm font-medium text-slate-600">
                            <th scope="col" class="px-3 py-2">No.</th>
                            <th scope="col" class="px-3 py-2">Kode</th>
                            <th scope="col" class="px-3 py-2">Nama Montir</th>
                            <th scope="col" class="px-3 py-2">Telepon</th>
                            <th scope="col" class="px-3 py-2">Status</th>
                            <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr v-for="(mechanic, index) in mechanics.data" :key="mechanic.id" class="hover:bg-slate-50">
                            <td class="px-3 py-3 font-mono text-slate-400">{{ rowNumber(index) }}</td>
                            <td class="px-3 py-3 font-mono font-semibold text-slate-600">{{ mechanic.code }}</td>
                            <td class="px-3 py-3 font-semibold text-slate-900">{{ mechanic.name }}</td>
                            <td class="px-3 py-3">{{ mechanic.phone || '—' }}</td>
                            <td class="px-3 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                    :class="(mechanic.status?.value ?? mechanic.status) === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                                    {{ (mechanic.status?.value ?? mechanic.status) === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route('admin.mechanics.show', mechanic.id)"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        title="Detail"
                                    >
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        Detail
                                    </Link>
                                    <Link
                                        :href="route('admin.mechanics.edit', mechanic.id)"
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
                                        @click="removeMechanic(mechanic)"
                                    >
                                        <i class="fa-solid fa-trash mr-1"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="mechanics.data.length === 0">
                            <td colspan="6" class="px-3 py-8 text-center text-slate-400">Belum ada data montir.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationControls :paginator="mechanics" route-name="admin.mechanics.index" />
        </section>
    </AuthenticatedLayout>
</template>
