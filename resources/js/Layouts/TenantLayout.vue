<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const activeTenant = computed(() => page.props.auth?.active_tenant);
const user = computed(() => page.props.auth?.user);
const plan = computed(() => activeTenant.value?.plan);
const planFeatures = computed(() => plan.value?.features || {});

const isSidebarOpen = ref(false);
const notificationsOpen = ref(false);
const deferredPrompt = ref(null);
const canInstall = ref(false);
const isDarkMode = ref(false);

const applyTheme = (dark) => {
    isDarkMode.value = dark;
    document.documentElement.classList.toggle('dark', dark);
    document.body.classList.toggle('dark', dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
};

const toggleTheme = () => {
    applyTheme(!isDarkMode.value);
};

onMounted(() => {
    applyTheme(localStorage.getItem('theme') === 'dark');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        canInstall.value = true;
    });
});

const installApp = async () => {
    if (!deferredPrompt.value) return;
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        canInstall.value = false;
    }
    deferredPrompt.value = null;
};

const allNavigation = [
    { name: 'لوحة التحكم', href: route('dashboard'), icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', active: route().current('dashboard') },
    { name: 'الموكلين', href: route('clients.index'), icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', active: route().current('clients.*') },
    { name: 'القضايا', href: route('cases.index'), icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', active: route().current('cases.*') },
    { name: 'أنواع القضايا', href: route('case-types.index'), icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', active: route().current('case-types.*') },
    { name: 'المهام والأعمال', href: route('tasks.index'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', active: route().current('tasks.*'), feature: 'tasks' },
    { name: 'الفواتير والأتعاب', href: route('invoices.index'), icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', active: route().current('invoices.*'), feature: 'billing' },
    { name: 'سندات القبض', href: route('payments.index'), icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', active: route().current('payments.*'), feature: 'billing' },
    { name: 'المصروفات', href: route('expenses.index'), icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', active: route().current('expenses.*'), feature: 'billing' },
    { name: 'فريق العمل والمستخدمين', href: route('tenant.users.index'), icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', active: route().current('tenant.users.*') },
    { name: 'الاشتراك والفوترة', href: route('tenant.billing.index'), icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', active: route().current('tenant.billing.*') },
    { name: 'سجل النشاطات', href: route('audit-logs.index'), icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', active: route().current('audit-logs.*') },
];

const navigation = computed(() =>
    allNavigation.filter((item) => !item.feature || planFeatures.value[item.feature])
);
</script>

<template>
    <div class="min-h-screen bg-stone-50 dark:bg-slate-950 text-stone-900 dark:text-slate-100 transition-colors" dir="rtl">
        <!-- Sidebar: logo + nav only -->
        <aside
            class="fixed inset-y-0 right-0 z-50 w-72 bg-white dark:bg-slate-900 border-l border-stone-200/80 dark:border-slate-800 transition-transform duration-300 transform lg:translate-x-0 flex flex-col shadow-[rgba(0,0,0,0.02)_0px_0px_20px]"
            :class="{ 'translate-x-0': isSidebarOpen, 'translate-x-full': !isSidebarOpen }"
        >
            <div class="h-16 lg:h-20 flex items-center px-6 lg:px-8 border-b border-stone-100/80 dark:border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-900/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-stone-800 dark:text-white tracking-tight">منصة جلسات</h1>
                        <p class="text-[10px] font-bold text-stone-400 dark:text-slate-500 uppercase tracking-widest mt-0.5">Tenant Portal</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 group relative overflow-hidden"
                    :class="[
                        item.active
                            ? 'text-blue-700 dark:text-blue-400 bg-blue-50/80 dark:bg-blue-950/60 shadow-sm'
                            : 'text-stone-500 dark:text-slate-400 hover:text-stone-800 dark:hover:text-white hover:bg-stone-50 dark:hover:bg-slate-800'
                    ]"
                    @click="isSidebarOpen = false"
                >
                    <div v-if="item.active" class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-l-full"></div>
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" :class="item.active ? 'text-blue-600 dark:text-blue-400' : 'text-stone-400 dark:text-slate-500 group-hover:text-stone-600 dark:group-hover:text-slate-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-4 border-t border-stone-100/80 dark:border-slate-800 space-y-2">
                <button
                    v-if="canInstall"
                    @click="installApp"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-700 text-white rounded-xl text-xs font-black hover:bg-blue-800 transition-all shadow-md shadow-blue-900/30"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    تثبيت تطبيق جلسات
                </button>
                <div
                    v-if="activeTenant?.expires_at"
                    class="text-center text-[11px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 px-3 py-1.5 rounded-xl flex items-center justify-center gap-1.5 shadow-sm"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>صالح حتى {{ activeTenant.expires_at }}</span>
                </div>
            </div>
        </aside>

        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 bg-stone-900/40 dark:bg-slate-950/70 backdrop-blur-sm z-40 lg:hidden"
            @click="isSidebarOpen = false"
        ></div>

        <div class="lg:pr-72 flex flex-col min-h-screen transition-all duration-300">
            <!-- Unified top navbar -->
            <header class="sticky top-0 z-30 h-16 lg:h-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-stone-200/60 dark:border-slate-800 flex items-center">
                <div class="w-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button
                            type="button"
                            @click="isSidebarOpen = true"
                            class="lg:hidden p-2 rounded-xl text-stone-500 dark:text-slate-400 hover:bg-stone-100 dark:hover:bg-slate-800 shrink-0"
                            aria-label="فتح القائمة"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h2 class="text-lg lg:text-xl font-bold text-stone-800 dark:text-white truncate">
                            <slot name="title" />
                        </h2>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <slot name="actions" />

                        <!-- Notifications -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="notificationsOpen = !notificationsOpen"
                                class="relative p-2.5 rounded-xl text-stone-600 dark:text-slate-300 hover:text-stone-900 dark:hover:text-white bg-white dark:bg-slate-800 hover:bg-stone-50 dark:hover:bg-slate-700 border border-stone-200 dark:border-slate-700 shadow-sm transition-all"
                                title="الإشعارات"
                                aria-label="الإشعارات"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </button>
                            <div
                                v-show="notificationsOpen"
                                class="fixed inset-0 z-40"
                                @click="notificationsOpen = false"
                            ></div>
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-show="notificationsOpen"
                                    class="absolute left-0 z-50 mt-2 w-72 origin-top-left rounded-xl bg-white dark:bg-slate-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
                                >
                                    <div class="px-4 py-3 border-b border-stone-100 dark:border-slate-700">
                                        <p class="text-sm font-bold text-stone-800 dark:text-white">الإشعارات</p>
                                    </div>
                                    <div class="px-4 py-8 text-center">
                                        <p class="text-sm text-stone-400 dark:text-slate-500">لا توجد إشعارات</p>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User dropdown -->
                        <Dropdown align="left" width="48" content-classes="py-1 bg-white dark:bg-slate-800">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 p-1.5 pe-2.5 rounded-xl bg-white dark:bg-slate-800 border border-stone-200 dark:border-slate-700 shadow-sm hover:bg-stone-50 dark:hover:bg-slate-700 transition-all"
                                >
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-700 dark:text-blue-400 font-bold text-sm shrink-0">
                                        {{ user?.name?.charAt(0) }}
                                    </div>
                                    <span class="hidden sm:block text-sm font-bold text-stone-700 dark:text-slate-200 max-w-[100px] truncate">{{ user?.name }}</span>
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-stone-100 dark:border-slate-700">
                                    <p class="text-sm font-bold text-stone-800 dark:text-white truncate">{{ user?.name }}</p>
                                    <p class="text-[11px] text-stone-400 dark:text-slate-500 truncate" dir="ltr">{{ user?.email }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleTheme"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-stone-700 dark:text-slate-200 hover:bg-stone-50 dark:hover:bg-slate-700 transition-colors"
                                >
                                    <svg v-if="isDarkMode" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                    {{ isDarkMode ? 'الوضع المضيء' : 'الوضع الداكن' }}
                                </button>
                                <DropdownLink :href="route('profile.edit')" class="dark:text-slate-200 dark:hover:bg-slate-700">
                                    الإعدادات
                                </DropdownLink>
                                <DropdownLink :href="route('profile.edit')" class="dark:text-slate-200 dark:hover:bg-slate-700">
                                    الملف الشخصي
                                </DropdownLink>
                                <div class="border-t border-stone-100 dark:border-slate-700">
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="text-red-600 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                    >
                                        تسجيل الخروج
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Trial Banner -->
            <div v-if="activeTenant?.is_trial" class="bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-slate-950 px-4 py-2.5 text-xs sm:text-sm font-bold flex items-center shadow-sm border-b border-amber-600/20">
                <div class="flex items-center gap-2 max-w-7xl mx-auto w-full justify-between">
                    <span class="flex items-center gap-2">
                        <span class="p-1 bg-slate-950/10 rounded-lg">🎁</span>
                        <span>فترة تجريبية مجانية لمكتبك</span>
                        <span class="bg-slate-950 text-amber-400 px-2.5 py-0.5 rounded-full text-xs font-black">متبقي {{ activeTenant?.days_left ?? 15 }} يوماً</span>
                    </span>
                    <span class="text-xs font-bold text-slate-900 opacity-90 hidden sm:inline">جميع الميزات مفعلة بالكامل لمكتبك الجديد</span>
                </div>
            </div>

            <div v-else-if="activeTenant?.days_left !== undefined && activeTenant?.days_left <= 7" class="bg-gradient-to-r from-rose-700 via-red-600 to-rose-700 text-white px-4 py-2.5 text-xs sm:text-sm font-bold flex items-center shadow-md">
                <div class="flex items-center gap-2 max-w-7xl mx-auto w-full justify-between">
                    <span class="flex items-center gap-2">
                        <span class="p-1 bg-white/20 rounded-lg">⚠️</span>
                        <span>تنبيه: اشتراك المكتب على وشك الانتهاء</span>
                        <span class="bg-white text-rose-800 px-2.5 py-0.5 rounded-full text-xs font-black">متبقي {{ activeTenant.days_left }} أيام</span>
                    </span>
                    <span class="text-xs font-bold opacity-90 hidden sm:inline">يرجى التواصل مع الإدارة لتجديد الاشتراك قبل الانتهاء (تاريخ الانتهاء: {{ activeTenant.expires_at }})</span>
                </div>
            </div>

            <div v-if="$page.props.flash?.success" class="px-4 sm:px-6 lg:px-8 mt-6">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm shadow-emerald-100/50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <div v-if="$page.props.flash?.error || $page.props.errors?.plan" class="px-4 sm:px-6 lg:px-8 mt-6">
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-5 py-3.5 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm shadow-rose-100/50">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    {{ $page.props.flash?.error || $page.props.errors?.plan }}
                </div>
            </div>

            <main class="flex-1 p-4 sm:px-6 lg:px-8 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e7e5e4;
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #d6d3d1;
}
</style>
