<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        required: true,
    },
});

const isEditing = computed(() => props.product !== null);
const isService = computed(() => form.type === 'jasa');
const submitLabel = computed(() => (isEditing.value ? 'Perbarui' : 'Simpan'));

const form = useForm({
    category_id: props.product?.category_id ?? '',
    code: props.product?.code ?? '',
    name: props.product?.name ?? '',
    type: props.product?.type?.value ?? props.product?.type ?? '',
    cost_price: props.product?.cost_price ?? '',
    selling_price: props.product?.selling_price ?? '',
    stock: props.product?.stock ?? 0,
    min_stock: props.product?.min_stock ?? 0,
    unit: props.product?.unit ?? 'Pcs',
    description: props.product?.description ?? '',
});

function submit() {
    if (isEditing.value) {
        form.put(route('admin.products.update', props.product.id));
        return;
    }

    form.post(route('admin.products.store'));
}
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ isEditing ? 'Edit Produk' : 'Produk Baru' }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                Lengkapi informasi produk atau jasa yang akan ditampilkan di katalog.
            </p>

            <form
                class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div>
                    <InputLabel for="code" value="Kode/SKU" />
                    <TextInput
                        id="code"
                        v-model="form.code"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        maxlength="50"
                        autocomplete="off"
                        placeholder="Contoh: OLI-10W40"
                    />
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>

                <div>
                    <InputLabel for="name" value="Nama Produk" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        maxlength="255"
                        autofocus
                        placeholder="Contoh: Oli Mesin 10W-40"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="category_id" value="Kategori" />
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        required
                        class="mt-1 block w-full cursor-pointer rounded-xl border-slate-300 p-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="" disabled>Pilih kategori produk</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.category_id" />
                </div>

                <div>
                    <InputLabel for="type" value="Jenis Produk" />
                    <select
                        id="type"
                        v-model="form.type"
                        required
                        class="mt-1 block w-full cursor-pointer rounded-xl border-slate-300 p-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="" disabled>Pilih jenis produk atau jasa</option>
                        <option value="barang">Barang</option>
                        <option value="jasa">Jasa</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <div v-if="!isService">
                    <InputLabel for="cost_price" value="Harga Modal" />
                    <TextInput
                        id="cost_price"
                        v-model="form.cost_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full"
                        required
                        placeholder="Contoh: 50000"
                    />
                    <InputError class="mt-2" :message="form.errors.cost_price" />
                </div>

                <div>
                    <InputLabel for="selling_price" value="Harga Jual" />
                    <TextInput
                        id="selling_price"
                        v-model="form.selling_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full"
                        required
                        placeholder="Contoh: 75000"
                    />
                    <InputError class="mt-2" :message="form.errors.selling_price" />
                </div>

                <template v-if="!isService">
                    <div>
                        <InputLabel for="stock" value="Stok" />
                        <TextInput
                            id="stock"
                            v-model="form.stock"
                            type="number"
                            min="0"
                            step="1"
                            class="mt-1 block w-full"
                            required
                            placeholder="Masukkan jumlah stok"
                        />
                        <InputError class="mt-2" :message="form.errors.stock" />
                    </div>

                    <div>
                        <InputLabel for="min_stock" value="Batas Minimum Stok" />
                        <TextInput
                            id="min_stock"
                            v-model="form.min_stock"
                            type="number"
                            min="0"
                            step="1"
                            class="mt-1 block w-full"
                            required
                            placeholder="Contoh: 5"
                        />
                        <InputError class="mt-2" :message="form.errors.min_stock" />
                    </div>

                    <div>
                        <InputLabel for="unit" value="Satuan" />
                        <TextInput
                            id="unit"
                            v-model="form.unit"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="50"
                            placeholder="Contoh: Pcs, Botol, Liter"
                        />
                        <InputError class="mt-2" :message="form.errors.unit" />
                    </div>
                </template>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Deskripsi" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        maxlength="500"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Deskripsi produk atau jasa (opsional)"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mt-2 flex items-center justify-end gap-3 border-t border-slate-100 pt-5 md:col-span-2">
                    <Link :href="route('admin.products.index')">
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
