<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    setting: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    name: props.setting?.name ?? '',
    phone: props.setting?.phone ?? '',
    email: props.setting?.email ?? '',
    address: props.setting?.address ?? '',
    logo: null,
    remove_logo: false,
    banks: (props.setting?.banks ?? []).map((bank) => ({
        bank_name: bank.bank_name ?? '',
        bank_account_number: bank.bank_account_number ?? '',
        bank_account_holder: bank.bank_account_holder ?? '',
    })),
    qris_payload: props.setting?.qris_payload ?? '',
    tax_percentage: props.setting?.tax_percentage ?? '0.00',
});

const logoPreview = ref(null);
const logoUrl = computed(() => logoPreview.value || (form.remove_logo ? null : props.setting?.logo_url));

function selectImage(event, key) {
    const file = event.target.files?.[0] ?? null;

    if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
    logoPreview.value = file ? URL.createObjectURL(file) : null;
    form[key] = file;
    form.remove_logo = false;
}

function removeImage(key) {
    if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
    logoPreview.value = null;
    form[key] = null;
    form.remove_logo = true;
}

function addBank() {
    form.banks.push({
        bank_name: '',
        bank_account_number: '',
        bank_account_holder: '',
    });
}

function removeBank(index) {
    form.banks.splice(index, 1);
}

function submit() {
    form.post(route('admin.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
}

onBeforeUnmount(() => {
    if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
});
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">Pengaturan Bengkel</h3>
            <p class="mt-1 text-sm text-slate-500">Atur identitas bengkel dan informasi pembayaran yang digunakan pada struk.</p>
            <form class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submit">
                <div class="border-b border-slate-100 pb-2 md:col-span-2">
                    <h4 class="text-sm font-bold text-slate-700">Identitas Bengkel</h4>
                </div>
                <div>
                    <InputLabel for="name" value="Nama Bengkel" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required maxlength="255" placeholder="Nama bengkel" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="phone" value="Nomor Telepon" />
                    <TextInput id="phone" v-model="form.phone" type="tel" class="mt-1 block w-full" required maxlength="50" placeholder="08xxxxxxxxxx" />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" maxlength="255" placeholder="email@contoh.com (opsional)" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="address" value="Alamat" />
                    <textarea id="address" v-model="form.address" rows="3" required maxlength="5000" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Alamat lengkap bengkel"></textarea>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="logo" value="Logo Bengkel" />
                    <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex h-24 w-36 items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50">
                            <img v-if="logoUrl" :src="logoUrl" alt="Pratinjau logo bengkel" class="h-full w-full object-contain p-2" />
                            <span v-else class="px-2 text-center text-xs text-slate-400">Belum ada logo</span>
                        </div>
                        <div class="flex-1">
                            <input id="logo" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" @change="selectImage($event, 'logo')" />
                            <p class="mt-1 text-xs text-slate-500">PNG, JPG, atau WEBP; maksimal 2 MB.</p>
                            <SecondaryButton v-if="logoUrl" type="button" class="mt-2 text-xs font-semibold text-red-600 hover:text-red-800" @click="removeImage('logo')">Hapus</SecondaryButton>
                            <InputError class="mt-2" :message="form.errors.logo" />
                        </div>
                    </div>
                </div>
                <div class="mt-2 border-b border-slate-100 pb-2 md:col-span-2">
                    <h4 class="text-sm font-bold text-slate-700">Pembayaran dan Pajak</h4>
                </div>
                <div class="md:col-span-2">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Rekening Bank</h4>
                            <p class="mt-1 text-xs text-slate-500">Tambahkan hingga 10 rekening untuk ditampilkan sebagai informasi pembayaran.</p>
                        </div>
                        <PrimaryButton type="button" :disabled="form.banks.length >= 10" @click="addBank">
                            Tambah Bank
                        </PrimaryButton>
                    </div>

                    <div v-if="form.banks.length" class="space-y-4">
                        <div
                            v-for="(bank, index) in form.banks"
                            :key="index"
                            class="rounded-xl border border-slate-200 bg-slate-50/60 p-4"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <h5 class="text-xs font-bold uppercase tracking-wide text-slate-600">Rekening {{ index + 1 }}</h5>
                                <SecondaryButton type="button" @click="removeBank(index)" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                    Hapus
                                </SecondaryButton>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <InputLabel :for="`banks-${index}-name`" value="Nama Bank" />
                                    <TextInput :id="`banks-${index}-name`" v-model="bank.bank_name" type="text" class="mt-1 block w-full bg-white" maxlength="255" placeholder="Contoh: BCA" />
                                    <InputError class="mt-2" :message="form.errors[`banks.${index}.bank_name`]" />
                                </div>
                                <div>
                                    <InputLabel :for="`banks-${index}-number`" value="Nomor Rekening" />
                                    <TextInput :id="`banks-${index}-number`" v-model="bank.bank_account_number" type="text" class="mt-1 block w-full bg-white" maxlength="100" placeholder="Nomor rekening" />
                                    <InputError class="mt-2" :message="form.errors[`banks.${index}.bank_account_number`]" />
                                </div>
                                <div>
                                    <InputLabel :for="`banks-${index}-holder`" value="Nama Pemilik Rekening" />
                                    <TextInput :id="`banks-${index}-holder`" v-model="bank.bank_account_holder" type="text" class="mt-1 block w-full bg-white" maxlength="255" placeholder="Nama pemilik rekening" />
                                    <InputError class="mt-2" :message="form.errors[`banks.${index}.bank_account_holder`]" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">
                        Belum ada rekening bank. Klik “Tambah Bank” untuk menambahkan rekening.
                    </p>
                    <InputError class="mt-2" :message="form.errors.banks" />
                </div>
                <div class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-4">
                    <div>
                        <InputLabel for="tax_percentage" value="Pajak (%)" />
                        <TextInput id="tax_percentage" v-model="form.tax_percentage" type="number" class="mt-1 block w-full" min="0" max="100" step="0.01" required />
                        <p class="mt-1 text-xs text-slate-500">Isi 0 jika pajak tidak digunakan.</p>
                        <InputError class="mt-2" :message="form.errors.tax_percentage" />
                    </div>
                    <div class="md:col-span-3">
                        <InputLabel for="qris_payload" value="Payload QRIS Struk" />
                        <textarea
                            id="qris_payload"
                            v-model="form.qris_payload"
                            rows="3"
                            maxlength="5000"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Masukkan payload QRIS yang akan dicetak pada struk"
                        ></textarea>
                        <div class="mt-2 flex items-start justify-between gap-4">
                            <InputError :message="form.errors.qris_payload" />
                            <span class="shrink-0 text-xs text-slate-400">{{ form.qris_payload.length }}/5000</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3 md:col-span-2">
                    <Link :href="route('admin.dashboard')">
                        <SecondaryButton type="button">Batal</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </section>
</template>