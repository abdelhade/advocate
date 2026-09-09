<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    admin: Object,
});

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    name: props.admin.name,
    username: props.admin.username,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.admins.update', props.admin.id));
};
</script>

<template>
    <Head :title="`تعديل المدير: ${admin.name}`" />

    <AdminLayout>
        <template #title>تعديل المدير</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('admin.admins.index')" class="hover:text-red-700 transition-colors">المديرين</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">تعديل: {{ admin.name }}</span>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white rounded-2xl border border-stone-200/80 p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-stone-800">تعديل بيانات المدير</h2>
                    <p class="text-sm text-stone-400 mt-1">اترك حقل كلمة المرور فارغاً إذا لم ترد تغييرها</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-stone-700 mb-2">
                            الاسم <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-stone-700 mb-2">
                            اسم المستخدم <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="username"
                            type="text"
                            v-model="form.username"
                            required
                            dir="ltr"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.username }"
                        />
                        <p v-if="form.errors.username" class="mt-1.5 text-sm text-red-600">{{ form.errors.username }}</p>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-stone-100 pt-6">
                        <p class="text-sm font-semibold text-stone-600 mb-1">تغيير كلمة المرور</p>
                        <p class="text-xs text-stone-400 mb-4">اختياري — اتركه فارغاً للإبقاء على كلمة المرور الحالية</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-stone-700 mb-2">
                            كلمة المرور الجديدة
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="كلمة المرور الجديدة (اختياري)"
                                dir="ltr"
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left pe-12"
                                :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.password }"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div v-if="form.password">
                        <label for="password_confirmation" class="block text-sm font-semibold text-stone-700 mb-2">
                            تأكيد كلمة المرور
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                placeholder="أعد إدخال كلمة المرور"
                                dir="ltr"
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left pe-12"
                            />
                            <button type="button" @click="showConfirm = !showConfirm" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors">
                                <svg v-if="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-red-200"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ التغييرات' }}
                        </button>
                        <Link :href="route('admin.admins.index')" class="px-6 py-3 border border-stone-200 text-stone-600 text-sm font-semibold rounded-xl hover:bg-stone-50 transition-colors">
                            إلغاء
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
