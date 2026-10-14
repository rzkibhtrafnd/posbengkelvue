<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

/**
 * @typedef {Object} UserSummary
 * @property {number} id
 * @property {string} name
 * @property {string} email
 * @property {string} role
 */

/**
 * @typedef {Object} UserPagination
 * @property {UserSummary[]} data
 * @property {number} current_page
 * @property {number} per_page
 * @property {Array<{url: string|null, label: string, active: boolean}>} links
 */

const props = defineProps({
    /** @type {import('vue').PropType<UserPagination>} */
    users: {
        type: Object,
        required: true,
    },
});

const authUserId = computed(() => usePage().props.auth.user.id);

function deleteUser(userId) {
    if (!window.confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        return;
    }

    router.delete(route('admin.users.destroy', userId), {
        preserveScroll: true,
    });
}

function userNumber(index) {
    return (props.users.current_page - 1) * props.users.per_page + index + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kelola Pengguna" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Kelola Pengguna Sistem
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Kelola Pengguna' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                        Daftar Pengguna
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Kelola akun pengguna, hak akses admin utama, dan operator kasir.
                    </p>
                </div>

                <PrimaryButton
                    type="button"
                    class="shrink-0"
                    @click="router.visit(route('admin.users.create'))"
                >
                    <i class="fa-solid fa-plus mr-2"></i>
                    Tambah Pengguna
                </PrimaryButton>
            </header>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-sm font-medium text-slate-600">
                            <th class="px-3 py-2" scope="col">No.</th>
                            <th class="px-3 py-2" scope="col">Nama Pengguna</th>
                            <th class="px-3 py-2" scope="col">Email</th>
                            <th class="px-3 py-2 text-center" scope="col">Hak Akses (Role)</th>
                            <th class="px-3 py-2 text-center" scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr
                            v-for="(user, index) in users.data"
                            :key="user.id"
                            class="transition-colors hover:bg-slate-50/60"
                        >
                            <td class="px-3 py-3 font-mono text-slate-400">
                                {{ userNumber(index) }}
                            </td>
                            <td class="px-3 py-3 font-semibold text-slate-900">
                                {{ user.name }}
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ user.email }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-block rounded-xl border px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider"
                                    :class="user.role === 'admin'
                                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                                        : 'border-blue-200 bg-blue-50 text-blue-700'"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route('admin.users.edit', user.id)"
                                        class="inline-flex items-center rounded border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <i class="fa-solid fa-pen mr-1"></i>
                                        Edit
                                    </Link>

                                    <button
                                        v-if="user.id !== authUserId"
                                        type="button"
                                        class="inline-flex items-center rounded border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        @click="deleteUser(user.id)"
                                    >
                                        <i class="fa-solid fa-trash mr-1"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td class="px-3 py-6 text-center text-slate-400" colspan="5">
                                Belum ada pengguna sistem terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls :paginator="users" route-name="admin.users.index" />
        </section>
    </AuthenticatedLayout>
</template>
