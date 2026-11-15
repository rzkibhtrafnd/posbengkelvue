<script setup>
import { computed } from 'vue';

const props = defineProps({
    products: { type: Array, required: true },
});

const maxQuantity = computed(() => Math.max(
    ...props.products.map((product) => Number(product.quantity) || 0),
    0,
));

function quantityWidth(quantity) {
    if (!maxQuantity.value) return 0;

    return (Number(quantity) / maxQuantity.value) * 100;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(product, index) in products"
            :key="product.product_name"
            class="flex items-start gap-3"
            :title="`${product.product_name}: ${product.quantity} terjual, omzet Rp ${formatCurrency(product.revenue)}`"
        >
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs font-black text-blue-700">
                {{ String(index + 1).padStart(2, '0') }}
            </span>
            <div class="min-w-0 flex-1 pt-0.5">
                <div class="flex items-center justify-between gap-3">
                    <p class="truncate text-sm font-semibold text-slate-800">{{ product.product_name }}</p>
                    <p class="shrink-0 text-xs font-bold text-slate-700">{{ product.quantity }} terjual</p>
                </div>
                <div
                    class="mt-2 h-2.5 overflow-hidden rounded-full bg-blue-50"
                    role="img"
                    :aria-label="`${product.product_name}, ${product.quantity} terjual`"
                >
                    <div
                        class="h-full rounded-full bg-blue-600 transition-all"
                        :style="{ width: `${quantityWidth(product.quantity)}%` }"
                    ></div>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Omzet Rp {{ formatCurrency(product.revenue) }}</p>
            </div>
        </div>
    </div>
</template>
