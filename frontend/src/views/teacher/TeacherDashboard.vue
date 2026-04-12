<template>
  <div class="flex-1 flex flex-col">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div
        class="w-12 h-12 border-4 border-slate-50 border-t-[#059669] rounded-full animate-spin"
      ></div>
    </div>

    <!-- Dashboard Content -->
    <div
      v-else
      class="flex-1 overflow-y-auto no-scrollbar scroll-smooth bg-slate-50"
    >
      <div class="w-full px-10 py-12 max-w-7xl mx-auto space-y-12">
        <!-- Error Message -->
        <div
          v-if="errorMessage"
          class="rounded-[2rem] border border-red-100 bg-red-50 p-6 flex items-center gap-5 text-red-600 shadow-sm animate-in fade-in slide-in-from-top-4 duration-500"
        >
          <div
            class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm border border-red-50 text-xl"
          >
            <i
              class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse"
            ></i>
          </div>
          <div class="flex-1">
            <p
              class="font-headline font-black uppercase text-[11px] tracking-[0.2em] mb-0.5 opacity-60"
            >
              System/Connection Error
            </p>
            <p class="text-[15px] font-bold">{{ errorMessage }}</p>
          </div>
          <button
            @click="fetchDashboardData"
            class="px-5 py-2.5 bg-white border border-red-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm"
          >
            Try Again
          </button>
        </div>

        

        <!-- MAIN LAYOUT GRID -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
          <!-- LEFT COLUMN (Widgets + Schedule) (8 cols) -->
          <div class="xl:col-span-8 space-y-12">
            <!-- WIDGETS ROW -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Widget 1: Classes Today -->
              <div
                class="bg-white rounded-[2rem] p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col hover:-translate-y-1 transition-transform duration-300 group cursor-pointer h-[230px]"
              >
                <div class="flex justify-between items-start mb-6">
                  <div
                    class="w-12 h-12 bg-[#F0FDF4] rounded-full flex items-center justify-center text-[#22C55E] group-hover:scale-110 group-hover:bg-[#DCFCE7] transition-all shrink-0"
                  >
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                  </div>
                  <span
                    class="text-[40px] font-headline font-black text-[#CBD5E1] group-hover:text-[#22C55E] transition-colors leading-none tracking-tighter"
                    >{{ todayClassesCount.toString().padStart(2, "0") }}</span
                  >
                </div>
                <div class="flex-1">
                  <h3
                    class="text-[19px] font-black text-[#1E293B] mb-2 leading-tight tracking-tight"
                  >
                    Lớp học hôm nay
                  </h3>
                  <p
                    class="text-[13px] text-[#64748B] font-medium leading-relaxed line-clamp-2"
                  >
                    Lịch dạy theo ca học đã được sắp xếp trong ngày.
                  </p>
                </div>
                <div class="mt-4 flex items-center">
                  <div class="flex -space-x-3">
                    <img
                      src="https://ui-avatars.com/api/?name=Hieu&background=10b981&color=fff"
                      class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm"
                    />
                    <img
                      src="https://ui-avatars.com/api/?name=Anh&background=3b82f6&color=fff"
                      class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm"
                    />
                    <img
                      src="https://ui-avatars.com/api/?name=Minh&background=f59e0b&color=fff"
                      class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm"
                    />
                    <div
                      class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[9px] font-black text-slate-500 shadow-sm"
                    >
                      +12
                    </div>
                  </div>
                </div>
              </div>

              <!-- Widget 2: Pending Grading -->
              <div
                class="bg-white rounded-[2rem] p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col hover:-translate-y-1 transition-transform duration-300 group cursor-pointer h-[230px]"
              >
                <div class="flex justify-between items-start mb-6">
                  <div
                    class="w-12 h-12 bg-[#FFF7ED] rounded-full flex items-center justify-center text-[#EA580C] group-hover:scale-110 group-hover:bg-[#FFEDD5] transition-all shrink-0"
                  >
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                  </div>
                  <span
                    class="text-[40px] font-headline font-black text-[#CBD5E1] group-hover:text-[#EA580C] transition-colors leading-none tracking-tighter"
                    >{{ pendingGradesCount.toString().padStart(2, "0") }}</span
                  >
                </div>
                <div class="flex-1">
                  <h3
                    class="text-[19px] font-black text-[#1E293B] mb-2 leading-tight tracking-tight"
                  >
                    Bài tập cần chấm
                  </h3>
                  <p
                    class="text-[13px] text-[#64748B] font-medium leading-relaxed line-clamp-2"
                  >
                    Các bài làm Essay và Assignment từ học viên.
                  </p>
                </div>
                <div class="mt-4">
                  <button
                    class="text-[11px] font-black uppercase tracking-wider text-[#C2410C] hover:text-[#9A3412] flex items-center gap-1.5 w-fit group/btn"
                  >
                    Xem danh sách
                    <i
                      class="fa-solid fa-chevron-right text-[10px] group-hover/btn:translate-x-1 transition-transform mt-0.5"
                    ></i>
                  </button>
                </div>
              </div>

              <!-- Widget 3: Notifications -->
              <div
                class="bg-white rounded-[2rem] p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col hover:-translate-y-1 transition-transform duration-300 group cursor-pointer h-[230px] relative overflow-hidden"
              >
                <!-- Pulse effect for unread -->
                <div
                  class="absolute top-8 right-8 w-1.5 h-1.5 bg-[#3B82F6] rounded-full animate-pulse shadow-[0_0_8px_blue]"
                ></div>

                <div class="flex justify-between items-start mb-6 relative">
                  <div
                    class="w-12 h-12 bg-[#EFF6FF] rounded-full flex items-center justify-center text-[#3B82F6] group-hover:scale-110 group-hover:bg-[#DBEAFE] transition-all shrink-0"
                  >
                    <i class="fa-solid fa-bullhorn text-lg"></i>
                  </div>
                  <span
                    class="text-[40px] font-headline font-black text-[#CBD5E1] group-hover:text-[#3B82F6] transition-colors mr-3 leading-none tracking-tighter"
                    >{{
                      unreadNotificationsCount.toString().padStart(2, "0")
                    }}</span
                  >
                </div>
                <div class="flex-1">
                  <h3
                    class="text-[19px] font-black text-[#1E293B] mb-2 leading-tight tracking-tight"
                  >
                    Thông báo mới
                  </h3>
                  <p
                    class="text-[13px] text-[#64748B] font-medium leading-relaxed line-clamp-2"
                  >
                    Cập nhật tin tức và thông báo từ trung tâm.
                  </p>
                </div>
                <div class="mt-4">
                  <span
                    class="bg-[#DBEAFE] text-[#1E40AF] text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md"
                    >Hệ thống</span
                  >
                </div>
              </div>
            </div>

            <!-- TODAY'S SCHEDULE -->
            <div class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h2
                    class="text-[26px] font-headline font-black text-[#1E293B] tracking-tight leading-none mb-1.5"
                  >
                    Lịch dạy hôm nay
                  </h2>
                  <p class="text-[14px] text-[#64748B] font-medium">
                    {{ currentDateFormatted }}
                  </p>
                </div>
              </div>

              <div class="space-y-4">
                <!-- Loop through Schedules -->
                <div
                  v-for="(schedule, index) in upcomingSchedules"
                  :key="index"
                  class="bg-white rounded-[2.5rem] border border-slate-50 p-8 flex items-center gap-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-md transition-shadow group"
                >
                  <!-- Time Block -->
                  <div
                    class="flex flex-col items-center justify-center min-w-[70px] shrink-0"
                  >
                    <span class="text-[14px] font-black text-[#10B981]">{{
                      schedule.time.split(" - ")[0]
                    }}</span>
                    <div class="h-8 w-px bg-emerald-100 my-1"></div>
                    <span class="text-[12px] font-bold text-[#94A3B8]">{{
                      schedule.time.split(" - ")[1]
                    }}</span>
                  </div>

                  <!-- Class Info -->
                  <div class="flex-1 min-w-0">
                    <h4
                      class="text-[18px] font-bold text-[#1E293B] leading-tight mb-2 truncate"
                    >
                      {{ schedule.class_name }}
                    </h4>
                    <div
                      class="flex items-center gap-3 text-[13px] text-[#64748B] font-medium"
                    >
                      <span>{{ schedule.room }}</span>
                      <span class="w-1 h-1 bg-[#CBD5E1] rounded-full"></span>
                      <span>{{ schedule.student_count }} Học viên</span>
                    </div>
                  </div>

                  <!-- Actions Block -->
                  <div class="shrink-0 flex items-center gap-4">
                    <button
                      v-if="schedule.status === 'ongoing'"
                      class="px-6 py-2.5 bg-[#ECFDF5] text-[#10B981] rounded-full text-[11px] font-black uppercase tracking-wider hover:bg-[#D1FAE5] transition-colors shadow-sm cursor-pointer border border-[#D1FAE5]"
                    >
                      Vào Lớp
                    </button>
                    <button
                      v-else-if="schedule.type === 'online'"
                      class="px-6 py-2.5 bg-[#EFF6FF] text-[#3B82F6] rounded-full text-[11px] font-black uppercase tracking-wider hover:bg-[#DBEAFE] transition-colors shadow-sm cursor-pointer border border-[#DBEAFE] whitespace-nowrap"
                    >
                      Link Online
                    </button>
                    <span
                      v-else
                      class="text-[10px] font-black text-[#94A3B8] uppercase tracking-widest px-4"
                      >Sắp tới</span
                    >
                  </div>
                </div>

                <div
                  v-if="upcomingSchedules.length === 0"
                  class="text-center py-10 text-slate-400"
                >
                  <i
                    class="fa-solid fa-mug-hot text-3xl mb-3 text-slate-200"
                  ></i>
                  <p class="text-sm font-medium">
                    Hôm nay bạn không có lịch dạy nào.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN (Progress & Tools) (4 cols) -->
          <div class="xl:col-span-4 space-y-6">
            <!-- CLASS PROGRESS PANEL -->
            <div
              class="bg-white rounded-[2.5rem] p-8 border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col relative overflow-hidden"
              style="min-height: 600px"
            >
              <div
                class="flex items-center justify-between mb-8 pb-4 border-b border-slate-50"
              >
                <h3
                  class="text-[22px] font-headline font-black text-[#1E293B] tracking-tight"
                >
                  Tiến độ giảng dạy
                </h3>
                <button
                  class="text-[#94A3B8] hover:text-[#475569] transition-colors"
                >
                  <i class="fa-solid fa-chart-line text-xl"></i>
                </button>
              </div>

              <div class="space-y-6 flex-1 relative z-10">
                <div v-for="(cls, idx) in classProgress" :key="idx" class="space-y-3 cursor-pointer group">
                  <div class="flex justify-between items-center">
                    <h4 class="text-[14px] font-bold text-[#1E293B] truncate pr-4">
                      {{ cls.name }}
                    </h4>
                    <span class="text-[12px] font-black" :class="cls.colorClass.replace('bg-', 'text-')">
                      {{ cls.progress }}%
                    </span>
                  </div>
                  <div class="h-2 w-full bg-[#F1F5F9] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 group-hover:opacity-80" :class="cls.colorClass" :style="{ width: cls.progress + '%' }"></div>
                  </div>
                  <div class="flex justify-between text-[9px] font-black uppercase text-[#94A3B8] tracking-wider">
                    <span>{{ cls.info1 }}</span>
                    <span>{{ cls.info2 }}</span>
                  </div>
                  <div v-if="idx < classProgress.length - 1" class="my-6 border-b border-dashed border-slate-200"></div>
                </div>

                <div v-if="classProgress.length === 0" class="text-center py-20 text-slate-300">
                   <p class="text-xs font-bold uppercase tracking-widest">Chưa có dữ liệu tiến độ</p>
                </div>
              </div>

              <!-- Teacher Insight Card -->
              <div
                class="mt-8 bg-[#F0FDF4] rounded-3xl p-5 flex items-start gap-4 border border-[#DCFCE7] relative z-10 w-[95%] self-center"
              >
                <div
                  class="w-8 h-8 rounded-full bg-[#10B981] text-white flex items-center justify-center shrink-0 shadow-sm mt-1"
                >
                  <i class="fa-solid fa-lightbulb text-[11px]"></i>
                </div>
                <div>
                  <span
                    class="text-[9px] font-black uppercase tracking-[0.2em] text-[#059669] mb-1 block"
                    >Teacher Insight</span
                  >
                  <p
                    class="text-[11px] font-bold text-[#064E3B] leading-relaxed pr-2"
                  >
                    Hệ thống tự động theo dõi tiến độ các lớp đang phụ trách.
                  </p>
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
      
      // Update stats
      todayClassesCount.value = data.todayClasses?.length || 0;
      pendingGradesCount.value = data.stats?.pendingGradesCount || 0;
      totalStudentsCount.value = data.stats?.totalStudentsCount || 0;
      activeClassesCount.value = data.stats?.activeClassesCount || 0;

      // Update lists
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
  const options = {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  };
  currentDateFormatted.value = date.toLocaleDateString("vi-VN", options);
  fetchDashboardData();
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap");

.font-headline {
  font-family: "Manrope", sans-serif;
}
.font-body {
  font-family: "Inter", sans-serif;
}

/* Shadow card premium */
.ambient-shadow {
  box-shadow: 0 55px 150px -25px rgba(26, 28, 27, 0.08);
}

/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
