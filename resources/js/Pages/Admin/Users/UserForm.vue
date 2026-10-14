<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const authUserId = computed(() => usePage().props.auth.user.id);
const isEditing = computed(() => props.user !== null);
const isSelf = computed(() => isEditing.value && props.user.id === authUserId.value);
const submitLabel = computed(() => (isEditing.value ? 'Perbarui' : 'Simpan'));
const formTitle = computed(() => (isEditing.value ? 'Edit Profil Pengguna' : 'User Baru'));
const formDescription = computed(() => (
    isEditing.value
        ? 'Isi formulir di bawah jika ingin memperbarui konfigurasi data atau password.'
        : 'Lengkapi data kredensial akses pengguna operasional POS Bengkel.'
));

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'kasir',
    password: '',
    password_confirmation: '',
});

function submit() {
    const options = {
        onFinish: () => form.reset('password', 'password_confirmation'),
    };

    if (isEditing.value) {
        form.patch(route('admin.users.update', props.user.id), options);
        return;
    }

    form.post(route('admin.users.store'), options);
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ formTitle }}
            </h3>
            <p class="text-sm text-slate-500">
                {{ formDescription }}
            </p>

            <form
                class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div :class="!isEditing && 'md:col-span-2'">
                    <InputLabel for="name" value="Nama Lengkap" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Contoh: Budi Santoso"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Alamat Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autocomplete="email"
                        placeholder="nama@contoh.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div :class="isEditing && 'md:col-span-2'">
                    <InputLabel for="role" value="Hak Akses (Role)" />
                    <select
                        id="role"
                        v-model="form.role"
                        :disabled="isSelf"
                        required
                        class="mt-1 block w-full cursor-pointer rounded-xl border-slate-300 p-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-slate-100"
                    >
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin Utama</option>
                    </select>
                    <p v-if="isSelf" class="mt-1.5 text-[11px] italic text-slate-400">
                        *Anda tidak dapat mengubah status hierarki akun Anda sendiri.
                    </p>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div class="mt-2 border-t pt-4 md:col-span-2">
                    <div
                        v-if="isEditing"
                        class="rounded-xl border border-slate-100 bg-slate-50/60 p-4"
                    >
                        <h4 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                            Ubah Password (Kosongkan jika tidak ingin diubah)
                        </h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="password" value="Password Baru" />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full bg-white"
                                    autocomplete="new-password"
                                    placeholder="Kosongkan jika tidak diubah"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    value="Konfirmasi Password Baru"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full bg-white"
                                    autocomplete="new-password"
                                    placeholder="Ulangi password baru"
                                />
                            </div>
                        </div>
                    </div>

                    <template v-else>
                        <h4 class="mb-3 text-sm font-bold text-slate-700">
                            Konfigurasi Keamanan Kredensial
                        </h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimal 8 karakter"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    value="Konfirmasi Password"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                />
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3 md:col-span-2">
                    <Link :href="route('admin.users.index')">
                        <SecondaryButton type="button">
                            Batal
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ submitLabel }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </section>
</template>
