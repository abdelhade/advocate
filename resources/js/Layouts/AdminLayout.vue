<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const admin = page.props.auth.admin;

const sidebarOpen = ref(false);

const navigation = [
    {
        name: 'لوحة التحكم',
        route: 'admin.dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'المكاتب',
        route: 'admin.tenants.index',
        matchRoutes: ['admin.tenants.index', 'admin.tenants.create', 'admin.tenants.show', 'admin.tenants.edit'],
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
    {
        name: 'المديرين',
        route: 'admin.admins.index',
        matchRoutes: ['admin.admins.index', 'admin.admins.create', 'admin.admins.edit'],
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    },
];

const isActive = (item) => {
    if (item.matchRoutes) {
        return item.matchRoutes.some(r => route().current(r));
    }
    return route().current(item.route);
};

const logout = () => {
    router.post(route('admin.logout'));
};

const flash = computed(() => page.props.flash || {});
</script>

<template>
    <div class="min-h-screen bg-stone-50 font-sans">
        <!-- Mobile Sidebar Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
                @click="sidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed top-0 right-0 z-50 w-[270px] h-full bg-white border-l border-stone-200/80 transform transition-transform duration-300 ease-out lg:translate-x-0 shadow-xl lg:shadow-none"
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
        >
            <!-- Logo -->
            <div class="h-[72px] flex items-center gap-3 px-6 border-b border-stone-100">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white flex-shrink-0 shadow-lg shadow-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div>
                    <span class="text-lg font-black text-stone-800">جلسات</span>
                    <span class="block text-[10px] text-stone-400 font-medium -mt-0.5">لوحة الإدارة</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest px-4 mb-3">القائمة الرئيسية</p>
                <Link
                    v-for="item in navigation"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group"
                    :class="isActive(item) ? 'bg-red-50 text-red-700 border border-red-100 shadow-sm' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800'"
                >
                    <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- Admin Info (bottom) -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-stone-100 bg-stone-50/50">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-stone-200 to-stone-300 flex items-center justify-center ring-2 ring-white">
                            <svg class="w-5 h-5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-stone-800">{{ admin.name }}</p>
                            <p class="text-[11px] text-stone-400">مدير النظام</p>
                        </div>
                    </div>
                </div>
                <button
                    @click="logout"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-red-700 bg-white hover:bg-red-50 border border-red-100 transition-all duration-200 hover:shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    تسجيل الخروج
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:mr-[270px]">
            <!-- Top Bar -->
            <header class="h-[72px] bg-white/80 backdrop-blur-lg border-b border-stone-200/60 flex items-center justify-between px-6 sticky top-0 z-30">
                <!-- Mobile Menu Button -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2.5 rounded-xl text-stone-500 hover:bg-stone-100 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <!-- Page Title -->
                <h1 class="text-lg font-black text-stone-800">
                    <slot name="title">لوحة التحكم</slot>
                </h1>

                <div class="flex items-center gap-4">
                    <a href="/" target="_blank" class="hidden sm:flex items-center gap-2 text-sm text-stone-400 hover:text-red-700 transition-colors px-3 py-2 rounded-lg hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        زيارة الموقع
                    </a>
                </div>
            </header>

            <!-- Flash Messages -->
            <Transition
                enter-active-class="transition-all duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="$page.props.flash?.success" class="mx-6 mt-4">
                    <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $page.props.flash.success }}
                    </div>
                </div>
            </Transition>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
