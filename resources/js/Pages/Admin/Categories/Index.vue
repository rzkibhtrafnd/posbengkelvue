<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

/**
 * @typedef {Object} Category
 * @property {number} id
 * @property {string} name
 * @property {string|null} description
 */

/**
 * @typedef {Object} CategoryPagination
 * @property {Category[]} data
 * @property {number} current_page
 * @property {number} per_page
 * @property {Array<{url: string|null, label: string, active: boolean}>} links
 */

defineProps({
    /** @type {import('vue').PropType<CategoryPagination>} */
    categories: {
        type: Object,
        required: true,
    },
});

function deleteCategory(category) {
    if (!window.confirm(`Hapus kategori "${category.name}"?`)) {
        return;
    }

    router.delete(route('admin.categories.destroy', category.id), {
        preserveScroll: true,
    });
}

function categoryNumber(categories, index) {
    return (categories.current_page - 1) * categories.per_page + index + 1;
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kelola Kategori" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Kelola Kategori
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Kelola Kategori' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                        Daftar Kategori
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Kelola kategori produk bengkel.
                    </p>
                </div>

                <Link :href="route('admin.categories.create')" class="shrink-0">
                    <PrimaryButton type="button">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Kategori
                    </PrimaryButton>
                </Link>
            </header>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr class="text-left text-sm font-medium text-slate-600">
                            <th class="px-3 py-2" scope="col">No.</th>
                            <th class="px-3 py-2" scope="col">Nama Kategori</th>
                            <th class="px-3 py-2" scope="col">Deskripsi</th>
                            <th class="px-3 py-2 text-center" scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <tr
                            v-for="(category, index) in categories.data"
                            :key="category.id"
                            class="transition-colors hover:bg-slate-50/60"
                        >
                            <td class="px-3 py-3 font-mono text-slate-400">
                                {{ categoryNumber(categories, index) }}
                            </td>
                            <td class="px-3 py-3 font-semibold text-slate-900">
                                {{ category.name }}
                            </td>
                            <td class="max-w-xl whitespace-pre-line px-3 py-3 text-slate-600">
                                {{ category.description || '—' }}
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="route('admin.categories.edit', category.id)"
                                        class="inline-flex items-center rounded border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <i class="fa-solid fa-pen mr-1"></i>
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        @click="deleteCategory(category)"
                                    >
                                        <i class="fa-solid fa-trash mr-1"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td class="px-3 py-6 text-center text-slate-400" colspan="4">
                                Belum ada kategori yang terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls :paginator="categories" route-name="admin.categories.index" />
        </section>
    </AuthenticatedLayout>
</template>
