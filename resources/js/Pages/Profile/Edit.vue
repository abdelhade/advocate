<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const user = usePage().props.auth.user;

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

// Profile Form
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

// Delete Account
const showDeleteModal = ref(false);
const deleteForm = useForm({
    password: '',
});

const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => showDeleteModal.value = false,
    });
};
</script>

<template>
    <Head title="الملف الشخصي" />

    <TenantLayout>
        <template #title>👤 الملف الشخصي</template>

        <div class="max-w-3xl space-y-6">
            <!-- Profile Info -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-stone-100">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-2xl shrink-0">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-stone-800">البيانات الشخصية</h3>
                            <p class="text-sm text-stone-400 mt-0.5">تحديث معلومات الحساب وعنوان البريد الإلكتروني</p>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="updateProfile" class="p-6 space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-stone-700 mb-2">الاسم الكامل</label>
                        <input id="name" type="text" v-model="profileForm.name" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" :class="{ 'border-red-500': profileForm.errors.name }" />
                        <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-700 mb-2">البريد الإلكتروني</label>
                        <input id="email" type="email" v-model="profileForm.email" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left" :class="{ 'border-red-500': profileForm.errors.email }" />
                        <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">{{ profileForm.errors.email }}</p>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800 font-medium">
                        ⚠️ عنوان البريد الإلكتروني غير مؤكّد. يرجى التحقق من بريدك الإلكتروني.
                    </div>

                    <div v-if="profileForm.recentlySuccessful" class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-sm text-emerald-700 font-bold">
                        ✅ تم حفظ التغييرات بنجاح
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="profileForm.processing" class="px-6 py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl transition-all shadow-sm disabled:opacity-50">
                            {{ profileForm.processing ? 'جارٍ الحفظ...' : 'حفظ التعديلات' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-stone-100">
                    <h3 class="text-lg font-bold text-stone-800">🔐 تغيير كلمة المرور</h3>
                    <p class="text-sm text-stone-400 mt-0.5">تأكد من استخدام كلمة مرور قوية ومعقدة</p>
                </div>
                <form @submit.prevent="updatePassword" class="p-6 space-y-5">
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-stone-700 mb-2">كلمة المرور الحالية</label>
                        <input id="current_password" type="password" v-model="passwordForm.current_password" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left" :class="{ 'border-red-500': passwordForm.errors.current_password }" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-stone-700 mb-2">كلمة المرور الجديدة</label>
                            <input id="password" type="password" v-model="passwordForm.password" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left" :class="{ 'border-red-500': passwordForm.errors.password }" />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-stone-700 mb-2">تأكيد كلمة المرور</label>
                            <input id="password_confirmation" type="password" v-model="passwordForm.password_confirmation" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-left" />
                        </div>
                    </div>

                    <div v-if="passwordForm.recentlySuccessful" class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-sm text-emerald-700 font-bold">
                        ✅ تم تغيير كلمة المرور بنجاح
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="passwordForm.processing" class="px-6 py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl transition-all shadow-sm disabled:opacity-50">
                            {{ passwordForm.processing ? 'جارٍ التغيير...' : 'تغيير كلمة المرور' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-2xl border border-red-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-red-50">
                    <h3 class="text-lg font-bold text-red-600 flex items-center gap-2">⚠️ منطقة الخطر</h3>
                    <p class="text-sm text-stone-400 mt-0.5">حذف الحساب نهائياً — هذا الإجراء لا يمكن التراجع عنه</p>
                </div>
                <div class="p-6">
                    <p class="text-sm text-stone-600 mb-4">
                        بمجرد حذف حسابك، سيتم حذف جميع بياناتك بشكل دائم ولا يمكن استعادتها.
                    </p>
                    <button @click="showDeleteModal = true" class="px-5 py-3 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-bold rounded-xl border border-red-200 transition-colors">
                        حذف الحساب نهائياً
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showDeleteModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف الحساب</h3>
                        <p class="text-sm text-stone-500 text-center mb-4">أدخل كلمة المرور لتأكيد الحذف النهائي</p>
                        <form @submit.prevent="deleteAccount">
                            <input type="password" v-model="deleteForm.password" placeholder="كلمة المرور" required dir="ltr" class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left mb-4" />
                            <p v-if="deleteForm.errors.password" class="mb-3 text-sm text-red-600">{{ deleteForm.errors.password }}</p>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="showDeleteModal = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors">إلغاء</button>
                                <button type="submit" :disabled="deleteForm.processing" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors">نعم، احذف الحساب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
