<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const FIXED_DOMAIN = 'jalsateg.com'

const form = ref({
  name: '',
  office_name: '',
  subdomain: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const loading = ref(false)
const successMessage = ref('')

const formattedSubdomain = computed(() => {
  if (!form.value.subdomain) return ''
  const clean = form.value.subdomain.toLowerCase().trim().replace(/[^a-z0-9-]/g, '')
  if (!clean) return ''
  return `https://${clean}.${FIXED_DOMAIN}`
})

const submit = async () => {
  errors.value = {}
  loading.value = true
  successMessage.value = ''

  try {
    const response = await axios.post('/register', {
      ...form.value,
      domain: FIXED_DOMAIN,
      subdomain: form.value.subdomain.toLowerCase().trim().replace(/[^a-z0-9-]/g, ''),
    })
    if (response.data.success) {
      successMessage.value = response.data.message
      setTimeout(() => {
        window.location.href = response.data.redirect_url
      }, 1200)
    }
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      errors.value = { general: 'حدث خطأ أثناء الاتصال بالخادم، يرجى المحاولة لاحقاً.' }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="تسجيل مكتب جديد - فترة تجريبية 15 يوم" />

  <div class="min-h-screen bg-stone-50 dark:bg-slate-950 text-stone-900 dark:text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans dir-rtl selection:bg-blue-600 selection:text-white transition-colors">
    <!-- Top Cobalt Blue Brand Accent Header -->
    <div class="fixed top-0 left-0 right-0 h-1.5 bg-blue-700"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <Link href="/" class="inline-flex items-center gap-3 text-3xl font-black text-stone-900 dark:text-white tracking-tight">
        <div class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center text-white shadow-lg shadow-blue-700/20">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
        </div>
        <span>جلسات</span>
      </Link>
      <h2 class="mt-5 text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
        تسجيل مكتب محاماة جديد
      </h2>
      <p class="mt-2 text-sm font-semibold text-stone-500 dark:text-slate-400">
        ابدأ فترتك التجريبية لمدة <span class="text-blue-700 dark:text-blue-300 font-extrabold px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 rounded-full border border-blue-100 dark:border-blue-800">15 يومًا مجاناً</span> بدون رسوم
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl px-4 sm:px-0">
      <div class="bg-white dark:bg-slate-900 py-8 px-6 shadow-xl shadow-stone-200/60 dark:shadow-slate-950/80 rounded-3xl border border-stone-200/80 dark:border-slate-800 sm:px-10">
        
        <!-- Success Alert -->
        <div v-if="successMessage" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl text-emerald-800 dark:text-emerald-300 text-sm font-bold text-center flex items-center justify-center gap-3">
          <svg class="w-5 h-5 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ successMessage }}
        </div>

        <!-- General Error Alert -->
        <div v-if="errors.general" class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl text-rose-700 dark:text-rose-300 text-sm font-bold text-center">
          {{ errors.general }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <!-- Lawyer Name & Office Name -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">اسم المحامي / المدير *</label>
              <input 
                v-model="form.name"
                type="text"
                required
                placeholder="الاسم الثلاثي"
                class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition"
              />
              <p v-if="errors.name" class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">اسم المكتب *</label>
              <input 
                v-model="form.office_name"
                type="text"
                required
                placeholder="مكتب {__________} للمحاماة"
                class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition"
              />
              <p v-if="errors.office_name" class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ errors.office_name[0] }}</p>
            </div>
          </div>

          <!-- Subdomain (fixed domain: jalsateg.com) -->
          <div class="bg-stone-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-stone-200 dark:border-slate-700 space-y-3">
            <div class="flex justify-between items-center">
              <label class="block text-xs font-bold text-stone-800 dark:text-slate-200">رابط مكتبك (Subdomain) *</label>
              <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md">نطاق خاص ومستقل 🔒</span>
            </div>

            <div class="flex items-center gap-2 dir-ltr">
              <input
                v-model="form.subdomain"
                type="text"
                required
                placeholder="اكتب الرابط بنفسك مثل alfahd"
                class="flex-1 px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-mono text-left"
                @input="form.subdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '')"
              />
              <span class="shrink-0 text-sm font-mono font-black text-stone-700 dark:text-slate-300 px-1">.jalsateg.com</span>
            </div>

            <div v-if="formattedSubdomain" class="p-3 bg-white dark:bg-slate-900 border border-stone-200 dark:border-slate-700 rounded-xl flex items-center justify-between gap-2 text-xs dir-rtl">
              <div class="flex items-center gap-2 overflow-hidden">
                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0 animate-pulse"></span>
                <span class="text-stone-500 dark:text-slate-400 font-bold flex-shrink-0">رابط مكتبك:</span>
                <span class="font-mono dir-ltr font-black text-blue-700 dark:text-blue-400 truncate">{{ formattedSubdomain }}</span>
              </div>
            </div>

            <p v-if="errors.subdomain" class="text-xs font-bold text-rose-600 dark:text-rose-400 dir-rtl">{{ errors.subdomain[0] }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">البريد الإلكتروني *</label>
            <input
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="lawyer@example.com"
              class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition"
            />
            <p class="mt-1 text-[11px] font-semibold text-stone-400 dark:text-slate-500">سنرسل رسالة تأكيد إلى هذا البريد بعد التسجيل</p>
            <p v-if="errors.email" class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ errors.email[0] }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">رقم التليفون *</label>
            <div class="flex gap-2 dir-ltr">
              <div class="flex items-center gap-1.5 px-3 py-3 bg-stone-100 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-600 dark:text-slate-300 text-sm font-bold shrink-0">
                <span class="text-base leading-none">🇪🇬</span>
                <span>+20</span>
              </div>
              <input
                v-model="form.phone"
                type="tel"
                required
                inputmode="numeric"
                autocomplete="tel"
                maxlength="11"
                placeholder="01012345678"
                class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition text-left"
                @input="form.phone = form.phone.replace(/[^\d]/g, '').slice(0, 11)"
              />
            </div>
            <p class="mt-1 text-[11px] font-semibold text-stone-400 dark:text-slate-500 dir-rtl">رقم مصري يبدأ بـ 010 أو 011 أو 012 أو 015</p>
            <p v-if="errors.phone" class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400 dir-rtl">{{ errors.phone[0] }}</p>
          </div>

          <!-- Password & Confirmation -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">كلمة المرور *</label>
              <input 
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition"
              />
              <p v-if="errors.password" class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-400">{{ errors.password[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">تأكيد كلمة المرور *</label>
              <input 
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 bg-stone-50 dark:bg-slate-800 border border-stone-200 dark:border-slate-700 rounded-xl text-stone-900 dark:text-white placeholder-stone-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-medium transition"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit"
            :disabled="loading"
            class="w-full mt-3 py-4 px-6 bg-blue-700 hover:bg-blue-800 text-white font-black text-base rounded-xl shadow-lg shadow-blue-700/20 hover:shadow-blue-700/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-3 cursor-pointer"
          >
            <svg v-if="loading" class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ loading ? 'جاري تجهيز المكتب والنطاق الخاص...' : '🚀 إنشاء المكتب وبدء الفترة التجريبية (15 يوماً)' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center border-t border-stone-100 dark:border-slate-800 pt-5 flex items-center justify-center gap-3 text-xs font-semibold text-stone-500 dark:text-slate-400">
          <span>لديك حساب بالفعل؟</span>
          <Link href="/login" class="text-blue-700 dark:text-blue-400 font-bold hover:underline">تسجيل الدخول</Link>
          <span class="text-stone-300 dark:text-slate-700">•</span>
          <Link href="/" class="text-stone-500 dark:text-slate-400 hover:text-stone-800 dark:hover:text-white transition">الصفحة الرئيسية</Link>
        </div>
      </div>
    </div>
  </div>
</template>
