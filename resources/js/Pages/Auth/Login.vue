<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const workshop = computed(() => page.props.workshop || {});
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-blue-600">Selamat datang</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Masuk ke akun Anda</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Gunakan email dan kata sandi yang telah terdaftar untuk melanjutkan.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800"
            role="status"
        >
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <span>{{ status }}</span>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Alamat email" class="font-semibold text-slate-700" />
                <div class="relative mt-2">
                    <i class="fa-regular fa-envelope pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full rounded-xl border-slate-200 py-3 pl-11 pr-4 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-600 focus:ring-blue-600"
                        placeholder="nama@email.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata sandi" class="font-semibold text-slate-700" />
                <div class="relative mt-2">
                    <i class="fa-solid fa-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="block w-full rounded-xl border-slate-200 py-3 pl-11 pr-4 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-600 focus:ring-blue-600"
                        placeholder="Masukkan kata sandi"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    name="remember"
                    class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-600"
                />
                Ingat saya di perangkat ini
            </label>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing"
            >
                <i v-if="form.processing" class="fa-solid fa-circle-notch animate-spin"></i>
                <i v-else class="fa-solid fa-arrow-right-to-bracket"></i>
                {{ form.processing ? 'Sedang masuk...' : 'Masuk' }}
            </button>
        </form>
    </GuestLayout>
</template>
