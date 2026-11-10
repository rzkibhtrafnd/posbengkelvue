<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
    homeUrl: {
        type: String,
        required: true,
    },
    isAuthenticated: {
        type: Boolean,
        required: true,
    },
});

const errorDetails = computed(() => {
    const errors = {
        400: {
            title: 'Permintaan Tidak Valid',
            description: 'Permintaan tidak dapat diproses. Periksa kembali data yang dikirim lalu coba lagi.',
            icon: 'fa-triangle-exclamation',
        },
        401: {
            title: 'Perlu Masuk',
            description: 'Sesi Anda tidak tersedia atau sudah berakhir. Silakan masuk kembali untuk melanjutkan.',
            icon: 'fa-user-lock',
        },
        403: {
            title: 'Akses Ditolak',
            description: 'Anda tidak memiliki izin untuk membuka halaman ini.',
            icon: 'fa-lock',
        },
        404: {
            title: 'Halaman Tidak Ditemukan',
            description: 'Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya tidak tepat.',
            icon: 'fa-magnifying-glass',
        },
        405: {
            title: 'Metode Tidak Diizinkan',
            description: 'Permintaan tidak didukung untuk halaman ini. Kembali ke halaman sebelumnya dan coba lagi.',
            icon: 'fa-ban',
        },
        408: {
            title: 'Permintaan Terlalu Lama',
            description: 'Server membutuhkan waktu terlalu lama untuk memproses permintaan. Silakan coba kembali.',
            icon: 'fa-hourglass',
        },
        419: {
            title: 'Sesi Kedaluwarsa',
            description: 'Sesi Anda sudah berakhir. Muat ulang halaman, lalu coba kembali.',
            icon: 'fa-clock',
        },
        429: {
            title: 'Terlalu Banyak Permintaan',
            description: 'Anda melakukan terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.',
            icon: 'fa-gauge-high',
        },
        500: {
            title: 'Terjadi Kesalahan',
            description: 'Terjadi masalah pada sistem saat memproses permintaan. Tim kami dapat menindaklanjutinya.',
            icon: 'fa-triangle-exclamation',
        },
        501: {
            title: 'Fitur Belum Tersedia',
            description: 'Server belum mendukung fitur yang diminta.',
            icon: 'fa-circle-exclamation',
        },
        502: {
            title: 'Gangguan Server',
            description: 'Server menerima respons yang tidak valid. Silakan coba kembali beberapa saat lagi.',
            icon: 'fa-server',
        },
        503: {
            title: 'Layanan Sementara Tidak Tersedia',
            description: 'Sistem sedang dalam pemeliharaan atau mengalami beban tinggi. Silakan coba kembali nanti.',
            icon: 'fa-screwdriver-wrench',
        },
        504: {
            title: 'Server Kehabisan Waktu',
            description: 'Server terlalu lama merespons. Silakan coba kembali beberapa saat lagi.',
            icon: 'fa-hourglass-end',
        },
    };

    return errors[props.status] ?? {
        title: 'Permintaan Tidak Dapat Diproses',
        description: 'Terjadi kendala saat memproses permintaan Anda. Silakan coba kembali.',
        icon: 'fa-circle-exclamation',
    };
});

const page = usePage();
const workshop = computed(() => page.props.workshop ?? {});

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head :title="`${status} - ${errorDetails.title}`" />

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-10 font-sans text-slate-900">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-blue-100/70 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 h-80 w-80 rounded-full bg-indigo-100/70 blur-3xl"></div>

        <section class="relative w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-7 text-center shadow-xl shadow-slate-200/60 sm:p-10">

            <div class="mx-auto mt-10 flex h-24 w-24 items-center justify-center rounded-3xl bg-blue-50 text-blue-600 ring-8 ring-blue-50/60">
                <i class="fa-solid text-4xl" :class="errorDetails.icon" aria-hidden="true"></i>
            </div>

            <p class="mt-8 font-mono text-6xl font-black tracking-tight text-blue-600 sm:text-7xl">
                {{ status }}
            </p>
            <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                {{ errorDetails.title }}
            </h1>
            <p class="mx-auto mt-3 text-sm leading-6 text-slate-500 sm:text-base">
                {{ errorDetails.description }}
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <Link
                    :href="homeUrl"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-xs font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                    {{ isAuthenticated ? 'Ke Dashboard' : 'Ke Halaman Masuk' }}
                </Link>
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 text-xs font-bold uppercase tracking-wide text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click="goBack"
                >
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    Kembali
                </button>
            </div>

            <footer class="mt-8 border-t border-slate-100 pt-5">
                <p class="text-xs text-slate-500">Jika masalah terus terjadi, hubungi administrator sistem.</p>
                <p class="mt-2 text-[10px] text-slate-400">
                    &copy; {{ new Date().getFullYear() }} POS Bengkel System. All rights reserved.
                </p>
            </footer>
        </section>
    </main>
</template>
