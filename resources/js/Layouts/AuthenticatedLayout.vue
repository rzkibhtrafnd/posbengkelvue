<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// State Sidebar & Profile
const sidebarOpen = ref(false);
const sidebarMinimized = ref(false);

// Akses data global dari Inertia ($page.props)
const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const flash = computed(() => page.props.flash || {});

// Helper Role Check (Sesuai dengan logika Blade sebelumnya)
const isAdmin = computed(() => {
    return user.value.role === 'admin' || user.value.role?.value === 'admin';
});
const dashboardUrl = computed(() => route(isAdmin.value ? 'admin.dashboard' : 'kasir.dashboard'));
const hasTransactionRoutes = computed(() => {
    const prefix = isAdmin.value ? 'admin' : 'kasir';

    return route().has(`${prefix}.transactions.create`)
        && route().has(`${prefix}.transactions.index`);
});
const hasAdminMechanicsRoute = computed(() => route().has('admin.mechanics.index'));
const hasAdminCategoriesRoute = computed(() => route().has('admin.categories.index'));
const hasProductRoute = computed(() => route().has(isAdmin.value ? 'admin.products.index' : 'kasir.products.index'));
const hasVehicleRoute = computed(() => route().has(isAdmin.value ? 'admin.vehicles.index' : 'kasir.vehicles.index'));

// Helper Route Check untuk Active State Menu
const isRouteActive = (pattern) => {
    return route().current(pattern);
};
</script>

<template>
    <div class="flex h-screen overflow-hidden font-sans text-slate-900 bg-slate-50 antialiased">
        
        <!-- Overlay Mobile Sidebar -->
        <div 
            v-if="sidebarOpen" 
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-20 bg-black/50 transition-opacity lg:hidden"
        ></div>

        <!-- Sidebar Navigation -->
        <aside 
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full', 
                sidebarMinimized ? 'lg:w-20' : 'lg:w-64'
            ]" 
            class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 h-full transition-all duration-300 transform bg-slate-900 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none"
        >
            <!-- Sidebar Header / Logo -->
            <div 
                class="flex items-center py-4 border-b h-16 bg-slate-950 border-slate-800" 
                :class="sidebarMinimized ? 'justify-center px-0' : 'justify-between px-6'"
            >
                <a :href="dashboardUrl" class="flex items-center text-white" :class="sidebarMinimized ? 'space-x-0' : 'space-x-3'">
                    <ApplicationLogo class="w-auto h-8 text-blue-500 fill-current shrink-0" />
                    <div v-show="!sidebarMinimized" class="flex flex-col leading-tight overflow-hidden">
                        <span class="text-sm font-black tracking-wider text-white whitespace-nowrap">POS BENGKEL</span>
                        <span class="text-[10px] font-bold text-blue-400 font-mono tracking-widest uppercase whitespace-nowrap">MANAGEMENT</span>
                    </div>
                </a>
                <button 
                    @click="sidebarOpen = false" 
                    class="text-gray-400 focus:outline-none lg:hidden" 
                    v-show="!sidebarMinimized"
                >
                    <i class="text-xl fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Area Menu Navigation -->
            <nav 
                class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto overflow-x-hidden" 
                :class="sidebarMinimized ? 'px-2' : 'px-4'"
            >
                <!-- Dashboard Link -->
                <Link 
                    :href="dashboardUrl" 
                    title="Dashboard"
                    class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('dashboard') || isRouteActive('*.dashboard')
                            ? 'bg-blue-600 text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i 
                        class="fa-solid fa-chart-pie transition-colors" 
                        :class="[
                            isRouteActive('dashboard') || isRouteActive('*.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Dashboard</span>
                </Link>

                <!-- Menu Transaksi / POS (Admin & Kasir) -->
                <Link
                    v-if="hasTransactionRoutes"
                    :href="isAdmin ? route('admin.transactions.create') : route('kasir.transactions.create')" 
                    title="POS Kasir"
                    class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('*.transactions.create') 
                            ? 'bg-blue-600 text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i 
                        class="fa-solid fa-cash-register transition-colors" 
                        :class="[
                            isRouteActive('*.transactions.create') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">POS Kasir</span>
                </Link>

                <!-- Menu Riwayat Transaksi (Admin & Kasir) -->
                <Link
                    v-if="hasTransactionRoutes"
                    :href="isAdmin ? route('admin.transactions.index') : route('kasir.transactions.index')" 
                    title="Riwayat Transaksi"
                    class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('*.transactions.index') || isRouteActive('*.transactions.show') 
                            ? 'bg-blue-600 text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i 
                        class="fa-solid fa-receipt transition-colors" 
                        :class="[
                            isRouteActive('*.transactions.index') || isRouteActive('*.transactions.show') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Riwayat Transaksi</span>
                </Link>

                <!-- Master Data Header -->
                <div class="pt-3 pb-1">
                    <p v-show="!sidebarMinimized" class="px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Master Data</p>
                    <div v-show="sidebarMinimized" class="border-t border-slate-800 my-2"></div>
                </div>

                <!-- Menu Khusus Admin -->
                <template v-if="isAdmin">
                    <Link 
                        :href="route('admin.users.index')" 
                        title="Kelola User"
                        class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                        :class="[
                            isRouteActive('admin.users.*') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            sidebarMinimized ? 'justify-center px-0' : 'px-4'
                        ]"
                    >
                        <i 
                            class="fa-solid fa-users transition-colors" 
                            :class="[
                                isRouteActive('admin.users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                                sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                            ]"
                        ></i>
                        <span v-show="!sidebarMinimized" class="whitespace-nowrap">Kelola User</span>
                    </Link>

                    <Link 
                        v-if="hasAdminMechanicsRoute"
                        :href="route('admin.mechanics.index')" 
                        title="Kelola Montir"
                        class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                        :class="[
                            isRouteActive('admin.mechanics.*') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            sidebarMinimized ? 'justify-center px-0' : 'px-4'
                        ]"
                    >
                        <i 
                            class="fa-solid fa-user-gear transition-colors" 
                            :class="[
                                isRouteActive('admin.mechanics.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                                sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                            ]"
                        ></i>
                        <span v-show="!sidebarMinimized" class="whitespace-nowrap">Kelola Montir</span>
                    </Link>

                    <Link 
                        v-if="hasAdminCategoriesRoute"
                        :href="route('admin.categories.index')" 
                        title="Kelola Kategori"
                        class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                        :class="[
                            isRouteActive('admin.categories.*') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                            sidebarMinimized ? 'justify-center px-0' : 'px-4'
                        ]"
                    >
                        <i 
                            class="fa-solid fa-tags transition-colors" 
                            :class="[
                                isRouteActive('admin.categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                                sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                            ]"
                        ></i>
                        <span v-show="!sidebarMinimized" class="whitespace-nowrap">Kelola Kategori</span>
                    </Link>
                </template>

                <!-- Menu Produk (Admin & Kasir) -->
                <Link
                    v-if="hasProductRoute"
                    :href="isAdmin ? route('admin.products.index') : route('kasir.products.index')" 
                    title="Kelola Produk"
                    class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('*.products.*') 
                            ? 'bg-blue-600 text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i 
                        class="fa-solid fa-boxes-stacked transition-colors" 
                        :class="[
                            isRouteActive('*.products.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Katalog Produk</span>
                </Link>

                <!-- Menu Kendaraan (Admin & Kasir) -->
                <Link
                    v-if="hasVehicleRoute"
                    :href="isAdmin ? route('admin.vehicles.index') : route('kasir.vehicles.index')" 
                    title="Kelola Kendaraan"
                    class="flex items-center py-3 text-sm font-medium rounded-xl transition-colors duration-200 group"
                    :class="[
                        isRouteActive('*.vehicles.*') 
                            ? 'bg-blue-600 text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        sidebarMinimized ? 'justify-center px-0' : 'px-4'
                    ]"
                >
                    <i 
                        class="fa-solid fa-motorcycle transition-colors" 
                        :class="[
                            isRouteActive('*.vehicles.*') ? 'text-white' : 'text-slate-400 group-hover:text-white',
                            sidebarMinimized ? 'text-xl' : 'w-5 mr-3 text-lg'
                        ]"
                    ></i>
                    <span v-show="!sidebarMinimized" class="whitespace-nowrap">Data Kendaraan</span>
                </Link>
            </nav>

            <!-- Sidebar Footer Profile -->
            <div 
                class="flex items-center p-4 border-t bg-slate-950 border-slate-800 text-slate-400 overflow-hidden" 
                :class="sidebarMinimized ? 'justify-center space-x-0' : 'space-x-3'"
            >
                <div class="flex items-center justify-center w-8 h-8 font-bold text-white bg-blue-600 border border-blue-500 rounded-full shrink-0 shadow-[0_0_15px_-3px_rgba(59,130,246,0.5)]">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                </div>
                <div v-show="!sidebarMinimized" class="flex-1 truncate">
                    <p class="text-xs font-medium leading-none text-white truncate">{{ user.name }}</p>
                    <span class="text-[9px] text-gray-500 font-mono block mt-1 uppercase tracking-wider">
                        Role: <span :class="isAdmin ? 'text-amber-400' : 'text-blue-400'" class="font-bold">{{ user.role?.value || user.role }}</span>
                    </span>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            
            <!-- Header Topbar -->
            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-slate-100 shadow-sm shrink-0">
                <div class="flex items-center flex-1 min-w-0 mr-4">
                    
                    <!-- Tombol Mobile Sidebar (Off-canvas) -->
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="mr-4 text-slate-500 focus:outline-none lg:hidden shrink-0 hover:text-blue-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    <!-- Tombol Desktop Sidebar (Minimize) -->
                    <button 
                        @click="sidebarMinimized = !sidebarMinimized" 
                        class="hidden mr-4 text-slate-500 focus:outline-none lg:block shrink-0 hover:text-blue-600 transition-colors" 
                        title="Toggle Sidebar"
                    >
                        <i 
                            class="fa-solid fa-bars-staggered text-lg transition-transform duration-300" 
                            :class="sidebarMinimized ? 'rotate-180' : ''"
                        ></i>
                    </button>

                    <!-- Page Title / Header Slot -->
                    <div v-if="$slots.header" class="w-full truncate">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="flex items-center shrink-0">
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 transition duration-150 ease-in-out bg-white rounded-xl hover:text-slate-700 focus:outline-none">
                                    <div class="mr-1 flex items-center">
                                        <i class="mr-2 text-lg fa-regular fa-circle-user"></i>
                                        <span>{{ user.name }}</span>
                                    </div>
                                    <svg class="w-4 h-4 fill-current ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    <i class="mr-2 text-gray-400 fa-solid fa-user-gear"></i>Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <span class="text-red-600">
                                        <i class="mr-2 fa-solid fa-arrow-right-from-bracket"></i>Log Out
                                    </span>
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 p-6 bg-slate-50">
                <!-- Flash Message Success -->
                <div v-if="flash.success" class="mb-4 overflow-hidden bg-white border border-slate-100 shadow-sm rounded-xl">
                    <div class="p-4 text-green-800 border-l-4 border-green-200 bg-green-50">
                        {{ flash.success }}
                    </div>
                </div>

                <!-- Flash Message Error -->
                <div v-if="flash.error" class="mb-4 overflow-hidden bg-white border border-red-100 shadow-sm rounded-xl" role="alert">
                    <div class="flex items-start gap-3 p-4 text-red-800 border-l-4 border-red-400 bg-red-50">
                        <span>{{ flash.error }}</span>
                    </div>
                </div>

                <!-- Slot Konten Halaman -->
                <slot />
            </main>

            <!-- Footer -->
            <footer class="mt-auto bg-white border-t border-slate-100">
                <div class="flex items-center justify-center px-6 py-3 mx-auto max-w-7xl">
                    <p class="text-xs text-center text-slate-500">&copy; {{ new Date().getFullYear() }} POS Bengkel System. All rights reserved.</p>
                </div>
            </footer>
        </div>

    </div>
</template>
