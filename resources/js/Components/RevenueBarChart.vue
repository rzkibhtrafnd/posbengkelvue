<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, required: true },
    type: { type: String, default: 'bar' },
});

const maxRevenue = computed(() => Math.max(...props.data.map((day) => Number(day.revenue) || 0), 0));
const maxTransactions = computed(() => Math.max(...props.data.map((day) => Number(day.transactions) || 0), 0));
const chartWidth = computed(() => Math.max(480, props.data.length * 48));
const mapLinePoints = (field, maxValue) => {
    const horizontalPadding = 24;
    const plotWidth = chartWidth.value - horizontalPadding * 2;
    const plotHeight = 104;

    return props.data.map((day, index) => {
        const x = props.data.length === 1
            ? chartWidth.value / 2
            : horizontalPadding + (index / (props.data.length - 1)) * plotWidth;
        const value = Number(day[field]) || 0;
        const y = 116 - (maxValue ? (value / maxValue) * plotHeight : 0);

        return { ...day, x, y };
    });
};
const revenueLinePoints = computed(() => mapLinePoints('revenue', maxRevenue.value));
const transactionLinePoints = computed(() => mapLinePoints('transactions', maxTransactions.value));
const revenueLinePath = computed(() => revenueLinePoints.value.map((point) => `${point.x},${point.y}`).join(' '));
const transactionLinePath = computed(() => transactionLinePoints.value.map((point) => `${point.x},${point.y}`).join(' '));

function barHeight(day) {
    if (maxRevenue.value === 0) return 5;

    return Math.max(5, (Number(day.revenue) / maxRevenue.value) * 100);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}
</script>

<template>
    <div class="overflow-x-auto">
        <div :style="{ minWidth: `${chartWidth}px` }">
            <template v-if="type === 'line'">
                <svg
                    class="h-40 w-full overflow-visible"
                    :viewBox="`0 0 ${chartWidth} 140`"
                    role="img"
                    aria-label="Grafik garis transaksi dan omzet bulanan"
                    preserveAspectRatio="none"
                >
                    <line x1="0" y1="116" :x2="chartWidth" y2="116" stroke="#e2e8f0" stroke-width="1" />
                    <line x1="0" y1="64" :x2="chartWidth" y2="64" stroke="#f1f5f9" stroke-width="1" />
                    <polyline
                        v-if="revenueLinePoints.length > 1"
                        :points="revenueLinePath"
                        fill="none"
                        stroke="#2563eb"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <polyline
                        v-if="transactionLinePoints.length > 1"
                        :points="transactionLinePath"
                        fill="none"
                        stroke="#2563eb"
                        stroke-width="2"
                        stroke-dasharray="5 5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <g v-for="point in revenueLinePoints" :key="point.date">
                        <circle :cx="point.x" :cy="point.y" r="4" fill="#2563eb">
                            <title>{{ point.label }} · {{ point.transactions }} transaksi · Rp {{ formatCurrency(point.revenue) }}</title>
                        </circle>
                    </g>
                    <g v-for="point in transactionLinePoints" :key="`transactions-${point.date}`">
                        <circle :cx="point.x" :cy="point.y" r="3" fill="white" stroke="#2563eb" stroke-width="2">
                            <title>{{ point.label }} · {{ point.transactions }} transaksi · Rp {{ formatCurrency(point.revenue) }}</title>
                        </circle>
                    </g>
                </svg>
                <div class="mb-2 flex justify-center gap-5 text-[10px] font-semibold text-slate-500 sm:text-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-0.5 w-4 bg-blue-600"></span>
                        Omzet
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-4 border-t-2 border-dashed border-blue-600"></span>
                        Transaksi
                    </span>
                </div>
                <div
                    class="grid gap-1 text-center text-[10px] font-semibold text-slate-500 sm:text-xs"
                    :style="{ gridTemplateColumns: `repeat(${data.length}, minmax(0, 1fr))` }"
                >
                    <span v-for="point in revenueLinePoints" :key="point.date" class="truncate">{{ point.label }}</span>
                </div>
            </template>

            <div
                v-else
                class="grid h-52 gap-1 sm:gap-2"
                :style="{ gridTemplateColumns: `repeat(${data.length}, minmax(0, 1fr))` }"
            >
                <div
                    v-for="day in data"
                    :key="day.date"
                    class="flex min-w-0 flex-col items-center justify-end gap-2"
                    :title="`${day.transactions} transaksi · Rp ${formatCurrency(day.revenue)}`"
                >
                    <span class="max-w-full truncate text-[10px] font-semibold text-slate-400 sm:text-xs">
                        {{ formatCurrency(day.revenue) }}
                    </span>
                    <div class="flex h-32 w-full items-end rounded-lg bg-slate-50 p-1">
                        <div
                            class="w-full rounded-md bg-blue-600 transition-all"
                            :style="{ height: `${barHeight(day)}%` }"
                        ></div>
                    </div>
                    <div class="text-center leading-tight">
                        <p class="text-[10px] font-bold text-slate-700 sm:text-xs">{{ day.label }}</p>
                        <p class="text-[10px] text-slate-400">{{ day.transactions }} trx</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
