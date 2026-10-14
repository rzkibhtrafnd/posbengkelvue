<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    paginator: {
        type: Object,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const pageSizes = [10, 25, 50, 100];

function changePageSize(event) {
    router.get(
        route(props.routeName),
        {
            ...props.filters,
            per_page: Number(event.target.value),
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <div
        v-if="paginator.total > 0"
        class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex flex-col gap-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:gap-5">
            <p>
                Menampilkan
                <span class="font-semibold text-slate-700">{{ paginator.from }}</span>
                sampai
                <span class="font-semibold text-slate-700">{{ paginator.to }}</span>
                dari
                <span class="font-semibold text-slate-700">{{ paginator.total }}</span>
                data
            </p>

            <label class="flex items-center gap-2">
                <span>Tampilkan</span>
                <select
                    :value="paginator.per_page"
                    class="rounded-lg border-slate-200 bg-white py-1 pl-2 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="changePageSize"
                >
                    <option v-for="size in pageSizes" :key="size" :value="size">
                        {{ size }}
                    </option>
                </select>
                <span>per halaman</span>
            </label>
        </div>

        <nav class="flex flex-wrap items-center justify-start gap-1 sm:justify-end" aria-label="Pagination">
            <template v-for="(link, index) in paginator.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex min-h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    :class="link.active
                        ? 'border-blue-600 bg-blue-600 text-white'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700'"
                    :aria-current="link.active ? 'page' : undefined"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex min-h-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-3 text-xs font-medium text-slate-300"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
