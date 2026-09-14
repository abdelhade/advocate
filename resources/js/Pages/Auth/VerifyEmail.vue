<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);

const email = computed(() => page.props.auth?.user?.email);
</script>

<template>
    <GuestLayout>
        <Head title="تأكيد البريد الإلكتروني" />

        <div class="mb-4 text-sm text-stone-600 leading-relaxed font-medium">
            تم إنشاء مكتبك بنجاح. أرسلنا رسالة تأكيد إلى
            <span class="font-bold text-stone-900">{{ email }}</span>.
            اضغط على رابط التأكيد داخل الرسالة لتفعيل حسابك.
        </div>

        <div
            class="mb-4 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2"
            v-if="verificationLinkSent"
        >
            تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    إعادة إرسال رسالة التأكيد
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-stone-600 underline hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    تسجيل الخروج
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
