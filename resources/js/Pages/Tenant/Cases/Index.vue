<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    cases: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const caseType = ref(props.filters?.case_type || '');
const sort = ref(props.filters?.sort || 'created_at');
const direction = ref(props.filters?.direction || 'desc');
const showFilters = ref(!!(props.filters?.status || props.filters?.case_type));

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    status: status.value || undefined,
    case_type: caseType.value || undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    direction: direction.value !== 'desc' || sort.value !== 'created_at' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('cases.index'), filterParams(), {
            preserveState: true,
            replace: true,
        });
    };
    if (immediate) {
        run();
    } else {
        filterTimeout = setTimeout(run, 400);
    }
};

watch([search, status, caseType], () => {
    updateFilters();
});

const toggleSort = (column) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
    updateFilters(true);
};

const sortIcon = (column) => {
    if (sort.value !== column) return 'none';
    return direction.value === 'asc' ? 'asc' : 'desc';
};

const statusLabel = (value) => {
    const map = {
        active: 'جارية',
        closed: 'مغلقة',
        judged: 'محكوم بها',
        postponed: 'مؤجلة',
        suspended: 'موقوفة',
        won: 'مكسوبة',
        lost: 'مخسورة',
    };
    return map[value] || value;
};

const confirmDelete = ref(null);

const deleteCase = (id) => {
    router.delete(route('cases.destroy', id), {
        onSuccess: () => {
            confirmDelete.value = null;
        },
    });
};

const hasActiveFilters = computed(() => !!(status.value || caseType.value));
</script>

<template>
    <Head title="القضايا" />

    <TenantLayout>
        <template #title>القضايا</template>

        <!-- Breadcrumb + add -->
        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">القضايا</span>
            </nav>
            <Link
                :href="route('cases.create')"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="إضافة قضية جديدة"
                aria-label="إضافة قضية جديدة"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </Link>
        </div>

        <!-- Search + filters toggle -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث برقم القضية أو العنوان..."
                        class="w-full pr-12 pl-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                    />
                </div>
                <button
                    type="button"
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-all shrink-0"
                    :class="showFilters || hasActiveFilters
                        ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400'
                        : 'border-stone-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-stone-600 dark:text-slate-300 hover:bg-stone-50 dark:hover:bg-slate-700'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    فلاتر
                    <span
                        v-if="hasActiveFilters"
                        class="w-2 h-2 rounded-full bg-blue-600"
                    ></span>
                </button>
            </div>

            <Transition
                enter-active-class="transition-all duration-200 overflow-hidden"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-40"
                leave-active-class="transition-all duration-150 overflow-hidden"
                leave-from-class="opacity-100 max-h-40"
                leave-to-class="opacity-0 max-h-0"
            >
                <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-stone-100 dark:border-slate-800">
                    <select
                        v-model="status"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الحالات</option>
                        <option value="active">جارية</option>
                        <option value="postponed">مؤجلة</option>
                        <option value="judged">محكوم بها</option>
                        <option value="closed">مغلقة</option>
                    </select>
                    <select
                        v-model="caseType"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الأنواع</option>
                        <option value="جنائي">جنائي</option>
                        <option value="مدني">مدني</option>
                        <option value="تجاري">تجاري</option>
                        <option value="أسرة">أسرة (أحوال شخصية)</option>
                        <option value="عمالي">عمالي</option>
                        <option value="إداري">إداري</option>
                    </select>
                </div>
            </Transition>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="cases.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search || status || caseType">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد قضايا مسجلة</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('case_number')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        رقم القضية
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('case_number') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('case_number') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('title')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        عنوان القضية
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('title') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('title') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الموكل</th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">النوع</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('status')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الحالة
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('status') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('status') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('created_at')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        تاريخ الإضافة
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('created_at') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('created_at') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                            <tr
                                v-for="legalCase in cases.data"
                                :key="legalCase.id"
                                class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 font-mono text-sm text-stone-600 dark:text-slate-300" dir="ltr">{{ legalCase.case_number }}</td>
                                <td class="px-6 py-4">
                                    <Link :href="route('cases.show', legalCase.id)" class="text-sm font-bold text-stone-800 dark:text-white hover:text-blue-700 dark:hover:text-blue-400 transition-colors">
                                        {{ legalCase.title }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-600 dark:text-slate-300">{{ legalCase.client_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-stone-100 dark:bg-slate-800 text-stone-800 dark:text-slate-200 border border-stone-200 dark:border-slate-700">
                                        {{ legalCase.case_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400': legalCase.status === 'active',
                                            'bg-stone-100 text-stone-600 dark:bg-slate-800 dark:text-slate-400': legalCase.status === 'closed',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400': legalCase.status === 'judged' || legalCase.status === 'won' || legalCase.status === 'lost',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-400': legalCase.status === 'postponed' || legalCase.status === 'suspended',
                                        }"
                                    >
                                        {{ statusLabel(legalCase.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400">{{ legalCase.created_at || '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link :href="route('cases.show', legalCase.id)" class="p-2 rounded-lg text-stone-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors" title="التفاصيل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </Link>
                                        <Link :href="route('cases.edit', legalCase.id)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </Link>
                                        <button
                                            @click="confirmDelete = legalCase.id"
                                            class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-rose-950/30 transition-colors"
                                            title="حذف"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="cases.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100 dark:border-slate-800">
                    <p class="text-sm text-stone-500 dark:text-slate-400">
                        عرض {{ cases.from }} - {{ cases.to }} من {{ cases.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in cases.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="link.active ? 'bg-blue-700 text-white font-bold' : 'text-stone-500 dark:text-slate-400 hover:bg-stone-100 dark:hover:bg-slate-800'"
                                v-html="link.label"
                                preserve-state
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 text-sm text-stone-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact aggregated stats -->
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>الإجمالي <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.total ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>جارية <strong class="text-emerald-700 dark:text-emerald-400 font-bold tabular-nums">{{ stats?.active ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>مؤجلة <strong class="text-orange-700 dark:text-orange-400 font-bold tabular-nums">{{ stats?.postponed ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>محكوم <strong class="text-blue-700 dark:text-blue-400 font-bold tabular-nums">{{ stats?.judged ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>مغلقة <strong class="text-stone-600 dark:text-slate-300 font-bold tabular-nums">{{ stats?.closed ?? 0 }}</strong></span>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirmDelete" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDelete = null">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-rose-950/50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 dark:text-white text-center mb-2">حذف القضية</h3>
                        <p class="text-sm text-stone-500 dark:text-slate-400 text-center mb-6">هل أنت متأكد من حذف هذه القضية؟ سيتم حذف جميع الجلسات والملاحظات المرتبطة بها نهائياً.</p>
                        <div class="flex items-center gap-3">
                            <button
                                @click="confirmDelete = null"
                                class="flex-1 px-4 py-3 border border-stone-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-stone-600 dark:text-slate-300 hover:bg-stone-50 dark:hover:bg-slate-800 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="deleteCase(confirmDelete)"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors"
                            >
                                نعم، احذف القضية
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
