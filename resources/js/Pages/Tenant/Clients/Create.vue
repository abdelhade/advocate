<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    phone: '',
    email: '',
    national_id: '',
    address: '',
    notes: '',
});

const submit = () => {
    form.post(route('clients.store'));
};
</script>

<template>
    <Head title="إضافة موكل جديد" />

    <TenantLayout>
        <template #title>إضافة موكل جديد</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('clients.index')" class="hover:text-blue-700 transition-colors">الموكلين</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">إضافة موكل</span>
        </div>

        <div class="max-w-3xl">
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 md:p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-base font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100">البيانات الأساسية</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-semibold text-stone-700 mb-2">
                                    اسم الموكل <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-stone-700 mb-2">رقم الهاتف</label>
                                <input
                                    id="phone"
                                    type="text"
                                    v-model="form.phone"
                                    dir="ltr"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="mt-1.5 text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>

                            <!-- National ID -->
                            <div>
                                <label for="national_id" class="block text-sm font-semibold text-stone-700 mb-2">رقم الهوية</label>
                                <input
                                    id="national_id"
                                    type="text"
                                    v-model="form.national_id"
                                    dir="ltr"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.national_id }"
                                />
                                <p v-if="form.errors.national_id" class="mt-1.5 text-sm text-red-600">{{ form.errors.national_id }}</p>
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-semibold text-stone-700 mb-2">البريد الإلكتروني</label>
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    dir="ltr"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div>
                        <h3 class="text-base font-bold text-stone-800 mb-4 pb-2 border-b border-stone-100">معلومات إضافية</h3>
                        <div class="space-y-6">
                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-semibold text-stone-700 mb-2">العنوان</label>
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    rows="2"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.address }"
                                ></textarea>
                                <p v-if="form.errors.address" class="mt-1.5 text-sm text-red-600">{{ form.errors.address }}</p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-semibold text-stone-700 mb-2">ملاحظات</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                                    :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.notes }"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1.5 text-sm text-red-600">{{ form.errors.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-6 border-t border-stone-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-blue-200"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ الموكل' }}
                        </button>
                        <Link :href="route('clients.index')" class="px-6 py-3 border border-stone-200 text-stone-600 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-colors">
                            إلغاء
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
