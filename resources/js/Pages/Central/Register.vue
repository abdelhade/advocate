<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
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

  <div class="min-h-screen bg-stone-50 text-stone-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans dir-rtl selection:bg-red-600 selection:text-white">
    <!-- Top Brand Accent Header -->
    <div class="fixed top-0 left-0 right-0 h-1.5 bg-red-700"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <Link href="/" class="inline-flex items-center gap-3 text-3xl font-black text-stone-900 tracking-tight">
        <div class="w-12 h-12 rounded-2xl bg-red-700 flex items-center justify-center text-white shadow-lg shadow-red-700/20">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
        </div>
        <span>جلسات</span>
      </Link>
      <h2 class="mt-5 text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">
        تسجيل مكتب محاماة جديد
      </h2>
      <p class="mt-2 text-sm font-semibold text-stone-500">
        ابدأ فترتك التجريبية لمدة <span class="text-red-700 font-extrabold px-2 py-0.5 bg-red-50 rounded-full border border-red-100">15 يومًا مجاناً</span> بدون رسوم
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl px-4 sm:px-0">
      <div class="bg-white py-8 px-6 shadow-xl shadow-stone-200/60 rounded-3xl border border-stone-200/80 sm:px-10">
        
        <!-- Success Alert -->
        <div v-if="successMessage" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold text-center flex items-center justify-center gap-3">
          <svg class="w-5 h-5 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ successMessage }}
        </div>

        <!-- General Error Alert -->
        <div v-if="errors.general" class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-bold text-center">
          {{ errors.general }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <!-- Lawyer Name & Office Name -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">اسم المحامي / المدير *</label>
              <input 
                v-model="form.name"
                type="text"
                required
                placeholder="أ. أحمد المنياوي"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
              <p v-if="errors.name" class="mt-1 text-xs font-bold text-rose-600">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">اسم المكتب *</label>
              <input 
                v-model="form.office_name"
                type="text"
                required
                @blur="autoSlug"
                placeholder="مكتب الفهد للمحاماة"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
              <p v-if="errors.office_name" class="mt-1 text-xs font-bold text-rose-600">{{ errors.office_name[0] }}</p>
            </div>
          </div>

          <!-- Subdomain Slug -->
          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">رابط المكتب الخاط (Subdomain) *</label>
            <div class="relative flex items-center dir-ltr">
              <span class="inline-flex items-center px-3.5 py-3 rounded-l-xl border border-r-0 border-stone-200 bg-stone-100 text-stone-600 text-xs font-mono font-bold">
                .{{ hostName }}
              </span>
              <input 
                v-model="form.subdomain"
                type="text"
                required
                placeholder="alfahd"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-r-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-mono transition text-left"
              />
            </div>
            <p v-if="formattedSubdomain" class="mt-1.5 text-xs font-bold text-red-700 dir-rtl">
              🌐 رابط مكتبك المخصص: <span class="font-mono dir-ltr font-extrabold text-stone-800">http://{{ formattedSubdomain }}</span>
            </p>
            <p v-if="errors.subdomain" class="mt-1 text-xs font-bold text-rose-600">{{ errors.subdomain[0] }}</p>
          </div>

          <!-- Email & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">البريد الإلكتروني *</label>
              <input 
                v-model="form.email"
                type="email"
                required
                placeholder="lawyer@example.com"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
              <p v-if="errors.email" class="mt-1 text-xs font-bold text-rose-600">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">رقم الهاتف *</label>
              <input 
                v-model="form.phone"
                type="text"
                required
                placeholder="01012345678"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
              <p v-if="errors.phone" class="mt-1 text-xs font-bold text-rose-600">{{ errors.phone[0] }}</p>
            </div>
          </div>

          <!-- Password & Confirmation -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">كلمة المرور *</label>
              <input 
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
              <p v-if="errors.password" class="mt-1 text-xs font-bold text-rose-600">{{ errors.password[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 mb-1.5">تأكيد كلمة المرور *</label>
              <input 
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit"
            :disabled="loading"
            class="w-full mt-3 py-4 px-6 bg-red-700 hover:bg-red-800 text-white font-black text-base rounded-xl shadow-lg shadow-red-700/20 hover:shadow-red-700/30 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-3 cursor-pointer"
          >
            <svg v-if="loading" class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ loading ? 'جاري تجهيز المكتب والنطاق الخاط...' : '🚀 إنشاء المكتب وبدء الفترة التجريبية (15 يوماً)' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center border-t border-stone-100 pt-5">
          <p class="text-xs font-semibold text-stone-500">
            لديك حساب أو مكتب بالفعل؟ 
            <Link href="/" class="text-red-700 font-bold hover:underline">العودة للصفحة الرئيسية</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
