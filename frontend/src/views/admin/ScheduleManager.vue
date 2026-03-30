<template>
  <div class="h-full flex flex-col p-4 md:p-6 animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-plus"></i> Lên lịch mới
      </button>
    </div>

    <div class="bg-white p-4 rounded-t-2xl shadow-sm border border-slate-100 flex justify-between items-center">
      <div class="flex items-center gap-2 md:gap-4">
        <button @click="changeMonth(-1)" class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 transition">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <h2 class="text-lg md:text-xl font-black text-slate-800 w-48 text-center uppercase tracking-wider">
          Tháng {{ currentMonth + 1 }} / {{ currentYear }}
        </h2>
        <button @click="changeMonth(1)" class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-500 transition">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
      <button @click="goToToday" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
        Hôm nay
      </button>
    </div>

    <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <div class="bg-slate-200 border border-slate-200 rounded-b-2xl overflow-hidden shadow-sm flex-1 flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center bg-white px-6 py-12 text-slate-500">
        Đang tải lịch giảng dạy...
      </div>

      <template v-else>
        <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
          <div v-for="day in weekDays" :key="day" class="py-3 text-center text-xs font-black text-slate-500 uppercase tracking-widest">
            {{ day }}
          </div>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 flex-1">
          <div
            v-for="(cell, index) in calendarCells"
            :key="index"
            class="bg-white min-h-[120px] p-2 flex flex-col transition-colors hover:bg-slate-50 group"
            :class="{ 'opacity-50 bg-slate-50/50': !cell.isCurrentMonth }"
          >
            <div class="flex justify-between items-start mb-2">
              <span
                class="w-7 h-7 flex items-center justify-center rounded-full text-sm font-bold"
                :class="isToday(cell.date) ? 'bg-[#16a34a] text-white shadow-md' : 'text-slate-600 group-hover:text-[#16a34a]'"
              >
                {{ cell.dayNumber }}
              </span>
              <button @click="openModal('add', cell.date)" class="text-slate-300 hover:text-[#16a34a] opacity-0 group-hover:opacity-100 transition">
                <i class="fa-solid fa-circle-plus text-xs"></i>
              </button>
            </div>

            <div class="flex-1 space-y-1.5 pr-1">
              <div
                v-for="schedule in getVisibleSchedules(cell.schedules)"
                :key="schedule.id"
                @click="openModal('edit', schedule)"
                class="p-1.5 rounded-lg border text-left cursor-pointer transition hover:-translate-y-0.5 shadow-sm relative overflow-hidden"
                :class="getScheduleStyle(schedule)"
              >
                <div class="text-[0.65rem] font-bold truncate leading-tight mb-0.5 flex items-center gap-1">
                  <i class="fa-regular fa-clock text-[0.6rem]"></i> {{ formatScheduleTime(schedule.start_time) }}
                </div>
                <div class="text-xs font-black truncate leading-tight">{{ schedule.class_name }}</div>
                <div v-if="schedule.course_name" class="text-[0.6rem] truncate opacity-80">{{ schedule.course_name }}</div>
                <div class="text-[0.6rem] font-medium truncate mt-0.5 opacity-80">
                  <i :class="schedule.teaching_type === 'online' ? 'fa-solid fa-video' : 'fa-solid fa-building'"></i>
                  {{ schedule.teacher_name || 'Chưa xếp giảng viên' }}
                </div>
              </div>

              <button
                v-if="getHiddenSchedulesCount(cell.schedules) > 0"
                type="button"
                @click="openDayDetails(cell)"
                class="w-full rounded-lg border border-dashed border-slate-200 bg-slate-50 px-2 py-1 text-left text-[0.7rem] font-bold text-slate-500 hover:border-slate-300 hover:text-slate-700 transition"
              >
                +{{ getHiddenSchedulesCount(cell.schedules) }} ca khác
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
      <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Lịch trong ngày</p>
            <h3 class="mt-1 text-xl font-black text-slate-900">Lịch dạy hôm nay</h3>
          </div>
          <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
            {{ todaySchedules.length }} ca
          </span>
        </div>

        <div class="mt-4 space-y-3">
          <button
            v-for="schedule in todaySchedules"
            :key="`today-${schedule.id}`"
            type="button"
            @click="openModal('edit', schedule)"
            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-white"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-black text-slate-900">{{ schedule.class_name }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ schedule.course_name || 'Chưa gắn khóa học' }}</div>
                <div class="mt-2 text-sm font-medium text-slate-600">
                  {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                </div>
              </div>
              <span class="rounded-full px-3 py-1 text-[0.7rem] font-bold" :class="getAgendaBadgeClass(schedule)">
                {{ getStatusLabel(schedule.status) }}
              </span>
            </div>
          </button>

          <div v-if="todaySchedules.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
            Hôm nay chưa có ca dạy nào.
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Sắp tới</p>
            <h3 class="mt-1 text-xl font-black text-slate-900">Các ca dạy kế tiếp</h3>
          </div>
          <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
            {{ upcomingSchedules.length }} ca
          </span>
        </div>

        <div class="mt-4 space-y-3">
          <button
            v-for="schedule in upcomingSchedules"
            :key="`upcoming-${schedule.id}`"
            type="button"
            @click="openModal('edit', schedule)"
            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition hover:-translate-y-0.5 hover:border-sky-200 hover:bg-white"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-black text-slate-900">{{ schedule.class_name }}</div>
                <div class="mt-1 text-xs text-slate-500">
                  {{ formatDate(schedule.study_date) }} · {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                </div>
                <div class="mt-2 text-sm text-slate-600">{{ schedule.teacher_name || 'Chưa xếp giảng viên' }}</div>
              </div>
              <span class="rounded-full px-3 py-1 text-[0.7rem] font-bold" :class="getAgendaBadgeClass(schedule)">
                {{ getStatusLabel(schedule.status) }}
              </span>
            </div>
          </button>

          <div v-if="upcomingSchedules.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
            Không có ca dạy sắp tới.
          </div>
        </div>
      </section>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-[#16a34a]"></i>
            {{ modalMode === 'add' ? 'Lên lịch ca dạy mới' : 'Chi tiết ca dạy' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveSchedule" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Lớp học <span class="text-red-500">*</span></label>
              <select v-model="formData.class_id" @change="handleClassChange" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
                <option value="" disabled>-- Chọn lớp học --</option>
                <option v-for="item in classOptions" :key="item.id" :value="item.id">{{ item.class_name }}{{ item.course_name ? ` - ${item.course_name}` : '' }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ngày học <span class="text-red-500">*</span></label>
              <input v-model="formData.study_date" type="date" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
            </div>

            <div class="flex gap-2">
              <div class="w-1/2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Giờ bắt đầu <span class="text-red-500">*</span></label>
                <input v-model="formData.start_time" type="time" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
              </div>
              <div class="w-1/2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Giờ kết thúc <span class="text-red-500">*</span></label>
                <input v-model="formData.end_time" type="time" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
              </div>
            </div>

            <template v-if="modalMode === 'add'">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kiểu lặp lịch</label>
                <select v-model="formData.recurrence_pattern" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
                  <option value="none">Không lặp</option>
                  <option value="mon_wed_fri">Thứ 2 / 4 / 6</option>
                  <option value="tue_thu_sat">Thứ 3 / 5 / 7</option>
                </select>
              </div>

              <div v-if="formData.recurrence_pattern !== 'none'">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Lặp đến ngày</label>
                <input v-model="formData.recurrence_end_date" type="date" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
              </div>

              <div class="md:col-span-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                Tổng số buổi học dự kiến: {{ totalLessons }} buổi
              </div>

              <div v-if="formData.recurrence_pattern !== 'none'" class="md:col-span-2 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                Hệ thống sẽ tự tạo nhiều buổi từ ngày bắt đầu đến ngày kết thúc theo nhóm ngày đã chọn.
              </div>
            </template>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Giảng viên đứng lớp</label>
              <select v-model="formData.teacher_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
                <option value="">-- Mặc định (GV chủ nhiệm) --</option>
                <option v-for="teacher in teacherOptions" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
              </select>
            </div>

            <div class="flex gap-2">
              <div class="w-1/2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hình thức</label>
                <select v-model="formData.teaching_type" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
                  <option value="offline">Offline</option>
                  <option value="online">Online</option>
                </select>
              </div>
              <div class="w-1/2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Trạng thái</label>
                <select v-model="formData.status" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
                  <option value="scheduled">Sắp tới</option>
                  <option value="completed">Đã hoàn thành</option>
                  <option value="canceled">Hủy/Nghỉ</option>
                </select>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                {{ formData.teaching_type === 'online' ? 'Link học (Zoom/Meet)' : 'Phòng học (VD: P.302 Cơ sở 1)' }}
              </label>
              <input v-model="formData.room_info" type="text" :placeholder="formData.teaching_type === 'online' ? 'https://...' : 'Nhập tên phòng...'" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ghi chú (bài học, tài liệu...)</label>
              <textarea v-model="formData.note" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"></textarea>
            </div>
          </div>

          <div class="pt-4 flex flex-col gap-4 border-t border-slate-100 mt-6 lg:flex-row lg:items-center lg:justify-between">
            <div v-if="modalMode === 'edit'" class="flex flex-wrap gap-2">
              <button type="button" @click="deleteSchedule(formData.id)" class="px-4 py-2.5 rounded-xl font-bold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition">
                <i class="fa-solid fa-trash-can mr-1"></i> Xóa ca này
              </button>
              <button type="button" @click="deleteSchedule(formData.id, 'future')" class="px-4 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 transition">
                <i class="fa-solid fa-calendar-xmark mr-1"></i> Xóa ca này và các ca về sau
              </button>
            </div>
            <div v-else></div>

            <div class="flex flex-wrap gap-3">
              <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                Hủy
              </button>
              <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu thông tin
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isDayDetailsOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="text-lg font-bold text-slate-800">Chi tiết lịch dạy ngày {{ formatDate(activeDayDetails.date) }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ activeDayDetails.schedules.length }} ca dạy trong ngày này</p>
          </div>
          <button @click="closeDayDetails" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto p-6">
          <div class="space-y-3">
            <button
              v-for="schedule in activeDayDetails.schedules"
              :key="`day-detail-${schedule.id}`"
              type="button"
              @click="openScheduleFromDayDetails(schedule)"
              class="w-full rounded-2xl border px-4 py-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm"
              :class="getScheduleStyle(schedule)"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <div class="text-base font-black">{{ schedule.class_name }}</div>
                  <div class="mt-1 text-sm opacity-80">{{ schedule.course_name || 'Chưa gắn khóa học' }}</div>
                  <div class="mt-2 text-sm font-medium">
                    {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                  </div>
                </div>
                <div class="text-right text-sm">
                  <div class="font-bold">{{ schedule.teacher_name || 'Chưa xếp giảng viên' }}</div>
                  <div class="mt-1 opacity-80">{{ schedule.room_info || 'Chưa có phòng/link học' }}</div>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'
import { openConfirm } from '../../utils/confirm'

const router = useRouter()
const schedules = ref([])
const classOptions = ref([])
const teacherOptions = ref([])
const isLoading = ref(false)
const errorMessage = ref('')

const weekDays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
const currentDate = new Date()
const currentMonth = ref(currentDate.getMonth())
const currentYear = ref(currentDate.getFullYear())

const isModalOpen = ref(false)
const isDayDetailsOpen = ref(false)
const activeDayDetails = ref({
  date: '',
  schedules: [],
})
const modalMode = ref('add')
const createDefaultFormData = (studyDate = '') => ({
  id: null,
  class_id: '',
  teacher_id: '',
  study_date: studyDate,
  start_time: '18:00',
  end_time: '19:30',
  teaching_type: 'offline',
  room_info: '',
  status: 'scheduled',
  note: '',
  recurrence_pattern: 'none',
  recurrence_end_date: '',
})
const formData = ref(createDefaultFormData())

const recurrenceWeekdaysMap = {
  none: [],
  mon_wed_fri: [1, 3, 5],
  tue_thu_sat: [2, 4, 6],
}

const totalLessons = computed(() => {
  const startDate = String(formData.value.study_date || '').trim()
  const endDate = String(formData.value.recurrence_end_date || '').trim()
  const recurrencePattern = String(formData.value.recurrence_pattern || 'none').trim()

  if (!startDate) return 0
  if (recurrencePattern === 'none') return 1
  if (!endDate) return 0

  const weekdays = recurrenceWeekdaysMap[recurrencePattern] || []
  if (weekdays.length === 0) return 0

  const start = new Date(`${startDate}T00:00:00`)
  const end = new Date(`${endDate}T00:00:00`)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || start > end) return 0

  let total = 0
  const cursor = new Date(start)
  while (cursor <= end) {
    const weekday = cursor.getDay() === 0 ? 7 : cursor.getDay()
    if (weekdays.includes(weekday)) {
      total += 1
    }
    cursor.setDate(cursor.getDate() + 1)
  }

  return total
})

const todayKey = computed(() => formatDateKey(new Date()))

const sortedSchedules = computed(() => (
  [...schedules.value].sort((left, right) => {
    const leftKey = `${left.study_date || ''} ${left.start_time || ''}`
    const rightKey = `${right.study_date || ''} ${right.start_time || ''}`
    return leftKey.localeCompare(rightKey)
  })
))

const todaySchedules = computed(() => (
  sortedSchedules.value.filter((schedule) => schedule.study_date === todayKey.value)
))

const upcomingSchedules = computed(() => (
  sortedSchedules.value
    .filter((schedule) => {
      const scheduleKey = `${schedule.study_date || ''} ${String(schedule.start_time || '').slice(0, 5)}`
      const now = new Date()
      const currentKey = `${formatDateKey(now)} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
      return scheduleKey > currentKey
    })
    .slice(0, 6)
))

const redirectToLogin = () => {
  clearAuthSession()
  router.push('/login')
}

const loadSchedules = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await apiFetch('admin/schedules.php')
    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      schedules.value = Array.isArray(result.data) ? result.data : []
      classOptions.value = Array.isArray(result.classes) ? result.classes : []
      teacherOptions.value = Array.isArray(result.teachers) ? result.teachers : []
    } else {
      errorMessage.value = result.message || 'Không tải được lịch giảng dạy.'
    }
  } catch (error) {
    console.error('Lỗi tải lịch:', error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadSchedules()
})

const formatDateKey = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

const createCellObj = (dayNumber, dateStr, isCurrentMonth) => ({
  dayNumber,
  date: dateStr,
  isCurrentMonth,
  schedules: schedules.value.filter((schedule) => schedule.study_date === dateStr),
})

const calendarCells = computed(() => {
  const cells = []
  const year = currentYear.value
  const month = currentMonth.value

  const firstDayOfMonth = new Date(year, month, 1)
  let startingDayOfWeek = firstDayOfMonth.getDay()
  startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1

  const lastDateOfMonth = new Date(year, month + 1, 0).getDate()
  const lastDateOfPrevMonth = new Date(year, month, 0).getDate()

  for (let index = startingDayOfWeek - 1; index >= 0; index--) {
    const date = new Date(year, month - 1, lastDateOfPrevMonth - index)
    cells.push(createCellObj(lastDateOfPrevMonth - index, formatDateKey(date), false))
  }

  for (let day = 1; day <= lastDateOfMonth; day++) {
    const date = new Date(year, month, day)
    cells.push(createCellObj(day, formatDateKey(date), true))
  }

  const cellsToAdd = cells.length % 7 === 0 ? 0 : 7 - (cells.length % 7)
  for (let day = 1; day <= cellsToAdd; day++) {
    const date = new Date(year, month + 1, day)
    cells.push(createCellObj(day, formatDateKey(date), false))
  }

  return cells
})

const changeMonth = (offset) => {
  let newMonth = currentMonth.value + offset
  if (newMonth > 11) {
    newMonth = 0
    currentYear.value++
  } else if (newMonth < 0) {
    newMonth = 11
    currentYear.value--
  }
  currentMonth.value = newMonth
}

const goToToday = () => {
  const today = new Date()
  currentMonth.value = today.getMonth()
  currentYear.value = today.getFullYear()
}

const isToday = (dateStr) => dateStr === formatDateKey(new Date())
const formatScheduleTime = (time) => String(time || '').slice(0, 5)
const formatDate = (dateStr) => {
  if (!dateStr) return 'Chưa xác định'
  const date = new Date(`${dateStr}T00:00:00`)
  return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString('vi-VN')
}
const getVisibleSchedules = (items) => items.slice(0, 2)
const getHiddenSchedulesCount = (items) => Math.max(items.length - 2, 0)

const getStatusLabel = (status) => {
  if (status === 'completed') return 'Đã xong'
  if (status === 'canceled') return 'Đã hủy'
  return 'Sắp tới'
}

const getAgendaBadgeClass = (schedule) => {
  if (schedule.status === 'completed') return 'bg-slate-100 text-slate-600'
  if (schedule.status === 'canceled') return 'bg-red-100 text-red-600'
  if (schedule.teaching_type === 'online') return 'bg-blue-100 text-blue-700'
  return 'bg-emerald-100 text-emerald-700'
}

const getScheduleStyle = (schedule) => {
  if (schedule.status === 'canceled') return 'bg-red-50 text-red-600 border-red-200 line-through opacity-70'
  if (schedule.status === 'completed') return 'bg-slate-100 text-slate-500 border-slate-200'
  if (schedule.teaching_type === 'online') return 'bg-blue-50 text-blue-700 border-blue-200'
  return 'bg-emerald-50 text-emerald-700 border-emerald-200'
}

const handleClassChange = () => {
  const selectedClass = classOptions.value.find((item) => Number(item.id) === Number(formData.value.class_id))
  if (!selectedClass) return

  if (selectedClass.start_date) {
    formData.value.study_date = selectedClass.start_date
  }

  if (selectedClass.end_date) {
    formData.value.recurrence_end_date = selectedClass.end_date
  }
}

const openDayDetails = (cell) => {
  activeDayDetails.value = {
    date: cell.date,
    schedules: [...cell.schedules].sort((left, right) => String(left.start_time || '').localeCompare(String(right.start_time || ''))),
  }
  isDayDetailsOpen.value = true
}

const closeDayDetails = () => {
  isDayDetailsOpen.value = false
  activeDayDetails.value = {
    date: '',
    schedules: [],
  }
}

const openScheduleFromDayDetails = (schedule) => {
  closeDayDetails()
  openModal('edit', schedule)
}

const openModal = (mode, data = null) => {
  isDayDetailsOpen.value = false
  modalMode.value = mode

  if (mode === 'edit' && data) {
    formData.value = {
      ...createDefaultFormData(),
      ...data,
      recurrence_pattern: 'none',
      recurrence_end_date: '',
      start_time: String(data.start_time || '').slice(0, 5),
      end_time: String(data.end_time || '').slice(0, 5),
    }
  } else {
    const selectedDate = typeof data === 'string' ? data : ''
    formData.value = createDefaultFormData(selectedDate)
  }

  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  formData.value = createDefaultFormData()
}

const parseTimeToMinutes = (value) => {
  if (!value) return NaN
  const [hour, minute] = String(value).split(':').map(Number)
  if (Number.isNaN(hour) || Number.isNaN(minute)) return NaN
  return hour * 60 + minute
}

const getIsoWeekday = (dateStr) => {
  const date = new Date(`${dateStr}T00:00:00`)
  const day = date.getDay()
  return day === 0 ? 7 : day
}

const validateForm = () => {
  const classId = Number(formData.value.class_id)
  const teacherId = formData.value.teacher_id === '' ? null : Number(formData.value.teacher_id)
  const studyDate = String(formData.value.study_date || '').trim()
  const startTime = String(formData.value.start_time || '').trim()
  const endTime = String(formData.value.end_time || '').trim()
  const teachingType = String(formData.value.teaching_type || 'offline').trim()
  const status = String(formData.value.status || 'scheduled').trim()
  const roomInfo = String(formData.value.room_info || '').trim()
  const note = String(formData.value.note || '').trim()
  const recurrencePattern = String(formData.value.recurrence_pattern || 'none').trim()
  const recurrenceEndDate = String(formData.value.recurrence_end_date || '').trim()

  if (!Number.isInteger(classId) || classId <= 0) {
    alert('Vui lòng chọn lớp học hợp lệ.')
    return false
  }

  if (teacherId !== null && (!Number.isInteger(teacherId) || teacherId <= 0)) {
    alert('Giảng viên được chọn không hợp lệ.')
    return false
  }

  if (!/^\d{4}-\d{2}-\d{2}$/.test(studyDate) || Number.isNaN(new Date(`${studyDate}T00:00:00`).getTime())) {
    alert('Ngày học không hợp lệ.')
    return false
  }

  if (!/^\d{2}:\d{2}$/.test(startTime) || !/^\d{2}:\d{2}$/.test(endTime)) {
    alert('Giờ học không hợp lệ.')
    return false
  }

  const startMinutes = parseTimeToMinutes(startTime)
  const endMinutes = parseTimeToMinutes(endTime)
  if (Number.isNaN(startMinutes) || Number.isNaN(endMinutes) || startMinutes >= endMinutes) {
    alert('Giờ kết thúc phải lớn hơn giờ bắt đầu.')
    return false
  }

  if (!['offline', 'online'].includes(teachingType)) {
    alert('Hình thức dạy không hợp lệ.')
    return false
  }

  if (!['scheduled', 'completed', 'canceled'].includes(status)) {
    alert('Trạng thái lịch học không hợp lệ.')
    return false
  }

  if (roomInfo && (roomInfo.length < 2 || roomInfo.length > 255)) {
    alert('Thông tin phòng học/link học phải từ 2 đến 255 ký tự.')
    return false
  }

  if (teachingType === 'online' && roomInfo && !/^https?:\/\/\S+$/i.test(roomInfo)) {
    alert('Link học online phải bắt đầu bằng http:// hoặc https://')
    return false
  }

  if (note && (note.length < 3 || note.length > 1000)) {
    alert('Ghi chú phải từ 3 đến 1000 ký tự nếu có nhập.')
    return false
  }

  if (modalMode.value === 'add' && recurrencePattern !== 'none') {
    if (!['mon_wed_fri', 'tue_thu_sat'].includes(recurrencePattern)) {
      alert('Kiểu lặp lịch không hợp lệ.')
      return false
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(recurrenceEndDate) || Number.isNaN(new Date(`${recurrenceEndDate}T00:00:00`).getTime())) {
      alert('Ngày kết thúc lặp lịch không hợp lệ.')
      return false
    }

    if (recurrenceEndDate < studyDate) {
      alert('Ngày kết thúc lặp lịch không được nhỏ hơn ngày bắt đầu.')
      return false
    }

    const allowedWeekdays = recurrenceWeekdaysMap[recurrencePattern] || []
    if (!allowedWeekdays.includes(getIsoWeekday(studyDate))) {
      alert('Ngày bắt đầu không nằm trong nhóm ngày lặp đã chọn.')
      return false
    }
  }

  const overlappingSchedule = schedules.value.find((schedule) => {
    if (Number(schedule.id) === Number(formData.value.id || 0)) return false
    if (schedule.study_date !== studyDate) return false

    const scheduleStart = parseTimeToMinutes(String(schedule.start_time || '').slice(0, 5))
    const scheduleEnd = parseTimeToMinutes(String(schedule.end_time || '').slice(0, 5))
    const overlap = startMinutes < scheduleEnd && endMinutes > scheduleStart
    if (!overlap) return false

    const sameClass = Number(schedule.class_id) === classId
    const sameTeacher = teacherId !== null && Number(schedule.teacher_id) === teacherId
    return sameClass || sameTeacher
  })

  if (overlappingSchedule) {
    if (Number(overlappingSchedule.class_id) === classId) {
      alert('Lớp học này đã có lịch trùng giờ trong ngày được chọn.')
      return false
    }
    alert('Giảng viên này đã có lịch trùng giờ trong ngày được chọn.')
    return false
  }

  return true
}

const saveSchedule = async () => {
  if (!validateForm()) return

  try {
    const method = modalMode.value === 'add' ? 'POST' : 'PUT'
    const res = await apiFetch('admin/schedules.php', {
      method,
      body: JSON.stringify(formData.value),
    })

    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      closeModal()
      loadSchedules()
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Lỗi kết nối máy chủ')
  }
}

const deleteSchedule = async (id, scope = 'single') => {
  const isFutureDelete = scope === 'future'
  const confirmMessage = isFutureDelete
    ? 'Bạn có chắc chắn muốn xóa ca này và toàn bộ các ca của lớp từ ngày này trở về sau không?'
    : 'Bạn có chắc chắn muốn xóa hoặc hủy riêng ca dạy này khỏi lịch?'

  const confirmed = await openConfirm({
    title: isFutureDelete ? 'Xóa nhiều ca dạy' : 'Xóa ca dạy',
    message: confirmMessage,
    confirmText: isFutureDelete ? 'Xóa các ca về sau' : 'Xóa ca này',
    cancelText: 'Không xóa',
    tone: 'danger',
  })
  if (!confirmed) return

  try {
    const query = new URLSearchParams({
      id: String(id),
      scope,
    })

    const res = await apiFetch(`admin/schedules.php?${query.toString()}`, { method: 'DELETE' })
    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      closeModal()
      loadSchedules()
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Lỗi xóa dữ liệu')
  }
}</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>



