<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    vehicle: { type: Object, default: null },
    customers: { type: Array, required: true },
});

const isEditing = computed(() => props.vehicle !== null);
const title = computed(() => (isEditing.value ? 'Edit Kendaraan' : 'Kendaraan & Pemilik Baru'));
const description = computed(() => (
    isEditing.value
        ? 'Perbarui rincian unit kendaraan dan informasi pemiliknya.'
        : 'Lengkapi rincian unit kendaraan dan informasi pemiliknya.'
));
const submitLabel = computed(() => (isEditing.value ? 'Perbarui' : 'Simpan'));
const isNewCustomer = ref(!props.vehicle?.customer_id);
const form = useForm({
    customer_id: props.vehicle?.customer_id ?? '',
    name: props.vehicle?.customer?.name ?? '',
    phone: props.vehicle?.customer?.phone ?? '',
    address: props.vehicle?.customer?.address ?? '',
    plate_number: props.vehicle?.plate_number ?? '',
    brand: props.vehicle?.brand ?? '',
    model: props.vehicle?.model ?? '',
    year: props.vehicle?.year ?? '',
    color: props.vehicle?.color ?? '',
});
const selectedCustomerId = ref(props.vehicle?.customer_id ?? '');

function setCustomerMode(useNewCustomer) {
    isNewCustomer.value = useNewCustomer;

    if (useNewCustomer) {
        selectedCustomerId.value = form.customer_id || selectedCustomerId.value;
        form.customer_id = '';
        return;
    }

    form.customer_id = selectedCustomerId.value;
}

function selectCustomer() {
    selectedCustomerId.value = form.customer_id;
}

function submit() {
    if (isEditing.value) {
        form.put(route('admin.vehicles.update', props.vehicle.id));
        return;
    }

    form.post(route('admin.vehicles.store'));
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
                <div class="border-b border-slate-100 pb-3 md:col-span-2">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                            Data Pemilik Kendaraan
                        </h4>
                        <span class="text-xs font-medium text-slate-500">
                            Pilih cara menambahkan pemilik kendaraan
                        </span>
                    </div>

                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            class="group flex min-h-[76px] items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            :class="isNewCustomer ? 'border-blue-500 bg-blue-50/70 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/70'"
                            :aria-pressed="isNewCustomer"
                            @click="setCustomerMode(true)"
                        >
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-800">
                                    Buat pelanggan baru
                                </span>
                                <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                                    Isi data pemilik yang belum terdaftar.
                                </span>
                            </span>
                            <i
                                v-if="isNewCustomer"
                                class="fa-solid fa-circle-check shrink-0 text-blue-600"
                                aria-hidden="true"
                            ></i>
                        </button>
                        <button
                            type="button"
                            class="group flex min-h-[76px] items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            :class="!isNewCustomer ? 'border-blue-500 bg-blue-50/70 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/70'"
                            :aria-pressed="!isNewCustomer"
                            @click="setCustomerMode(false)"
                        >
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-800">
                                    Pilih pelanggan terdaftar
                                </span>
                                <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                                    Gunakan data pemilik yang sudah tersimpan.
                                </span>
                            </span>
                            <i
                                v-if="!isNewCustomer"
                                class="fa-solid fa-circle-check shrink-0 text-blue-600"
                                aria-hidden="true"
                            ></i>
                        </button>
                    </div>

                    <div v-if="!isNewCustomer">
                        <InputLabel for="customer_id" value="Pelanggan Terdaftar" />
                        <select
                            id="customer_id"
                            v-model="form.customer_id"
                            required
                            class="mt-1 block w-full cursor-pointer rounded-xl border-slate-300 p-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @change="selectCustomer"
                        >
                            <option value="">Pilih nama atau nomor telepon</option>
                            <option
                                v-for="customer in customers"
                                :key="customer.id"
                                :value="customer.id"
                            >
                                {{ customer.name }}{{ customer.phone ? ` (${customer.phone})` : ' (Tanpa No. HP)' }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.customer_id" />
                    </div>

                    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Nama Lengkap Pemilik" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="255"
                                required
                                autofocus
                                placeholder="Contoh: Ahmad Subagyo"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="phone" value="No. Telepon / WA Pemilik" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="mt-1 block w-full font-mono"
                                maxlength="20"
                                placeholder="081234567890"
                            />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="address" value="Alamat Pemilik (Opsional)" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="2"
                                maxlength="500"
                                class="mt-1 block w-full rounded-xl border-slate-300 p-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Alamat pemilik (opsional)"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <h4 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-800">
                        Rincian Unit Kendaraan
                    </h4>
                </div>

                <div>
                    <InputLabel for="plate_number" value="Plat Nomor / Nomor Polisi" />
                    <TextInput
                        id="plate_number"
                        v-model="form.plate_number"
                        type="text"
                        class="mt-1 block w-full font-mono font-bold uppercase"
                        maxlength="20"
                        required
                        placeholder="Contoh: B 1234 ABC"
                    />
                    <InputError class="mt-2" :message="form.errors.plate_number" />
                </div>
                <div>
                    <InputLabel for="brand" value="Merk Kendaraan" />
                    <TextInput
                        id="brand"
                        v-model="form.brand"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="100"
                        required
                        placeholder="Contoh: Honda, Yamaha, Suzuki"
                    />
                    <InputError class="mt-2" :message="form.errors.brand" />
                </div>
                <div>
                    <InputLabel for="model" value="Model / Tipe Kendaraan" />
                    <TextInput
                        id="model"
                        v-model="form.model"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="100"
                        required
                        placeholder="Contoh: Vario 160, Mio M3, Supra X"
                    />
                    <InputError class="mt-2" :message="form.errors.model" />
                </div>
                <div>
                    <InputLabel for="year" value="Tahun Pembuatan" />
                    <TextInput
                        id="year"
                        v-model="form.year"
                        type="text"
                        inputmode="numeric"
                        class="mt-1 block w-full font-mono"
                        maxlength="4"
                        placeholder="Contoh: 2022"
                    />
                    <InputError class="mt-2" :message="form.errors.year" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="color" value="Warna Kendaraan" />
                    <TextInput
                        id="color"
                        v-model="form.color"
                        type="text"
                        class="mt-1 block w-full"
                        maxlength="50"
                        placeholder="Contoh: Hitam Dop, Merah"
                    />
                    <InputError class="mt-2" :message="form.errors.color" />
                </div>

                <div class="mt-4 flex items-center justify-end gap-3 md:col-span-2">
                    <Link :href="route('admin.vehicles.index')"><SecondaryButton type="button">Batal</SecondaryButton></Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : submitLabel }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </section>
</template>
