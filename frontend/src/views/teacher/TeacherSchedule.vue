<template>
  <div class="px-10 py-8 min-h-[calc(100vh-100px)]">
    
    <!-- ── Header Section ── -->
    <div class="flex items-end justify-between mb-10">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-500 mb-2">{{ viewMode === 'weekly' ? 'Lịch trình hàng tuần' : 'Tổng quan hàng tháng' }}</p>
        <h1 class="text-4xl font-headline font-black text-slate-900 tracking-tight leading-none">Lịch Giảng Dạy</h1>
      </div>
      
      <div class="flex items-center gap-4">
        <!-- View Toggle -->
        <button @click="toggleView" class="px-6 py-3 rounded-full bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2">
          <i :class="viewMode === 'weekly' ? 'fa-solid fa-calendar-days' : 'fa-solid fa-list-ul'"></i>
          {{ viewMode === 'weekly' ? 'Xem Tổng Quan' : 'Xem Theo Tuần' }}
        </button>

        <!-- Navigation -->
        <div class="flex items-center bg-white border border-slate-100 rounded-full px-6 py-3 shadow-sm">
          <button @click="viewMode === 'weekly' ? changeWeek(-1) : changeMonth(-1)" class="text-slate-400 hover:text-emerald-500 transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
          </button>
          <span class="mx-6 text-sm font-bold text-slate-800 w-48 text-center">
            {{ viewMode === 'weekly' ? weekLabel : `Tháng ${currentMonth + 1}, ${currentYear}` }}
          </span>
          <button @click="viewMode === 'weekly' ? changeWeek(1) : changeMonth(1)" class="text-slate-400 hover:text-emerald-500 transition-colors">
            <i class="fa-solid fa-chevron-right text-sm"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Loading/Error State ── -->
    <div v-if="isLoading" class="flex items-center justify-center py-20">
      <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
    </div>

    <div v-else-if="errorMessage" class="bg-red-50 text-red-600 p-6 rounded-[2rem] border border-red-100 mb-8 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <i class="fa-solid fa-circle-exclamation text-xl"></i>
        <span class="font-bold">{{ errorMessage }}</span>
      </div>
      <button @click="changeWeek(0)" class="text-xs font-black uppercase tracking-widest bg-white px-4 py-2 rounded-xl shadow-sm hover:bg-red-500 hover:text-white transition-all">Thử lại</button>
    </div>

    <!-- ── Weekly View Grid ── -->
    <div v-if="viewMode === 'weekly' && !isLoading" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4 mb-12">
      <div v-for="(day, index) in scheduleData" :key="index" class="flex flex-col">
        <div class="border-b-2 pb-4 mb-6 transition-all" :class="[ day.isActive ? 'border-primary' : 'border-slate-100' ]">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1" :class="{'text-primary': day.isActive}">{{ day.day }}</p>
          <p class="text-2xl font-headline font-black" :class="day.isActive ? 'text-primary' : 'text-slate-900'">{{ day.date }}</p>
        </div>

        <div class="flex-1 flex flex-col gap-5">
          <div v-if="day.classes.length === 0" class="flex-1 min-h-[160px] border-2 border-dashed border-slate-200 rounded-[2rem] flex flex-col items-center justify-center p-6 text-slate-400">
            <i class="fa-regular fa-calendar-xmark text-2xl mb-3 opacity-50"></i>
            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-center">Không có<br>lịch dạy</p>
          </div>

          <div v-for="(cls, cIdx) in day.classes" :key="cIdx" 
               class="rounded-[2.5rem] p-6 relative transition-all duration-300 group/card"
               :class="cls.isPrimary ? 'bg-gradient-to-br from-emerald-600 to-emerald-500 text-white shadow-xl shadow-emerald-500/20' : 'bg-white text-slate-900 shadow-sm border border-slate-50 hover:shadow-md'">
            <div class="flex items-center justify-between mb-4">
              <span class="text-[9px] font-black uppercase tracking-[0.2em] px-2.5 py-1 rounded-md" 
                    :class="cls.isPrimary ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-500'">
                {{ cls.type }}
              </span>
              <button class="opacity-50 hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-ellipsis" :class="cls.isPrimary ? 'text-white' : 'text-slate-400'"></i>
              </button>
            </div>
            <div v-if="cls.isPrimary" class="absolute top-6 right-6 w-2.5 h-2.5 bg-primary rounded-full shadow-[0_0_12px_var(--color-primary)]"></div>
            <h3 class="font-headline font-black text-[17px] leading-tight mb-5 tracking-tight pr-4">{{ cls.title }}</h3>
            <div class="space-y-2 mb-6 text-xs font-semibold" :class="cls.isPrimary ? 'text-emerald-50' : 'text-slate-500'">
              <div class="flex items-center gap-2">
                <i class="fa-regular fa-clock opacity-70"></i>
                <span>{{ cls.time }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i :class="cls.isOnline ? 'fa-solid fa-video opacity-70' : 'fa-solid fa-location-dot opacity-70'"></i>
                <span>{{ cls.location }}</span>
              </div>
            </div>
            <button v-if="cls.isPrimary" @click="handleJoinClass(cls)" class="w-full bg-primary/20 hover:bg-primary/30 border border-white/20 text-white rounded-xl py-3 text-sm font-bold transition-colors">Vào Lớp</button>
            <button v-else @click="$router.push('/teacher/classes/' + cls.id)" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-100 text-slate-700 rounded-xl py-3 text-sm font-bold transition-colors">Xem Chi Tiết</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Monthly View Grid ── -->
    <div v-else-if="viewMode === 'monthly' && !isLoading" class="mb-12 animate__animated animate__fadeIn">
      <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <!-- Weekdays header -->
        <div class="grid grid-cols-7 border-b border-slate-50 bg-slate-50/30">
          <div v-for="day in weekDays" :key="day" class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
            {{ day }}
          </div>
        </div>
        <!-- Days grid -->
        <div class="grid grid-cols-7 gap-px bg-slate-50">
          <div v-for="(cell, index) in calendarCells" :key="index" 
               class="min-h-[140px] bg-white p-4 transition-all hover:bg-emerald-50/30 group"
               :class="{ 'bg-slate-50/50 grayscale-[50%] opacity-40': !cell.isCurrentMonth }">
            <div class="flex justify-between items-start mb-3">
              <span class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-black transition-all"
                    :class="isToday(cell.date) ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-400 group-hover:text-emerald-600'">
                {{ cell.dayNumber }}
              </span>
            </div>
            <div class="space-y-1.5 overflow-y-auto max-h-[100px] no-scrollbar">
              <div v-for="cls in cell.schedules" :key="cls.id"
                   class="px-2 py-1.5 rounded-lg text-[9px] font-bold border truncate transition-all cursor-pointer hover:shadow-md"
                   :class="cls.status === 'completed' ? 'bg-slate-50 border-slate-100 text-slate-400' : 'bg-emerald-50 border-emerald-100 text-emerald-700 hover:bg-emerald-100'"
                   @click="$router.push('/teacher/classes/' + (cls.class_id || cls.id))">
                <span class="opacity-70 mr-1">{{ cls.start_time }}</span>
                {{ cls.class_name }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Bottom Section ── -->
    <div class="grid grid-cols-3 gap-8">
      
      <!-- L Requests (2 columns) -->
      <div class="col-span-2 bg-slate-50 rounded-[2.5rem] p-8">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-xl font-headline font-black text-slate-900 tracking-tight">Yêu cầu dạy thay / bù</h2>
          <a href="#" class="text-xs font-black uppercase tracking-[0.1em] text-emerald-500 hover:text-emerald-600">Quản lý tất cả</a>
        </div>

        <div v-for="sub in substitutions" :key="sub.id" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-50 flex items-center justify-between mb-4">
          <div class="flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500">
              <i class="fa-solid fa-user-clock text-xl"></i>
            </div>
            <div>
              <h4 class="font-headline font-black text-[15px] text-slate-900 leading-tight mb-1">{{ sub.title }}</h4>
              <p class="text-xs font-bold text-slate-400">{{ sub.info }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button class="bg-[#059669] hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-colors shadow-sm">Chấp nhận</button>
          </div>
        </div>
        <div v-if="substitutions.length === 0" class="text-center py-6 text-slate-400 font-bold italic text-xs">
          Chưa có yêu cầu dạy thay mới.
        </div>
      </div>

      <!-- Upcoming Exams (1 column) -->
      <div class="col-span-1 bg-slate-200/60 rounded-[2.5rem] p-8 relative overflow-hidden group">
        <!-- Abstract Decoration -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/20 rounded-full blur-3xl group-hover:bg-white/30 transition-all duration-700"></div>
        <div class="absolute right-0 bottom-0 text-[180px] text-white/40 leading-none font-black translate-y-12 translate-x-12 select-none rotate-12 drop-shadow-xl z-0">
          <i class="fa-solid fa-file-contract"></i>
        </div>

        <h2 class="text-xl font-headline font-black text-slate-900 tracking-tight mb-8 relative z-10">Lịch thi sắp tới</h2>

        <div class="space-y-6 relative z-10 mb-10">
          <div v-for="(exam, exIdx) in upcomingExams" :key="exIdx" class="flex gap-4">
            <div class="text-center w-12 shrink-0">
              <p class="text-[10px] font-black uppercase text-emerald-500 tracking-widest mb-0.5">Th.{{ exam.month }}</p>
              <p class="text-2xl font-headline font-black text-slate-900 leading-none">{{ exam.day }}</p>
            </div>
            <div>
              <p class="font-headline font-black text-[15px] text-slate-900 mb-0.5">{{ exam.title }}</p>
              <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-500 line-clamp-1">{{ exam.level }}</p>
            </div>
          </div>
          <div v-if="upcomingExams.length === 0" class="text-center py-6 text-slate-500 font-bold italic text-xs">
            Không có lịch thi tuần này.
          </div>
        </div>

        <div class="relative z-10 mt-auto pt-4 border-t border-slate-300/50 flex justify-between items-center">
          <a href="#" class="text-[11px] font-black uppercase tracking-[0.1em] text-emerald-600 hover:text-emerald-600 flex items-center gap-2">
            Xem toàn bộ lịch đánh giá <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>

        <!-- Floating Add Button -->
        <button class="absolute right-6 top-[55%] -translate-y-1/2 w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center text-2xl shadow-xl shadow-emerald-500/30 transition-transform hover:scale-110 z-20">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from "../../utils/api"
import { notifyError } from "../../utils/notify"

const isLoading = ref(true)
const errorMessage = ref("")
const viewMode = ref('weekly') // 'weekly' or 'monthly'

// State cho Weekly
const scheduleData = ref([])
const weekLabel = ref("")
const currentStartDate = ref(new Date())

// State cho Monthly
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const monthlySchedules = ref([])
const substitutions = ref([])
const upcomingExams = ref([])

// --- Cài đặt ngày ---
const weekDays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']

const getMonday = (d) => {
  const date = new Date(d);
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(date.setDate(diff));
}

const formatDateKey = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

// --- Fetch Data ---
const fetchSchedule = async (date) => {
  isLoading.value = true
  errorMessage.value = ""
  
  const dateStr = date.toISOString().split('T')[0]
  
  try {
    const res = await apiFetch(`teacher/schedule.php?start_date=${dateStr}`)
    const result = await res.json()
    
    if (result.status === 'success') {
      scheduleData.value = result.data.weekSchedule
      weekLabel.value = result.data.weekLabel
      substitutions.value = result.data.substitutions || []
      upcomingExams.value = result.data.upcomingExams || []
    } else {
      errorMessage.value = result.message || "Không thể tải lịch dạy."
    }
  } catch (err) {
    console.error("Schedule error:", err)
    errorMessage.value = "Lỗi kết nối máy chủ."
    notifyError("Lỗi đồng bộ lịch trình.")
  } finally {
    isLoading.value = false
  }
}

const fetchMonthlySchedule = async () => {
  isLoading.value = true
  errorMessage.value = ""
  
  // Tính toán dải ngày để fetch (bao gồm cả padding của tháng trước/sau)
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  let start = new Date(firstDay)
  let dayOfWeek = start.getDay() 
  let diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1
  start.setDate(start.getDate() - diff)

  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  let end = new Date(lastDay)
  let endDayOfWeek = end.getDay()
  let endDiff = endDayOfWeek === 0 ? 0 : 7 - endDayOfWeek
  end.setDate(end.getDate() + endDiff)

  try {
    const startStr = formatDateKey(start)
    const endStr = formatDateKey(end)
    const res = await apiFetch(`teacher/schedule_monthly.php?start_date=${startStr}&end_date=${endStr}`)
    const result = await res.json()
    
    if (result.status === 'success') {
      monthlySchedules.value = result.data
    } else {
      errorMessage.value = result.message || "Không thể tải lịch tháng."
    }
  } catch (err) {
    errorMessage.value = "Lỗi kết nối máy chủ."
  } finally {
    isLoading.value = false
  }
}

// --- Logic Lịch ---
const calendarCells = computed(() => {
  const cells = []
  const year = currentYear.value
  const month = currentMonth.value

  const firstDayOfMonth = new Date(year, month, 1)
  let startingDayOfWeek = firstDayOfMonth.getDay()
  startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1

  const lastDateOfMonth = new Date(year, month + 1, 0).getDate()
  const lastDateOfPrevMonth = new Date(year, month, 0).getDate()

  // Padding tháng trước
  for (let index = startingDayOfWeek - 1; index >= 0; index--) {
    const day = lastDateOfPrevMonth - index
    const date = new Date(year, month - 1, day)
    const dKey = formatDateKey(date)
    cells.push({
      dayNumber: day,
      date: dKey,
      isCurrentMonth: false,
      schedules: monthlySchedules.value.filter(s => s.study_date === dKey)
    })
  }

  // Tháng hiện tại
  for (let day = 1; day <= lastDateOfMonth; day++) {
    const date = new Date(year, month, day)
    const dKey = formatDateKey(date)
    cells.push({
      dayNumber: day,
      date: dKey,
      isCurrentMonth: true,
      schedules: monthlySchedules.value.filter(s => s.study_date === dKey)
    })
  }

  // Padding tháng sau
  const cellsToAdd = cells.length % 7 === 0 ? 0 : 7 - (cells.length % 7)
  for (let day = 1; day <= cellsToAdd; day++) {
    const date = new Date(year, month + 1, day)
    const dKey = formatDateKey(date)
    cells.push({
      dayNumber: day,
      date: dKey,
      isCurrentMonth: false,
      schedules: monthlySchedules.value.filter(s => s.study_date === dKey)
    })
  }

  return cells
})

// --- Điều hướng ---
const changeWeek = (offset) => {
  const newDate = new Date(currentStartDate.value)
  newDate.setDate(newDate.getDate() + (offset * 7))
  currentStartDate.value = newDate
  fetchSchedule(getMonday(newDate))
}

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
  fetchMonthlySchedule()
}

const toggleView = () => {
  viewMode.value = viewMode.value === 'weekly' ? 'monthly' : 'weekly'
  if (viewMode.value === 'monthly') {
    fetchMonthlySchedule()
  } else {
    fetchSchedule(getMonday(currentStartDate.value))
  }
}

const handleJoinClass = (cls) => {
  const loc = cls.location || cls.room_info
  if (cls.isOnline || (cls.teaching_type === 'online')) {
    if (loc && loc.startsWith('http')) {
       window.open(loc, '_blank')
    } else {
       alert("Link học: " + loc)
    }
  } else {
    alert("Vui lòng đến " + loc + " để bắt đầu tiết dạy.")
  }
}

const isToday = (dateStr) => dateStr === formatDateKey(new Date())

onMounted(() => {
  const monday = getMonday(new Date())
  currentStartDate.value = monday
  fetchSchedule(monday)
})
</script>

<style scoped>
/* Optional custom scrollbars or specific effects */
</style>
