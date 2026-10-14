<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UserForm from '@/Pages/Admin/Users/UserForm.vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit User" />

        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Edit User
            </h2>
        </template>

        <Breadcrumb
            :items="[
                { label: 'Kelola Pengguna', url: route('admin.users.index') },
                { label: 'Edit User' },
            ]"
        />

        <UserForm :user="user" />
    </AuthenticatedLayout>
</template>
