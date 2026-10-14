<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => props.category !== null);
const title = computed(() => (isEditing.value ? 'Edit Kategori' : 'Kategori Baru'));
const description = computed(() => (
    isEditing.value
        ? 'Perbarui informasi kategori produk.'
        : 'Masukkan nama dan deskripsi kategori produk.'
));
const submitLabel = computed(() => (isEditing.value ? 'Perbarui' : 'Simpan'));

const form = useForm({
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
});

function submit() {
    if (isEditing.value) {
        form.put(route('admin.categories.update', props.category.id));
        return;
    }

    form.post(route('admin.categories.store'));
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
                <div class="md:col-span-2">
                    <InputLabel for="name" value="Nama Kategori" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        maxlength="255"
                        autofocus
                        placeholder="Contoh: Oli & Pelumas"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Deskripsi" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="5"
                        maxlength="500"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Deskripsi kategori (opsional)"
                    ></textarea>
                    <div class="mt-2 flex items-start justify-between gap-4">
                        <InputError :message="form.errors.description" />
                        <span class="shrink-0 text-xs text-slate-400">
                            {{ form.description.length }}/500
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3 md:col-span-2">
                    <Link :href="route('admin.categories.index')">
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
