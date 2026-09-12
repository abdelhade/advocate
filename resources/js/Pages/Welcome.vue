<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const isMenuOpen = ref(false);

const features = [
    {
        title: 'بوابة الموكلين',
        description: 'بوابة معزولة بالكامل تتيح للموكلين متابعة القضايا والمستندات الخاصة بهم ذاتياً بكل سهولة وأمان.',
        icon: 'M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4'
    },
    {
        title: 'إدارة القضايا الشاملة',
        description: 'تنظيم كافة القضايا وحالاتها، مواعيد الجلسات، والمستندات المرتبطة بشكل مركزي وفعّال لمكتب المحاماة.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
    },
    {
        title: 'متابعة المستحقات المالية',
        description: 'تتبع دقيق للمدفوعات، والمبالغ المتبقية، وكشوفات الحساب لكل موكل بدقة متناهية.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'أرشفة المراسلات (الوارد والصادر)',
        description: 'أرشفة إلكترونية للخطابات، وربطها برقم القيد والجهة والقضية المعنية مع حفظ آمن للوثائق.',
        icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'
    }
];
</script>

<template>
    <Head title="الرئيسية | نظام جلسات" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500 selection:text-white">
        <!-- Navbar -->
        <nav class="absolute w-full z-50 transition-all duration-300 backdrop-blur-md bg-white/70 border-b border-slate-200/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-blue-600">جلسات</span>
                    </div>

                    <div class="hidden md:flex items-center space-x-4 space-x-reverse" v-if="canLogin">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-slate-600 hover:text-indigo-600 font-medium transition-colors">لوحة التحكم</Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-slate-600 hover:text-indigo-600 font-medium transition-colors px-4 py-2 rounded-lg hover:bg-slate-100/50">تسجيل الدخول</Link>
                            <Link v-if="canRegister" :href="route('register')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/30 transition-all hover:-translate-y-0.5">حساب جديد</Link>
                        </template>
                    </div>

                    <div class="md:hidden flex items-center">
                        <button @click="isMenuOpen = !isMenuOpen" class="text-slate-500 hover:text-indigo-600 focus:outline-none p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div v-show="isMenuOpen" class="md:hidden bg-white border-b border-slate-100 shadow-xl absolute w-full">
                <div class="px-4 pt-2 pb-6 space-y-2" v-if="canLogin">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="block w-full text-center bg-indigo-50 text-indigo-700 font-medium px-4 py-3 rounded-xl">لوحة التحكم</Link>
                    <template v-else>
                        <Link :href="route('login')" class="block w-full text-center text-slate-600 hover:bg-slate-50 font-medium px-4 py-3 rounded-xl transition-colors">تسجيل الدخول</Link>
                        <Link v-if="canRegister" :href="route('register')" class="block w-full text-center bg-indigo-600 text-white font-medium px-4 py-3 rounded-xl shadow-md">حساب جديد</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
            <!-- Background Ornaments -->
            <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3">
                <div class="w-[600px] h-[600px] rounded-full bg-indigo-200/40 blur-3xl opacity-50 mix-blend-multiply"></div>
            </div>
            <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
                <div class="w-[500px] h-[500px] rounded-full bg-blue-200/40 blur-3xl opacity-50 mix-blend-multiply"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-sm font-medium mb-8">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-600"></span>
                    نظام متكامل لإدارة مكاتب المحاماة
                </div>
                
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-black text-slate-900 tracking-tight leading-[1.1] mb-8">
                    مستقبل <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">إدارة القضايا</span><br> يبدأ من هنا
                </h1>
                
                <p class="mt-6 text-xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                    نظام سحابي متطور يقدم حلاً شاملاً للمحامين والمستشارين القانونيين، مع بوابة مخصصة للموكلين تتيح لهم متابعة قضاياهم ومستحقاتهم المالية بكل شفافية وأمان.
                </p>
                
                <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
                    <Link v-if="canRegister" :href="route('register')" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1 text-lg flex items-center justify-center gap-2">
                        ابدأ تجربتك المجانية
                        <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </Link>
                    <a href="#features" class="px-8 py-4 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl shadow-sm border border-slate-200 transition-all hover:-translate-y-1 text-lg flex items-center justify-center gap-2">
                        اكتشف المميزات
                    </a>
                </div>

                <!-- Dashboard Preview Image -->
                <div class="mt-20 relative max-w-5xl mx-auto">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-50 via-transparent to-transparent z-10 top-1/2"></div>
                    <div class="rounded-2xl border border-slate-200/60 bg-white/50 backdrop-blur-sm p-2 shadow-2xl shadow-indigo-900/5">
                        <div class="rounded-xl overflow-hidden bg-slate-100 aspect-video relative flex items-center justify-center border border-slate-100">
                            <!-- Placeholder for actual dashboard screenshot -->
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200"></div>
                            <div class="relative z-10 text-slate-400 flex flex-col items-center">
                                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                                <span class="text-xl font-medium">واجهة لوحة التحكم التفاعلية</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-indigo-600 font-semibold tracking-wide uppercase mb-3">مميزات النظام</h2>
                    <p class="mt-2 text-3xl leading-8 font-black tracking-tight text-slate-900 sm:text-4xl">
                        كل ما تحتاجه لإدارة مكتبك بنجاح
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-slate-500 mx-auto">
                        صُمم النظام لتلبية احتياجات مكاتب المحاماة بكفاءة، مع التركيز على سهولة الاستخدام، والأمان، وتنظيم البيانات.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 mt-12">
                    <div v-for="(feature, index) in features" :key="index" 
                         class="group relative bg-slate-50 rounded-3xl p-8 hover:bg-white hover:shadow-xl hover:shadow-indigo-100/50 transition-all duration-300 border border-slate-100 hover:border-indigo-100 overflow-hidden">
                        
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                        
                        <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors">{{ feature.title }}</h3>
                        <p class="text-slate-600 text-lg leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-20 overflow-hidden">
            <div class="absolute inset-0 bg-indigo-900"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-indigo-800 to-blue-900 opacity-90"></div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">جاهز للارتقاء بإدارة مكتبك؟</h2>
                <p class="text-indigo-100 text-xl mb-10 opacity-90">
                    انضم إلى مكاتب المحاماة الرائدة التي تعتمد على منصتنا لتنظيم أعمالها وتحسين تجربة موكليها.
                </p>
                <Link v-if="canRegister" :href="route('register')" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold rounded-xl text-indigo-900 bg-white hover:bg-indigo-50 shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl">
                    أنشئ مساحة عملك الآن
                </Link>
            </div>
            
            <!-- Decor -->
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-indigo-500/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-96 h-96 bg-blue-500/30 rounded-full blur-3xl"></div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-50 border-t border-slate-200 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-xl font-bold text-slate-800">جلسات</span>
                </div>
                <p class="text-slate-500 text-sm">
                    &copy; {{ new Date().getFullYear() }} جميع الحقوق محفوظة لبرنامج جلسات لإدارة مكاتب المحاماة.
                </p>
                <div class="flex space-x-4 space-x-reverse">
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors">
                        <span class="sr-only">تويتر</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors">
                        <span class="sr-only">لينكد إن</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
