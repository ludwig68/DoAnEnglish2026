<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4">
      <section class="grid gap-8 lg:grid-cols-[0.95fr_1fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
          <h1 class="text-3xl font-black text-slate-900">Liên hệ với trung tâm</h1>
          <p class="mt-3 text-sm leading-relaxed text-slate-500">
            Gửi phản hồi, câu hỏi hoặc nhu cầu hỗ trợ của bạn. Trung tâm sẽ tiếp nhận và phản hồi sớm nhất có thể.
          </p>

          <div class="mt-8 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
              <p class="font-bold text-slate-900">Email</p>
              <p class="mt-1 text-sm text-slate-500">zayluon@gmail.com</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
              <p class="font-bold text-slate-900">Số điện thoại</p>
              <p class="mt-1 text-sm text-slate-500">+84 364 132 169</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
              <p class="font-bold text-slate-900">Địa chỉ</p>
              <p class="mt-1 text-sm text-slate-500">Hồ Chí Minh, Việt Nam</p>
            </div>
          </div>
        </div>

        <div class="rounded-[2rem] bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 text-white shadow-xl shadow-slate-900/10">
          <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-[#7AE582]">
            <i class="fa-regular fa-paper-plane"></i> Liên hệ
          </span>
          <h2 class="mt-6 text-3xl font-black">Gửi phản hồi hoặc câu hỏi của bạn</h2>
          <p class="mt-3 text-sm leading-relaxed text-slate-300">
            Bạn cần hỗ trợ tài khoản, thông tin khóa học hoặc muốn góp ý để trung tâm cải thiện dịch vụ? Hãy để lại lời nhắn tại đây.
          </p>

          <div v-if="message" :class="messageClass" class="mt-5 rounded-2xl px-4 py-3 text-sm font-medium">
            {{ message }}
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitForm">
            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-white">Họ và tên</span>
              <input v-model="form.full_name" type="text" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-400 focus:border-[#7AE582] focus:bg-white/10">
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-white">Email</span>
              <input v-model="form.email" type="email" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-400 focus:border-[#7AE582] focus:bg-white/10">
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-white">Nội dung</span>
              <textarea v-model="form.message" rows="6" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-400 focus:border-[#7AE582] focus:bg-white/10" placeholder="Nhập câu hỏi hoặc phản hồi của bạn..."></textarea>
            </label>

            <button :disabled="isSubmitting" type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-[#7AE582] px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-emerald-300 disabled:opacity-60">
              <span v-if="isSubmitting">Đang gửi...</span>
              <span v-else>Gửi liên hệ</span>
            </button>
          </form>
        </div>
      </section>
    </div>
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

const messageClass = computed(() => (
  messageType.value === 'error'
    ? 'border border-red-200 bg-red-50 text-red-700'
    : 'border border-emerald-200 bg-emerald-50 text-emerald-700'
))

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

    if (result.status === 'success') {
      form.value.message = ''
    }
  } catch (error) {
    messageType.value = 'error'
    message.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
