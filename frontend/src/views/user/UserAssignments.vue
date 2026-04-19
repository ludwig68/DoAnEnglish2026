<template>
  <div class="flex-1 flex flex-col">

    <!-- Trạng thái tải dữ liệu -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div class="w-12 h-12 border-4 border-slate-50 border-t-emerald-400 rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full px-10 py-14">

        <!-- Thông báo lỗi -->
        <div v-if="errorMessage" class="mb-10 rounded-[2.5rem] border border-red-100 bg-red-50/50 p-6 flex items-center gap-5 text-red-600 shadow-sm">
          <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm border border-red-50 text-xl">
            <i class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse"></i>
          </div>
          <div class="flex-1">
            <p class="font-headline font-black uppercase text-[11px] tracking-[0.2em] mb-0.5 opacity-60">Lỗi kết nối</p>
            <p class="text-[15px] font-bold">{{ errorMessage }}</p>
          </div>
          <button @click="fetchAssignments" class="px-5 py-2.5 bg-white border border-red-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm">Thử lại</button>
        </div>

        <!-- Trạng thái không có khóa học -->
        <div v-if="!errorMessage && courses.length === 0" class="bg-slate-50/50 rounded-[2.5rem] border border-dashed border-slate-200 p-20 text-center">
          <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-slate-200 shadow-sm">
            <i class="fa-solid fa-rectangle-list text-2xl"></i>
          </div>
          <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Bạn chưa đăng ký khóa học nào</p>
          <router-link to="/courses" class="text-[11px] font-black text-emerald-500 uppercase hover:underline">Khám phá khóa học ngay</router-link>
        </div>

        <!-- PHASE 1: CHỌN KHÓA HỌC -->
        <div v-if="!selectedCourseId && courses.length > 1" class="animate__animated animate__fadeIn">
          <div class="mb-12">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-3">Khóa học của bạn</p>
            <h2 class="text-3xl lg:text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight mb-3">
              Chọn Lớp Để Làm Bài
            </h2>
            <p class="text-sm text-slate-400 font-medium leading-relaxed max-w-lg">
              Bạn đang tham gia nhiều khóa học, vui lòng lựa chọn khóa học bên dưới để tiếp tục.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div 
              v-for="course in courses" :key="course.course_id"
              @click="selectedCourseId = course.course_id"
              class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-2 transition-all duration-500 group cursor-pointer relative overflow-hidden"
            >
              <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-50 rounded-full blur-3xl group-hover:bg-emerald-100/50 transition-all"></div>
              <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                  <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-[0.15em] rounded-lg border border-emerald-100/50">{{ course.level || 'Khóa học' }}</span>
                  <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest">{{ course.shift_name || course.class_name }}</span>
                </div>
                <h3 class="text-xl font-headline font-black text-slate-800 tracking-tight leading-tight mb-4 group-hover:text-emerald-600 transition-colors">
                  {{ course.course_title }}
                </h3>
                <div class="flex flex-col gap-2">
                  <p class="text-[12px] font-bold text-slate-500 flex items-center gap-2"><i class="fa-solid fa-layer-group opacity-50"></i> {{ course.lessons?.length || 0 }} Chương học</p>
                </div>
                
                <button class="mt-8 w-full py-4 rounded-[1.5rem] bg-slate-50 text-slate-600 font-black text-[11px] uppercase tracking-widest group-hover:bg-emerald-500 group-hover:text-white transition-all flex items-center justify-center gap-2">
                  Xem bài tập <i class="fa-solid fa-arrow-right-long group-hover:translate-x-1 transition-transform"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- PHASE 2: HIỂN THỊ BÀI TẬP CỦA KHÓA HỌC -->
        <div v-else-if="activeCourse" class="animate__animated animate__fadeIn">

          <!-- Tiêu đề khóa học -->
          <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-slate-50">
            <div>
              <nav v-if="courses.length > 1" class="mb-4 flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                <span @click="selectedCourseId = null" class="hover:text-emerald-500 cursor-pointer transition-colors">
                  <i class="fa-solid fa-arrow-left mr-1.5"></i>Khóa học khác
                </span>
                <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
                <span class="text-emerald-500">Bài tập</span>
              </nav>

              <div class="flex items-center gap-3 mb-3">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-[0.15em] rounded-lg border border-emerald-100/50">{{ activeCourse.level || 'Khóa học' }}</span>
                <span class="text-[10px] text-emerald-500 font-black uppercase tracking-widest">{{ activeCourse.shift_name || activeCourse.class_name }}</span>
              </div>
              <h2 class="text-3xl lg:text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight mb-3">
                {{ activeCourse.course_title }}
              </h2>
              <p class="text-sm text-slate-400 font-medium leading-relaxed max-w-lg">
                Lựa chọn bài học bạn muốn rèn luyện hôm nay để đạt được mục tiêu học tập.
              </p>
            </div>
            
            <button v-if="courses.length > 1" @click="selectedCourseId = null" class="shrink-0 px-6 py-3 rounded-2xl border-2 border-slate-100 hover:border-emerald-500 text-slate-500 hover:text-emerald-600 text-[11px] font-black uppercase tracking-widest transition-all">
              <i class="fa-solid fa-rotate mr-2"></i>Đổi khóa
            </button>
          </div>

          <div class="grid grid-cols-12 gap-10">

            <!-- ═══ CỘT CHÍNH: Lesson Cards (8/12) ═══ -->
            <div class="col-span-12 lg:col-span-8">
              <!-- Không có lesson -->
              <div v-if="activeCourse.lessons.length === 0" class="bg-slate-50/50 rounded-[2.5rem] border border-dashed border-slate-200 p-16 text-center">
                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mx-auto mb-5 text-slate-200 shadow-sm">
                  <i class="fa-solid fa-book text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Chưa có bài học nào</p>
              </div>

              <!-- Grid lessons -->
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                <div
                  v-for="(lesson, idx) in activeCourse.lessons"
                  :key="lesson.id"
                  @click="!lesson.locked && openLessonDetail(lesson, activeCourse)"
                  class="group relative bg-white rounded-[2rem] border transition-all duration-500 overflow-hidden"
                  :class="[
                    lesson.locked
                      ? 'border-slate-100 opacity-60 cursor-not-allowed grayscale'
                      : 'border-slate-100 hover:border-emerald-200 hover:shadow-xl cursor-pointer',
                    lesson.isActive ? 'ring-2 ring-emerald-400/30 border-emerald-200' : ''
                  ]"
                >
                  <!-- Badge ĐANG HỌC -->
                  <div v-if="lesson.isActive" class="absolute top-5 right-5 z-10">
                    <span class="px-3 py-1.5 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-[0.15em] rounded-lg shadow-sm">
                      Đang học
                    </span>
                  </div>

                  <!-- Phần trên: Số thứ tự + Icon -->
                  <div class="relative px-8 pt-10 pb-6">
                    <span class="absolute top-4 left-6 text-[5rem] font-headline font-black leading-none select-none pointer-events-none"
                      :class="lesson.locked ? 'text-slate-100' : 'text-emerald-100/80'">
                      {{ String(idx + 1).padStart(2, '0') }}
                    </span>
                    <div class="relative z-10 flex justify-end">
                      <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm border"
                        :class="lesson.locked ? 'bg-slate-50 border-slate-100 text-slate-300' : 'bg-emerald-50 border-emerald-100/50 text-emerald-500'">
                        <i :class="lesson.icon" class="text-lg"></i>
                      </div>
                    </div>
                  </div>

                  <!-- Phần dưới: Thông tin -->
                  <div class="px-8 pb-8">
                    <h4 class="text-lg font-headline font-black text-slate-800 tracking-tight leading-tight mb-4 group-hover:text-emerald-600 transition-colors uppercase">
                      {{ lesson.title }}
                    </h4>

                    <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400 mb-6">
                      <span>{{ lesson.assignmentCount }} Assignments</span>
                      <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                      <span v-if="lesson.locked" class="text-amber-500">Locked</span>
                      <span v-else>{{ lesson.completionPercent }}% Completed</span>
                    </div>

                    <!-- Thanh tiến độ -->
                    <div v-if="!lesson.locked" class="h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden">
                      <div class="h-full rounded-full transition-all duration-1000 ease-out"
                        :class="lesson.completionPercent === 100 ? 'bg-emerald-400' : 'bg-blue-500'"
                        :style="{ width: lesson.completionPercent + '%' }">
                      </div>
                    </div>
                    <div v-else class="h-1.5 w-24 bg-slate-100 rounded-full"></div>
                  </div>
                </div>
              </div>

            </div>

            <!-- ═══ CỘT PHỤ: Sidebar Widgets (4/12) ═══ -->
            <div class="col-span-12 lg:col-span-4 space-y-10">

              <!-- Widget: Bảng xếp hạng (Mock) -->
              <section class="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                  <h3 class="text-xl font-headline font-black text-slate-800 tracking-tight">Bảng xếp hạng</h3>
                  <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-400 border border-amber-100/50 shadow-sm">
                    <i class="fa-solid fa-trophy text-base"></i>
                  </div>
                </div>

                <div class="space-y-5">
                  <div
                    v-for="(player, pIdx) in leaderboardData"
                    :key="player.id"
                    class="flex items-center gap-4 p-3 rounded-2xl transition-all duration-300"
                    :class="player.is_current ? 'bg-emerald-50/80 border border-emerald-100/50' : 'hover:bg-slate-50'"
                  >
                    <div class="relative shrink-0">
                      <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(player.full_name)}&background=${pIdx === 0 ? '7AE582' : '111'}&color=ffffff&rounded=true&bold=true&size=128`" :alt="player.full_name" class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow-sm">
                      <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-black shadow-sm"
                        :class="pIdx === 0 ? 'bg-amber-400 text-white' : 'bg-slate-800 text-white'">
                        {{ pIdx + 1 }}
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-headline font-black truncate leading-none mb-1"
                        :class="player.is_current ? 'text-emerald-700' : 'text-slate-800'">
                        {{ player.is_current ? `Bạn (${player.full_name})` : player.full_name }}
                      </p>
                      <p class="text-[10px] font-black uppercase tracking-[0.15em]"
                        :class="player.is_current ? 'text-emerald-500' : 'text-amber-500'">
                        {{ player.total_points }} POINTS
                      </p>
                    </div>
                  </div>
                </div>

                <button class="mt-8 w-full text-center text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 hover:text-emerald-600 transition-colors py-2">
                  Tất cả xếp hạng
                </button>
              </section>

              <!-- Widget: Mẹo học tập thông minh -->
              <section class="relative bg-emerald-600 rounded-[2rem] p-8 overflow-hidden shadow-lg">
                <div class="absolute -top-6 -right-6 w-28 h-28 bg-emerald-500 rounded-full opacity-30 pointer-events-none"></div>
                <div class="absolute bottom-4 -left-4 w-16 h-16 bg-emerald-400 rounded-full opacity-20 pointer-events-none"></div>

                <div class="relative z-10">
                  <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-6 border border-white/10 shadow-inner">
                    <i class="fa-solid fa-lightbulb text-xl text-white"></i>
                  </div>

                  <h3 class="text-lg font-headline font-black text-white tracking-tight mb-3 leading-tight">
                    Mẹo học tập thông minh
                  </h3>
                  <p class="text-emerald-100 text-[12px] font-medium leading-relaxed mb-6">
                    Sử dụng phương pháp <strong class="text-white font-black">Spaced Repetition</strong> để tối ưu khả năng ghi nhớ từ vựng học thuật lên đến 80%.
                  </p>

                  <button class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-white hover:text-emerald-200 transition-colors group">
                    Khám phá ngay
                    <i class="fa-solid fa-arrow-right text-[9px] transition-transform group-hover:translate-x-1"></i>
                  </button>
                </div>
              </section>
            </div> <!-- End CỘT PHỤ -->
          </div> <!-- End grid -->
        </div> <!-- End PHASE 2 -->

      </div> <!-- End padding wrapper -->
    </div> <!-- End main content wrapper -->

    <!-- ═══ MODAL: Chi tiết bài tập của Lesson ═══ -->
    <div v-if="selectedLesson" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4 py-8 backdrop-blur-md" @click.self="closeDetail">
      <div class="w-full max-w-4xl h-[90vh] rounded-[2.5rem] bg-slate-50 shadow-2xl flex flex-col animate-in fade-in zoom-in duration-300 overflow-hidden relative">

        <!-- Biểu tượng trang trí phông nền -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-100 rounded-full blur-3xl opacity-50 pointer-events-none -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-50 rounded-full blur-3xl opacity-50 pointer-events-none translate-y-1/2 -translate-x-1/3"></div>

        <!-- Nút Close nổi -->
        <button @click="closeDetail" class="absolute top-6 right-6 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 hover:text-red-500 shadow-md hover:bg-red-50 transition-all shrink-0">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <!-- Header -->
        <div class="relative px-12 pt-12 pb-6 shrink-0 z-10">
          <h2 class="text-3xl font-headline font-black text-slate-800 tracking-tight">{{ selectedLesson.title }}</h2>
          <p class="text-[13px] text-slate-500 font-medium mt-2">Danh sách bài tập và tài liệu khóa học</p>
        </div>

        <!-- Filter Bar -->
        <div class="px-12 py-4 shrink-0 z-10">
          <div class="inline-flex bg-white rounded-[1.25rem] p-1.5 shadow-sm border border-slate-100 w-auto bg-opacity-80 backdrop-blur-md">
            <button @click="activeFilter = 'all'" class="px-8 py-3 rounded-xl text-[12px] font-black transition-all duration-300" :class="activeFilter === 'all' ? 'bg-slate-50 shadow-sm text-slate-800' : 'text-slate-400 hover:text-slate-600'">Tất cả</button>
            <button @click="activeFilter = 'upcoming'" class="px-8 py-3 rounded-xl text-[12px] font-black transition-all duration-300" :class="activeFilter === 'upcoming' ? 'bg-slate-50 shadow-sm text-slate-800' : 'text-slate-400 hover:text-slate-600'">Sắp đến hạn</button>
            <button @click="activeFilter = 'completed'" class="px-8 py-3 rounded-xl text-[12px] font-black transition-all duration-300" :class="activeFilter === 'completed' ? 'bg-slate-50 shadow-sm text-slate-800' : 'text-slate-400 hover:text-slate-600'">Đã nộp</button>
            <button @click="activeFilter = 'overdue'" class="px-8 py-3 rounded-xl text-[12px] font-black transition-all duration-300" :class="activeFilter === 'overdue' ? 'bg-slate-50 shadow-sm text-slate-800' : 'text-slate-400 hover:text-slate-600'">Quá hạn</button>
          </div>
        </div>

        <!-- Danh sách Assignments -->
        <div class="flex-1 overflow-y-auto no-scrollbar px-12 py-4 pb-12 space-y-6 z-10">

          <!-- Không có bài tập -->
          <div v-if="filteredAssignments.length === 0" class="py-20 text-center">
            <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-200 shadow-sm border border-slate-50">
              <i class="fa-solid fa-clipboard-check text-2xl"></i>
            </div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-relaxed">Không tìm thấy bài tập nào<br>trong mục này</p>
          </div>

          <div
            v-for="(assignment, aIdx) in filteredAssignments"
            :key="assignment.id"
            class="group bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm hover:shadow-xl hover:border-emerald-100 transition-all duration-500 flex flex-col md:flex-row items-center justify-between gap-8 w-full block"
          >
            <div class="flex-1 min-w-0 w-full">
              <!-- Top Row Info -->
              <div class="flex items-center gap-4 mb-5">
                <!-- Course Level Badge -->
                <span class="px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.15em] rounded-lg border"
                  :class="assignment.status === 'completed' ? 'bg-slate-50 text-slate-400 border-slate-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100/50'">
                  {{ selectedLessonCourse?.level || 'IELTS ACADEMIC' }}
                </span>
                
                <!-- Status/Deadline Text -->
                <span v-if="assignment.status === 'completed' || assignment.status === 'pending_grading'" class="text-[11px] font-bold text-slate-400">
                  Đã hoàn thành {{ formatDate(assignment.deadline) }}
                </span>
                <span v-else-if="assignment.status === 'in_progress'" class="text-[11px] font-bold text-red-500 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                  Cần nộp trước {{ formatTime(assignment.deadline) }}
                </span>
                <span v-else class="text-[11px] font-bold text-slate-400">
                  Hạn nộp: {{ formatDate(assignment.deadline) }}
                </span>
              </div>

              <!-- Title -->
              <h3 class="text-xl md:text-2xl font-headline font-black text-slate-900 leading-tight mb-6 group-hover:text-emerald-600 transition-colors">
                {{ assignment.title }}
              </h3>

              <!-- Bottom Meta layout -->
              <div class="flex items-center gap-6">
                <!-- Time -->
                <div class="flex items-center gap-2 text-[12px] font-bold text-slate-400">
                  <i class="fa-solid fa-stopwatch text-[16px]" :class="assignment.status === 'completed' ? 'text-slate-300' : 'text-slate-400'"></i>
                  <span class="pt-0.5">45 phút</span>
                </div>
                <!-- Type -->
                <div class="flex items-center gap-2 text-[12px] font-bold" :class="assignment.status === 'completed' ? 'text-emerald-500' : 'text-slate-400'">
                  <i :class="assignment.status === 'completed' ? 'fa-solid fa-medal' : 'fa-solid fa-file-signature'" class="text-[16px]"></i>
                  <span class="pt-0.5">{{ assignment.status === 'completed' ? 'Trắc nghiệm' : getTypeLabel(assignment.type) }}</span>
                </div>
              </div>
            </div>

            <!-- Right Side CTA -->
            <div class="shrink-0 flex items-center gap-8 w-full md:w-auto mt-4 md:mt-0 justify-between md:justify-end border-t md:border-t-0 border-slate-100 pt-6 md:pt-0">
              
              <!-- Thể hiện kết quả nếu đã làm -->
              <div v-if="assignment.status === 'completed' || assignment.status === 'pending_grading'" class="flex flex-col items-center mr-2">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1.5">Kết quả</span>
                <span v-if="assignment.status === 'pending_grading'" class="text-[12px] font-black text-amber-500 uppercase">Chờ chấm</span>
                <span v-else class="text-[2.5rem] font-headline font-black text-emerald-500 leading-none">{{ assignment.score ?? 0 }}</span>
              </div>

              <!-- Nút bấm -->
              <button v-if="assignment.status === 'completed' || assignment.status === 'pending_grading'" @click="launchExercise(assignment)" class="px-8 py-3.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-[1.25rem] text-[13px] font-black hover:bg-emerald-100 hover:text-emerald-800 transition-colors shadow-sm">
                {{ assignment.status === 'pending_grading' ? 'Xem lại bài' : 'Làm lại' }}
              </button>
              <button v-else @click="launchExercise(assignment)" class="px-10 py-3.5 text-[13px] font-black tracking-widest text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 hover:-translate-y-0.5 transition-all duration-300 rounded-[1.25rem] outline-none">
                Làm bài ngay
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ═══ EXERCISE OVERLAY: Dispatch to MixedQuizPlayer ═══ -->
    <MixedQuizPlayer
      v-if="activeExercise"
      :quiz="activeExercise.quiz"
      @close="closeExercise"
      @submitted="closeExercise"
    />

  </div>
</template>

<script setup>
/**
 * UserAssignments.vue
 * Trang bài tập cho học viên – hiển thị lessons + assignments thật từ API.
 * Bảng xếp hạng vẫn dùng mock data.
 */
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'
import { notifyError } from '../../utils/notify'
import MixedQuizPlayer from './quiz-exercises/MixedQuizPlayer.vue'

// ── Props từ UserLayout ──
const props = defineProps({
  user: {
    type: Object,
    default: () => ({ full_name: 'Học viên', email: '', role: 'student' })
  }
})

const router = useRouter()
const route = useRoute()

// ── State ──
const isLoading = ref(true)
const errorMessage = ref('')
const courses = ref([])
const selectedCourseId = ref(null)
const selectedLesson = ref(null)
const selectedLessonCourse = ref(null)
const activeFilter = ref('all')
const activeExercise = ref(null) // { quiz, courseName }

// ── Computed ──
const userName = computed(() => props.user?.full_name || 'Học viên')
const activeCourse = computed(() => courses.value.find(c => c.course_id === selectedCourseId.value) || null)

const filteredAssignments = computed(() => {
  if (!selectedLesson.value) return []
  const list = selectedLesson.value.assignments || []
  if (activeFilter.value === 'all') return list
  if (activeFilter.value === 'completed') return list.filter(a => a.status === 'completed')
  if (activeFilter.value === 'upcoming') return list.filter(a => a.status === 'in_progress')
  if (activeFilter.value === 'overdue') return list.filter(a => a.status === 'locked')
  return list
})

// ── Formatters ──
const formatTime = (datetime) => {
  if (!datetime) return '23:59'
  const d = new Date(datetime)
  return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

const formatDate = (datetime) => {
  if (!datetime) return '22 Th10'
  const d = new Date(datetime)
  const month = d.getMonth() + 1
  return d.getDate() + ' Th' + month
}

// ── Mock Data: Leaderboard (giữ nguyên) ──
const leaderboardData = ref([])

// ── Methods ──

/**
 * Fetch assignments data from API
 */
const fetchAssignments = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await apiFetch('user/assignments.php')
    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }
    const result = await response.json()
    if (result.status === 'success') {
      const newCourses = result.data.courses || []
      courses.value = newCourses
      
      if (route.query.courseId) {
        selectedCourseId.value = parseInt(route.query.courseId)
      } else if (newCourses.length === 1) {
        selectedCourseId.value = newCourses[0].course_id
      }
      
      // Preserve active lesson if modal is open to update progress reactively
      if (selectedLesson.value) {
        for (const c of newCourses) {
          const l = c.lessons?.find(x => x.id === selectedLesson.value.id)
          if (l) {
            selectedLesson.value = l
            break
          }
        }
      }
      
      // Xử lý Leaderboard
      if (Array.isArray(result.data.leaderboard)) {
        leaderboardData.value = result.data.leaderboard.map(s => ({
          ...s,
          is_current: String(s.full_name) === String(userName.value) // Fallback check since we don't have user.id in props here easily
        }))
      }
    } else {
      errorMessage.value = result.message || 'Không thể tải dữ liệu bài tập.'
    }
  } catch {
    errorMessage.value = 'Lỗi kết nối máy chủ. Vui lòng kiểm tra lại.'
    notifyError('Mất kết nối tới hệ thống máy chủ.')
  } finally {
    isLoading.value = false
  }
}

const openLessonDetail = (lesson, course) => {
  selectedLesson.value = lesson
  selectedLessonCourse.value = course
  activeFilter.value = 'all'
}

const closeDetail = () => {
  selectedLesson.value = null
  selectedLessonCourse.value = null
}

const launchExercise = (assignment) => {
  if (String(assignment.id).startsWith('wa_')) {
    if (assignment.status === 'completed') {
      notifySuccess(`Giảng viên đã chấm bài của bạn với số điểm: ${assignment.score || 0}`, 'Đã có kết quả')
    } else if (assignment.status === 'pending_grading') {
      notifyWarning('Bài luận của bạn đã được nộp và đang chờ giảng viên chấm điểm.', 'Chờ chấm bài')
    } else {
      notifyWarning('Vui lòng nộp bài viết này qua hệ thống Nộp bài tập chuyên dụng (đang hoàn thiện).', 'Đảm bảo chất lượng')
    }
    return
  }
  activeExercise.value = {
    quiz: assignment,
    courseName: selectedLessonCourse.value?.course_title || 'Khóa học',
    lessonTitle: selectedLesson.value?.title || 'Bài học'
  }
}

const closeExercise = async () => {
  activeExercise.value = null
  await fetchAssignments()
  window.dispatchEvent(new Event('refresh-dashboard'))
}

const getTypeBadgeClass = (type) => {
  switch (type) {
    case 'pre_class': return 'bg-blue-50 text-blue-500 border border-blue-100'
    case 'post_class': return 'bg-amber-50 text-amber-600 border border-amber-100'
    default: return 'bg-slate-50 text-slate-500 border border-slate-100'
  }
}

const getTypeLabel = (type) => {
  switch (type) {
    case 'pre_class': return 'Pre-class'
    case 'post_class': return 'Post-class'
    default: return 'General'
  }
}

// ── Lifecycle ──
onMounted(() => {
  fetchAssignments()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
