<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const activeTenant = computed(() => page.props.auth?.active_tenant);

const isSidebarOpen = ref(false);
const deferredPrompt = ref(null);
const canInstall = ref(false);
const isDarkMode = ref(false);

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    isDarkMode.value = localStorage.getItem('theme') === 'dark';
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        document.body.classList.add('dark');
    }

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

const navigation = [
    { name: 'لوحة التحكم', href: route('dashboard'), icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', active: route().current('dashboard') },
    { name: 'الموكلين', href: route('clients.index'), icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', active: route().current('clients.*') },
    { name: 'القضايا', href: route('cases.index'), icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', active: route().current('cases.*') },
    { name: 'المهام والأعمال', href: route('tasks.index'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', active: route().current('tasks.*') },
    { name: 'الفواتير والأتعاب', href: route('invoices.index'), icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', active: route().current('invoices.*') },
    { name: 'سندات القبض', href: route('payments.index'), icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', active: route().current('payments.*') },
    { name: 'المصروفات', href: route('expenses.index'), icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', active: route().current('expenses.*') },
    { name: 'فريق العمل والمستخدمين', href: route('tenant.users.index'), icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', active: route().current('tenant.users.*') },
    { name: 'سجل النشاطات', href: route('audit-logs.index'), icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', active: route().current('audit-logs.*') },
];
</script>

<template>
    <div class="min-h-screen bg-stone-50" dir="rtl">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 right-0 z-50 w-72 bg-white border-l border-stone-200/80 transition-transform duration-300 transform lg:translate-x-0 flex flex-col shadow-[rgba(0,0,0,0.02)_0px_0px_20px]"
            :class="{ 'translate-x-0': isSidebarOpen, 'translate-x-full': !isSidebarOpen }"
        >
            <!-- Logo -->
            <div class="h-20 flex items-center px-8 border-b border-stone-100/80 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-700 flex items-center justify-center shadow-lg shadow-blue-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-stone-800 tracking-tight">منصة جلسات</h1>
                        <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mt-0.5">Tenant Portal</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 group relative overflow-hidden"
                    :class="[
                        item.active 
                            ? 'text-blue-700 bg-blue-50/80 shadow-sm' 
                            : 'text-stone-500 hover:text-stone-800 hover:bg-stone-50'
                    ]"
                >
                    <div v-if="item.active" class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-l-full"></div>
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" :class="item.active ? 'text-blue-600' : 'text-stone-400 group-hover:text-stone-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Profile & Subscription Info -->
            <div class="p-6 border-t border-stone-100/80 bg-stone-50/50">
                <button
                    v-if="canInstall"
                    @click="installApp"
                    class="w-full mb-3 flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-700 text-white rounded-xl text-xs font-black hover:bg-blue-800 transition-all shadow-md shadow-blue-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    تثبيت تطبيق جلسات
                </button>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-stone-200/80 shadow-sm mb-2">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm shrink-0">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-stone-800 truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-[11px] text-stone-400 truncate" dir="ltr">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>

                <!-- Sidebar Subscription Validity Date -->
                <div v-if="activeTenant?.expires_at" class="mt-2 text-center text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-3 py-1.5 rounded-xl flex items-center justify-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>صالح حتى {{ activeTenant.expires_at }}</span>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('profile.edit')" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        الملف
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-red-600 hover:bg-red-50 transition-colors border border-transparent hover:border-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        خروج
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Overlay -->
        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-40 lg:hidden"
            @click="isSidebarOpen = false"
        ></div>

        <!-- Main Content -->
        <div class="lg:pr-72 flex flex-col min-h-screen transition-all duration-300">
            <!-- Mobile Header -->
            <div class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 bg-white border-b border-stone-200/80 lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-lg font-black text-stone-800">منصة جلسات</span>
                </div>
                <button
                    @click="isSidebarOpen = true"
                    class="p-2 text-stone-500 rounded-xl hover:bg-stone-100"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <!-- Page Header -->
            <header class="bg-white/50 backdrop-blur-xl border-b border-stone-200/50 sticky top-0 z-20 lg:top-0 lg:h-20 flex items-center">
                <div class="w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4 lg:py-0">
                    <h2 class="text-xl font-bold text-stone-800 flex items-center gap-2">
                        <slot name="title" />
                    </h2>
                    <div class="flex items-center gap-3">
                        <button
                            @click="toggleTheme"
                            type="button"
                            class="px-3 py-1.5 rounded-xl text-stone-600 hover:text-stone-900 bg-white hover:bg-stone-50 transition-all flex items-center gap-2 text-xs font-bold border border-stone-200 shadow-sm cursor-pointer"
                            :title="isDarkMode ? 'التحويل للوضع المضيء' : 'التحويل للوضع الداكن'"
                        >
                            <svg v-if="isDarkMode" class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <svg v-else class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            <span>{{ isDarkMode ? 'مضيء' : 'داكن' }}</span>
                        </button>
                        <slot name="actions" />
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

            <!-- Expiring Soon Warning Banner (Only shown when <= 7 days left) -->
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

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="px-4 sm:px-6 lg:px-8 mt-6">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm shadow-emerald-100/50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    {{ $page.props.flash.success }}
                </div>
            </div>

            <!-- Content -->
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
