<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UserForm from '@/Pages/Admin/Users/UserForm.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah User" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Tambah User
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Kelola Pengguna', url: route('admin.users.index') },
                { label: 'Tambah User Baru' },
            ]"
        />

        <UserForm />
    </AuthenticatedLayout>
</template>
