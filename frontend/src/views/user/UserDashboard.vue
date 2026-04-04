<template>
  <div class="flex-1 flex flex-col">

    <!-- Trạng thái tải dữ liệu -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div class="w-12 h-12 border-4 border-slate-50 border-t-emerald-400 rounded-full animate-spin"></div>
    </div>

    <!-- Nội dung Dashboard -->
    <div v-else class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full px-10 py-14">

        <!-- THÔNG BÁO LỖI HỆ THỐNG -->
        <div v-if="errorMessage" class="mb-10 rounded-[2.5rem] border border-red-100 bg-red-50/50 p-6 flex items-center gap-5 text-red-600 shadow-[0_2px_15px_rgba(239,68,68,0.05)] animate-in fade-in slide-in-from-top-4 duration-500">
           <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm border border-red-50 text-xl overflow-hidden">
              <i class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse"></i>
           </div>
           <div class="flex-1">
              <p class="font-headline font-black uppercase text-[11px] tracking-[0.2em] mb-0.5 opacity-60">Lỗi kết nối / Hệ thống</p>
              <p class="text-[15px] font-bold">{{ errorMessage }}</p>
           </div>
           <button @click="fetchDashboard" class="px-5 py-2.5 bg-white border border-red-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm">Thử lại</button>
        </div>

        <div class="grid grid-cols-12 gap-16">
          <!-- Cột nội dung (Trái - 8/12) -->
          <div class="col-span-12 lg:col-span-8 space-y-24">

            <!-- Status Header Row: Mục tiêu & Tin nhắn (Đã chuyển lên đầu) -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-16">
              
              <!-- Widget: KPI Mục tiêu học tập -->
              <section class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm flex items-center gap-8 relative overflow-hidden group/goal">
                <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-emerald-50 rounded-full blur-xl opacity-50"></div>
                
                <div class="relative flex shrink-0 items-center justify-center">
                  <!-- Vòng tròn (Xoay 180 độ khi di chuột vào khối cha) -->
                  <div class="transition-all duration-[1000ms] group-hover/goal:rotate-180">
                    <svg class="w-32 h-32 transform -rotate-90">
                      <circle class="text-slate-50" cx="64" cy="64" fill="transparent" r="58" stroke="currentColor" stroke-width="3"></circle>
                      <circle class="text-[#7AE582]" cx="64" cy="64" fill="transparent" r="58"
                        stroke="currentColor" stroke-width="6" stroke-linecap="round"
                        stroke-dasharray="364.4" :stroke-dashoffset="364.4 * (1 - goalPercentage / 100)"
                        style="transition: stroke-dashoffset 2s cubic-bezier(0.85, 0, 0.15, 1)"></circle>
                    </svg>
                  </div>
                  <!-- Phần trăm (Giữ nguyên không xoay) -->
                  <div class="absolute flex flex-col items-center pointer-events-none">
                    <span class="text-2xl font-headline font-black text-slate-800 leading-none">{{ goalPercentage }}%</span>
                  </div>
                </div>

                <div class="flex-1 min-w-0">
                  <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Mục tiêu học tập</h3>
                  <p class="text-sm font-headline font-black text-slate-800 leading-tight mb-2 uppercase">Tiến độ tuần này</p>
                  <p class="text-[11px] text-slate-400 font-bold italic leading-relaxed">"Gần đạt mục tiêu rồi {{ firstName }}!"</p>
                </div>
              </section>

              <!-- Widget: Phản hồi giảng viên -->
              <section class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-100/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center justify-between mb-6 relative z-10">
                  <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Tin nhắn mới</h3>
                  <div class="w-1.5 h-1.5 rounded-full bg-[#7AE582] animate-pulse"></div>
                </div>
                <div class="flex items-start gap-4 relative z-10 min-w-0">
                  <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(upcomingSchedules[0]?.teacher_name || 'Sarah')}&background=111&color=ffffff&rounded=true&bold=true`" class="w-10 h-10 rounded-xl object-cover ring-2 ring-slate-50 shadow-sm shrink-0">
                  <div class="flex-1 bg-slate-50 p-4 rounded-2xl rounded-tl-none border border-slate-100 shadow-inner min-w-0">
                    <p class="text-[10px] text-slate-600 leading-relaxed font-bold italic opacity-80 line-clamp-2">
                      "Chào {{ firstName }}, {{ enrolledCourses.length > 0 ? 'hãy bắt đầu bài học hôm nay nhé!' : 'đăng ký khóa học ngay nhé!' }}..."
                    </p>
                  </div>
                </div>
              </section>

            </div>

            <!-- Khu vực khóa học đang diễn ra -->
            <section class="space-y-10">
              <div class="flex items-center justify-between">
                <h3 class="text-2xl font-headline font-black tracking-tight text-slate-800">Khóa học hiện tại</h3>
                <router-link to="/courses" class="text-emerald-500 font-black flex items-center gap-1.5 hover:gap-3 transition-all text-[10px] uppercase tracking-widest group">
                  Xem tất cả <i class="fa-solid fa-arrow-right text-[9px] transition-transform group-hover:translate-x-1"></i>
                </router-link>
              </div>

              <div v-if="enrolledCourses.length > 0" class="flex flex-col gap-10">
                <!-- Thẻ khóa học DẠNG NGANG (Đã sửa lỗi vỡ layout) -->
                <div v-for="course in enrolledCourses.slice(0, 4)" :key="course.id" class="group bg-white rounded-[2.5rem] ambient-shadow overflow-hidden border border-slate-50 transition-all duration-500 hover:shadow-xl flex flex-col lg:flex-row min-w-0">
                  
                  <!-- Khối ảnh (Thu nhỏ lại để không chiếm hết chỗ) -->
                  <div class="relative w-full lg:w-64 xl:w-72 h-48 lg:h-auto overflow-hidden bg-slate-100 shrink-0">
                    <img v-if="course.image_url" :src="course.image_url" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110">
                    <div v-else class="w-full h-full bg-slate-100 flex items-center justify-center">
                      <i class="fa-solid fa-book text-slate-200 text-5xl"></i>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-transparent opacity-60"></div>
                    <div class="absolute top-4 left-4">
                      <div class="px-2.5 py-1 bg-emerald-500 backdrop-blur-md rounded-lg text-[8px] font-black text-white uppercase tracking-widest shadow-sm">
                        {{ course.level || 'HỌC THUẬT' }}
                      </div>
                    </div>
                  </div>

                  <!-- Khối nội dung (Sử dụng min-w-0 để tự động xuống dòng) -->
                  <div class="flex-1 p-6 lg:p-8 flex flex-col justify-between min-w-0">
                    <div class="min-w-0">
                      <div class="flex items-center justify-between mb-3">
                         <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em]">{{ course.class_name || 'LỚP HỌC' }}</p>
                         <span class="text-[9px] text-slate-300 font-bold uppercase tracking-widest italic opacity-40">ID: #C{{ course.id }}</span>
                      </div>
                      <h4 class="text-lg lg:text-xl font-headline font-black text-slate-800 mb-3 leading-tight tracking-tight uppercase group-hover:text-emerald-500 transition-colors truncate lg:whitespace-normal lg:line-clamp-2">
                        {{ course.title }}
                      </h4>
                      <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed mb-6 flex items-center gap-2">
                         <i class="fa-solid fa-calendar-day text-emerald-500 opacity-60"></i>
                         {{ new Date(course.start_date).toLocaleDateString('vi-VN') }}
                      </p>
                    </div>

                    <div class="space-y-6">
                      <!-- Thanh phần trăm hoàn thành -->
                      <div class="space-y-3">
                        <div class="flex justify-between text-[9px] font-black uppercase tracking-widest text-slate-400">
                          <span>Tiến độ</span>
                          <span class="text-emerald-500 font-bold">{{ course.status === 'completed' ? '100%' : '65%' }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                          <div class="h-full bg-gradient-to-r from-emerald-400 to-[#7AE582] rounded-full transition-all duration-1000 ease-in-out shadow-[0_0_10px_rgba(122,229,130,0.2)]" 
                            :style="{ width: course.status === 'completed' ? '100%' : '65%' }"></div>
                        </div>
                      </div>

                      <div class="flex items-center justify-end">
                         <router-link :to="`/course/${course.id}`" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-center hover:bg-emerald-500 transition-all active:scale-95 duration-500 shadow-md">
                            Tiếp tục
                         </router-link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
                <!-- Empty State -->
                <div v-else class="bg-slate-50/50 rounded-[2.5rem] border border-dashed border-slate-200 p-20 text-center">
                  <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-slate-200 shadow-sm">
                    <i class="fa-solid fa-graduation-cap text-2xl"></i>
                  </div>
                  <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Bạn chưa đăng ký khóa học nào</p>
                  <router-link to="/courses" class="text-[11px] font-black text-emerald-500 uppercase hover:underline">Khám phá ngay khóa học</router-link>
                </div>
            </section>
          </div>

          <!-- Cột Tiện ích (Phải 4/12) -->
          <div class="col-span-12 lg:col-span-4 space-y-12">

            <!-- Widget: Lịch học (Dữ liệu thật) -->
            <section class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm space-y-10">
              <div class="flex items-center justify-between">
                <h3 class="text-2xl font-headline font-black text-slate-800 tracking-tight leading-tight">Lịch học sắp tới</h3>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100/50">
                  <i class="fa-solid fa-calendar-check text-xl"></i>
                </div>
              </div>

              <div v-if="upcomingSchedules.length > 0" class="space-y-10">
                <!-- Danh sách lịch học - Redesigned (Đã giới hạn 3 buổi) -->
                <div v-for="(sch, index) in upcomingSchedules.slice(0, 3)" :key="sch.id" 
                  class="flex items-start gap-5 group cursor-pointer transition-all"
                  :class="index > 0 ? 'opacity-40 grayscale hover:opacity-100 hover:grayscale-0' : ''">
                  
                  <!-- Date Badge (Cố định kích thước) -->
                  <div class="w-16 h-16 rounded-full flex flex-col items-center justify-center shrink-0 border border-emerald-100/50 shadow-sm transition-transform group-hover:scale-105"
                    :class="index === 0 ? 'bg-emerald-50' : 'bg-slate-50 border-slate-100'">
                    <span class="text-[9px] font-black uppercase tracking-widest"
                      :class="index === 0 ? 'text-emerald-500/80' : 'text-slate-400'">
                      {{ formatDay(sch.study_date) }}
                    </span>
                    <span class="text-2xl font-headline font-black leading-none mt-0.5"
                      :class="index === 0 ? 'text-emerald-500' : 'text-slate-400'">
                      {{ sch.study_date.split('-')[2] }}
                    </span>
                  </div>

                  <!-- Info (Logic Tiêu đề Thông minh: Ưu tiên Bài học > Khóa học) -->
                  <div class="flex-1 min-w-0 pt-0.5">
                    <h4 class="text-[15px] font-black leading-[1.3] group-hover:text-emerald-600 transition-colors line-clamp-2 uppercase tracking-tight"
                      :class="index === 0 ? 'text-slate-800' : 'text-slate-400'">
                      {{ sch.lesson_title || sch.course_title }}
                    </h4>
                    <div class="mt-3 space-y-1.5">
                      <div class="flex items-center gap-2" :class="index === 0 ? 'text-slate-400' : 'text-slate-300'">
                        <i class="fa-solid fa-clock text-[10px] opacity-60"></i>
                        <span class="text-[11px] font-bold">{{ sch.start_time }} – {{ sch.end_time }}</span>
                      </div>
                      <div class="flex items-center gap-2" :class="index === 0 ? 'text-slate-400' : 'text-slate-300'">
                        <i class="fa-solid fa-user-tie text-[10px] opacity-60"></i>
                        <span class="text-[11px] font-bold uppercase tracking-tight">GV: {{ sch.teacher_name || 'Đang cập nhật' }}</span>
                      </div>
                      <div class="flex items-center gap-2" :class="index === 0 ? 'text-slate-400' : 'text-slate-300'">
                        <i :class="sch.teaching_type === 'online' ? 'fa-solid fa-video' : 'fa-solid fa-location-dot'" class="text-[10px] opacity-60"></i>
                        <span class="text-[11px] font-bold truncate">{{ sch.teaching_type === 'online' ? 'Học Online (Zoom)' : (sch.room_info || 'Phòng học 402') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Empty State schedule -->
              <div v-else class="py-10 text-center space-y-4">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-200">
                  <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                </div>
                <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Không có lịch học sắp tới</p>
              </div>

              <button class="group flex w-full items-center justify-center py-4 bg-emerald-50/50 text-[#10B981] rounded-full text-[13px] font-black uppercase tracking-widest hover:bg-emerald-100 active:scale-95 transition-all border border-emerald-100/50 shadow-sm">
                <span>XEM LỊCH CHI TIẾT</span>
              </button>
            </section>


          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * UserDashboard.vue
 * Trang nội dung chính trong khu vực người dùng.
 * Nhận prop `user` từ UserLayout.vue (layout cha).
 * Chỉ chịu trách nhiệm: fetch data dashboard, hiển thị khóa học, widgets.
 */
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'
import { notifyError } from '../../utils/notify'

// ── Props từ UserLayout ──
const props = defineProps({
  user: {
    type: Object,
    default: () => ({ full_name: 'Học viên', email: '', role: 'student' })
  }
})

const router = useRouter()

// ── State ──
const isLoading = ref(true)
const errorMessage = ref('')
const enrolledCourses = ref([])
const upcomingSchedules = ref([])
const stats = ref({ activeCourses: 0, completedCourses: 0, submittedAssignments: 0, avgScore: 0, completedLessons: 0 })

// % Mục tiêu học tập - kích hoạt hiệu ứng khi mount
const goalPercentage = ref(0)

// ── Computed ──

/** Trích xuất tên gọi cuối (VD: "Nguyễn Văn Minh" → "Minh") */
const firstName = computed(() => {
  const name = (props.user?.full_name || 'Học viên').trim()
  const parts = name.split(/\s+/)
  return parts[parts.length - 1]
})

/** Định dạng thứ trong tuần bằng tiếng Việt */
const formatDay = (dateStr) => {
  const date = new Date(dateStr)
  const days = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7']
  return days[date.getDay()]
}

// ── Methods ──

/**
 * @function fetchDashboard
 * @description Tải dữ liệu tổng quan: thống kê & danh sách khóa học.
 */
const fetchDashboard = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await apiFetch('user/dashboard.php')
    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }
    const result = await response.json()
    if (result.status === 'success') {
      stats.value = result.data.stats
      enrolledCourses.value = Array.isArray(result.data.enrolledCourses) ? result.data.enrolledCourses : []
      upcomingSchedules.value = Array.isArray(result.data.upcomingSchedules) ? result.data.upcomingSchedules : []
      
      // Tính toán phần trăm tiến độ tổng thể (Dự trên khóa học và bài học)
      const targetPerc = stats.value.activeCourses > 0 ? 80 : 0
      setTimeout(() => { goalPercentage.value = targetPerc }, 600)
    } else {
      errorMessage.value = result.message || 'Hệ thống không thể tải dữ liệu Dashboard.'
    }
  } catch {
    errorMessage.value = 'Lỗi kết nối máy chủ. Vui lòng kiểm tra lại đường truyền internet.'
    notifyError('Mất kết nối tới hệ thống máy chủ.')
  } finally {
    isLoading.value = false
  }
}

// ── Lifecycle ──
onMounted(() => {
  fetchDashboard()
  // Kích hoạt hiệu ứng vẽ vòng tròn mục tiêu sau khi trang mount
  setTimeout(() => { goalPercentage.value = 80 }, 600)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

/* Shadow card premium */
.ambient-shadow {
  box-shadow: 0 55px 150px -25px rgba(26, 28, 27, 0.08);
}

/* Ẩn thanh cuộn trình duyệt */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Giới hạn 2 dòng văn bản */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.rounded-\[3rem\] { border-radius: 3rem; }
.rounded-\[3\.5rem\] { border-radius: 3.5rem; }
.rounded-\[4rem\] { border-radius: 4rem; }
.rounded-\[4\.5rem\] { border-radius: 4.5rem; }
</style>
