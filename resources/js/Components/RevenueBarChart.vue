<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    data: { type: Array, required: true },
    type: { type: String, default: 'bar' },
    showTransactions: { type: Boolean, default: false },
});

const plotLeft = 82;
const plotTop = 20;
const plotBottom = 116;
const chartViewport = ref(null);
const viewportWidth = ref(560);
const rightAxisWidth = computed(() => (props.type === 'line' || props.showTransactions ? 48 : 12));
let resizeObserver;

onMounted(() => {
    resizeObserver = new ResizeObserver(([entry]) => {
        viewportWidth.value = Math.max(Math.floor(entry.contentRect.width), 560);
    });

    if (chartViewport.value) {
        resizeObserver.observe(chartViewport.value);
    }
});

onBeforeUnmount(() => resizeObserver?.disconnect());

const chartWidth = computed(() => Math.max(
    viewportWidth.value,
    props.data.length * 40 + plotLeft + rightAxisWidth.value + 12,
));
const plotRight = computed(() => chartWidth.value - rightAxisWidth.value - 12);
const plotWidth = computed(() => plotRight.value - plotLeft);
const maxRevenue = computed(() => Math.max(...props.data.map((item) => Number(item.revenue) || 0), 0));
const maxTransactions = computed(() => Math.max(...props.data.map((item) => Number(item.transactions) || 0), 0));
const tooltip = ref(null);
const yTicks = computed(() => [1, 0.5, 0].map((ratio) => ({
    y: plotBottom - ratio * (plotBottom - plotTop),
    revenue: Math.round(maxRevenue.value * ratio),
    transactions: Math.round(maxTransactions.value * ratio),
})));

function mapLinePoints(field, maxValue) {
    return props.data.map((item, index) => {
        const x = props.data.length === 1
            ? plotLeft + plotWidth.value / 2
            : plotLeft + (index / (props.data.length - 1)) * plotWidth.value;
        const value = Number(item[field]) || 0;
        const y = plotBottom - (maxValue ? (value / maxValue) * (plotBottom - plotTop) : 0);

        return { ...item, x, y };
    });
}

const revenueLinePoints = computed(() => mapLinePoints('revenue', maxRevenue.value));
const transactionLinePoints = computed(() => mapLinePoints('transactions', maxTransactions.value));
const revenueLinePath = computed(() => revenueLinePoints.value.map((point) => `${point.x},${point.y}`).join(' '));
const transactionLinePath = computed(() => transactionLinePoints.value.map((point) => `${point.x},${point.y}`).join(' '));

const barGroups = computed(() => {
    const groupWidth = plotWidth.value / Math.max(props.data.length, 1);
    const barWidth = Math.min(17, groupWidth * (props.showTransactions ? 0.3 : 0.5));

    return props.data.map((item, index) => {
        const center = plotLeft + groupWidth * (index + 0.5);
        const revenueHeight = maxRevenue.value
            ? (Number(item.revenue) / maxRevenue.value) * (plotBottom - plotTop)
            : 0;
        const transactionHeight = maxTransactions.value
            ? (Number(item.transactions) / maxTransactions.value) * (plotBottom - plotTop)
            : 0;

        return {
            ...item,
            hitX: center - groupWidth / 2,
            hitWidth: groupWidth,
            revenueX: center - barWidth - 1,
            transactionX: center + 1,
            barWidth,
            revenueY: plotBottom - revenueHeight,
            revenueHeight,
            transactionY: plotBottom - transactionHeight,
            transactionHeight,
            singleX: center - barWidth / 2,
        };
    });
});

const categoryWidth = computed(() => `${plotWidth.value}px`);
const categoryOffset = computed(() => `${plotLeft}px`);

function formatCompactCurrency(value) {
    return `Rp ${new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(Number(value) || 0)}`;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
}

function showTooltip(event, item) {
    const left = Math.min(event.clientX + 14, window.innerWidth - 236);
    const top = Math.min(event.clientY + 14, window.innerHeight - 108);

    tooltip.value = {
        ...item,
        left: Math.max(left, 8),
        top: Math.max(top, 8),
    };
}

function hideTooltip() {
    tooltip.value = null;
}
</script>

<template>
    <div class="relative">
        <div ref="chartViewport" class="overflow-x-auto">
            <div :style="{ minWidth: `${chartWidth}px` }">
            <div class="mb-3 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[10px] font-semibold text-slate-600 sm:text-xs">
                <span class="inline-flex items-center gap-2 whitespace-nowrap">
                    <span class="h-2.5 w-2.5 rounded-sm bg-blue-600"></span>
                    Omzet
                </span>
                <span v-if="type === 'line' || showTransactions" class="inline-flex items-center gap-2 whitespace-nowrap">
                    <span
                        v-if="type === 'line'"
                        class="w-4 border-t-2 border-dashed border-blue-400"
                    ></span>
                    <span v-else class="h-2.5 w-2.5 rounded-sm bg-blue-300"></span>
                    Transaksi
                </span>
            </div>

            <svg
                class="h-36 w-full"
                :viewBox="`0 0 ${chartWidth} 140`"
                role="img"
                :aria-label="type === 'line' ? 'Grafik garis tren omzet dan transaksi' : 'Grafik batang tren omzet dan transaksi'"
                preserveAspectRatio="none"
            >
                <g v-for="tick in yTicks" :key="tick.y">
                    <line
                        :x1="plotLeft"
                        :y1="tick.y"
                        :x2="plotRight"
                        :y2="tick.y"
                        stroke="#e2e8f0"
                        stroke-width="1"
                    />
                    <text
                        :x="plotLeft - 10"
                        :y="tick.y + 3"
                        text-anchor="end"
                        fill="#2563eb"
                        font-size="10"
                    >
                        {{ formatCompactCurrency(tick.revenue) }}
                    </text>
                    <text
                        v-if="type === 'line' || showTransactions"
                        :x="plotRight + 9"
                        :y="tick.y + 3"
                        text-anchor="start"
                        fill="#64748b"
                        font-size="10"
                    >
                        {{ tick.transactions }} trx
                    </text>
                </g>
                <template v-if="type === 'line'">
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
                        stroke="#60a5fa"
                        stroke-width="2"
                        stroke-dasharray="5 5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <g v-for="point in revenueLinePoints" :key="point.date">
                        <circle
                            :cx="point.x"
                            :cy="point.y"
                            r="10"
                            fill="transparent"
                            pointer-events="all"
                            class="cursor-crosshair"
                            @pointerenter="showTooltip($event, point)"
                            @pointermove="showTooltip($event, point)"
                            @pointerleave="hideTooltip"
                        >
                            <title>{{ point.label }} · {{ point.transactions }} transaksi · Rp {{ formatCurrency(point.revenue) }}</title>
                        </circle>
                        <circle :cx="point.x" :cy="point.y" r="3.5" fill="#2563eb" pointer-events="none" />
                    </g>
                    <g v-for="point in transactionLinePoints" :key="`transactions-${point.date}`">
                        <circle
                            :cx="point.x"
                            :cy="point.y"
                            r="10"
                            fill="transparent"
                            pointer-events="all"
                            class="cursor-crosshair"
                            @pointerenter="showTooltip($event, point)"
                            @pointermove="showTooltip($event, point)"
                            @pointerleave="hideTooltip"
                        >
                            <title>{{ point.label }} · {{ point.transactions }} transaksi · Rp {{ formatCurrency(point.revenue) }}</title>
                        </circle>
                        <circle :cx="point.x" :cy="point.y" r="3" fill="white" stroke="#60a5fa" stroke-width="2" pointer-events="none" />
                    </g>
                </template>
                <template v-else>
                    <g v-for="bar in barGroups" :key="bar.date">
                        <rect
                            :x="bar.hitX"
                            :y="plotTop"
                            :width="bar.hitWidth"
                            :height="plotBottom - plotTop"
                            fill="transparent"
                            pointer-events="all"
                            @pointerenter="showTooltip($event, bar)"
                            @pointermove="showTooltip($event, bar)"
                            @pointerleave="hideTooltip"
                        />
                        <rect
                            v-if="showTransactions"
                            :x="bar.revenueX"
                            :y="bar.revenueY"
                            :width="bar.barWidth"
                            :height="Math.max(bar.revenueHeight, 1)"
                            rx="3"
                            fill="#2563eb"
                            class="cursor-pointer"
                            @pointerenter="showTooltip($event, bar)"
                            @pointermove="showTooltip($event, bar)"
                            @pointerleave="hideTooltip"
                        >
                            <title>{{ bar.label }} · Omzet Rp {{ formatCurrency(bar.revenue) }}</title>
                        </rect>
                        <rect
                            v-if="showTransactions"
                            :x="bar.transactionX"
                            :y="bar.transactionY"
                            :width="bar.barWidth"
                            :height="Math.max(bar.transactionHeight, 1)"
                            rx="3"
                            fill="#93c5fd"
                            class="cursor-pointer"
                            @pointerenter="showTooltip($event, bar)"
                            @pointermove="showTooltip($event, bar)"
                            @pointerleave="hideTooltip"
                        >
                            <title>{{ bar.label }} · {{ bar.transactions }} transaksi</title>
                        </rect>
                        <rect
                            v-else
                            :x="bar.singleX"
                            :y="bar.revenueY"
                            :width="bar.barWidth"
                            :height="Math.max(bar.revenueHeight, 1)"
                            rx="3"
                            fill="#2563eb"
                            class="cursor-pointer"
                            @pointerenter="showTooltip($event, bar)"
                            @pointermove="showTooltip($event, bar)"
                            @pointerleave="hideTooltip"
                        >
                            <title>{{ bar.label }} · {{ bar.transactions }} transaksi · Omzet Rp {{ formatCurrency(bar.revenue) }}</title>
                        </rect>
                    </g>
                </template>
            </svg>

            <div
                class="grid text-center text-[10px] font-semibold text-slate-500 sm:text-xs"
                :style="{ width: categoryWidth, marginLeft: categoryOffset, gridTemplateColumns: `repeat(${data.length}, minmax(0, 1fr))` }"
            >
                <span v-for="item in data" :key="item.date" class="truncate px-0.5">{{ item.label }}</span>
            </div>
            </div>
        </div>
        <div
            v-if="tooltip"
            class="pointer-events-none fixed z-[100] w-56 rounded-xl bg-slate-900 px-3 py-2.5 text-xs text-white shadow-xl"
            :style="{ left: `${tooltip.left}px`, top: `${tooltip.top}px` }"
        >
            <p class="mb-2 border-b border-white/15 pb-1.5 font-bold">{{ tooltip.label }}</p>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-300">Omzet</span>
                <span class="font-semibold">Rp {{ formatCurrency(tooltip.revenue) }}</span>
            </div>
            <div class="mt-1 flex items-center justify-between gap-3">
                <span class="text-slate-300">Transaksi</span>
                <span class="font-semibold">{{ tooltip.transactions }}</span>
            </div>
        </div>
    </div>
</template>
