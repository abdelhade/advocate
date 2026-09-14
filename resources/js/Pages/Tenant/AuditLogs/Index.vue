<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    entityTypesMap: Object,
});

const search = ref(props.filters?.search || '');
const actionFilter = ref(props.filters?.action || '');
const entityTypeFilter = ref(props.filters?.entity_type || '');

const selectedLogDetails = ref(null);

const applyFilters = () => {
    router.get(route('audit-logs.index'), {
        search: search.value || undefined,
        action: actionFilter.value || undefined,
        entity_type: entityTypeFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    actionFilter.value = '';
    entityTypeFilter.value = '';
    router.get(route('audit-logs.index'));
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const getEntityName = (fullClass) => {
    return props.entityTypesMap[fullClass] || fullClass?.split('\\').pop() || fullClass;
};

const getActionBadgeClass = (action) => {
    switch (action) {
        case 'created':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'updated':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'deleted':
            return 'bg-red-50 text-red-700 border-red-200';
        case 'restored':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        default:
            return 'bg-stone-50 text-stone-700 border-stone-200';
    }
};

const getActionLabel = (action) => {
    switch (action) {
        case 'created':
            return '🟢 إنشاء / إضافة';
        case 'updated':
            return '🔵 تعديل / تحديث';
        case 'deleted':
            return '🔴 حذف';
        case 'restored':
            return '🟡 استعادة';
        default:
            return action;
    }
};
</script>

<template>
    <Head title="سجل النشاطات والتغيرات" />

    <TenantLayout>
        <template #title>
            <div class="flex items-center gap-2 text-sm text-stone-500">
                <Link :href="route('dashboard')" class="hover:text-blue-700 font-medium">الرئيسية</Link>
                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-stone-800 font-bold">سجل النشاطات والتغيرات</span>
            </div>
        </template>

        <!-- Filters Bar -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-5 mb-6 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 items-center">
                <!-- Search Input -->
                <div class="relative">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="البحث باسم المستخدم أو الرقم..."
                        class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-stone-200 text-stone-800 placeholder-stone-400 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    />
                    <svg class="w-4 h-4 text-stone-400 absolute right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Entity Type Dropdown -->
                <div>
                    <select
                        v-model="entityTypeFilter"
                        @change="applyFilters"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-800 text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    >
                        <option value="">جميع الكيانات والملفات</option>
                        <option v-for="(label, key) in entityTypesMap" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- Action Dropdown -->
                <div>
                    <select
                        v-model="actionFilter"
                        @change="applyFilters"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-800 text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                    >
                        <option value="">جميع الإجراءات (إضافة / تعديل / حذف)</option>
                        <option value="created">🟢 إنشاء / إضافة</option>
                        <option value="updated">🔵 تعديل / تحديث</option>
                        <option value="deleted">🔴 حذف</option>
                    </select>
                </div>

                <!-- Reset Filters Button -->
                <div class="flex items-center gap-2">
                    <button
                        v-if="search || actionFilter || entityTypeFilter"
                        @click="clearFilters"
                        class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition border border-stone-200 w-full sm:w-auto"
                    >
                        إعادة ضبط
                    </button>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-stone-700 border-collapse">
                    <thead class="bg-stone-50 text-xs font-bold text-stone-500 border-b border-stone-200/80">
                        <tr>
                            <th class="px-6 py-4">المستخدم / منفذ الإجراء</th>
                            <th class="px-6 py-4">نوع الإجراء</th>
                            <th class="px-6 py-4">الكيان / السجل المستهدف</th>
                            <th class="px-6 py-4">عنوان IP</th>
                            <th class="px-6 py-4">تاريخ ووقت النشاط</th>
                            <th class="px-6 py-4 text-left">تفاصيل التغيرات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-stone-50/70 transition-colors">
                            <td class="px-6 py-4 font-bold text-stone-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-stone-100 text-stone-700 flex items-center justify-center font-bold text-xs shrink-0 border border-stone-200">
                                        {{ log.user?.name ? log.user.name.charAt(0) : '⚙️' }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-stone-800">{{ log.user?.name || 'النظام التلقائي' }}</p>
                                        <p class="text-[11px] text-stone-400 font-mono" dir="ltr">{{ log.user?.email || '-' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span :class="['px-3 py-1 text-xs font-extrabold rounded-full border', getActionBadgeClass(log.action)]">
                                    {{ getActionLabel(log.action) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-stone-800 text-xs">{{ getEntityName(log.entity_type) }}</span>
                                    <span class="text-[11px] font-mono text-stone-400 bg-stone-100 px-2 py-0.5 rounded">ID: {{ log.entity_id }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-xs font-mono text-stone-500" dir="ltr">
                                {{ log.ip_address || '-' }}
                            </td>

                            <td class="px-6 py-4 text-xs font-medium text-stone-500">
                                {{ new Date(log.created_at).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' }) }}
                            </td>

                            <td class="px-6 py-4 text-left">
                                <button
                                    v-if="log.old_values || log.new_values"
                                    @click="selectedLogDetails = log"
                                    class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs rounded-lg transition border border-stone-200"
                                >
                                    معاينة البيانات 🔍
                                </button>
                                <span v-else class="text-stone-300 text-xs">-</span>
                            </td>
                        </tr>

                        <tr v-if="!logs.data || logs.data.length === 0">
                            <td colspan="6" class="p-12 text-center text-stone-400">
                                <div class="w-12 h-12 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-3 text-xl">
                                    🛡️
                                </div>
                                <p class="font-bold text-stone-700 text-sm">لا توجد نشاطات أو تغييرات مسجلة حالياً</p>
                                <p class="text-xs text-stone-400 mt-1">يتم تسجلي كافة عمليات الإضافة والتعديل والحذف لكافة ملفات النظام تلقائياً هنا</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-stone-100 flex items-center justify-between">
                <div class="flex flex-wrap gap-1">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, index) in logs.links"
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                            link.active ? 'bg-blue-700 text-white' : link.url ? 'bg-stone-100 text-stone-700 hover:bg-stone-200' : 'text-stone-300'
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="selectedLogDetails" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="selectedLogDetails = null">
                    <div class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl max-h-[85vh] overflow-y-auto">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                            <h3 class="text-base font-bold text-stone-800 flex items-center gap-2">
                                🔍 تفاصيل النشاط: {{ getEntityName(selectedLogDetails.entity_type) }}
                            </h3>
                            <button @click="selectedLogDetails = null" class="text-stone-400 hover:text-stone-600 p-1">
                                ✕
                            </button>
                        </div>

                        <!-- Changes Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- New Values -->
                            <div v-if="selectedLogDetails.new_values" class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-200/60">
                                <h4 class="text-xs font-bold text-emerald-800 mb-3">البيانات / القيم الجديدة (New Values)</h4>
                                <pre class="text-[11px] text-stone-800 bg-white p-3 rounded-lg border border-emerald-100 font-mono overflow-x-auto" dir="ltr">{{ JSON.stringify(selectedLogDetails.new_values, null, 2) }}</pre>
                            </div>

                            <!-- Old Values -->
                            <div v-if="selectedLogDetails.old_values" class="bg-rose-50/50 rounded-xl p-4 border border-rose-200/60">
                                <h4 class="text-xs font-bold text-rose-800 mb-3">القيم السابقة قبل التعديل (Old Values)</h4>
                                <pre class="text-[11px] text-stone-800 bg-white p-3 rounded-lg border border-rose-100 font-mono overflow-x-auto" dir="ltr">{{ JSON.stringify(selectedLogDetails.old_values, null, 2) }}</pre>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button @click="selectedLogDetails = null" class="px-5 py-2.5 bg-stone-800 text-white rounded-xl text-xs font-bold hover:bg-stone-900 transition">
                                إغلاق
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
