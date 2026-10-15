<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import SettingForm from '@/Pages/Admin/Settings/SettingForm.vue';

defineProps({
    setting: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pengaturan Bengkel" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Pengaturan Bengkel
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Pengaturan Bengkel' }]" />

        <SettingForm :setting="setting" />
    </AuthenticatedLayout>
</template>
