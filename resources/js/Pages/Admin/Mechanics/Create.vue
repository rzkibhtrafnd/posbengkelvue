<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import MechanicForm from '@/Pages/Admin/Mechanics/MechanicForm.vue';

defineProps({ generatedCode: { type: String, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Montir" />
        <template #header><h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Tambah Montir</h2></template>
        <Breadcrumb :items="[{ label: 'Kelola Montir', url: route('admin.mechanics.index') }, { label: 'Tambah Montir' }]" />
        <MechanicForm :generated-code="generatedCode" />
    </AuthenticatedLayout>
</template>
