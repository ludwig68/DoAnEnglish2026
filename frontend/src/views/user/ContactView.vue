<template>
  <div class="bg-white min-h-screen">

    <!-- Hero banner -->
    <section class="relative overflow-hidden py-16" style="background: linear-gradient(160deg, #f0fdf4 0%, #ffffff 60%, #f0fdf4 100%)">
      <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle, #16a34a 1px, transparent 1px); background-size: 24px 24px;"></div>
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-100/40 rounded-full blur-3xl pointer-events-none"></div>
      <div class="relative max-w-5xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
        <div>
          <div class="inline-flex items-center gap-2 bg-white border border-emerald-200 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full mb-4">
            <i class="fa-solid fa-envelope text-[0.65rem]"></i> Liên hệ
          </div>
          <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-3">Chúng tôi luôn<br><span class="text-emerald-600">sẵn sàng hỗ trợ</span></h1>
          <p class="text-slate-500 text-[15px] max-w-md leading-relaxed">Gửi câu hỏi, ý kiến phản hồi hoặc yêu cầu hỗ trợ — chúng tôi sẽ trả lời trong vòng 24 giờ.</p>
        </div>
        <div class="hidden md:flex flex-col gap-3 shrink-0">
          <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
              <i class="fa-solid fa-envelope text-sm"></i>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 font-medium">Email</p>
              <p class="text-sm font-bold text-slate-800">zayluon@gmail.com</p>
            </div>
          </div>
          <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
              <i class="fa-solid fa-phone text-sm"></i>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 font-medium">Hotline</p>
              <p class="text-sm font-bold text-slate-800">+84 364 132 169</p>
            </div>
          </div>
          <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500">
              <i class="fa-solid fa-location-dot text-sm"></i>
            </div>
            <div>
              <p class="text-[11px] text-slate-400 font-medium">Địa chỉ</p>
              <p class="text-sm font-bold text-slate-800">Hồ Chí Minh, Việt Nam</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Form + Map section -->
    <section class="py-12 bg-white">
      <div class="max-w-5xl mx-auto px-6">
        <div class="grid lg:grid-cols-[1.4fr_1fr] gap-8">

          <!-- Contact form -->
          <div class="bg-slate-50 rounded-3xl border border-slate-100 p-8">
            <h2 class="text-xl font-black text-slate-900 mb-1">Gửi tin nhắn đến chúng tôi</h2>
            <p class="text-sm text-slate-500 mb-7">Điền thông tin phía dưới, chúng tôi sẽ phản hồi sớm nhất.</p>

            <div v-if="message" :class="messageClass" class="mb-6 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
              <i :class="messageType === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
              {{ message }}
            </div>

            <form class="space-y-4" @submit.prevent="submitForm">
              <div class="grid sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-600 mb-1.5">Họ và tên</label>
                  <input v-model="form.full_name" type="text" placeholder="Nguyễn Văn A"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-600 mb-1.5">Email</label>
                  <input v-model="form.email" type="email" placeholder="email@vi-du.com"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Nội dung</label>
                <textarea v-model="form.message" rows="6" placeholder="Nhập câu hỏi hoặc phản hồi của bạn..."
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 resize-none"></textarea>
              </div>

              <button :disabled="isSubmitting" type="submit"
                class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-opacity disabled:opacity-60 flex items-center justify-center gap-2"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
                <i v-if="isSubmitting" class="fa-solid fa-spinner animate-spin"></i>
                {{ isSubmitting ? 'Đang gửi...' : 'Gửi liên hệ' }}
                <i v-if="!isSubmitting" class="fa-solid fa-paper-plane text-xs"></i>
              </button>
            </form>
          </div>

          <!-- Map + socials -->
          <div class="flex flex-col gap-5">
            <!-- Map -->
            <div class="flex-1 rounded-3xl overflow-hidden border border-slate-200 min-h-[240px]">
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9544104258935!2d106.67525717589443!3d10.737997189408455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f62a90e5dbd%3A0x674d5126513db295!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgU8OgaSBHw7Ru!5e0!3m2!1svi!2s!4v1774721804211!5m2!1svi!2s"
                width="100%" height="100%" style="border:0; display:block;" allowfullscreen="" loading="lazy"
              ></iframe>
            </div>

            <!-- Social links -->
            <div class="bg-slate-50 rounded-3xl border border-slate-100 p-6">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Mạng xã hội</p>
              <div class="flex gap-3">
                <a href="#" class="flex-1 flex flex-col items-center gap-2 py-4 rounded-2xl bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50 transition-all group">
                  <i class="fa-brands fa-facebook-f text-lg"></i>
                  <span class="text-[10px] font-bold">Facebook</span>
                </a>
                <a href="#" class="flex-1 flex flex-col items-center gap-2 py-4 rounded-2xl bg-white border border-slate-200 text-slate-500 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all group">
                  <i class="fa-brands fa-youtube text-lg"></i>
                  <span class="text-[10px] font-bold">YouTube</span>
                </a>
                <a href="#" class="flex-1 flex flex-col items-center gap-2 py-4 rounded-2xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300 hover:bg-slate-100 transition-all group">
                  <i class="fa-brands fa-tiktok text-lg"></i>
                  <span class="text-[10px] font-bold">TikTok</span>
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
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
</script>
