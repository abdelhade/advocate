<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login.submit'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="تسجيل دخول المدير" />

    <div class="min-h-screen bg-stone-50 flex items-center justify-center px-4 font-sans">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-700 text-white mb-4">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <h1 class="text-2xl font-black text-stone-900">لوحة تحكم المدير</h1>
                <p class="text-stone-500 mt-1">سجّل دخولك للوصول إلى لوحة الإدارة</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-stone-700 mb-2">
                            اسم المستخدم
                        </label>
                        <input
                            id="username"
                            type="text"
                            v-model="form.username"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-colors"
                            placeholder="أدخل اسم المستخدم"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.username }"
                        />
                        <p v-if="form.errors.username" class="mt-1.5 text-sm text-red-600">{{ form.errors.username }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-stone-700 mb-2">
                            كلمة المرور
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-colors pe-12"
                                placeholder="أدخل كلمة المرور"
                                :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded border-stone-300 text-red-700 focus:ring-red-600"
                        />
                        <label for="remember" class="ms-2 text-sm text-stone-600">تذكرني</label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">تسجيل الدخول</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            جارٍ التحقق...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Back to home -->
            <div class="text-center mt-6">
                <a href="/" class="text-sm text-stone-500 hover:text-red-700 transition-colors">
                    ← العودة للصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
</template>
