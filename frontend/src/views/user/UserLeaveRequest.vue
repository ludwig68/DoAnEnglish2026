<template>
  <div class="flex-1 flex flex-col">

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div class="w-12 h-12 border-4 border-slate-50 border-t-emerald-400 rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full px-10 py-14">

        <!-- Page Title -->
        <div class="mb-14">
          <h2 class="text-3xl lg:text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight mb-3">Đơn xin nghỉ học</h2>
          <p class="text-sm text-slate-400 font-medium leading-relaxed max-w-lg">
            Vui lòng hoàn thành thông tin dưới đây để gửi yêu cầu tạm nghỉ. Đơn sẽ được xử lý trong vòng 24 giờ làm việc.
          </p>
        </div>

        <div class="grid grid-cols-12 gap-10">

          <!-- ═══ CỘT TRÁI: Form Đơn Nghỉ (7/12) ═══ -->
          <div class="col-span-12 lg:col-span-7 space-y-10">

            <!-- Form Card -->
            <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-10 relative overflow-hidden">
              <!-- Decorative -->
              <div class="absolute -top-12 -right-12 w-28 h-28 bg-emerald-50 rounded-full opacity-50 pointer-events-none blur-xl"></div>

              <form @submit.prevent="submitLeave" class="relative z-10 space-y-8">

                <!-- Thông báo kết quả -->
                <transition name="slide-down">
                  <div v-if="formMessage" class="rounded-2xl px-6 py-4 text-[13px] font-bold shadow-sm flex items-center gap-3 border transition-all"
                    :class="formSuccess ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-red-50 border-red-100 text-red-600'">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                      :class="formSuccess ? 'bg-emerald-500' : 'bg-red-400'">
                      <i :class="formSuccess ? 'fa-solid fa-check' : 'fa-solid fa-xmark'" class="text-white text-[10px]"></i>
                    </div>
                    {{ formMessage }}
                  </div>
                </transition>

                <!-- Khóa học -->
                <div class="space-y-3">
                  <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Khóa học hiện tại</label>
                  <div class="relative">
                    <select v-model="form.class_id"
                      class="w-full appearance-none rounded-2xl border border-slate-100 bg-[#F8F9FB] px-6 py-4 pr-12 text-[14px] font-bold text-slate-700 outline-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-200 cursor-pointer">
                      <option value="" disabled>Chọn khóa học...</option>
                      <option v-for="cls in enrolledClasses" :key="cls.class_id" :value="cls.class_id">
                        {{ cls.label }}
                      </option>
                    </select>
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                      <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                  </div>
                </div>

                <!-- Ngày bắt đầu & kết thúc -->
                <div class="grid grid-cols-2 gap-6">
                  <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Ngày bắt đầu</label>
                    <input v-model="form.start_date" type="date"
                      class="w-full rounded-2xl border border-slate-100 bg-[#F8F9FB] px-6 py-4 text-[14px] font-bold text-slate-700 outline-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-200">
                  </div>
                  <div class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Ngày kết thúc</label>
                    <input v-model="form.end_date" type="date"
                      class="w-full rounded-2xl border border-slate-100 bg-[#F8F9FB] px-6 py-4 text-[14px] font-bold text-slate-700 outline-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-200">
                  </div>
                </div>

                <!-- Lý do -->
                <div class="space-y-3">
                  <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Lý do nghỉ học</label>
                  <textarea v-model="form.reason" rows="4"
                    class="w-full rounded-2xl border border-slate-100 bg-[#F8F9FB] px-6 py-4 text-[14px] font-bold text-slate-700 outline-none resize-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-200 placeholder:text-slate-300 placeholder:font-medium"
                    placeholder="Ghi rõ lý do và kế hoạch học bù của bạn..."></textarea>
                </div>

                <!-- Submit -->
                <button type="submit" :disabled="isSubmitting"
                  class="flex items-center gap-3 px-10 py-4 bg-[#008035] text-white rounded-2xl text-[13px] font-black uppercase tracking-widest shadow-[0_6px_20px_rgba(0,128,53,0.25)] hover:shadow-[0_10px_30px_rgba(0,128,53,0.35)] hover:bg-[#006d2c] hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                  <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin"></i>
                  <i v-else class="fa-solid fa-paper-plane"></i>
                  <span>{{ isSubmitting ? 'Đang gửi...' : 'Nộp đơn' }}</span>
                </button>
              </form>
            </section>

            <!-- Chính sách nghỉ -->
            <section class="bg-blue-50/60 rounded-[2rem] border border-blue-100/50 p-8 flex gap-5">
              <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                <i class="fa-solid fa-circle-info text-white text-sm"></i>
              </div>
              <div>
                <h4 class="text-[12px] font-headline font-black text-blue-700 uppercase tracking-widest mb-2">Chính sách nghỉ học</h4>
                <p class="text-[12px] text-blue-600/80 font-medium leading-relaxed">
                  Học viên được phép nghỉ tối đa <strong class="font-black">3 buổi</strong> mỗi khóa học. Mọi yêu cầu nghỉ học trên 3 buổi cần có xác nhận bằng văn bản đính kèm. Nghỉ không phép có thể ảnh hưởng đến kết quả đánh giá cuối khóa.
                </p>
              </div>
            </section>
          </div>

          <!-- ═══ CỘT PHẢI: Lịch sử + Thống kê (5/12) ═══ -->
          <div class="col-span-12 lg:col-span-5 space-y-10">

            <!-- Lịch sử yêu cầu -->
            <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8">
              <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-headline font-black text-slate-800 tracking-tight">Lịch sử yêu cầu</h3>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-100">
                  <i class="fa-solid fa-clock-rotate-left text-base"></i>
                </div>
              </div>

              <!-- Empty state -->
              <div v-if="requests.length === 0" class="py-12 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                  <i class="fa-solid fa-inbox text-2xl"></i>
                </div>
                <p class="text-[11px] font-black text-slate-300 uppercase tracking-widest">Chưa có đơn nghỉ nào</p>
              </div>

              <!-- Request list -->
              <div v-else class="space-y-5">
                <div v-for="req in requests.slice(0, 5)" :key="req.id"
                  class="flex items-start gap-4 group">
                  <!-- Timeline dot -->
                  <div class="flex flex-col items-center gap-1 mt-1.5 shrink-0">
                    <div class="w-2.5 h-2.5 rounded-full"
                      :class="req.status === 'approved' ? 'bg-emerald-400' : req.status === 'rejected' ? 'bg-red-400' : 'bg-amber-400'"></div>
                    <div class="w-px h-10 bg-slate-100"></div>
                  </div>
                  <!-- Content -->
                  <div class="flex-1 min-w-0 pb-5">
                    <div class="flex items-center justify-between gap-3 mb-1.5">
                      <p class="text-[13px] font-headline font-black text-slate-700 truncate">
                        {{ req.level || 'N/A' }} – {{ formatDateShort(req.start_date) }}
                      </p>
                      <span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg shrink-0"
                        :class="getStatusBadgeClass(req.status)">
                        {{ getStatusLabel(req.status) }}
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-bold leading-relaxed">
                      Lý do: {{ req.reason.length > 50 ? req.reason.substring(0, 50) + '...' : req.reason }}
                    </p>
                    <p v-if="req.end_date !== req.start_date" class="text-[10px] text-slate-300 font-bold mt-0.5">
                      Hạn bù: {{ formatDateShort(req.end_date) }}
                    </p>
                  </div>
                </div>
              </div>

              <button v-if="requests.length > 5" class="mt-6 w-full text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-emerald-500 transition-colors py-2 border-t border-slate-50 pt-5">
                Xem tất cả lịch sử
              </button>
            </section>

            <!-- Chuyên cần hiện tại -->
            <section class="relative bg-[#008035] rounded-[2rem] p-8 overflow-hidden shadow-lg text-white">
              <div class="absolute -top-6 -right-6 w-28 h-28 bg-emerald-400 rounded-full opacity-20 pointer-events-none"></div>
              <div class="absolute bottom-4 -left-4 w-16 h-16 bg-emerald-300 rounded-full opacity-15 pointer-events-none"></div>

              <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200 mb-3">Chuyên cần hiện tại</p>
                <div class="text-[52px] font-headline font-black leading-none mb-4">
                  {{ stats.attendance_rate }}<span class="text-[28px] text-emerald-200">%</span>
                </div>
                <p class="text-[11px] text-emerald-100/80 font-medium leading-relaxed italic">
                  "{{ stats.attendance_rate >= 90
                    ? 'Bạn đang có tỉ lệ chuyên cần rất tốt. Duy trì trên 90% để đạt kết quả tối ưu!'
                    : stats.attendance_rate >= 70
                      ? 'Tỉ lệ chuyên cần đang ở mức trung bình. Hãy cố gắng giảm số buổi nghỉ nhé!'
                      : 'Tỉ lệ chuyên cần đang thấp. Bạn cần tham gia đầy đủ hơn để đảm bảo kết quả học tập.' }}"
                </p>
              </div>
            </section>

            <!-- Cần hỗ trợ? -->
            <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 flex items-start gap-5">
              <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-400 border border-amber-100/50 shadow-sm shrink-0">
                <i class="fa-solid fa-circle-question text-xl"></i>
              </div>
              <div>
                <h4 class="text-[14px] font-headline font-black text-slate-800 mb-1.5">Cần hỗ trợ?</h4>
                <p class="text-[11px] text-slate-400 font-medium leading-relaxed mb-4">Chat ngay với đội ngũ tư vấn học tập của chúng tôi.</p>
                <router-link to="/user/support" class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 hover:text-emerald-600 transition-colors flex items-center gap-1.5 group">
                  Liên hệ ngay <i class="fa-solid fa-arrow-right text-[8px] group-hover:translate-x-1 transition-transform"></i>
                </router-link>
              </div>
            </section>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'

const props = defineProps({
  user: { type: Object, default: () => ({}) }
})

// ── State ──
const isLoading = ref(true)
const isSubmitting = ref(false)
const formMessage = ref('')
const formSuccess = ref(false)

const requests = ref([])
const enrolledClasses = ref([])
const stats = ref({ total_schedules: 0, total_leaves: 0, attendance_rate: 100 })

const form = ref({
  class_id: '',
  start_date: '',
  end_date: '',
  reason: ''
})

// ── Methods ──
const fetchData = async () => {
  isLoading.value = true
  try {
    const res = await apiFetch('user/leave_requests.php')
    const result = await res.json()
    if (result.status === 'success') {
      requests.value = result.data.requests || []
      enrolledClasses.value = result.data.enrolled_classes || []
      stats.value = result.data.stats || { total_schedules: 0, total_leaves: 0, attendance_rate: 100 }
    }
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

const submitLeave = async () => {
  formMessage.value = ''
  formSuccess.value = false
  isSubmitting.value = true

  try {
    const res = await apiFetch('user/leave_requests.php', {
      method: 'POST',
      body: JSON.stringify(form.value)
    })
    const result = await res.json()
    if (result.status === 'success') {
      formMessage.value = result.message
      formSuccess.value = true
      form.value = { class_id: '', start_date: '', end_date: '', reason: '' }
      await fetchData() // Refresh list
    } else {
      formMessage.value = result.message || 'Có lỗi xảy ra.'
      formSuccess.value = false
    }
  } catch {
    formMessage.value = 'Mất kết nối máy chủ.'
    formSuccess.value = false
  } finally {
    isSubmitting.value = false
  }
}

// ── Formatters ──
const formatDateShort = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const getStatusLabel = (s) => {
  if (s === 'approved') return 'Đã duyệt'
  if (s === 'rejected') return 'Từ chối'
  return 'Đang chờ'
}

const getStatusBadgeClass = (s) => {
  if (s === 'approved') return 'bg-emerald-50 text-emerald-600 border border-emerald-100'
  if (s === 'rejected') return 'bg-red-50 text-red-500 border border-red-100'
  return 'bg-amber-50 text-amber-600 border border-amber-100'
}

// ── Lifecycle ──
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
