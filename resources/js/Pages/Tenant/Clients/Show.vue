<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    client: Object,
});

const confirmDelete = ref(false);

const deleteClient = () => {
    router.delete(route('clients.destroy', props.client.id));
};
</script>

<template>
    <Head :title="`ملف الموكل: ${client.name}`" />

    <TenantLayout>
        <template #title>ملف الموكل</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('clients.index')" class="hover:text-blue-700 transition-colors">الموكلين</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">{{ client.name }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Client Details -->
                <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl shrink-0">
                                {{ client.name.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-stone-800">{{ client.name }}</h2>
                                <p class="text-sm text-stone-500 mt-1">تاريخ الإضافة: {{ client.created_at }}</p>
                            </div>
                        </div>
                        <Link :href="route('clients.edit', client.id)" class="p-2.5 bg-stone-50 text-stone-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="تعديل البيانات">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </Link>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">رقم الهاتف</p>
                                <p class="text-sm font-medium text-stone-800" dir="ltr">{{ client.phone || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">البريد الإلكتروني</p>
                                <p class="text-sm font-medium text-stone-800" dir="ltr">{{ client.email || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">رقم الهوية</p>
                                <p class="text-sm font-medium text-stone-800" dir="ltr">{{ client.national_id || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">العنوان</p>
                                <p class="text-sm font-medium text-stone-800">{{ client.address || '—' }}</p>
                            </div>
                            <div class="sm:col-span-2" v-if="client.notes">
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">ملاحظات إضافية</p>
                                <div class="p-4 bg-yellow-50/50 border border-yellow-100 rounded-xl text-sm text-stone-700 whitespace-pre-wrap">{{ client.notes }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Cases -->
                <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-stone-800">قضايا الموكل</h3>
                        <Link :href="route('cases.create', { client_id: client.id })" class="text-sm font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">
                            + إضافة قضية
                        </Link>
                    </div>
                    <div v-if="client.legal_cases.length === 0" class="p-8 text-center">
                        <p class="text-stone-500">لا توجد قضايا مسجلة لهذا الموكل</p>
                    </div>
                    <div v-else class="divide-y divide-stone-100">
                        <div v-for="legalCase in client.legal_cases" :key="legalCase.id" class="p-5 hover:bg-stone-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <Link :href="route('cases.show', legalCase.id)" class="text-base font-bold text-stone-800 hover:text-blue-600 transition-colors">
                                        {{ legalCase.title }}
                                    </Link>
                                    <p class="text-sm text-stone-500 mt-1">رقم القضية: <span class="font-mono text-stone-600" dir="ltr">{{ legalCase.case_number }}</span></p>
                                </div>
                                <span
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': legalCase.status === 'active',
                                        'bg-stone-100 text-stone-600': legalCase.status === 'closed',
                                        'bg-blue-100 text-blue-700': legalCase.status === 'judged',
                                        'bg-orange-100 text-orange-700': legalCase.status === 'postponed'
                                    }"
                                >
                                    {{ 
                                        legalCase.status === 'active' ? 'جارية' : 
                                        (legalCase.status === 'closed' ? 'مغلقة' : 
                                        (legalCase.status === 'judged' ? 'محكوم بها' : 'مؤجلة')) 
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-200">
                    <h3 class="text-blue-100 text-sm font-semibold mb-4">ملخص نشاط الموكل</h3>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div>
                            <p class="text-3xl font-black">{{ client.legal_cases.length }}</p>
                            <p class="text-blue-100 text-sm">إجمالي القضايا</p>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-red-600 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        منطقة الخطر
                    </h3>
                    <button
                        @click="confirmDelete = true"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-100 transition-colors"
                    >
                        حذف الموكل
                    </button>
                    <p class="text-xs text-stone-400 mt-3 text-center leading-relaxed">
                        حذف الموكل سيؤدي إلى حذف جميع قضاياه والبيانات المرتبطة بها بشكل نهائي.
                    </p>
                </div>
            </div>
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
                <div v-if="confirmDelete" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDelete = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف الموكل</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف الموكل <strong class="text-stone-800">{{ client.name }}</strong>؟ سيتم حذف جميع القضايا والبيانات المرتبطة به. لا يمكن التراجع.</p>
                        <div class="flex items-center gap-3">
                            <button @click="confirmDelete = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors">
                                إلغاء
                            </button>
                            <button @click="deleteClient" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors">
                                نعم، احذف الموكل
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
