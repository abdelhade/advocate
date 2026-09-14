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

const availableDomains = ref([
  { label: 'jalsateg.com (النطاق الرئيسي)', value: 'jalsateg.com' },
  { label: 'jalsat.sa (النطاق السعودي)', value: 'jalsat.sa' },
  { label: 'jalsat.app (النطاق السريع)', value: 'jalsat.app' },
])

const isDev = window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost'
const currentHost = isDev ? 'localhost' : window.location.hostname
const selectedDomain = ref(availableDomains.value.some(d => d.value === currentHost) ? currentHost : 'jalsateg.com')

const protocol = window.location.protocol === 'https:' ? 'https:' : 'http:'
const portSuffix = (isDev && window.location.port) ? `:${window.location.port}` : ''

const formattedSubdomain = computed(() => {
  if (!form.value.subdomain) return ''
  const clean = form.value.subdomain.toLowerCase().trim().replace(/[^a-z0-9-]/g, '')
  const domain = isDev ? 'localhost' : selectedDomain.value
  return `${protocol}//${clean}.${domain}${portSuffix}`
})

const autoSlug = () => {
  if (!form.value.subdomain && form.value.office_name) {
    // Convert common office names or fallback to office-slug
    const name = form.value.office_name.trim().toLowerCase()
    let slug = name
      .replace(/[^\u0621-\u064A0-9a-zA-Z\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/مكتب|للمحاماة|والاستشارات|القانونية/g, '')
      .trim()
    
    if (!slug || slug.length < 2) {
      slug = 'office-' + Math.floor(1000 + Math.random() * 9000)
    } else {
      slug = 'office-' + Math.floor(100 + Math.random() * 900)
    }
    form.value.subdomain = slug.replace(/[^a-z0-9-]/g, '')
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

          <!-- Subdomain & Domain Selection -->
          <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-3">
            <div class="flex justify-between items-center">
              <label class="block text-xs font-bold text-stone-800">رابط النطاق الفرعي الخاص بمكتبك (Subdomain) *</label>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">نطاق خاص ومستقل 🔒</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 dir-ltr">
              <!-- Subdomain Slug Input -->
              <div class="sm:col-span-7 relative">
                <input 
                  v-model="form.subdomain"
                  type="text"
                  required
                  placeholder="alfahd"
                  class="w-full px-3.5 py-2.5 bg-white border border-stone-300 rounded-xl text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-mono text-left"
                />
              </div>

              <!-- Domain Selector Dropdown -->
              <div class="sm:col-span-5">
                <select
                  v-model="selectedDomain"
                  class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-xl text-stone-800 text-xs font-mono font-bold focus:outline-none focus:border-red-600"
                >
                  <option v-for="d in availableDomains" :key="d.value" :value="d.value">
                    .{{ d.value }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Live Subdomain URL Preview Badge -->
            <div v-if="formattedSubdomain" class="p-3 bg-white border border-stone-200 rounded-xl flex items-center justify-between gap-2 text-xs dir-rtl">
              <div class="flex items-center gap-2 overflow-hidden">
                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0 animate-pulse"></span>
                <span class="text-stone-500 font-bold flex-shrink-0">رابط مكتبك المستقل:</span>
                <span class="font-mono dir-ltr font-black text-red-700 truncate">{{ formattedSubdomain }}</span>
              </div>
              <span class="text-[10px] font-bold text-stone-400 bg-stone-100 px-2 py-1 rounded-md flex-shrink-0">متاح للمكتب</span>
            </div>

            <p v-if="errors.subdomain" class="text-xs font-bold text-rose-600 dir-rtl">{{ errors.subdomain[0] }}</p>
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

        <div class="mt-6 text-center border-t border-stone-100 pt-5 flex items-center justify-center gap-3 text-xs font-semibold text-stone-500">
          <span>لديك حساب بالفعل؟</span>
          <Link href="/login" class="text-red-700 font-bold hover:underline">تسجيل الدخول</Link>
          <span class="text-stone-300">•</span>
          <Link href="/" class="text-stone-500 hover:text-stone-800 transition">الصفحة الرئيسية</Link>
        </div>
      </div>
    </div>
  </div>
</template>
