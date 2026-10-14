<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({ mechanic: { type: Object, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Detail Montir: ${mechanic.name}`" />
        <template #header><h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Detail Montir</h2></template>
        <Breadcrumb :items="[{ label: 'Kelola Montir', url: route('admin.mechanics.index') }, { label: 'Detail Montir' }]" />
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-mono text-sm text-slate-500">{{ mechanic.code }}</p>
                    <h3 class="mt-1 text-xl font-bold text-slate-900">{{ mechanic.name }}</h3>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.mechanics.index')" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Kembali</Link>
                    <Link :href="route('admin.mechanics.edit', mechanic.id)"><PrimaryButton type="button"><i class="fa-solid fa-pen mr-2"></i>Edit</PrimaryButton></Link>
                </div>
            </header>
            <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor Telepon</dt><dd class="mt-1 text-sm font-medium text-slate-900">{{ mechanic.phone || 'Belum diisi' }}</dd></div>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1 text-sm font-semibold" :class="(mechanic.status?.value ?? mechanic.status) === 'active' ? 'text-emerald-700' : 'text-slate-600'">{{ (mechanic.status?.value ?? mechanic.status) === 'active' ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div class="rounded-xl bg-slate-50 p-4 sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat</dt><dd class="mt-1 whitespace-pre-line text-sm text-slate-900">{{ mechanic.address || 'Belum diisi' }}</dd></div>
            </dl>
        </section>
    </AuthenticatedLayout>
</template>
