<script setup>
import { nextTick, ref, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
const displayValue = ref(formatValue(props.modelValue));
let lastEmittedValue;

function formatValue(value) {
    if (value === '' || value === null || value === undefined) return '';
    const amount = Number(value);

    if (!Number.isFinite(amount)) return '';

    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(amount);
}

watch(() => props.modelValue, (value) => {
    if (lastEmittedValue !== undefined && String(value ?? '') === lastEmittedValue) {
        lastEmittedValue = undefined;
        return;
    }

    displayValue.value = formatValue(value);
});

function handleInput(event) {
    const input = event.target;
    const cursor = input.selectionStart ?? input.value.length;
    const rawValue = input.value.replace(/[^\d,]/g, '');
    const commaIndex = rawValue.indexOf(',');
    const hasDecimal = commaIndex !== -1;
    const integerPart = (hasDecimal ? rawValue.slice(0, commaIndex) : rawValue).replace(/\D/g, '');
    const decimalPart = hasDecimal
        ? rawValue.slice(commaIndex + 1).replace(/\D/g, '').slice(0, 2)
        : '';
    const digitsBeforeCursor = input.value.slice(0, cursor).replace(/\D/g, '').length;
    const decimalBeforeCursor = input.value.slice(0, cursor).includes(',');
    const formattedInteger = integerPart.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const formatted = `${formattedInteger || (hasDecimal ? '0' : '')}${hasDecimal ? `,${decimalPart}` : ''}`;
    const normalizedInteger = integerPart.replace(/^0+(?=\d)/, '');
    const normalizedValue = `${normalizedInteger || (hasDecimal ? '0' : '')}${hasDecimal ? `.${decimalPart}` : ''}`;

    displayValue.value = formatted;
    lastEmittedValue = normalizedValue;
    emit('update:modelValue', normalizedInteger || hasDecimal ? normalizedValue : '');

    nextTick(() => {
        let position = 0;
        let digitCount = 0;

        while (position < formatted.length && digitCount < digitsBeforeCursor) {
            if (/\d/.test(formatted[position])) digitCount += 1;
            position += 1;
        }

        const formattedCommaIndex = formatted.indexOf(',');
        if (decimalBeforeCursor && formattedCommaIndex !== -1 && position === formattedCommaIndex) {
            position += 1;
        }

        input.setSelectionRange(position, position);
    });
}
</script>

<template>
    <input
        v-bind="$attrs"
        class="rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        type="text"
        inputmode="numeric"
        :value="displayValue"
        @input="handleInput"
    />
</template>
