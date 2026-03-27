<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4">
      <section class="grid gap-8 lg:grid-cols-[1fr_0.95fr]">
        <div class="rounded-[2rem] bg-slate-900 px-8 py-10 text-white shadow-xl shadow-slate-900/10">
          <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-bold text-[#7AE582]">
            <i class="fa-solid fa-headset"></i> Đăng ký tư vấn
          </span>
          <h1 class="mt-6 text-4xl font-black leading-tight">Nhận tư vấn lộ trình học phù hợp với bạn</h1>
          <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-300">
            Gửi thông tin của bạn để trung tâm liên hệ, tư vấn khóa học, lộ trình và cách học phù hợp với mục tiêu hiện tại.
          </p>

          <div class="mt-8 space-y-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
              <p class="font-bold text-white">Tư vấn nhanh</p>
              <p class="mt-2 text-sm text-slate-300">Hỗ trợ chọn khóa học theo mục tiêu giao tiếp, nền tảng hoặc luyện thi.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
              <p class="font-bold text-white">Linh hoạt thời gian</p>
              <p class="mt-2 text-sm text-slate-300">Bạn để lại thông tin, trung tâm sẽ chủ động liên hệ trong thời gian phù hợp.</p>
            </div>
          </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
          <h2 class="text-2xl font-black text-slate-900">Gửi thông tin tư vấn</h2>
          <p class="mt-2 text-sm text-slate-500">Điền thông tin để trung tâm liên hệ lại với bạn.</p>

          <div v-if="message" :class="messageClass" class="mt-5 rounded-2xl px-4 py-3 text-sm font-medium">
            {{ message }}
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitForm">
            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Họ và tên</span>
              <input v-model="form.full_name" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#16a34a] focus:bg-white">
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Số điện thoại</span>
              <input v-model="form.phone" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#16a34a] focus:bg-white">
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Email</span>
              <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#16a34a] focus:bg-white">
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Nhu cầu của bạn</span>
              <textarea v-model="form.note" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#16a34a] focus:bg-white" placeholder="Ví dụ: Tôi muốn học giao tiếp cơ bản trong 3 tháng..."></textarea>
            </label>

            <button :disabled="isSubmitting" type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-[#7AE582] px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-emerald-300 disabled:opacity-60">
              <span v-if="isSubmitting">Đang gửi...</span>
              <span v-else>Gửi đăng ký tư vấn</span>
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
  phone: currentUser?.phone ?? '',
  email: currentUser?.email ?? '',
  note: '',
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
    const response = await apiFetch('public/consultation.php', {
      method: 'POST',
      body: JSON.stringify(form.value),
    })

    const result = await response.json()
    messageType.value = result.status === 'success' ? 'success' : 'error'
    message.value = result.message || 'Không thể gửi đăng ký tư vấn.'

    if (result.status === 'success') {
      form.value.note = ''
    }
  } catch (error) {
    messageType.value = 'error'
    message.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
