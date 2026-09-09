<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    case: Object,
    clients: Array,
});

const form = useForm({
    case_number: props.case.case_number,
    title: props.case.title,
    client_id: props.case.client_id,
    case_type: props.case.case_type,
    court: props.case.court || '',
    status: props.case.status,
    opponent_name: props.case.opponent_name || '',
    opponent_lawyer: props.case.opponent_lawyer || '',
    description: props.case.description || '',
    filed_at: props.case.filed_at || '',
});

const submit = () => {
    form.put(route('cases.update', props.case.id));
};
</script>

<template>
    <Head :title="`تعديل القضية: ${case.title}`" />

    <TenantLayout>
        <template #title>تعديل القضية</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('cases.index')" class="hover:text-blue-700 transition-colors">القضايا</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <Link :href="route('cases.show', case.id)" class="hover:text-blue-700 transition-colors truncate max-w-[150px]">{{ case.title }}</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">تعديل</span>
        </div>

        <div class="max-w-4xl">
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 md:p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-base font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                            البيانات الأساسية
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Case Number -->
                            <div>
                                <label for="case_number" class="block text-sm font-semibold text-stone-700 mb-2">رقم القضية <span class="text-red-500">*</span></label>
                                <input id="case_number" type="text" v-model="form.case_number" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left" :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.case_number }" />
                                <p v-if="form.errors.case_number" class="mt-1 text-sm text-red-600">{{ form.errors.case_number }}</p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-semibold text-stone-700 mb-2">موضوع القضية <span class="text-red-500">*</span></label>
                                <input id="title" type="text" v-model="form.title" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.title }" />
                            </div>

                            <!-- Client -->
                            <div>
                                <label for="client_id" class="block text-sm font-semibold text-stone-700 mb-2">الموكل <span class="text-red-500">*</span></label>
                                <select id="client_id" v-model="form.client_id" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>
                            </div>

                            <!-- Case Type -->
                            <div>
                                <label for="case_type" class="block text-sm font-semibold text-stone-700 mb-2">نوع القضية <span class="text-red-500">*</span></label>
                                <select id="case_type" v-model="form.case_type" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    <option value="جنائي">جنائي</option>
                                    <option value="مدني">مدني</option>
                                    <option value="تجاري">تجاري</option>
                                    <option value="أسرة">أسرة (أحوال شخصية)</option>
                                    <option value="عمالي">عمالي</option>
                                    <option value="إداري">إداري</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Judicial Info -->
                    <div>
                        <h3 class="text-base font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                            البيانات القضائية
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-semibold text-stone-700 mb-2">حالة القضية <span class="text-red-500">*</span></label>
                                <select id="status" v-model="form.status" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    <option value="active">جارية</option>
                                    <option value="postponed">مؤجلة</option>
                                    <option value="judged">محكوم بها</option>
                                    <option value="closed">مغلقة</option>
                                </select>
                            </div>

                            <!-- Court -->
                            <div>
                                <label for="court" class="block text-sm font-semibold text-stone-700 mb-2">المحكمة المختصة</label>
                                <input id="court" type="text" v-model="form.court" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                            </div>

                            <!-- Opponent Name -->
                            <div>
                                <label for="opponent_name" class="block text-sm font-semibold text-stone-700 mb-2">اسم الخصم</label>
                                <input id="opponent_name" type="text" v-model="form.opponent_name" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                            </div>

                            <!-- Opponent Lawyer -->
                            <div>
                                <label for="opponent_lawyer" class="block text-sm font-semibold text-stone-700 mb-2">محامي الخصم</label>
                                <input id="opponent_lawyer" type="text" v-model="form.opponent_lawyer" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                            </div>

                            <!-- Filed Date -->
                            <div>
                                <label for="filed_at" class="block text-sm font-semibold text-stone-700 mb-2">تاريخ رفع القضية</label>
                                <input id="filed_at" type="date" v-model="form.filed_at" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3 class="text-base font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                            تفاصيل إضافية
                        </h3>
                        <div>
                            <label for="description" class="block text-sm font-semibold text-stone-700 mb-2">وصف وملخص القضية</label>
                            <textarea id="description" v-model="form.description" rows="4" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-6 border-t border-stone-100">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl transition-all duration-200 disabled:opacity-50 shadow-sm hover:shadow-blue-200">
                            {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ التعديلات' }}
                        </button>
                        <Link :href="route('cases.show', case.id)" class="px-6 py-3 border border-stone-200 text-stone-600 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-colors">إلغاء</Link>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
