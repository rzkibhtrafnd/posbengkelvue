<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const workshop = computed(() => page.props.workshop || {});
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <div class="mx-auto grid min-h-screen max-w-6xl lg:grid-cols-2 lg:items-center lg:px-8 lg:py-10">
            <aside class="relative hidden min-h-[680px] flex-col justify-between overflow-hidden rounded-l-3xl bg-blue-700 p-12 text-white lg:flex">
                <div class="relative z-10 flex items-center gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white p-2">
                        <ApplicationLogo class="h-full w-full text-blue-700" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100">Sistem Kasir</p>
                        <p class="mt-1 truncate text-xl font-black">{{ workshop.name || 'POS Bengkel' }}</p>
                    </div>
                </div>

                <div class="relative z-10 max-w-md">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-200">Kelola bengkel lebih mudah</p>
                    <h1 class="mt-4 text-4xl font-black leading-tight tracking-tight">
                        Semua operasional bengkel, dalam satu sistem.
                    </h1>
                    <p class="mt-5 text-base leading-7 text-blue-100">
                        Kelola transaksi, persediaan, dan layanan pelanggan dengan praktis dan terorganisir.
                    </p>
                    <div class="mt-10 space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                                <i class="fa-solid fa-cash-register text-sm"></i>
                            </span>
                            <span class="text-sm font-semibold text-blue-50">Transaksi cepat dan tercatat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                                <i class="fa-solid fa-boxes-stacked text-sm"></i>
                            </span>
                            <span class="text-sm font-semibold text-blue-50">Pantau produk dan stok</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                                <i class="fa-solid fa-chart-column text-sm"></i>
                            </span>
                            <span class="text-sm font-semibold text-blue-50">Lihat ringkasan performa</span>
                        </div>
                    </div>
                </div>

                <p class="relative z-10 text-xs font-medium text-blue-200">
                    &copy; {{ new Date().getFullYear() }} {{ workshop.name || 'POS Bengkel' }}
                </p>
                <div class="pointer-events-none absolute -bottom-24 -right-20 h-80 w-80 rounded-full border-[48px] border-white/5"></div>
            </aside>

            <main class="flex min-h-screen items-center justify-center bg-white px-5 py-10 shadow-xl sm:px-10 lg:min-h-[680px] lg:rounded-r-3xl lg:px-16">
                <div class="w-full max-w-md">
                    <div class="mb-10 flex items-center gap-3 lg:hidden">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 p-2">
                            <ApplicationLogo class="h-full w-full text-white" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Sistem Kasir</p>
                            <p class="truncate font-black text-slate-900">{{ workshop.name || 'POS Bengkel' }}</p>
                        </div>
                    </div>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
