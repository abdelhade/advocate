<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    case: Object,
});

const legalCase = computed(() => props.case);

// Session Management
const showAddSessionModal = ref(false);
const sessionForm = useForm({
    session_date: '',
    session_time: '',
    location: '',
    decision: '',
    next_session_date: '',
    notes: '',
});

const submitSession = () => {
    sessionForm.post(route('cases.sessions.store', props.case.id), {
        onSuccess: () => {
            sessionForm.reset();
            showAddSessionModal.value = false;
        },
    });
};

const confirmDeleteSession = ref(null);
const deleteSession = (sessionId) => {
    router.delete(route('cases.sessions.destroy', [props.case.id, sessionId]), {
        onSuccess: () => {
            confirmDeleteSession.value = null;
        },
    });
};

// Notes Management
const noteForm = useForm({
    content: '',
});

const submitNote = () => {
    noteForm.post(route('cases.notes.store', props.case.id), {
        onSuccess: () => noteForm.reset(),
    });
};

// Case Management
const confirmDeleteCase = ref(false);
const deleteCase = () => {
    router.delete(route('cases.destroy', props.case.id));
};
</script>

<template>
    <Head :title="`تفاصيل القضية: ${legalCase.title}`" />

    <TenantLayout>
        <template #title>تفاصيل القضية</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('cases.index')" class="hover:text-blue-700 transition-colors">القضايا</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium truncate max-w-[200px]" dir="auto">{{ legalCase.title }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content (Left side in RTL) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Case Info Card -->
                <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-stone-100 flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-mono text-sm text-stone-500 bg-stone-100 px-2.5 py-1 rounded-lg" dir="ltr">{{ legalCase.case_number }}</span>
                                <span
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold"
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
                            <h2 class="text-2xl font-black text-stone-800">{{ legalCase.title }}</h2>
                            <p class="text-sm font-semibold text-blue-600 mt-2">نوع القضية: {{ legalCase.case_type }}</p>
                        </div>
                        <Link :href="route('cases.edit', legalCase.id)" class="shrink-0 p-2.5 bg-stone-50 text-stone-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="تعديل القضية">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </Link>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                            <!-- Court Details -->
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    المحكمة المختصة
                                </p>
                                <p class="text-sm font-medium text-stone-800">{{ legalCase.court || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    تاريخ الرفع
                                </p>
                                <p class="text-sm font-medium text-stone-800">{{ legalCase.filed_at || '—' }}</p>
                            </div>
                            <!-- Opponent Details -->
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    اسم الخصم
                                </p>
                                <p class="text-sm font-medium text-stone-800">{{ legalCase.opponent_name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    محامي الخصم
                                </p>
                                <p class="text-sm font-medium text-stone-800">{{ legalCase.opponent_lawyer || '—' }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="legalCase.description" class="pt-6 border-t border-stone-100">
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">وصف القضية وملخصها</p>
                            <div class="text-sm text-stone-700 whitespace-pre-wrap leading-relaxed">{{ legalCase.description }}</div>
                        </div>
                    </div>
                </div>

                <!-- Sessions Tracker -->
                <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-stone-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            جلسات القضية
                        </h3>
                        <button @click="showAddSessionModal = true" class="text-sm font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">
                            + إضافة جلسة
                        </button>
                    </div>

                    <div v-if="legalCase.sessions.length === 0" class="p-8 text-center bg-stone-50/50">
                        <div class="w-16 h-16 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-stone-600 font-semibold">لا توجد جلسات مسجلة</p>
                        <p class="text-sm text-stone-400 mt-1">قم بإضافة الجلسات لتتبع مسار القضية</p>
                    </div>

                    <div v-else class="relative p-6">
                        <!-- Timeline Line -->
                        <div class="absolute right-9 top-8 bottom-8 w-px bg-stone-200"></div>
                        
                        <div class="space-y-6">
                            <div v-for="(session, index) in legalCase.sessions" :key="session.id" class="relative pl-4 pr-10">
                                <!-- Timeline Dot -->
                                <div class="absolute right-[5px] top-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm z-10" :class="index === legalCase.sessions.length - 1 ? 'bg-orange-500' : 'bg-stone-300'"></div>
                                
                                <div class="bg-stone-50 border border-stone-200/80 rounded-xl p-4 hover:shadow-md transition-shadow relative group">
                                    <!-- Delete Session Button -->
                                    <button 
                                        @click="confirmDeleteSession = session.id"
                                        class="absolute left-3 top-3 p-1.5 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all"
                                        title="حذف الجلسة"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>

                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold" :class="index === legalCase.sessions.length - 1 ? 'bg-orange-100 text-orange-700' : 'bg-stone-200 text-stone-700'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ session.session_date }}
                                        </span>
                                        <span v-if="session.session_time" class="text-sm font-semibold text-stone-500" dir="ltr">{{ session.session_time }}</span>
                                    </div>
                                    <p v-if="session.location" class="text-sm font-semibold text-stone-700 mb-2 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ session.location }}
                                    </p>
                                    <div v-if="session.decision" class="mt-3 p-3 bg-white rounded-lg border border-stone-100 text-sm text-stone-700">
                                        <strong class="text-stone-900 block mb-1">القرار / النتيجة:</strong>
                                        {{ session.decision }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right side in RTL) -->
            <div class="space-y-6">
                <!-- Client Info Widget -->
                <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100">الموكل المرتبط</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg shrink-0">
                            {{ legalCase.client.name.charAt(0) }}
                        </div>
                        <div>
                            <Link :href="route('clients.show', legalCase.client.id)" class="text-base font-bold text-stone-800 hover:text-blue-700 transition-colors">
                                {{ legalCase.client.name }}
                            </Link>
                            <p class="text-xs text-stone-500 mt-0.5" dir="ltr">{{ legalCase.client.phone || 'بدون رقم' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="bg-stone-50 rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden flex flex-col h-[500px]">
                    <div class="p-4 border-b border-stone-200 bg-white">
                        <h3 class="text-sm font-bold text-stone-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            ملاحظات القضية
                        </h3>
                    </div>
                    
                    <!-- Notes List -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                        <div v-if="legalCase.notes.length === 0" class="text-center py-8">
                            <p class="text-stone-400 text-sm">لا توجد ملاحظات</p>
                        </div>
                        <div v-for="note in legalCase.notes" :key="note.id" class="bg-white p-3 rounded-xl border border-stone-200/60 shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-stone-800">{{ note.user_name }}</span>
                                <span class="text-[10px] text-stone-400" dir="ltr">{{ note.created_at }}</span>
                            </div>
                            <p class="text-sm text-stone-600 whitespace-pre-wrap">{{ note.content }}</p>
                        </div>
                    </div>

                    <!-- Add Note Form -->
                    <div class="p-3 bg-white border-t border-stone-200">
                        <form @submit.prevent="submitNote" class="relative">
                            <textarea
                                v-model="noteForm.content"
                                rows="2"
                                placeholder="أضف ملاحظة جديدة..."
                                class="w-full pl-12 pr-3 py-2 text-sm rounded-lg border border-stone-200 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none custom-scrollbar"
                                required
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="noteForm.processing || !noteForm.content"
                                class="absolute left-2 bottom-2 p-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
                    <button
                        @click="confirmDeleteCase = true"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-100 transition-colors"
                    >
                        حذف القضية
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Session Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddSessionModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showAddSessionModal = false">
                    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-6 border-b border-stone-100 flex items-center justify-between bg-stone-50/50 shrink-0">
                            <h3 class="text-lg font-bold text-stone-800">إضافة جلسة جديدة</h3>
                            <button @click="showAddSessionModal = false" class="text-stone-400 hover:text-stone-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                            <form @submit.prevent="submitSession" id="sessionForm" class="space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-stone-700 mb-2">تاريخ الجلسة <span class="text-red-500">*</span></label>
                                        <input type="date" v-model="sessionForm.session_date" required class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm" />
                                        <p v-if="sessionForm.errors.session_date" class="mt-1 text-xs text-red-600">{{ sessionForm.errors.session_date }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-stone-700 mb-2">وقت الجلسة</label>
                                        <input type="time" v-model="sessionForm.session_time" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm" />
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-stone-700 mb-2">مكان الانعقاد / الدائرة</label>
                                    <input type="text" v-model="sessionForm.location" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-stone-700 mb-2">القرار / النتيجة</label>
                                    <textarea v-model="sessionForm.decision" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 bg-stone-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm resize-none"></textarea>
                                </div>
                            </form>
                        </div>
                        
                        <div class="p-6 border-t border-stone-100 flex items-center gap-3 bg-stone-50/50 shrink-0">
                            <button type="submit" form="sessionForm" :disabled="sessionForm.processing" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors">
                                {{ sessionForm.processing ? 'جارٍ الحفظ...' : 'حفظ الجلسة' }}
                            </button>
                            <button @click="showAddSessionModal = false" type="button" class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-100 transition-colors">
                                إلغاء
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Case Delete Confirmation Modal -->
        <Teleport to="body">
            <!-- (Similar to client delete modal) -->
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="confirmDeleteCase" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDeleteCase = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف القضية</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف هذه القضية بشكل نهائي؟</p>
                        <div class="flex gap-3">
                            <button @click="confirmDeleteCase = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50">إلغاء</button>
                            <button @click="deleteCase" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold">نعم، احذف</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Session Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="confirmDeleteSession" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDeleteSession = null">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف الجلسة</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف هذه الجلسة؟</p>
                        <div class="flex gap-3">
                            <button @click="confirmDeleteSession = null" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50">إلغاء</button>
                            <button @click="deleteSession(confirmDeleteSession)" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold">نعم، احذف</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </TenantLayout>
</template>
