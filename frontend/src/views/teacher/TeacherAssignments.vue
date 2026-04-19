<template>
  <div class="px-10 py-12 min-h-screen bg-slate-50/30 animate__animated animate__fadeIn">
    
    <!-- ── Header (Tiêu đề & Thống kê hàng chờ) ── -->
    <div class="mb-12">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-3">Đánh giá & Chấm điểm</p>
          <h1 class="text-4xl font-headline font-black text-slate-900 tracking-tight leading-none">
            {{ currentTab?.label || 'Quản lý Bài tập' }}
          </h1>
          <p class="text-slate-400 text-sm mt-3 font-medium">
            Hiện có <span class="font-black text-slate-700">{{ stats.pending }} bài đang chờ chấm</span> và 
            <span class="font-black text-slate-700">{{ stats.graded }} bài đã hoàn tất</span>.
          </p>
        </div>
        
        <!-- Filter Class Selection -->
        <div class="flex items-center gap-3">
          <div class="relative group">
            <i class="fa-solid fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
            <select v-model="filterClass" @change="loadQueue"
              class="pl-11 pr-10 py-3.5 rounded-2xl border border-slate-100 text-[11px] font-black uppercase tracking-widest text-slate-600 bg-white shadow-sm outline-none focus:ring-4 focus:ring-emerald-400/5 focus:border-emerald-200 transition-all appearance-none cursor-pointer">
              <option value="">Tất cả lớp học</option>
              <option v-for="cls in myClasses" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
            <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Navigation Tabs ── -->
    <div class="flex items-center gap-3 mb-10 bg-white border border-slate-50 rounded-[1.5rem] p-1.5 w-fit shadow-sm">
      <button 
        @click="$router.push({ query: { tab: 'queue' } })"
        class="px-8 py-3 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95"
        :class="activeTab === 'queue' ? 'bg-slate-900 text-white shadow-xl shadow-slate-200' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50'"
      >
        Hàng chờ chấm
      </button>
      <button 
        @click="$router.push({ query: { tab: 'history' } })"
        class="px-8 py-3 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95"
        :class="activeTab === 'history' ? 'bg-slate-900 text-white shadow-xl shadow-slate-200' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50'"
      >
        Lịch sử đã chấm
      </button>
    </div>

    <!-- ══ Nội dung chính theo Tab ══ -->

    <!-- TAB: Hàng chờ chấm điểm -->
    <template v-if="activeTab === 'queue'">
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-32 text-center">
        <div class="w-12 h-12 border-4 border-emerald-50 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-300 animate-pulse">Đang tải danh sách bài nộp...</p>
      </div>

      <div v-else-if="queue.length === 0"
        class="flex flex-col items-center justify-center py-32 bg-white rounded-[3.5rem] border border-slate-50 shadow-sm relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        <div class="w-20 h-20 rounded-[2rem] bg-emerald-50 flex items-center justify-center text-emerald-500 mb-6">
          <i class="fa-solid fa-circle-check text-4xl"></i>
        </div>
        <p class="font-black text-lg text-slate-700">Tất cả bài tập đã được chấm!</p>
        <p class="text-[13px] mt-1 text-slate-400 font-bold">Hiện không có bài nộp nào mới trong hàng chờ.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in queue" :key="sub.id"
          @click="openGrader(sub.id)"
          class="bg-white rounded-[2.5rem] border border-slate-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-500 p-8 flex items-center gap-8 cursor-pointer group">
          
          <div class="relative shrink-0">
            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(sub.student_name)}&background=f0fdf4&color=10b981&bold=true&size=56`"
              class="w-14 h-14 rounded-2xl shadow-sm group-hover:scale-110 transition-transform duration-500" :alt="sub.student_name">
            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white"></div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                {{ sub.class_name }}
              </span>
              <span class="text-[10px] text-slate-400 font-bold flex items-center gap-1.5">
                <i class="fa-regular fa-clock opacity-50"></i> {{ formatDate(sub.submitted_at) }}
              </span>
            </div>
            <h3 class="font-headline font-black text-lg text-slate-800 leading-none truncate mb-1">
              {{ sub.student_name }}
            </h3>
            <p class="text-xs text-slate-400 font-bold truncate">Bài tập: <span class="text-slate-500 font-black">"{{ sub.assignment_title }}"</span></p>
          </div>

          <div class="text-center shrink-0 border-x border-slate-50 px-8">
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Ước tính</p>
            <p class="text-lg font-headline font-black text-slate-700">~{{ sub.approx_word_count }} <span class="text-[10px] text-slate-300">TỪ</span></p>
          </div>

          <div class="shrink-0 min-w-[120px] text-right">
             <span v-if="sub.score !== null"
              class="text-3xl font-headline font-black"
              :class="sub.score >= 7 ? 'text-emerald-500' : sub.score >= 5 ? 'text-amber-500' : 'text-red-400'">
              {{ sub.score.toFixed(1) }}
            </span>
            <div v-else class="flex flex-col items-end">
              <span class="px-5 py-2.5 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-amber-100 group-hover:bg-amber-500 group-hover:text-white group-hover:border-amber-500 transition-all">
                Đang chờ chấm
              </span>
            </div>
          </div>

          <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm">
            <i class="fa-solid fa-chevron-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
          </div>
        </div>
      </div>
    </template>

    <!-- TAB: Lịch sử nộp bài -->
    <template v-else-if="activeTab === 'history'">
      <div v-if="isLoadingHistory" class="flex flex-col items-center justify-center py-32 text-center">
        <div class="w-12 h-12 border-4 border-emerald-50 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">Đang tải lịch sử chấm điểm...</p>
      </div>

      <div v-else-if="history.length === 0"
        class="flex flex-col items-center justify-center py-32 bg-white rounded-[3.5rem] border border-slate-50 shadow-sm">
        <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
          <i class="fa-solid fa-clock-rotate-left text-4xl"></i>
        </div>
        <p class="font-black text-lg text-slate-700">Chưa có bài nào được chấm</p>
        <p class="text-[13px] mt-1 text-slate-400 font-bold">Lịch sử chấm điểm sẽ xuất hiện tại đây sau khi bạn gửi phản hồi.</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div v-for="sub in history" :key="sub.id"
          @click="openGrader(sub.id)"
          class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-500 p-6 flex items-center gap-6 cursor-pointer group">
          
          <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(sub.student_name)}&background=f1f5f9&color=64748b&bold=true&size=48`"
            class="w-12 h-12 rounded-2xl shrink-0 group-hover:scale-105 transition-transform" :alt="sub.student_name">
          
          <div class="flex-1 min-w-0">
            <h4 class="font-headline font-black text-[15px] text-slate-900 truncate leading-none mb-2 group-hover:text-emerald-600 transition-colors">
              {{ sub.student_name }}
            </h4>
            <p class="text-[11px] text-slate-400 font-bold truncate uppercase tracking-tight">
              {{ sub.assignment_title }} · {{ sub.class_name }}
            </p>
          </div>

          <div class="text-right shrink-0">
            <div class="text-2xl font-headline font-black leading-none mb-1"
              :class="sub.score >= 7 ? 'text-emerald-500' : sub.score >= 5 ? 'text-amber-500' : 'text-red-400'">
              {{ parseFloat(sub.score).toFixed(1) }}
            </div>
            <p class="text-[9px] text-slate-300 font-black uppercase tracking-widest">{{ formatDate(sub.submitted_at) }}</p>
          </div>
        </div>
      </div>
    </template>

    <!-- ══ MODAL: Giao diện chấm điểm (Premium Grader) ══ -->
    <div v-if="gradingModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6 sm:p-10 animate__animated animate__fadeIn">
      <div 
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"
        @click="gradingModal = false"
      ></div>

      <div class="bg-white w-full max-w-7xl h-full max-h-[90vh] rounded-[3.5rem] shadow-2xl flex relative overflow-hidden animate__animated animate__zoomIn animate__faster">
        
        <!-- Cột TRÁI: Nội dung bài luận -->
        <div class="flex-1 flex flex-col overflow-hidden border-r border-slate-50 bg-white">
          <!-- Top Toolbar -->
          <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between shrink-0 bg-white/80 backdrop-blur-sm z-10 sticky top-0">
            <div class="flex items-center gap-6">
              <button 
                @click="gradingModal = false" 
                class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all flex items-center justify-center active:scale-90"
              >
                <i class="fa-solid fa-arrow-left"></i>
              </button>
              <div>
                <h2 class="font-headline font-black text-2xl text-slate-900 leading-none">Đánh giá bài làm</h2>
                <p class="text-[11px] text-slate-400 mt-2 font-bold uppercase tracking-widest">
                  Bài tập: <span class="text-slate-600">{{ selectedSub?.assignment_title }}</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
               <button @click="() => window.print()" class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-all hover:shadow-sm">
                  <i class="fa-solid fa-download text-sm"></i>
               </button>
            </div>
          </div>

          <!-- Nội dung chính -->
          <div class="flex-1 overflow-y-auto px-10 py-10 max-w-4xl mx-auto w-full">
            <div class="bg-emerald-50/50 rounded-[2.5rem] p-10 mb-10 border border-emerald-100/50 shadow-sm relative overflow-hidden group">
              <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-100/30 rounded-full blur-2xl"></div>
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-lightbulb"></i> Đề bài / Prompt
              </p>
              <p class="text-[15px] text-emerald-900/80 font-bold leading-relaxed italic">{{ selectedSub?.prompt || 'Không có đề bài cụ thể.' }}</p>
            </div>

            <div 
              v-if="selectedSub?.submission_content"
              class="prose prose-slate max-w-none px-4 py-4"
            >
              <div 
                class="text-[17px] leading-[2] text-slate-700 font-medium font-body submission-text"
                v-html="selectedSub.submission_content"
              ></div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-24 text-slate-300">
               <i class="fa-solid fa-file-circle-xmark text-5xl mb-6 opacity-20"></i>
               <p class="font-black text-lg">Nội dung bài viết trống</p>
               <p class="text-sm mt-1 font-bold">Học viên nộp bài nhưng không có nội dung văn bản.</p>
            </div>
          </div>
        </div>

        <!-- Cột PHẢI: Panel Chấm điểm -->
        <div class="w-[420px] shrink-0 bg-slate-50/50 flex flex-col overflow-y-auto">
          
          <!-- Thông tin học viên Card -->
          <div class="p-8 border-b border-slate-50">
            <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-white flex flex-col gap-6">
               <div class="flex items-center gap-4">
                 <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(selectedSub?.student_name || '')}&background=10b981&color=fff&bold=true&size=64`"
                  class="w-16 h-16 rounded-2xl shadow-emerald-100 shadow-lg">
                 <div class="flex-1 min-w-0">
                    <h4 class="font-headline font-black text-lg text-slate-900 truncate tracking-tight">{{ selectedSub?.student_name }}</h4>
                    <p class="text-[11px] text-emerald-600 font-black uppercase tracking-widest mt-1">Lớp: {{ selectedSub?.class_name }}</p>
                 </div>
               </div>
               <div class="grid grid-cols-2 gap-3">
                  <div class="bg-slate-50 rounded-2xl p-4 text-center">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Nộp lúc</p>
                    <p class="text-xs font-black text-slate-700">{{ formatDateTime(selectedSub?.submitted_at) }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-2xl p-4 text-center">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Word Count</p>
                    <p class="text-xs font-black text-slate-700">{{ selectedSub?.word_count || 0 }} từ</p>
                  </div>
               </div>
            </div>
          </div>

          <!-- Tiêu chí & Điểm số -->
          <div class="p-8 flex-1 space-y-10">
            
            <div class="space-y-8">
              <div v-for="rubric in rubricItems" :key="rubric.key" class="group">
                <div class="flex justify-between items-end mb-4">
                  <span class="text-xs font-black text-slate-600 uppercase tracking-widest">{{ rubric.label }}</span>
                  <span class="text-2xl font-headline font-black transition-colors"
                    :class="rubric.score >= 7 ? 'text-emerald-500' : rubric.score >= 5 ? 'text-amber-500' : 'text-red-400'">
                    {{ parseFloat(rubric.score).toFixed(1) }}
                  </span>
                </div>
                <!-- Custom Slider -->
                <div class="relative h-2.5 bg-white rounded-full border border-slate-100 shadow-sm overflow-hidden group-hover:border-emerald-200 transition-colors">
                  <input type="range" min="0" max="10" step="0.5" v-model.number="rubric.score"
                    :disabled="isGraded"
                    class="absolute inset-0 w-full h-full opacity-0 z-50 transition-all"
                    :class="isGraded ? 'cursor-not-allowed' : 'cursor-pointer'">
                  <div 
                    class="h-full rounded-full transition-all duration-300 relative"
                    :class="rubric.score >= 7 ? 'bg-emerald-500' : rubric.score >= 5 ? 'bg-amber-400' : 'bg-red-400'"
                    :style="`width: ${(rubric.score / 10) * 100}%`"
                  >
                    <div class="absolute top-0 right-0 h-full w-2 bg-white/20 blur-sm"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tổng điểm Highlight -->
            <div class="bg-white rounded-[3rem] p-8 border border-white shadow-xl shadow-slate-200/50 flex items-center justify-between group">
               <div>
                  <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2">Điểm tổng hợp</p>
                  <p class="text-xs font-black transition-colors" 
                    :class="aggregateScore >= 7 ? 'text-emerald-500' : 'text-amber-500'">
                    {{ aggregateScore >= 8 ? 'Xuất sắc' : aggregateScore >= 6.5 ? 'Tốt' : aggregateScore >= 5 ? 'Đạt yêu cầu' : 'Cần cố gắng' }}
                  </p>
               </div>
               <div 
                class="w-20 h-20 rounded-[2rem] flex items-center justify-center font-headline font-black text-3xl shadow-2xl transition-all group-hover:scale-105 duration-500"
                :class="aggregateScore >= 7 ? 'bg-emerald-500 text-white shadow-emerald-500/20' : aggregateScore >= 5 ? 'bg-amber-400 text-white shadow-amber-400/20' : 'bg-red-500 text-white shadow-red-500/20'"
               >
                 {{ aggregateScore.toFixed(1) }}
               </div>
            </div>

            <!-- Nhận xét TextArea -->
            <div class="space-y-4">
              <h3 class="font-headline font-black text-sm text-slate-900 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-500"></i> Phản hồi chi tiết
              </h3>
              <textarea v-model="feedbackText" rows="6"
                :disabled="isGraded"
                placeholder="Nhập nhận xét xây dựng cho học viên..."
                class="w-full resize-none rounded-[2rem] border-2 border-transparent bg-white px-6 py-6 text-[13px] font-bold text-slate-700 leading-[1.8] outline-none transition-all shadow-sm placeholder:text-slate-200"
                :class="isGraded ? 'opacity-70 bg-slate-50' : 'focus:border-emerald-500/20 focus:ring-4 focus:ring-emerald-500/5'">
              </textarea>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="p-8 pt-0 flex gap-4">
            <template v-if="!isGraded">
              <button 
                @click="saveDraft"
                class="flex-1 py-4 rounded-2xl bg-white border border-slate-100 text-slate-500 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 hover:text-slate-800 transition-all active:scale-95"
              >
                Lưu bản nháp
              </button>
              <button 
                @click="submitGrade" 
                :disabled="isGrading"
                class="flex-[1.5] py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 hover:-translate-y-1 transition-all disabled:opacity-50 flex items-center justify-center gap-3 active:scale-95"
              >
                <span v-if="isGrading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                Gửi &amp; Công bố điểm
              </button>
            </template>
            <template v-else>
               <button 
                @click="gradingModal = false"
                class="w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest text-white bg-slate-800 hover:bg-slate-900 shadow-lg shadow-slate-900/20 hover:-translate-y-1 transition-all active:scale-95 flex items-center justify-center gap-2"
              >
                Đóng
              </button>
            </template>
          </div>

          <div v-if="gradeSuccess" class="px-8 pb-8 transition-all animate__animated animate__fadeInUp">
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5 flex items-center gap-4">
              <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check"></i>
              </div>
              <p class="text-xs font-black text-emerald-700">Đã gửi điểm thành công!</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'

const route = useRoute()
const router = useRouter()

// --- Active tab logic ---
const activeTab = computed(() => route.query.tab || 'queue')

const currentTab = computed(() => {
  const map = {
    queue:    { label: 'Hàng chờ chấm bài' },
    history:  { label: 'Lịch sử đánh giá' },
  }
  return map[activeTab.value] || null
})

// --- State ---
const isLoading = ref(false)
const isLoadingHistory = ref(false)
const isGrading = ref(false)
const gradeSuccess = ref(false)
const gradingModal = ref(false)

const queue = ref([])
const history = ref([])
const myClasses = ref([])
const filterClass = ref('')
const stats = ref({ pending: 0, graded: 0 })
const selectedSub = ref(null)

const feedbackText = ref('')
const rubricItems = ref([
  { key: 'grammar',  label: 'Độ chính xác ngữ pháp',    score: 7.0 },
  { key: 'cohesion', label: 'Mạch lạc & Liên kết',       score: 7.0 },
  { key: 'lexical',  label: 'Tài nguyên từ vựng',         score: 7.0 },
  { key: 'task',     label: 'Hoàn thành yêu cầu đề bài', score: 7.0 },
])

// --- Computed ---
const isGraded = computed(() => selectedSub.value && selectedSub.value.status === 'completed')

const aggregateScore = computed(() => {
  const total = rubricItems.value.reduce((s, r) => s + Number(r.score), 0)
  return total / rubricItems.value.length
})

// --- Methods ---
const formatDate = (dt) => {
  if (!dt) return ''
  return new Date(dt).toLocaleDateString('vi-VN', { 
    day: '2-digit', month: '2-digit', year: 'numeric' 
  })
}

const formatDateTime = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('vi-VN') + ' | ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

const loadClasses = async () => {
  try {
    const res = await apiFetch('teacher/classes.php')
    const result = await res.json()
    if (result.status === 'success') myClasses.value = result.data
  } catch {}
}

const loadQueue = async () => {
  isLoading.value = true
  try {
    const url = filterClass.value
      ? `teacher/grading.php?mode=queue&class_id=${filterClass.value}`
      : 'teacher/grading.php?mode=queue'
    const res = await apiFetch(url)
    const result = await res.json()
    if (result.status === 'success') {
      queue.value = result.data
      stats.value = result.stats
    } else notifyError(result.message)
  } catch { notifyError('Lỗi kết nối máy chủ.') }
  finally { isLoading.value = false }
}

const loadHistory = async () => {
  isLoadingHistory.value = true
  try {
    const res = await apiFetch('teacher/grading.php?mode=history')
    const result = await res.json()
    if (result.status === 'success') {
      history.value = result.data
      stats.value = result.stats
    }
  } catch {}
  finally { isLoadingHistory.value = false }
}

const openGrader = async (subId) => {
  try {
    const res = await apiFetch(`teacher/grading.php?submission_id=${subId}`)
    const result = await res.json()
    if (result.status === 'success') {
      selectedSub.value = result.data
      gradingModal.value = true
      feedbackText.value = result.data.feedback || ''
      gradeSuccess.value = false
      if (result.data.rubric_data) {
        // rubric_data có thể là string JSON hoặc object
        const rData = typeof result.data.rubric_data === 'string'
          ? JSON.parse(result.data.rubric_data)
          : result.data.rubric_data
        rubricItems.value.forEach(item => {
          if (rData[item.key] !== undefined) item.score = rData[item.key]
        })
      } else {
        const avg = result.data.score ?? 7
        rubricItems.value.forEach(r => r.score = avg)
      }
    } else notifyError(result.message)
  } catch { notifyError('Không thể tải bài nộp.') }
}

const saveDraft = async () => {
  await doGrade(false)
}

const submitGrade = async () => {
  if (!feedbackText.value.trim()) {
    notifyWarning('Vui lòng nhập nhận xét cho học viên trước khi gửi.')
    return
  }
  await doGrade(true)
}

const doGrade = async (notify) => {
  isGrading.value = true
  try {
    const res = await apiFetch('teacher/grading.php', {
      method: 'PUT',
      body: JSON.stringify({
        submission_id: selectedSub.value.id,
        score: aggregateScore.value,
        feedback: feedbackText.value,
        rubric: rubricItems.value.reduce((acc, r) => { acc[r.key] = r.score; return acc }, {}),
      })
    })
    const result = await res.json()
    if (result.status === 'success') {
      gradeSuccess.value = true
      notifySuccess(notify ? 'Đã gửi điểm và phản hồi cho học viên!' : 'Đã lưu bản nháp thành công.')
      if(notify) {
        stats.value.pending = Math.max(0, stats.value.pending - 1)
        stats.value.graded++
        queue.value = queue.value.filter(s => s.id !== selectedSub.value.id)
        setTimeout(() => gradingModal.value = false, 1500)
      }
    } else notifyError(result.message)
  } catch { notifyError('Lỗi kết nối máy chủ.') }
  finally { isGrading.value = false }
}

// --- Watchers ---
watch(activeTab, (tab) => {
  if (tab === 'history') loadHistory()
  if (tab === 'queue') loadQueue()
}, { immediate: false })

// --- Lifecycle ---
onMounted(() => {
  loadClasses()
  loadQueue()
})
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap");

.font-headline { font-family: 'Manrope', sans-serif; }
body { font-family: 'Inter', sans-serif; }

.submission-text {
  white-space: pre-wrap;
  word-break: break-word;
}

/* Custom range input styling */
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 24px;
  width: 24px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  border: 2px solid #10b981;
}

.prose h4 { font-weight: 900; color: #1e293b; margin-top: 2rem; margin-bottom: 1rem; }
</style>
