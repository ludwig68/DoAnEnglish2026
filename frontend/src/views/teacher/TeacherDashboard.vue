<template>
  <div class="px-10 py-12 min-h-screen bg-slate-50/30 animate__animated animate__fadeIn">
    
    <!-- ── Header (Chào hỏi & Overview) ── -->
    <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-3">Hệ thống giảng viên</p>
        <h1 class="font-headline text-4xl font-black tracking-tight text-slate-900 leading-none">
          Chào buổi sáng, {{ teacherName }}
        </h1>
        <p class="text-slate-400 text-sm mt-3 font-medium">
          Hôm nay là <span class="font-bold text-slate-600">{{ currentDateFormatted }}</span>. Chúc bạn có một ngày giảng dạy tuyệt vời!
        </p>
      </div>
    </div>

    <!-- ── Stats Grid (3 Widgets) ── -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
      <!-- Widget: Classes Today -->
      <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500 cursor-pointer">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-50 rounded-full blur-3xl group-hover:bg-emerald-100/50 transition-all"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 transform group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-graduation-cap text-2xl"></i>
            </div>
            <span class="text-[48px] font-headline font-black text-slate-100 group-hover:text-emerald-500 transition-all duration-700 leading-none tracking-tighter">
              {{ String(todayClassesCount).padStart(2, '0') }}
            </span>
          </div>
          <h3 class="text-lg font-black text-slate-800 mb-2">Lớp học hôm nay</h3>
          <p class="text-[13px] text-slate-400 font-bold leading-relaxed pr-4">Lịch dạy và hoạt động giảng dạy chính trong ngày.</p>
        </div>
      </div>

      <!-- Widget: Pending Grading -->
      <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-500 cursor-pointer">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-50 rounded-full blur-3xl group-hover:bg-amber-100/50 transition-all"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 transform group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-clipboard-check text-2xl"></i>
            </div>
            <span class="text-[48px] font-headline font-black text-slate-100 group-hover:text-amber-500 transition-all duration-700 leading-none tracking-tighter">
              {{ String(pendingGradesCount).padStart(2, '0') }}
            </span>
          </div>
          <h3 class="text-lg font-black text-slate-800 mb-2">Bài tập chờ chấm</h3>
          <p class="text-[13px] text-slate-400 font-bold leading-relaxed pr-4">Học viên đang mong chờ phản hồi từ bạn.</p>
        </div>
      </div>

      <!-- Widget: Active Classes -->
      <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-500 cursor-pointer">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl group-hover:bg-blue-100/50 transition-all"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-500 transform group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-users-viewfinder text-2xl"></i>
            </div>
            <span class="text-[48px] font-headline font-black text-slate-100 group-hover:text-blue-500 transition-all duration-700 leading-none tracking-tighter">
              {{ String(activeClassesCount).padStart(2, '0') }}
            </span>
          </div>
          <h3 class="text-lg font-black text-slate-800 mb-2">Lớp đang giảng dạy</h3>
          <p class="text-[13px] text-slate-400 font-bold leading-relaxed pr-4">Tổng số lớp học bạn đang phụ trách hiện nay.</p>
        </div>
      </div>
    </div>

    <!-- ── Main Grid ── -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
      
      <!-- [LEFT]: Today's Schedule -->
      <div class="xl:col-span-8">
        <div class="bg-white rounded-[3.5rem] border border-slate-50 p-10 shadow-[0_20px_60px_rgb(15,23,42,0.02)]">
          <div class="flex items-center justify-between mb-10">
            <h2 class="text-2xl font-headline font-black text-slate-800 tracking-tight">Lịch dạy hôm nay</h2>
            <button @click="$router.push('/teacher/schedule')" class="text-[10px] font-black uppercase tracking-widest text-emerald-500 hover:text-emerald-600 transition-colors">Xem toàn bộ lịch</button>
          </div>

          <div v-if="upcomingSchedules.length > 0" class="space-y-6">
            <div 
              v-for="(schedule, index) in upcomingSchedules" 
              :key="index"
              @click="$router.push(`/teacher/attendance/${schedule.id}`)"
              class="group relative bg-slate-50/50 rounded-[2.5rem] p-8 border border-transparent hover:border-emerald-100 hover:bg-white hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-500 cursor-pointer flex items-center gap-8"
            >
              <!-- Time -->
              <div class="flex flex-col items-center justify-center min-w-[100px] py-4 bg-white rounded-3xl border border-slate-50 shadow-sm transition-all group-hover:border-emerald-50">
                <span class="text-[16px] font-black text-emerald-500">{{ schedule.time.split(' - ')[0] }}</span>
                <div class="w-6 h-[2px] bg-slate-100 my-2 group-hover:bg-emerald-100 transition-colors"></div>
                <span class="text-[12px] font-bold text-slate-400 group-hover:text-slate-500 transition-colors">{{ schedule.time.split(' - ')[1] }}</span>
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-lg">
                    {{ schedule.type || 'OFFLINE' }}
                  </span>
                  <span v-if="schedule.status === 'ongoing'" class="flex items-center gap-2 text-[9px] font-black uppercase text-emerald-500 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Đang diễn ra
                  </span>
                </div>
                <h4 class="text-xl font-bold text-slate-800 truncate mb-1">{{ schedule.class_name }}</h4>
                <div class="flex items-center gap-5 text-xs font-bold text-slate-400">
                  <span class="flex items-center gap-2"><i class="fa-solid fa-door-open opacity-40"></i> {{ schedule.room || 'Phòng học' }}</span>
                  <span class="flex items-center gap-2"><i class="fa-solid fa-users opacity-40"></i> {{ schedule.student_count }} học viên</span>
                </div>
              </div>

              <!-- Arrow -->
              <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 group-hover:text-emerald-500 group-hover:border-emerald-50 group-hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-right-long transform group-hover:translate-x-1 transition-transform"></i>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
              <i class="fa-solid fa-mug-hot text-4xl"></i>
            </div>
            <h4 class="text-lg font-black text-slate-700">Không có lịch dạy</h4>
            <p class="text-slate-400 text-sm mt-1 max-w-[250px] font-medium leading-relaxed">Bạn không có ca học nào được sắp xếp trong hôm nay.</p>
          </div>
        </div>
      </div>

      <!-- [RIGHT]: Teaching Progress Cards -->
      <div class="xl:col-span-4 space-y-10">
        <div class="bg-white rounded-[3.5rem] border border-slate-50 p-10 shadow-[0_20px_60px_rgb(15,23,42,0.02)]">
          <div class="flex items-center justify-between mb-10 pb-4 border-b border-slate-50">
            <h3 class="text-xl font-headline font-black text-slate-800 tracking-tight">Tiến độ lớp học</h3>
            <i class="fa-solid fa-chart-simple text-slate-300"></i>
          </div>

          <div v-if="classProgress.length > 0" class="space-y-10 overflow-hidden">
            <div v-for="(cls, idx) in classProgress" :key="idx" class="relative group">
              <div class="flex justify-between items-end mb-3">
                <div class="flex-1 min-w-0 pr-4">
                  <h4 class="text-[14px] font-black text-slate-800 truncate leading-none mb-2 group-hover:text-emerald-600 transition-colors">{{ cls.name }}</h4>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ cls.info1 }} · {{ cls.info2 }}</p>
                </div>
                <div class="text-right">
                  <span class="text-xl font-headline font-black text-slate-800">{{ cls.progress }}<span class="text-[10px] ml-0.5">%</span></span>
                </div>
              </div>
              
              <!-- Custom Premium Progress Bar -->
              <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden relative">
                <div 
                  class="h-full rounded-full transition-all duration-1000 ease-out relative"
                  :style="{ 
                    width: cls.progress + '%',
                    backgroundColor: '#10b981'
                  }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 text-slate-300">
            <p class="text-[10px] font-black uppercase tracking-widest">Không có dữ liệu tiến độ</p>
          </div>
          
          <!-- Insight Card (Bottom of Right Column) -->
          <div class="mt-12 bg-emerald-50 rounded-[2rem] p-6 border border-emerald-100 flex items-start gap-4 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-emerald-100 rounded-full blur-xl group-hover:scale-150 transition-transform duration-700"></div>
            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-emerald-500 shadow-sm shrink-0 relative z-10">
              <i class="fa-solid fa-bolt-lightning text-sm"></i>
            </div>
            <div class="relative z-10">
              <p class="text-[11px] font-black text-emerald-800 mb-1 uppercase tracking-wider">Teacher Insight</p>
              <p class="text-[11px] font-bold text-emerald-600/80 leading-relaxed pr-2">
                Hệ thống tự động theo dõi tiến độ dựa trên các buổi học đã điểm danh.
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { apiFetch } from "../../utils/api";
import { authSession } from "../../utils/auth";
import { notifyError } from "../../utils/notify";

// --- State ---
const isLoading = ref(true);
const errorMessage = ref("");
const teacherName = computed(() => authSession.value?.user?.full_name || "Giảng viên");
const currentDateFormatted = ref("");

// Stats counters
const todayClassesCount = ref(0);
const pendingGradesCount = ref(0);
const unreadNotificationsCount = ref(0);
const totalStudentsCount = ref(0);
const activeClassesCount = ref(0);

const upcomingSchedules = ref([]);
const classProgress = ref([]);

// --- Methods ---
const fetchDashboardData = async () => {
  isLoading.value = true;
  errorMessage.value = "";
  try {
    const response = await apiFetch("teacher/dashboard.php");
    if (response.status === 401 || response.status === 403) {
      errorMessage.value = "Phiên đăng nhập hết hạn hoặc không có quyền giảng viên.";
      return;
    }
    
    const result = await response.json();
    if (result.status === "success" && result.data) {
      const data = result.data;
      
      todayClassesCount.value = data.todayClasses?.length || 0;
      pendingGradesCount.value = data.stats?.pendingGradesCount || 0;
      totalStudentsCount.value = data.stats?.totalStudentsCount || 0;
      activeClassesCount.value = data.stats?.activeClassesCount || 0;

      upcomingSchedules.value = data.todayClasses || [];
      classProgress.value = data.classProgress || [];
    } else {
      errorMessage.value = result.message || "Không thể tải dữ liệu dashboard.";
    }
  } catch (err) {
    console.error("Dashboard error:", err);
    errorMessage.value = "Lỗi kết nối máy chủ. Vui lòng thử lại sau.";
    notifyError("Lỗi đồng bộ dữ liệu giảng viên.");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const date = new Date();
  const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
  currentDateFormatted.value = date.toLocaleDateString("vi-VN", options);
  fetchDashboardData();
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap");

.font-headline { font-family: "Manrope", sans-serif; }
body { font-family: "Inter", sans-serif; }

/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
