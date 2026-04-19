<template>
  <div class="px-10 py-12 min-h-screen bg-slate-50/30 animate__animated animate__fadeIn">
    
    <!-- ── Header (Tiêu đề & Thông tin tổng quan) ── -->
    <div class="mb-12">
      <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-3">Quản lý đào tạo</p>
      <h1 class="text-4xl font-headline font-black text-slate-900 tracking-tight leading-none">Danh sách lớp học</h1>
      <p class="text-slate-400 text-sm mt-3 font-medium">
        Chào buổi sáng, Giáo sư. Hiện tại hệ thống đang quản lý <span class="font-black text-slate-700">{{ classes.length }} lớp học</span> được phân công cho bạn.
      </p>
    </div>

    <!-- ── Trạng thái Tải dữ liệu (Loading) ── -->
    <div v-if="isLoading" class="flex items-center justify-center py-32">
      <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-emerald-50 border-t-emerald-500 rounded-full animate-spin"></div>
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-300 animate-pulse text-center">Đang đồng bộ dữ liệu lớp học...</p>
      </div>
    </div>

    <template v-else>
      <!-- ── Thẻ thống kê nhanh (Quick Stats Info Cards) ── -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-6 group hover:shadow-lg transition-all duration-500">
          <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-users-between-lines text-2xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tổng học viên</p>
            <div class="text-3xl font-headline font-black text-slate-900 leading-none">{{ stats.total_students }}</div>
            <p class="text-emerald-500 text-[10px] font-bold mt-2">Duy trì ổn định</p>
          </div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-6 group hover:shadow-lg transition-all duration-500">
          <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-chart-pie text-2xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tỉ lệ chuyên cần</p>
            <div class="text-3xl font-headline font-black text-slate-900 leading-none">{{ stats.avg_attendance }}<span class="text-lg text-slate-400 font-bold">%</span></div>
            <p class="text-blue-500 text-[10px] font-bold mt-2">Vượt mục tiêu 90%</p>
          </div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-6 group hover:shadow-lg transition-all duration-500">
          <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Bài tập tồn đọng</p>
            <div class="text-3xl font-headline font-black text-slate-900 leading-none">{{ String(stats.pending_grading).padStart(2, '0') }}</div>
            <p class="text-red-400 text-[10px] font-bold mt-2">Cần xử lý trong tuần</p>
          </div>
        </div>
      </div>

      <!-- ── Thanh lọc lớp học (Advanced Tabs) ── -->
      <div class="flex items-center gap-3 mb-10 bg-white border border-slate-50 rounded-[1.5rem] p-1.5 w-fit shadow-sm">
        <button v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value; loadClasses()"
          class="px-8 py-3 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95"
          :class="activeTab === tab.value
            ? 'bg-slate-900 text-white shadow-xl shadow-slate-200'
            : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50'">
          {{ tab.label }}
        </button>
      </div>

      <!-- ── Lưới thẻ lớp học (Class Cards Grid) ── -->
      <div v-if="classes.length === 0" class="flex flex-col items-center justify-center py-32 text-slate-400 bg-white rounded-[3.5rem] border border-slate-50 shadow-sm relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
        <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
          <i class="fa-solid fa-door-closed text-4xl"></i>
        </div>
        <p class="font-black text-lg text-slate-700 mt-2">Không tìm thấy lớp học</p>
        <p class="text-[13px] mt-1 font-bold">Hãy thử thay đổi tiêu chí bộ lọc của bạn.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-10">
        <div v-for="cls in classes" :key="cls.id"
          class="bg-white rounded-[3rem] p-8 border border-slate-50 shadow-[0_15px_45px_rgb(0,0,0,0.02)] hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 transition-all duration-500 flex flex-col relative overflow-hidden group">
          
          <div class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-50/30 rounded-full blur-3xl group-hover:bg-emerald-100/50 transition-all duration-700"></div>

          <!-- Top Badge & Label -->
          <div class="flex items-center justify-between mb-8 relative z-10">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] px-4 py-2 rounded-xl border border-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 group-hover:border-emerald-100 transition-all">
               {{ cls.category_name || cls.course_level || 'General' }}
            </span>
            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:text-emerald-500 transition-colors">
               <i class="fa-solid fa-ellipsis-h text-sm"></i>
            </div>
          </div>

          <!-- Title -->
          <div class="mb-8 relative z-10">
            <h3 class="font-headline font-black text-2xl text-slate-800 tracking-tight leading-tight mb-2 group-hover:text-emerald-600 transition-colors">
              {{ cls.class_name }}
            </h3>
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">
              ID: #EA-{{ String(cls.id).padStart(4, '0') }} · {{ cls.course_title }}
            </p>
          </div>

          <!-- Content Details -->
          <div class="grid grid-cols-2 gap-4 mb-10 relative z-10">
            <div class="bg-slate-50/50 rounded-[1.5rem] p-4 border border-transparent group-hover:border-emerald-50 transition-colors">
               <p class="text-[9px] font-black uppercase text-slate-300 mb-1">Ngày bắt đầu</p>
               <p class="text-xs font-black text-slate-700">{{ cls.start_date ? new Date(cls.start_date).toLocaleDateString('vi-VN') : 'Chưa có' }}</p>
            </div>
            <div class="bg-slate-50/50 rounded-[1.5rem] p-4 border border-transparent group-hover:border-emerald-50 transition-colors">
               <p class="text-[9px] font-black uppercase text-slate-300 mb-1">Số lượng</p>
               <p class="text-xs font-black text-slate-700">{{ cls.student_count }} học viên</p>
            </div>
          </div>

          <!-- Real-time Status Stats -->
          <div class="space-y-6 mb-10 relative z-10 flex-1">
            <!-- Attendance Rate -->
            <div>
              <div class="flex justify-between items-end text-[11px] font-black mb-2">
                <span class="uppercase tracking-widest text-slate-400">Tỉ lệ chuyên cần</span>
                <span :class="cls.attendance_pct >= 85 ? 'text-emerald-500' : cls.attendance_pct >= 70 ? 'text-amber-500' : 'text-red-500'">
                  {{ cls.attendance_pct }}%
                </span>
              </div>
              <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000 ease-out"
                  :class="cls.attendance_pct >= 85 ? 'bg-emerald-500' : cls.attendance_pct >= 70 ? 'bg-amber-400' : 'bg-red-400'"
                  :style="`width: ${cls.attendance_pct}%`"></div>
              </div>
            </div>
            <!-- Course Progress -->
            <div>
              <div class="flex justify-between items-end text-[11px] font-black mb-2">
                <span class="uppercase tracking-widest text-slate-400">Tiến độ khóa học</span>
                <span class="text-emerald-500">{{ progressPct(cls) }}%</span>
              </div>
              <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-1000 ease-out"
                  :style="`width: ${progressPct(cls)}%`"></div>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <button
            @click="$router.push('/teacher/classes/' + cls.id)"
            class="w-full py-4 rounded-[1.5rem] bg-white border-2 border-slate-100 hover:border-emerald-500 hover:bg-emerald-500 hover:text-white text-slate-800 font-headline font-black text-xs uppercase tracking-widest transition-all duration-300 relative z-10 group/btn"
          >
            Quản lý chi tiết
            <i class="fa-solid fa-arrow-right-long ml-2 transition-transform group-hover/btn:translate-x-1"></i>
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
  { label: 'Tất cả lớp', value: '' },
  { label: 'Đang mở', value: 'active' },
  { label: 'Sắp tới', value: 'upcoming' },
  { label: 'Hoàn thành', value: 'completed' },
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

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap");

.font-headline { font-family: 'Manrope', sans-serif; }
body { font-family: 'Inter', sans-serif; }
</style>
