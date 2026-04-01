<template>
  <div class="bg-white min-h-screen">

    <!-- Hero banner -->
    <section class="relative overflow-hidden py-16" style="background: linear-gradient(160deg, #f0fdf4 0%, #ffffff 60%, #f0fdf4 100%)">
      <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle, #16a34a 1px, transparent 1px); background-size: 24px 24px;"></div>
      <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-emerald-100/40 rounded-full blur-3xl pointer-events-none"></div>
      <div class="relative max-w-5xl mx-auto px-6">
        <div class="inline-flex items-center gap-2 bg-white border border-emerald-200 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full mb-4">
          <i class="fa-solid fa-headset text-[0.65rem]"></i> Hỗ trợ tư vấn
        </div>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-4">
          Tìm lộ trình học<br><span class="text-emerald-600">phù hợp với bạn</span>
        </h1>
        <p class="text-slate-500 text-[15px] max-w-lg leading-relaxed mb-8">
          Đăng ký nhận tư vấn miễn phí từ đội ngũ giáo viên. Chúng tôi sẽ giúp bạn chọn đúng lộ trình theo mục tiêu.
        </p>

        <!-- Stats row -->
        <div class="flex flex-wrap items-center gap-6">
          <div v-for="s in stats" :key="s.label" class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-4 py-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm" :class="s.bg">
              <i :class="['fa-solid', s.icon, s.color]"></i>
            </div>
            <div>
              <p class="text-base font-black text-slate-900 leading-none">{{ s.value }}</p>
              <p class="text-[10px] text-slate-400 font-medium">{{ s.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="py-12 bg-white">
      <div class="max-w-5xl mx-auto px-6">
        <div class="grid lg:grid-cols-[1fr_1.4fr] gap-8">

          <!-- Left: benfits -->
          <div class="space-y-4">
            <h2 class="text-lg font-black text-slate-800 mb-5">Khi đăng ký tư vấn bạn sẽ nhận được:</h2>

            <div v-for="b in benefits" :key="b.title"
              class="flex items-start gap-4 p-5 rounded-2xl border border-slate-100 bg-slate-50 hover:border-emerald-200 hover:bg-emerald-50/40 transition-all duration-200">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-sm" :class="b.iconBg">
                <i :class="['fa-solid', b.icon, b.iconColor]"></i>
              </div>
              <div>
                <p class="font-bold text-slate-800 text-sm">{{ b.title }}</p>
                <p class="text-slate-500 text-xs mt-0.5 leading-relaxed">{{ b.desc }}</p>
              </div>
            </div>

            <!-- Contact info -->
            <div class="mt-2 pt-6 border-t border-slate-100 space-y-3">
              <div class="flex items-center gap-3 text-sm text-slate-500">
                <i class="fa-solid fa-phone text-emerald-500 w-4 text-center"></i>
                +84 364 132 169
              </div>
              <div class="flex items-center gap-3 text-sm text-slate-500">
                <i class="fa-solid fa-envelope text-emerald-500 w-4 text-center"></i>
                zayluon@gmail.com
              </div>
              <div class="flex items-center gap-3 text-sm text-slate-500">
                <i class="fa-solid fa-clock text-emerald-500 w-4 text-center"></i>
                Thứ 2 – Thứ 7, 8:00 – 20:00
              </div>
            </div>
          </div>

          <!-- Right: form -->
          <div class="bg-slate-50 rounded-3xl border border-slate-100 p-8">
            <h2 class="text-xl font-black text-slate-900 mb-1">Đăng ký tư vấn miễn phí</h2>
            <p class="text-sm text-slate-500 mb-6">Chúng tôi sẽ liên hệ trong vòng 24 giờ làm việc.</p>

            <div v-if="message" :class="messageClass" class="mb-5 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
              <i :class="messageType === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
              {{ message }}
            </div>

            <form class="space-y-4" @submit.prevent="submitForm">
              <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Họ và tên <span class="text-red-400">*</span></label>
                <input v-model="form.full_name" type="text" placeholder="Nguyễn Văn A" required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
              </div>

              <div class="grid sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-600 mb-1.5">Số điện thoại</label>
                  <input v-model="form.phone" type="tel" placeholder="0912 345 678"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-600 mb-1.5">Email</label>
                  <input v-model="form.email" type="email" placeholder="email@vi-du.com"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Mục tiêu học tập</label>
                <div class="flex flex-wrap gap-2 mb-3">
                  <button v-for="g in goals" :key="g" type="button"
                    @click="form.note = g"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                    :class="form.note === g
                      ? 'bg-emerald-500 border-emerald-500 text-white'
                      : 'bg-white border-slate-200 text-slate-500 hover:border-emerald-300 hover:text-emerald-600'"
                  >{{ g }}</button>
                </div>
                <textarea v-model="form.note" rows="3" placeholder="Hoặc mô tả chi tiết nhu cầu của bạn..."
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 resize-none"></textarea>
              </div>

              <button :disabled="isSubmitting" type="submit"
                class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-opacity disabled:opacity-60 flex items-center justify-center gap-2"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
                <i v-if="isSubmitting" class="fa-solid fa-spinner animate-spin"></i>
                {{ isSubmitting ? 'Đang gửi...' : 'Đăng ký tư vấn miễn phí' }}
                <i v-if="!isSubmitting" class="fa-solid fa-arrow-right text-xs"></i>
              </button>

              <p class="text-center text-xs text-slate-400">
                <i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i>
                Thông tin của bạn được bảo mật tuyệt đối.
              </p>
            </form>
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

const stats = [
  { icon: 'fa-users', value: '500+', label: 'Học viên', bg: 'bg-emerald-50', color: 'text-emerald-600' },
  { icon: 'fa-star', value: '4.9★', label: 'Đánh giá', bg: 'bg-amber-50', color: 'text-amber-500' },
  { icon: 'fa-book-open', value: '20+', label: 'Khóa học', bg: 'bg-blue-50', color: 'text-blue-500' },
]

const benefits = [
  { icon: 'fa-route', title: 'Lộ trình cá nhân hoá', desc: 'Nhận đề xuất khóa học dựa trên trình độ và mục tiêu cụ thể của bạn.', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { icon: 'fa-comments', title: 'Tư vấn 1-1 miễn phí', desc: 'Trực tiếp trao đổi với giáo viên có kinh nghiệm để giải đáp mọi thắc mắc.', iconBg: 'bg-blue-50', iconColor: 'text-blue-500' },
  { icon: 'fa-gift', title: 'Ưu đãi học phí độc quyền', desc: 'Nhận mức giá ưu đãi đặc biệt dành riêng cho học viên đăng ký qua tư vấn.', iconBg: 'bg-amber-50', iconColor: 'text-amber-500' },
]

const goals = ['Giao tiếp cơ bản', 'Luyện thi IELTS', 'Luyện thi TOEIC', 'Tiếng Anh công việc']

const form = ref({
  full_name: currentUser?.full_name ?? '',
  phone: currentUser?.phone ?? '',
  email: currentUser?.email ?? '',
  note: '',
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
    const response = await apiFetch('public/consultation.php', {
      method: 'POST',
      body: JSON.stringify(form.value),
    })
    const result = await response.json()
    messageType.value = result.status === 'success' ? 'success' : 'error'
    message.value = result.message || 'Không thể gửi đăng ký tư vấn.'
    if (result.status === 'success') form.value.note = ''
  } catch {
    messageType.value = 'error'
    message.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
