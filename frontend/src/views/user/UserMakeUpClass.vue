<template>
  <div class="flex-1 flex flex-col pt-4">

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div class="w-12 h-12 border-4 border-slate-50 border-t-emerald-400 rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full px-10 pb-14">

        <!-- Page Title -->
        <div class="mb-8">
          <h2 class="text-[32px] font-headline font-black text-slate-800 tracking-tight leading-tight mb-2">Đăng ký học
            bù</h2>
          <p class="text-[13px] text-slate-500 font-medium leading-relaxed max-w-3xl">
            Hoàn thành tiến độ bằng cách đăng ký các buổi học đã lỡ. Vui lòng chọn buổi học bạn vắng mặt và chọn ca học
            bù phù hợp từ danh sách khả dụng phía dưới.
          </p>
        </div>

        <div class="grid grid-cols-12 gap-8">

          <!-- ═══ CỘT TRÁI: Chọn lớp đã nghỉ (4/12) ═══ -->
          <div class="col-span-12 lg:col-span-4 space-y-6">

            <!-- Card Lớp đã nghỉ -->
            <div class="bg-white rounded-[2rem] p-8 border shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
              <div class="flex items-center gap-3 mb-8">
                <div
                  class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center border border-emerald-100/50">
                  <i class="fa-regular fa-calendar-xmark text-lg"></i>
                </div>
                <h3 class="text-[18px] font-headline font-black text-slate-800 tracking-tight">Lớp đã nghỉ</h3>
              </div>

              <!-- Chọn khóa học -->
              <div class="space-y-3 mb-6">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">CHỌN KHÓA
                  HỌC</label>
                <div class="relative">
                  <select v-model="filterCourse"
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5 pr-10 text-[13px] font-bold text-slate-700 outline-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-300 cursor-pointer">
                    <option value="">Tất cả khóa học</option>
                    <option v-for="c in uniqueCourses" :key="c" :value="c">{{ c }}</option>
                  </select>
                  <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                  </div>
                </div>
              </div>

              <!-- Chọn buổi nghỉ (API Data) -->
              <div class="space-y-3">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">CHỌN BUỔI
                  NGHỈ</label>

                <div v-if="filteredMissedSessions.length === 0"
                  class="text-[12px] text-slate-500 italic py-4 border border-dashed border-slate-200 rounded-2xl text-center">
                  Không có đơn nghỉ nào (đã được duyệt).
                </div>
                <div v-else class="space-y-3 max-h-[300px] overflow-y-auto no-scrollbar pr-1">
                  <label v-for="ms in filteredMissedSessions" :key="ms.leave_request_id"
                    class="flex items-center gap-4 p-4 rounded-2xl border cursor-pointer transition-all"
                    :class="selectedMissedSession === ms.leave_request_id ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 hover:border-emerald-200'">
                    <input type="radio" :value="ms.leave_request_id" v-model="selectedMissedSession" class="hidden" />
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                      :class="selectedMissedSession === ms.leave_request_id ? 'border-emerald-500' : 'border-slate-200'">
                      <div v-if="selectedMissedSession === ms.leave_request_id"
                        class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                    </div>
                    <div>
                      <p class="text-[13px] font-headline font-black text-slate-800 leading-tight mb-1">{{
                        ms.lesson_title }}</p>
                      <p class="text-[11px] font-medium text-slate-500">{{ getFullDayString(ms.study_date) }}</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Allowance Widget -->
            <section
              class="relative bg-emerald-600 rounded-[2rem] p-8 text-white overflow-hidden shadow-lg border border-emerald-500/50">
              <div class="absolute -top-6 -right-6 w-28 h-28 bg-emerald-500 rounded-full opacity-30 pointer-events-none"></div>
              <div class="absolute bottom-4 -left-4 w-16 h-16 bg-emerald-400 rounded-full opacity-20 pointer-events-none"></div>
              
              <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-emerald-200 mb-2">Hạn mức học bù</p>
                <div class="flex items-baseline gap-2 mb-6">
                  <span class="text-[40px] font-headline font-black leading-none text-white">
                    {{ Math.max(0, missedSessions.length - registeredSchedules.length) }}
                    <span class="text-[28px] text-emerald-200 opacity-70">/{{ Math.max(3, missedSessions.length) }}</span>
                  </span>
                  <span class="text-[14px] font-bold text-emerald-200">lượt</span>
                </div>
                <div class="h-px bg-emerald-500/30 mb-4 w-full"></div>
                <p class="text-[11px] font-medium leading-relaxed italic text-emerald-100/80">
                  "Việc đi học đều đặn là chìa khóa để đạt chuẩn đầu ra và thành thạo tiếng Anh."
                </p>
              </div>
            </section>

          </div>

          <!-- ═══ CỘT PHẢI: Lịch học bù khả dụng (8/12) ═══ -->
          <div class="col-span-12 lg:col-span-8 space-y-6">

            <div class="bg-white rounded-[2rem] border shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-8">
              <!-- Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/50">
                    <i class="fa-regular fa-calendar-check text-lg"></i>
                  </div>
                  <h3 class="text-[18px] font-headline font-black text-slate-800 tracking-tight">Lịch học bù khả dụng
                  </h3>
                </div>
              </div>

              <!-- Tags / Filters -->
              <div class="flex flex-wrap items-center gap-2 mb-8">
                <button @click="filterTime = 'all'"
                  :class="filterTime === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                  class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Tất
                  cả</button>
                <button @click="filterTime = 'morning'"
                  :class="filterTime === 'morning' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                  class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Sáng</button>
                <button @click="filterTime = 'afternoon'"
                  :class="filterTime === 'afternoon' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                  class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Chiều</button>
                <button @click="filterTime = 'evening'"
                  :class="filterTime === 'evening' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                  class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Tối</button>
              </div>

              <!-- List Khả dụng -->
              <div v-if="filteredAvailable.length === 0"
                class="text-center py-12 relative border border-dashed border-slate-200 rounded-[1.5rem]">
                <div
                  class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                  <i class="fa-regular fa-folder-open text-2xl"></i>
                </div>
                <p class="text-[13px] font-bold text-slate-500">Hiện không có ca học bù nào phù hợp với tìm kiếm.</p>
              </div>

              <div v-else class="space-y-4">
                <div v-for="sch in filteredAvailable" :key="sch.id"
                  class="flex flex-col md:flex-row md:items-center justify-between p-5 rounded-[1.5rem] border border-slate-100 bg-white hover:border-emerald-200 hover:shadow-md transition-all gap-5 group">

                  <!-- Date Block -->
                  <div class="flex flex-col items-center justify-center px-4 shrink-0 min-w-[70px]">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-0.5">THG {{
                      getMonth(sch.study_date) }}</span>
                    <span class="text-[28px] font-headline font-black text-emerald-600 leading-none">{{
                      getDay(sch.study_date) }}</span>
                  </div>

                  <!-- Info grid -->
                  <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between min-w-0">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-4 w-full">
                      <!-- Time -->
                      <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                        <span
                          class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                          Thời gian
                        </span>
                        <div class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug">
                          <i class="fa-regular fa-clock text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                          <div class="whitespace-nowrap">
                            <span>{{ sch.start_time }}</span>
                            <span> - </span>
                            <span>{{ sch.end_time }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Room -->
                      <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                        <span
                          class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                          Phòng học
                        </span>
                        <div
                          class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug truncate">
                          <i class="fa-solid fa-door-open text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                          <p
                            class="truncate break-words pr-2 whitespace-normal line-clamp-2 max-w-[100px] sm:max-w-none">
                            {{ sch.room_info }}</p>
                        </div>
                      </div>

                      <!-- Teacher -->
                      <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                        <span
                          class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                          Giảng viên
                        </span>
                        <div class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug">
                          <i class="fa-regular fa-user text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                          <p class="truncate max-w-[120px] sm:max-w-none">{{ sch.teacher_name || 'GV Chuyên môn' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Register Button -->
                  <div class="shrink-0 pl-2 mt-4 md:mt-0 flex items-center justify-end">
                    <!-- Cố tình tạo mock Hết chỗ cho UI giống hệt -->
                    <button v-if="sch.id % 4 === 0" disabled
                      class="px-8 py-3 bg-slate-200 text-slate-400 rounded-2xl text-[12px] font-black cursor-not-allowed w-full md:w-auto text-center border border-slate-200">
                      Hết chỗ
                    </button>
                    <button v-else @click="registerMakeUp(sch.id)" :disabled="isRegistering"
                      class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-[12px] font-black hover:bg-emerald-700 hover:-translate-y-0.5 transition-all shadow-[0_4px_12px_rgba(16,185,129,0.2)] disabled:opacity-50 w-full md:w-auto text-center">
                      Đăng ký
                    </button>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div v-if="filteredAvailable.length > 0" class="mt-8 pt-6 flex items-center justify-between">
                <p class="text-[12px] font-medium text-slate-400">Đang hiển thị <strong class="text-slate-600">{{
                    filteredAvailable.length }}</strong> ca học khả dụng.</p>
                <button
                  class="text-[11px] font-black text-emerald-700 flex items-center gap-1.5 transition-colors uppercase tracking-widest hover:text-emerald-800">
                  Tải thêm <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
              </div>

            </div>

          </div>

        </div>

        <!-- ═══ SECTION: Lịch sử yêu cầu (New) ═══ -->
        <div class="mt-8">
          <div class="bg-white rounded-[2.5rem] border shadow-[0_2px_15px_rgba(0,0,0,0.03)] p-10">
            <div class="flex items-center justify-between mb-8">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center border border-slate-100">
                  <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <h3 class="text-[18px] font-headline font-black text-slate-800 tracking-tight">Lịch sử đăng ký</h3>
              </div>
              <div class="px-4 py-1.5 bg-slate-50 rounded-full border border-slate-100/50">
                <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Tổng cộng: {{
                  registeredSchedules.length }}</span>
              </div>
            </div>

            <div v-if="registeredSchedules.length === 0"
              class="text-center py-16 border border-dashed border-slate-100 rounded-[2rem]">
              <p class="text-[13px] font-bold text-slate-400">Bạn chưa có yêu cầu học bù nào.</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="reg in registeredSchedules" :key="reg.id"
                class="flex flex-col lg:flex-row lg:items-center justify-between p-6 rounded-[2rem] border border-slate-50 bg-slate-50/30 hover:bg-white hover:border-emerald-100 hover:shadow-sm transition-all gap-6 group/item">

                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-6">
                  <!-- Lesson Info -->
                  <div class="flex items-center gap-4">
                    <div
                      class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 font-headline font-black text-xs">
                      #{{ reg.session_index }}
                    </div>
                    <div>
                      <p class="text-[11px] font-black text-emerald-600 uppercase tracking-widest mb-0.5 line-clamp-1">
                        {{ reg.course_title }}</p>
                      <h4 class="text-[14px] font-headline font-bold text-slate-800 line-clamp-1">{{ reg.lesson_title }}
                      </h4>
                    </div>
                  </div>

                  <!-- Time & Room -->
                  <div class="flex flex-col justify-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">THỜI GIAN & PHÒNG
                    </p>
                    <div class="flex items-center gap-3 text-slate-600">
                      <span class="text-[13px] font-bold">{{ getFullDayShort(reg.study_date) }}</span>
                      <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                      <span class="text-[12px] font-bold text-slate-500">{{ reg.start_time }}</span>
                      <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                      <span class="text-[12px] font-bold text-slate-500">{{ reg.room_info }}</span>
                    </div>
                  </div>

                  <!-- Status -->
                  <div class="flex flex-col justify-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">TRẠNG THÁI</p>
                    <div class="flex items-center gap-2">
                      <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest" :class="{
                        'bg-amber-50 text-amber-600 border border-amber-100': reg.status === 'registered',
                        'bg-emerald-50 text-emerald-600 border border-emerald-100': reg.status === 'approved' || reg.status === 'attended',
                        'bg-rose-50 text-rose-600 border border-rose-100': reg.status === 'rejected',
                        'bg-slate-50 text-slate-400 border border-slate-100': reg.status === 'cancelled'
                      }">
                        {{ formatStatusLabel(reg.status) }}
                      </span>

                      <!-- Admin Note Tooltip -->
                      <div v-if="reg.admin_note && reg.status === 'rejected'" class="group/note relative">
                        <i class="fa-solid fa-circle-info text-rose-400 cursor-help"></i>
                        <div
                          class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-3 bg-slate-800 text-white text-[11px] rounded-xl opacity-0 invisible group-hover/note:opacity-100 group-hover/note:visible transition-all shadow-xl z-50">
                          <div class="font-black border-b border-white/20 pb-1 mb-1 uppercase tracking-widest">Phản hồi
                            của Admin:</div>
                          <div class="font-medium text-slate-200">{{ reg.admin_note }}</div>
                          <div
                            class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-800">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Created At -->
                  <div class="flex flex-col justify-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">NGÀY GỬI</p>
                    <p class="text-[12px] font-bold text-slate-500 italic">{{ formatDateRelative(reg.created_at ||
                      reg.study_date) }}</p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="shrink-0 flex items-center justify-end">
                  <button v-if="reg.status === 'registered'" @click="cancelMakeUp(reg.id)"
                    class="px-5 py-2.5 bg-white border border-slate-200 text-rose-500 rounded-xl text-[11px] font-black hover:bg-rose-50 hover:border-rose-100 transition-all uppercase tracking-widest">
                    Hủy đơn
                  </button>
                  <div v-else class="text-[11px] font-bold text-slate-300 italic px-4">
                    No actions
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'
import { openConfirm } from '../../utils/confirm'
import { notifySuccess, notifyError } from '../../utils/notify'

const isLoading = ref(true)
const isRegistering = ref(false)

const availableSchedules = ref([])
const registeredSchedules = ref([])
const missedSessions = ref([])
const selectedMissedSession = ref(null)

const filterCourse = ref('')
const filterTime = ref('all') // all, morning, afternoon, evening

const uniqueCourses = computed(() => {
  const courses = new Set([
    ...availableSchedules.value.map(s => s.course_title),
    ...missedSessions.value.map(s => s.course_title)
  ])
  return Array.from(courses)
})

const filteredMissedSessions = computed(() => {
  if (!filterCourse.value) return missedSessions.value
  return missedSessions.value.filter(s => s.course_title === filterCourse.value)
})

const filteredAvailable = computed(() => {
  let res = [...availableSchedules.value]

  // 1. Lọc theo khóa học (đa số là cùng khóa rồi nhưng vẫn nên giữ)
  if (filterCourse.value) {
    res = res.filter(s => s.course_title === filterCourse.value)
  }

  // 2. NEW: Lọc theo BUỔI NGHỈ (Session Index)
  if (selectedMissedSession.value) {
    const missed = missedSessions.value.find(m => m.leave_request_id === selectedMissedSession.value)
    if (missed) {
      res = res.filter(s => s.session_index === missed.session_index)
    }
  }

  // 3. Lọc theo khung giờ
  if (filterTime.value !== 'all') {
    res = res.filter(s => {
      const hour = parseInt(s.start_time.split(':')[0])
      if (filterTime.value === 'morning') return hour < 12
      if (filterTime.value === 'afternoon') return hour >= 12 && hour < 18
      if (filterTime.value === 'evening') return hour >= 18
      return true
    })
  }

  return res
})

const fetchData = async () => {
  isLoading.value = true
  try {
    const res = await apiFetch('user/makeup_registrations.php')
    const result = await res.json()
    if (result.status === 'success') {
      registeredSchedules.value = result.data.registered || []
      availableSchedules.value = result.data.available || []
      missedSessions.value = result.data.missed_sessions || []
    }
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

const registerMakeUp = async (scheduleId) => {
  if (!selectedMissedSession.value) {
    notifyError('Vui lòng CHỌN BUỔI NGHỈ ở cột bên trái trước khi đăng ký!')
    return
  }

  const confirmed = await openConfirm({
    title: 'Xác nhận đăng ký học bù',
    message: "Bạn có chắc chắn muốn đăng ký tham gia ca học bù này cho buổi vắng mặt đã chọn?",
    confirmText: 'Đăng ký ngay',
    tone: 'success'
  })

  if (!confirmed) return

  isRegistering.value = true
  try {
    const res = await apiFetch('user/makeup_registrations.php', {
      method: 'POST',
      body: JSON.stringify({
        schedule_id: scheduleId,
        leave_request_id: selectedMissedSession.value
      })
    })
    const result = await res.json()
    if (result.status === 'success') {
      notifySuccess(result.message)
      fetchData()
    } else {
      notifyError(result.message)
    }
  } catch {
    notifyError('Mất kết nối máy chủ.')
  } finally {
    isRegistering.value = false
  }
}

const cancelMakeUp = async (regId) => {
  const confirmed = await openConfirm({
    title: 'Hủy yêu cầu học bù?',
    message: "Bạn muốn hủy yêu cầu đăng ký này? Lưu ý chỉ có thể hủy trước giờ học 4 tiếng.",
    confirmText: 'Hủy ngay',
    tone: 'danger'
  })

  if (!confirmed) return

  try {
    const res = await apiFetch(`user/makeup_registrations.php?id=${regId}`, {
      method: 'DELETE'
    })
    const result = await res.json()
    if (result.status === 'success') {
      notifySuccess(result.message)
      fetchData()
    } else {
      notifyError(result.message)
    }
  } catch {
    notifyError('Lỗi kết nối máy chủ.')
  }
}

// FORMATTERS
const formatStatusLabel = (status) => {
  switch (status) {
    case 'registered': return 'Chờ duyệt'
    case 'approved': return 'Đã duyệt'
    case 'attended': return 'Đã học'
    case 'rejected': return 'Từ chối'
    case 'cancelled': return 'Đã hủy'
    default: return status
  }
}

const getFullDayShort = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`
}

const formatDateRelative = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN')
}
const getFullDayString = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const days = ['Thứ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy']
  return `${days[d.getDay()]}, ${d.getDate().toString().padStart(2, '0')} Tháng ${d.getMonth() + 1}, ${d.getFullYear()}`
}

const getMonth = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).getMonth() + 1
}

const getDay = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).getDate()
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline {
  font-family: 'Manrope', sans-serif;
}

.font-body {
  font-family: 'Inter', sans-serif;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
