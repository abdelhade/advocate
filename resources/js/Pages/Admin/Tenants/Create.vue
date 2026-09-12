<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    id: '',
    domain: '',
});

const submit = () => {
    form.post(route('admin.tenants.store'));
};
</script>

<template>
    <Head title="إضافة مكتب جديد" />

    <AdminLayout>
        <template #title>إضافة مكتب جديد</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('admin.tenants.index')" class="hover:text-red-700 transition-colors">المكاتب</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">إضافة مكتب جديد</span>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white rounded-2xl border border-stone-200/80 p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-stone-800">بيانات المكتب</h2>
                    <p class="text-sm text-stone-400 mt-1">أدخل معرّف المكتب والنطاق لإنشاء مكتب محاماة جديد على المنصة</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Tenant ID -->
                    <div>
                        <label for="id" class="block text-sm font-semibold text-stone-700 mb-2">
                            معرّف المكتب
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="id"
                            type="text"
                            v-model="form.id"
                            required
                            autofocus
                            placeholder="مثال: alfahd-law"
                            dir="ltr"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.id }"
                        />
                        <p class="mt-1.5 text-xs text-stone-400">أحرف إنجليزية صغيرة وأرقام وشرطات فقط</p>
                        <p v-if="form.errors.id" class="mt-1.5 text-sm text-red-600">{{ form.errors.id }}</p>
                    </div>

                    <!-- Domain -->
                    <div>
                        <label for="domain" class="block text-sm font-semibold text-stone-700 mb-2">
                            النطاق
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="domain"
                            type="text"
                            v-model="form.domain"
                            required
                            placeholder="مثال: alfahd.jalsat.test"
                            dir="ltr"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.domain }"
                        />
                        <p class="mt-1.5 text-xs text-stone-400">النطاق الذي سيستخدمه المكتب للوصول للمنصة</p>
                        <p v-if="form.errors.domain" class="mt-1.5 text-sm text-red-600">{{ form.errors.domain }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-red-200"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            {{ form.processing ? 'جارٍ الإنشاء...' : 'إنشاء المكتب' }}
                        </button>
                        <Link :href="route('admin.tenants.index')" class="px-6 py-3 border border-stone-200 text-stone-600 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-colors">
                            إلغاء
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
