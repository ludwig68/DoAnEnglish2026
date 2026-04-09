<template>
  <div class="flex-1 flex flex-col font-body">
    <div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full max-w-4xl mx-auto px-10 py-16 space-y-20">

        <!-- Header / Search -->
        <div class="text-center">
          <p class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-600 mb-4">Help Center</p>
          <h1 class="text-4xl md:text-5xl font-headline font-black text-slate-800 tracking-tight leading-tight mb-8">
            How can we help you today?
          </h1>
          
          <div class="max-w-2xl mx-auto relative group flex items-center bg-white border border-slate-200 rounded-full p-1.5 focus-within:ring-2 focus-within:ring-emerald-400/20 shadow-sm transition-all">
            <i class="fa-solid fa-magnifying-glass absolute left-6 text-slate-400 text-sm"></i>
            <input 
              type="text" 
              class="w-full pl-14 pr-4 py-3 bg-transparent text-[14px] text-slate-800 outline-none font-medium placeholder:text-slate-400"
              placeholder="Search for articles, guides, and more..."
            />
            <button class="text-white text-[13px] font-bold px-8 py-3 rounded-full transition-transform hover:-translate-y-0.5 shadow-md shadow-emerald-200 shrink-0" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
              Search
            </button>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-3 mt-5 text-[11px] font-bold">
            <span class="text-slate-400">Popular:</span>
            <a href="#" class="text-emerald-700 hover:text-emerald-500 transition-colors">Billing Issues</a>
            <a href="#" class="text-emerald-700 hover:text-emerald-500 transition-colors">Zoom Links</a>
            <a href="#" class="text-emerald-700 hover:text-emerald-500 transition-colors">Password Reset</a>
          </div>
        </div>

        <!-- Category Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Card 1 -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-start group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 shrink-0">
              <i class="fa-solid fa-book-open"></i>
            </div>
            <h3 class="font-headline font-black text-[15px] leading-tight text-slate-800 mb-2">Course Access</h3>
            <p class="text-[11px] text-slate-500 font-medium leading-relaxed flex-1 mb-5">
              Learn how to access your curriculum, syllabus, and study materials across all devices.
            </p>
            <a href="#" class="text-emerald-600 text-[10px] font-black tracking-wide uppercase group-hover:text-emerald-500 flex items-center gap-1.5 transition-colors mt-auto">
              Explore Guides <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
          </div>

          <!-- Card 2 -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-start group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 shrink-0">
              <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <h3 class="font-headline font-black text-[15px] leading-tight text-slate-800 mb-2">Payments & Billing</h3>
            <p class="text-[11px] text-slate-500 font-medium leading-relaxed flex-1 mb-5">
              Manage subscriptions, invoices, and payment methods. View our refund policy.
            </p>
            <a href="#" class="text-emerald-600 text-[10px] font-black tracking-wide uppercase group-hover:text-emerald-500 flex items-center gap-1.5 transition-colors mt-auto">
              View Billing <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
          </div>

          <!-- Card 3 -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-start group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 shrink-0">
              <i class="fa-solid fa-gear"></i>
            </div>
            <h3 class="font-headline font-black text-[15px] leading-tight text-slate-800 mb-2">Technical Support</h3>
            <p class="text-[11px] text-slate-500 font-medium leading-relaxed flex-1 mb-5">
              Troubleshoot video playback, audio settings, and browser compatibility for live sessions.
            </p>
            <a href="#" class="text-emerald-600 text-[10px] font-black tracking-wide uppercase group-hover:text-emerald-500 flex items-center gap-1.5 transition-colors mt-auto">
              Get Help <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
          </div>

          <!-- Card 4 -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all flex flex-col items-start group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 shrink-0">
              <i class="fa-solid fa-users"></i>
            </div>
            <h3 class="font-headline font-black text-[15px] leading-tight text-slate-800 mb-2">Community</h3>
            <p class="text-[11px] text-slate-500 font-medium leading-relaxed flex-1 mb-5">
              Connect with fellow students, find study groups, and participate in discussion forums.
            </p>
            <a href="#" class="text-emerald-600 text-[10px] font-black tracking-wide uppercase group-hover:text-emerald-500 flex items-center gap-1.5 transition-colors mt-auto">
              Join Discussion <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
          </div>
        </div>

        <!-- Split Support Block -->
        <div class="rounded-[2.5rem] overflow-hidden flex flex-col md:flex-row border border-slate-100">
          <div class="md:w-1/2 p-12 bg-slate-50 flex flex-col justify-center">
            <h2 class="text-2xl lg:text-3xl font-headline font-black text-slate-800 leading-tight tracking-tight mb-4">
              Can't find what you're looking for?
            </h2>
            <p class="text-[13px] text-slate-500 font-medium leading-relaxed mb-8">
              Our dedicated academic support team is available 24/7 to ensure your learning journey remains uninterrupted.
            </p>
            <div class="flex items-center gap-4">
              <div class="flex -space-x-3">
                <img src="https://ui-avatars.com/api/?name=Team+A&background=111&color=fff" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm">
                <img src="https://ui-avatars.com/api/?name=Team+B&background=16a34a&color=fff" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm">
                <img src="https://ui-avatars.com/api/?name=Team+C&background=0284c7&color=fff" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm">
              </div>
              <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Support team is online</span>
            </div>
          </div>
          <div class="md:w-1/2 p-12 bg-emerald-50/50 flex flex-col justify-center space-y-4">
            
            <a href="#" class="bg-white rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow transition-shadow group">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                  <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                  <h4 class="text-[13px] font-black text-slate-800">Live Chat</h4>
                  <p class="text-[10px] text-slate-400 font-medium mt-0.5">Wait time: <span class="text-emerald-600 font-bold">< 2 mins</span></p>
                </div>
              </div>
              <i class="fa-solid fa-chevron-right text-slate-300 text-[10px] group-hover:text-emerald-500 transition-colors"></i>
            </a>

            <a href="#" class="bg-white rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow transition-shadow group">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                  <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                  <h4 class="text-[13px] font-black text-slate-800">Email Support</h4>
                  <p class="text-[10px] text-slate-400 font-medium mt-0.5">Avg. response: <span class="text-emerald-600 font-bold">4 hours</span></p>
                </div>
              </div>
              <i class="fa-solid fa-chevron-right text-slate-300 text-[10px] group-hover:text-emerald-500 transition-colors"></i>
            </a>

            <a href="#" class="bg-white rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow transition-shadow group">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                  <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                  <h4 class="text-[13px] font-black text-slate-800">Phone Support</h4>
                  <p class="text-[10px] text-slate-400 font-medium mt-0.5">Mon-Fri: <span class="text-emerald-600 font-bold">08:00 - 20:00</span></p>
                </div>
              </div>
              <i class="fa-solid fa-chevron-right text-slate-300 text-[10px] group-hover:text-emerald-500 transition-colors"></i>
            </a>
            
          </div>
        </div>

        <!-- Form Component from ContactView -->
        <div class="bg-slate-50/50 rounded-[2.5rem] border border-slate-100 p-10 max-w-2xl mx-auto">
          <h2 class="text-2xl font-headline font-black text-slate-900 mb-1">Gửi tin nhắn đến chúng tôi</h2>
          <p class="text-sm text-slate-500 mb-8">Điền thông tin phía dưới, chúng tôi sẽ phản hồi sớm nhất.</p>

          <div v-if="message" :class="messageClass" class="mb-6 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
            <i :class="messageType === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
            {{ message }}
          </div>

          <form class="space-y-4" @submit.prevent="submitForm">
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Họ và tên</label>
                <input v-model="form.full_name" type="text" placeholder="Nguyễn Văn A"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-[13px] text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Email</label>
                <input v-model="form.email" type="email" placeholder="email@vi-du.com"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-[13px] text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Nội dung</label>
              <textarea v-model="form.message" rows="4" placeholder="Nhập câu hỏi hoặc phản hồi của bạn..."
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-[13px] text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 resize-none"></textarea>
            </div>

            <button :disabled="isSubmitting" type="submit"
              class="w-full py-4 rounded-full text-[13px] font-bold text-white transition-all hover:shadow-lg disabled:opacity-60 flex items-center justify-center gap-2 mt-4 hover:-translate-y-0.5"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%);">
              <i v-if="isSubmitting" class="fa-solid fa-spinner animate-spin"></i>
              {{ isSubmitting ? 'Đang gửi...' : 'Gửi liên hệ' }}
              <i v-if="!isSubmitting" class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
            </button>
          </form>
        </div>

        <!-- FAQs -->
        <div class="max-w-3xl mx-auto pt-8">
          <div class="flex items-end justify-between mb-8 border-b border-slate-100 pb-4">
            <div>
              <h2 class="text-2xl font-headline font-black text-slate-900">Frequently Asked Questions</h2>
              <p class="text-[12px] text-slate-500 mt-1 font-medium">Quick answers to common student inquiries.</p>
            </div>
            <a href="#" class="text-[11px] font-black text-emerald-600 hover:text-emerald-700 underline underline-offset-4 decoration-emerald-200 mb-1 pb-1">
              View All FAQs
            </a>
          </div>

          <div class="space-y-1">
            <div v-for="(faq, idx) in faqs" :key="idx" class="group flex items-center justify-between py-4 border-b border-slate-100 cursor-pointer">
              <h4 class="text-[13px] font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">{{ faq }}</h4>
              <i class="fa-solid fa-plus text-slate-300 text-xs group-hover:text-emerald-500 transition-colors"></i>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'
import { getCurrentUser } from '../../utils/auth'

const currentUser = getCurrentUser()

const form = ref({
  full_name: currentUser?.full_name ?? '',
  email: currentUser?.email ?? '',
  message: '',
})

const isSubmitting = ref(false)
const message = ref('')
const messageType = ref('success')

const messageClass = computed(() =>
  messageType.value === 'error'
    ? 'border border-red-200 bg-red-50 text-red-700'
    : 'border border-emerald-200 bg-emerald-50 text-emerald-700'
)

const submitForm = async () => {
  message.value = ''
  isSubmitting.value = true
  try {
    const response = await apiFetch('public/contact.php', {
      method: 'POST',
      body: JSON.stringify(form.value),
    })
    const result = await response.json()
    messageType.value = result.status === 'success' ? 'success' : 'error'
    message.value = result.message || 'Không thể gửi liên hệ.'
    if (result.status === 'success') form.value.message = ''
  } catch {
    messageType.value = 'error'
    message.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isSubmitting.value = false
  }
}

const faqs = [
  'How do I change my course schedule?',
  'Where can I find my graded assignments?',
  'Can I pause my subscription for a month?',
  'How do I download offline materials?'
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
