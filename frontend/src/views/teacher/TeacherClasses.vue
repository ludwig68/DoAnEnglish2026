<template>
  <div class="px-10 py-8 min-h-screen">

    <!-- ── Header ── -->
    <div class="mb-10">
      <h1 class="text-4xl font-headline font-black text-slate-900 tracking-tight leading-none">Quản lý lớp học</h1>
      <p class="text-slate-400 text-sm mt-2 font-medium">
        Chào buổi sáng, Giáo sư. Hôm nay bạn có <span class="font-black text-slate-700">{{ classes.length }} lớp học</span> cần lưu ý.
      </p>
    </div>

    <!-- ── Loading ── -->
    <div v-if="isLoading" class="flex items-center justify-center py-32">
      <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin"></div>
        <p class="text-xs font-black uppercase tracking-widest text-slate-400 animate-pulse">Đang tải lớp học...</p>
      </div>
    </div>

    <template v-else>
      <!-- ── Stats Grid ── -->
      <div class="grid grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-[2rem] p-7 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500">
              <i class="fa-solid fa-users-between-lines text-lg"></i>
            </div>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Total Students</span>
          </div>
          <div class="text-[42px] font-headline font-black text-slate-900 leading-none">{{ stats.total_students }}</div>
          <p class="text-emerald-500 text-xs font-bold mt-2 flex items-center gap-1">
            <i class="fa-solid fa-arrow-trend-up"></i> Tất cả học viên đang hoạt động
          </p>
        </div>
        <div class="bg-white rounded-[2rem] p-7 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-500">
              <i class="fa-solid fa-chart-pie text-lg"></i>
            </div>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Avg. Attendance</span>
          </div>
          <div class="text-[42px] font-headline font-black text-slate-900 leading-none">{{ stats.avg_attendance }}<span class="text-2xl text-slate-400">%</span></div>
          <p class="text-blue-500 text-xs font-bold mt-2 flex items-center gap-1">
            <i class="fa-solid fa-check-circle"></i> Vượt mục tiêu 90%
          </p>
        </div>
        <div class="bg-white rounded-[2rem] p-7 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl bg-red-50 flex items-center justify-center text-red-400">
              <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Pending Grading</span>
          </div>
          <div class="text-[42px] font-headline font-black text-slate-900 leading-none">{{ String(stats.pending_grading).padStart(2, '0') }}</div>
          <p class="text-red-400 text-xs font-bold mt-2 flex items-center gap-1">
            <i class="fa-solid fa-triangle-exclamation"></i> Cần chấm điểm sớm
          </p>
        </div>
      </div>

      <!-- ── Filter Tabs ── -->
      <div class="flex items-center gap-2 mb-8 bg-white border border-slate-100 rounded-2xl p-1.5 w-fit shadow-sm">
        <button v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value; loadClasses()"
          class="px-6 py-2.5 rounded-xl text-sm font-black transition-all"
          :class="activeTab === tab.value
            ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30'
            : 'text-slate-400 hover:text-slate-700 hover:bg-slate-50'">
          {{ tab.label }}
        </button>
      </div>

      <!-- ── Class Cards Grid ── -->
      <div v-if="classes.length === 0" class="flex flex-col items-center justify-center py-24 text-slate-400 bg-white rounded-[2.5rem] border border-slate-100">
        <i class="fa-solid fa-door-closed text-5xl mb-4 opacity-30"></i>
        <p class="font-black text-lg">Không có lớp nào</p>
        <p class="text-sm mt-1 opacity-70">Thử chọn bộ lọc khác</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="cls in classes" :key="cls.id"
          class="bg-white rounded-[2.5rem] p-7 border border-slate-50 shadow-sm hover:shadow-xl hover:shadow-slate-100/80 hover:-translate-y-1 transition-all duration-300 flex flex-col">

          <!-- Top: Badge + More -->
          <div class="flex items-center justify-between mb-5">
            <span class="text-[9px] font-black uppercase tracking-[0.18em] px-3 py-1.5 rounded-lg"
              :class="categoryColor(cls.category_name)">
              {{ cls.category_name || cls.course_level || 'General' }}
            </span>
            <button class="text-slate-300 hover:text-slate-500 w-8 h-8 rounded-xl hover:bg-slate-50 flex items-center justify-center transition-all">
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
          </div>

          <!-- Title -->
          <h3 class="font-headline font-black text-[18px] text-slate-900 tracking-tight leading-tight mb-1">
            {{ cls.class_name }}
          </h3>
          <p class="text-xs text-slate-400 font-medium mb-6 truncate">
            ID: #EA-{{ String(cls.id).padStart(4, '0') }} · {{ cls.course_title }}
          </p>

          <!-- Details -->
          <div class="flex items-center gap-6 mb-6">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
              <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400"><i class="fa-regular fa-calendar"></i></div>
              <div>
                <p class="text-[9px] uppercase tracking-widest text-slate-400 font-black">Schedule</p>
                <p class="text-slate-700 font-bold">
                  {{ cls.start_date ? new Date(cls.start_date).toLocaleDateString('vi-VN') : 'Chưa có' }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
              <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 relative">
                <i class="fa-solid fa-user-group"></i>
              </div>
              <div>
                <p class="text-[9px] uppercase tracking-widest text-slate-400 font-black">Students</p>
                <p class="text-slate-700 font-bold">{{ cls.student_count }} active</p>
              </div>
            </div>
          </div>

          <!-- Progress Bars -->
          <div class="space-y-3 mb-7 flex-1">
            <div>
              <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">
                <span>Attendance Rate</span>
                <span :class="cls.attendance_pct >= 85 ? 'text-emerald-500' : cls.attendance_pct >= 70 ? 'text-amber-500' : 'text-red-500'">
                  {{ cls.attendance_pct }}%
                </span>
              </div>
              <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="cls.attendance_pct >= 85 ? 'bg-emerald-500' : cls.attendance_pct >= 70 ? 'bg-amber-400' : 'bg-red-400'"
                  :style="`width: ${cls.attendance_pct}%`"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">
                <span>Course Progress</span>
                <span class="text-emerald-500">{{ progressPct(cls) }}%</span>
              </div>
              <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-700"
                  :style="`width: ${progressPct(cls)}%`"></div>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <button
            @click="$router.push('/teacher/classes/' + cls.id)"
            class="w-full py-3.5 rounded-2xl bg-slate-50 hover:bg-slate-900 text-slate-700 hover:text-white font-headline font-black text-sm tracking-tight transition-all duration-300 border border-slate-100 hover:border-slate-900">
            Quản lý lớp
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'
import { notifyError } from '../../utils/notify'

const isLoading = ref(true)
const classes = ref([])
const stats = ref({ total_students: 0, avg_attendance: 0, pending_grading: 0 })
const activeTab = ref('')

const tabs = [
  { label: 'Tất cả', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Upcoming', value: 'upcoming' },
  { label: 'Completed', value: 'completed' },
]

const loadClasses = async () => {
  isLoading.value = true
  try {
    const url = activeTab.value ? `teacher/classes.php?status=${activeTab.value}` : 'teacher/classes.php'
    const res = await apiFetch(url)
    const result = await res.json()
    if (result.status === 'success') {
      classes.value = result.data
      stats.value = result.stats
    } else {
      notifyError(result.message || 'Không thể tải danh sách lớp.')
    }
  } catch (err) {
    notifyError('Lỗi kết nối máy chủ.')
  } finally {
    isLoading.value = false
  }
}

const categoryColor = (cat) => {
  const map = {
    'IELTS': 'bg-emerald-50 text-emerald-600',
    'TOEIC': 'bg-blue-50 text-blue-600',
    'Business English': 'bg-violet-50 text-violet-600',
    'Kids': 'bg-amber-50 text-amber-600',
  }
  return map[cat] || 'bg-slate-100 text-slate-600'
}

const progressPct = (cls) => {
  if (!cls.start_date || !cls.end_date) return 0
  const start = new Date(cls.start_date)
  const end = new Date(cls.end_date)
  const now = new Date()
  if (now <= start) return 0
  if (now >= end) return 100
  return Math.round(((now - start) / (end - start)) * 100)
}

onMounted(loadClasses)
</script>
