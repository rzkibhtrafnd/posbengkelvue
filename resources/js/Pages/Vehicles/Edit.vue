<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import VehicleForm from '@/Pages/Vehicles/VehicleForm.vue';

defineProps({ vehicle: { type: Object, required: true }, customers: { type: Array, required: true } });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Kendaraan" />
        <template #header><h2 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">Edit Kendaraan</h2></template>
        <Breadcrumb :items="[{ label: 'Data Kendaraan', url: route('admin.vehicles.index') }, { label: 'Edit Kendaraan' }]" />
        <VehicleForm :vehicle="vehicle" :customers="customers" />
    </AuthenticatedLayout>
</template>
