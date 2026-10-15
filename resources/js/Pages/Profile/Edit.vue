<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-2xl">
                Profil
            </h2>
        </template>

        <Breadcrumb :items="[{ label: 'Profil' }]" />

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <header class="border-b border-slate-100 pb-5">
                <h3 class="text-lg font-bold tracking-tight text-slate-900">Pengaturan Akun</h3>
                <p class="mt-1 text-sm text-slate-500">Kelola informasi profil dan keamanan akun Anda.</p>
            </header>

            <div class="mt-5 grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-6">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-6">
                    <UpdatePasswordForm />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
