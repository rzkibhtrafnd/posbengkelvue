<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    mechanic: {
        type: Object,
        default: null,
    },
    generatedCode: {
        type: String,
        default: '',
    },
});

const isEditing = computed(() => props.mechanic !== null);
const title = computed(() => (isEditing.value ? 'Edit Data Montir' : 'Data Montir Baru'));
const description = computed(() => (
    isEditing.value
        ? 'Perbarui informasi montir.'
        : 'Masukkan informasi montir bengkel.'
));
const submitLabel = computed(() => (isEditing.value ? 'Perbarui' : 'Simpan'));

const form = useForm({
    code: props.mechanic?.code ?? props.generatedCode,
    name: props.mechanic?.name ?? '',
    phone: props.mechanic?.phone ?? '',
    address: props.mechanic?.address ?? '',
    status: props.mechanic ? (props.mechanic.status?.value ?? props.mechanic.status) : 'active',
});

function submit() {
    if (isEditing.value) {
        form.put(route('admin.mechanics.update', props.mechanic.id));
        return;
    }

    form.post(route('admin.mechanics.store'));
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ title }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                {{ description }}
            </p>
            <form
                class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel for="code" value="Kode Montir" />
                    <TextInput
                        id="code"
                        v-model="form.code"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="50"
                        autocomplete="off"
                        placeholder="Contoh: MTR-0001"
                    />
                    <p class="mt-1 text-xs text-slate-400">Kosongkan untuk membuat kode otomatis.</p>
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>
                <div>
                    <InputLabel for="name" value="Nama Montir" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="255"
                        required
                        autofocus
                        placeholder="Nama lengkap"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="phone" value="Nomor Telepon" />
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="mt-1 block w-full"
                        maxlength="20"
                        placeholder="08xxxxxxxxxx"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
                <div>
                    <InputLabel for="status" value="Status" />
                    <select
                        id="status"
                        v-model="form.status"
                        required
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.status" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="address" value="Alamat" />
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="4"
                        maxlength="500"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Alamat montir (opsional)"
                    ></textarea>
                    <div class="mt-2 flex justify-between gap-4">
                        <InputError :message="form.errors.address" />
                        <span class="shrink-0 text-xs text-slate-400">{{ form.address.length }}/500</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3 md:col-span-2">
                    <Link :href="route('admin.mechanics.index')">
                        <SecondaryButton type="button">
                            Batal
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : submitLabel }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </section>
</template>
