<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue-inertia'
import axios from 'axios'

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

const hostName = window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost' 
  ? 'localhost' 
  : window.location.hostname

const formattedSubdomain = computed(() => {
  if (!form.value.subdomain) return ''
  const clean = form.value.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '')
  return `${clean}.${hostName}:${window.location.port || 8080}`
})

const autoSlug = () => {
  if (!form.value.subdomain && form.value.office_name) {
    // Generate simple slug from office name if empty
    form.value.subdomain = 'office-' + Math.floor(1000 + Math.random() * 9000)
  }
}

const submit = async () => {
  errors.value = {}
  loading.value = true
  successMessage.value = ''

  try {
    const response = await axios.post('/register', form.value)
    if (response.data.success) {
      successMessage.value = response.data.message
      setTimeout(() => {
        window.location.href = response.data.redirect_url
      }, 1500)
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

  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 dir-rtl font-sans selection:bg-amber-500 selection:text-slate-950">
    <!-- Top Decorative Gradient -->
    <div class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <Link href="/" class="inline-flex items-center gap-3 text-3xl font-extrabold text-amber-400">
        <span class="p-2 bg-amber-500/10 rounded-xl border border-amber-500/20 shadow-lg shadow-amber-500/5">⚖️</span>
        أدفوكيت SaaS
      </Link>
      <h2 class="mt-4 text-2xl font-extrabold text-slate-100">
        ابدأ فترتك التجريبية المجانية
      </h2>
      <p class="mt-2 text-sm text-slate-400">
        تجربة كاملة المميزات لمدة <span class="text-amber-400 font-semibold px-2 py-0.5 bg-amber-500/10 rounded-full border border-amber-500/20">15 يومًا مجاناً</span> - بدون بطاقة ائتمان
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl px-4 sm:px-0">
      <div class="bg-slate-900/90 backdrop-blur-md py-8 px-6 shadow-2xl rounded-2xl border border-slate-800 sm:px-10">
        
        <div v-if="successMessage" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm font-semibold text-center flex items-center justify-center gap-2">
          <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ successMessage }}
        </div>

        <div v-if="errors.general" class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm text-center">
          {{ errors.general }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <!-- Lawyer Name & Office Name -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">اسم المحامي / المدير *</label>
              <input 
                v-model="form.name"
                type="text"
                required
                placeholder="أ. أحمد المنياوي"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
              <p v-if="errors.name" class="mt-1 text-xs text-rose-400">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">اسم المكتب *</label>
              <input 
                v-model="form.office_name"
                type="text"
                required
                @blur="autoSlug"
                placeholder="مكتب الفهد للمحاماة"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
              <p v-if="errors.office_name" class="mt-1 text-xs text-rose-400">{{ errors.office_name[0] }}</p>
            </div>
          </div>

          <!-- Subdomain Slug -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">رابط المكتب الخاط (Subdomain) *</label>
            <div class="relative flex items-center dir-ltr">
              <span class="inline-flex items-center px-3 py-2.5 rounded-l-xl border border-r-0 border-slate-800 bg-slate-950 text-slate-400 text-xs font-mono">
                .{{ hostName }}
              </span>
              <input 
                v-model="form.subdomain"
                type="text"
                required
                placeholder="alfahd"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-r-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm font-mono transition text-left"
              />
            </div>
            <p v-if="formattedSubdomain" class="mt-1.5 text-xs text-amber-400/90 font-mono">
              🌐 رابط مكتبك سيكون: http://{{ formattedSubdomain }}
            </p>
            <p v-if="errors.subdomain" class="mt-1 text-xs text-rose-400">{{ errors.subdomain[0] }}</p>
          </div>

          <!-- Email & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">البريد الإلكتروني *</label>
              <input 
                v-model="form.email"
                type="email"
                required
                placeholder="lawyer@example.com"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
              <p v-if="errors.email" class="mt-1 text-xs text-rose-400">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">رقم الهاتف *</label>
              <input 
                v-model="form.phone"
                type="text"
                required
                placeholder="01012345678"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
              <p v-if="errors.phone" class="mt-1 text-xs text-rose-400">{{ errors.phone[0] }}</p>
            </div>
          </div>

          <!-- Password & Password Confirmation -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">كلمة المرور *</label>
              <input 
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
              <p v-if="errors.password" class="mt-1 text-xs text-rose-400">{{ errors.password[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">تأكيد كلمة المرور *</label>
              <input 
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm transition"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit"
            :disabled="loading"
            class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
          >
            <svg v-if="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ loading ? 'جاري تجهيز المكتب والنطاق الخاص بك...' : '🚀 إنشاء المكتب وبدء الفترة التجريبية (15 يوماً)' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center border-t border-slate-800/80 pt-4">
          <p class="text-xs text-slate-400">
            لديك مكتب بالفعل؟ 
            <Link href="/" class="text-amber-400 font-semibold hover:underline">العودة للرئيسية</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
