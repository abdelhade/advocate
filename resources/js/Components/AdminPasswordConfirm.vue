<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'تأكيد بكلمة مرور المدير' },
    description: { type: String, default: 'أدخل كلمة مرور المدير لتأكيد هذا التغيير.' },
    confirmLabel: { type: String, default: 'تأكيد الإجراء' },
    error: { type: String, default: '' },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'confirm']);

const password = ref('');

const submit = () => {
    if (!password.value) return;
    emit('confirm', password.value);
};

const close = () => {
    password.value = '';
    emit('close');
};

defineExpose({ clear: () => { password.value = ''; } });
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/45 backdrop-blur-sm"
                @click.self="close"
            >
                <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-right dir-rtl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-lg font-black text-stone-900 mb-1">{{ title }}</h3>
                    <p class="text-xs text-stone-500 font-semibold mb-4">{{ description }}</p>

                    <label class="block text-xs font-bold text-stone-700 mb-1.5">كلمة مرور المدير *</label>
                    <input
                        v-model="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-900 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium mb-2"
                        @keyup.enter="submit"
                    />
                    <p v-if="error" class="text-xs font-bold text-rose-600 mb-3">{{ error }}</p>

                    <div class="flex items-center gap-3 mt-4">
                        <button
                            type="button"
                            class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50"
                            @click="close"
                        >
                            إلغاء
                        </button>
                        <button
                            type="button"
                            class="flex-1 px-4 py-3 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-bold disabled:opacity-50"
                            :disabled="processing || !password"
                            @click="submit"
                        >
                            {{ processing ? 'جاري التأكيد...' : confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
