<template>
  <div class="px-10 py-8 min-h-screen font-body">

    <!-- ── Header ── -->
    <div class="mb-8">
      <div class="flex items-end justify-between">
        <div>
          <h1 class="text-3xl font-headline font-black text-slate-900 tracking-tight">
            {{ currentTab?.label || 'Bài tập & Chấm điểm' }}
          </h1>
          <p class="text-slate-400 text-sm mt-1">
            <span class="font-black text-slate-700">{{ stats.pending }}</span> bài đang chờ chấm ·
            <span class="font-black text-slate-700">{{ stats.graded }}</span> bài đã chấm xong
          </p>
        </div>
        <div class="flex items-center gap-3">
          <select v-model="filterClass" @change="loadQueue"
            class="px-4 py-2.5 rounded-2xl border border-slate-100 text-sm font-bold text-slate-600 bg-white shadow-sm outline-none focus:ring-2 focus:ring-emerald-400/20">
            <option value="">Tất cả lớp</option>
            <option v-for="cls in myClasses" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ══ TAB: Hàng chờ chấm điểm ══ -->
    <template v-if="activeTab === 'queue'">
      <div v-if="isLoading" class="flex items-center justify-center py-24">
        <div class="w-10 h-10 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin"></div>
      </div>

      <div v-else-if="queue.length === 0"
        class="flex flex-col items-center justify-center py-28 bg-white rounded-[2.5rem] border border-slate-50 text-slate-400">
        <i class="fa-solid fa-circle-check text-5xl mb-4 text-emerald-300"></i>
        <p class="font-black text-lg">Không có bài nào chờ chấm!</p>
        <p class="text-sm mt-1 opacity-70">Tất cả bài tập đã được xử lý xong</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="sub in queue" :key="sub.id"
          @click="openGrader(sub.id)"
          class="bg-white rounded-[2rem] border border-slate-50 shadow-sm hover:shadow-xl hover:border-emerald-100 hover:-translate-y-0.5 transition-all duration-300 p-6 flex items-center gap-6 cursor-pointer group">
          <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(sub.student_name)}&background=d1fae5&color=059669&bold=true&size=40`"
            class="w-12 h-12 rounded-2xl shrink-0 shadow-sm" :alt="sub.student_name">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600">{{ sub.class_name }}</span>
              <span class="text-[11px] text-slate-400 font-medium">{{ formatDate(sub.submitted_at) }}</span>
            </div>
            <h3 class="font-headline font-black text-[15px] text-slate-900 leading-tight truncate">{{ sub.student_name }}</h3>
            <p class="text-xs text-slate-400 font-medium truncate mt-0.5">{{ sub.assignment_title }}</p>
          </div>
          <div class="text-center shrink-0">
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Số từ</p>
            <p class="text-lg font-headline font-black text-slate-700">~{{ sub.approx_word_count }}</p>
          </div>
          <div class="shrink-0">
            <span v-if="sub.score !== null"
              class="text-2xl font-headline font-black"
              :class="sub.score >= 7 ? 'text-emerald-500' : sub.score >= 5 ? 'text-amber-500' : 'text-red-400'">
              {{ sub.score.toFixed(1) }}
            </span>
            <span v-else class="px-4 py-2 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-amber-100">
              Chưa chấm
            </span>
          </div>
          <i class="fa-solid fa-chevron-right text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all text-xs shrink-0"></i>
        </div>
      </div>
    </template>

    <!-- ══ TAB: Lịch sử nộp bài ══ -->
    <template v-else-if="activeTab === 'history'">
      <div v-if="isLoadingHistory" class="flex items-center justify-center py-24">
        <div class="w-10 h-10 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin"></div>
      </div>
      <div v-else-if="history.length === 0"
        class="flex flex-col items-center justify-center py-28 bg-white rounded-[2.5rem] border border-slate-50 text-slate-400">
        <i class="fa-solid fa-clock-rotate-left text-5xl mb-4 opacity-30"></i>
        <p class="font-black text-lg">Chưa có lịch sử chấm điểm</p>
      </div>
      <div v-else class="space-y-3">
        <div v-for="sub in history" :key="sub.id"
          @click="openGrader(sub.id)"
          class="bg-white rounded-[2rem] border border-slate-50 shadow-sm hover:shadow-md transition-all p-5 flex items-center gap-5 cursor-pointer">
          <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(sub.student_name)}&background=f1f5f9&color=64748b&bold=true&size=40`"
            class="w-10 h-10 rounded-2xl shrink-0" :alt="sub.student_name">
          <div class="flex-1 min-w-0">
            <p class="font-headline font-black text-[14px] text-slate-900 truncate">{{ sub.student_name }}</p>
            <p class="text-xs text-slate-400 font-medium truncate">{{ sub.assignment_title }} · {{ sub.class_name }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-xl font-headline font-black"
              :class="sub.score >= 7 ? 'text-emerald-500' : sub.score >= 5 ? 'text-amber-500' : 'text-red-400'">
              {{ parseFloat(sub.score).toFixed(1) }}
            </p>
            <p class="text-[9px] text-slate-400 font-bold uppercase">{{ formatDate(sub.submitted_at) }}</p>
          </div>
        </div>
      </div>
    </template>

    <!-- ══ TAB: Quản lý tiêu chí ══ -->
    <template v-else-if="activeTab === 'rubric'">
      <div class="flex flex-col items-center justify-center py-28 bg-white rounded-[2.5rem] border border-slate-50 text-slate-400">
        <i class="fa-solid fa-ruler-combined text-5xl mb-4 opacity-30"></i>
        <p class="font-black text-lg">Quản lý tiêu chí chấm điểm</p>
        <p class="text-sm mt-1 opacity-70">Chức năng đang được phát triển</p>
      </div>
    </template>

    <!-- ══ TAB: Thống kê ══ -->
    <template v-else-if="activeTab === 'analytics'">
      <div class="flex flex-col items-center justify-center py-28 bg-white rounded-[2.5rem] border border-slate-50 text-slate-400">
        <i class="fa-solid fa-chart-line text-5xl mb-4 opacity-30"></i>
        <p class="font-black text-lg">Thống kê chấm điểm</p>
        <p class="text-sm mt-1 opacity-70">Chức năng đang được phát triển</p>
      </div>
    </template>

    <!-- ══ MODAL: Giao diện chấm điểm ══ -->
    <div v-if="gradingModal" class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm flex" @click.self="gradingModal = false">
      <div class="bg-white w-full max-w-6xl mx-auto my-4 rounded-[2.5rem] shadow-2xl flex overflow-hidden animate-in zoom-in-95 duration-300">

        <!-- Bài luận -->
        <div class="flex-1 flex flex-col overflow-hidden border-r border-slate-100">
          <!-- Top -->
          <div class="bg-white border-b border-slate-100 px-8 py-5 flex items-center justify-between shrink-0">
            <div>
              <button @click="gradingModal = false" class="flex items-center gap-2 text-xs font-black text-slate-400 hover:text-emerald-500 transition-colors mb-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
              </button>
              <h2 class="font-headline font-black text-lg text-slate-900 leading-none">Đánh giá bài luận</h2>
              <p class="text-xs text-slate-400 mt-0.5">Bài tập: "{{ selectedSub?.assignment_title }}"</p>
            </div>
            <div class="flex gap-2">
              <button @click="() => window.print()" class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-all">
                <i class="fa-solid fa-print text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Nội dung bài -->
          <div class="flex-1 overflow-y-auto px-8 py-8 max-w-3xl mx-auto w-full">
            <div class="bg-slate-50 rounded-[1.5rem] p-5 mb-6 border border-slate-100">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Đề bài</p>
              <p class="text-sm text-slate-700 font-medium leading-relaxed">{{ selectedSub?.prompt || 'Không có đề bài' }}</p>
            </div>
            <div v-if="selectedSub?.submission_content"
              class="prose prose-slate max-w-none text-[14px] leading-[1.85] text-slate-700"
              v-html="selectedSub.submission_content"></div>
            <div v-else class="text-center py-16 text-slate-400">
              <i class="fa-solid fa-file-circle-xmark text-3xl mb-3"></i>
              <p class="font-bold text-sm">Học viên chưa nộp nội dung bài viết</p>
            </div>
          </div>
        </div>

        <!-- Panel chấm điểm -->
        <div class="w-80 shrink-0 flex flex-col overflow-y-auto">
          <!-- Thông tin học viên -->
          <div class="px-6 py-6 border-b border-slate-50">
            <div class="flex items-center gap-3 mb-4">
              <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(selectedSub?.student_name || '')}&background=d1fae5&color=059669&bold=true&size=48`"
                class="w-12 h-12 rounded-2xl shadow-sm">
              <div>
                <p class="font-headline font-black text-[14px] text-slate-900">{{ selectedSub?.student_name }}</p>
                <p class="text-[10px] text-slate-400 font-bold">Lớp: {{ selectedSub?.class_name }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-slate-50 rounded-2xl p-3 text-center">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Ngày nộp</p>
                <p class="text-xs font-black text-slate-700">{{ formatDateTime(selectedSub?.submitted_at) }}</p>
              </div>
              <div class="bg-slate-50 rounded-2xl p-3 text-center">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Số từ</p>
                <p class="text-xs font-black text-slate-700">{{ selectedSub?.word_count }} từ</p>
              </div>
            </div>
          </div>

          <!-- Tiêu chí chấm -->
          <div class="px-6 py-5 border-b border-slate-50">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-headline font-black text-sm text-slate-900">Tiêu chí chấm điểm</h3>
              <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg">Trình độ C1</span>
            </div>
            <div class="space-y-5">
              <div v-for="rubric in rubricItems" :key="rubric.key">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-[11px] font-bold text-slate-600">{{ rubric.label }}</span>
                  <span class="text-[13px] font-headline font-black"
                    :class="rubric.score >= 7 ? 'text-emerald-500' : rubric.score >= 5 ? 'text-amber-500' : 'text-red-400'">
                    {{ parseFloat(rubric.score).toFixed(1) }}
                  </span>
                </div>
                <div class="relative h-2 bg-slate-100 rounded-full">
                  <input type="range" min="0" max="10" step="0.5" v-model.number="rubric.score"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                  <div class="h-full rounded-full transition-all duration-300"
                    :class="rubric.score >= 7 ? 'bg-emerald-500' : rubric.score >= 5 ? 'bg-amber-400' : 'bg-red-400'"
                    :style="`width: ${(rubric.score / 10) * 100}%`"></div>
                  <div class="absolute top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full bg-white border-2 shadow-md transition-all duration-300"
                    :class="rubric.score >= 7 ? 'border-emerald-500' : rubric.score >= 5 ? 'border-amber-400' : 'border-red-400'"
                    :style="`left: calc(${(rubric.score / 10) * 100}% - 7px)`"></div>
                </div>
                <div class="flex justify-between text-[9px] font-bold text-slate-300 mt-1">
                  <span>Cơ bản</span><span>Nâng cao</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Điểm tổng -->
          <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
            <div>
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Điểm tổng hợp</p>
              <p class="text-[9px] font-bold text-emerald-500">
                {{ aggregateScore >= 8 ? 'Xuất sắc' : aggregateScore >= 6.5 ? 'Tốt' : aggregateScore >= 5 ? 'Đạt' : 'Chưa đạt' }}
              </p>
            </div>
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-headline font-black text-2xl shadow-lg"
              :class="aggregateScore >= 7 ? 'bg-emerald-500 text-white shadow-emerald-500/30' : aggregateScore >= 5 ? 'bg-amber-400 text-white shadow-amber-400/30' : 'bg-red-400 text-white shadow-red-400/30'">
              {{ aggregateScore.toFixed(1) }}
            </div>
          </div>

          <!-- Nhận xét -->
          <div class="px-6 py-5 border-b border-slate-50">
            <h3 class="font-headline font-black text-sm text-slate-900 mb-3">Nhận xét tổng thể</h3>
            <textarea v-model="feedbackText" rows="5"
              placeholder="Nhận xét về bài viết của học viên..."
              class="w-full resize-none rounded-2xl border border-slate-100 bg-slate-50/50 px-4 py-3 text-[12px] text-slate-700 leading-relaxed outline-none focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-300 transition-all placeholder:text-slate-300">
            </textarea>
          </div>

          <!-- Buttons -->
          <div class="px-6 py-5 space-y-3">
            <button @click="saveDraft"
              class="w-full py-3 rounded-2xl border border-slate-100 bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
              Lưu nháp
            </button>
            <button @click="submitGrade" :disabled="isGrading"
              class="w-full py-3 rounded-2xl bg-emerald-500 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 hover:-translate-y-0.5 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
              <span v-if="isGrading" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              Gửi điểm &amp; Phản hồi
            </button>
          </div>

          <div v-if="gradeSuccess" class="mx-6 mb-4 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-center">
            <i class="fa-solid fa-circle-check text-emerald-500 text-xl mb-1"></i>
            <p class="text-xs font-black text-emerald-600">Đã chấm điểm thành công!</p>
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

// ── Active tab từ URL query ──
const activeTab = computed(() => route.query.tab || 'queue')

const currentTab = computed(() => {
  const map = {
    queue:    { label: 'Hàng chờ chấm điểm' },
    history:  { label: 'Lịch sử nộp bài' },
    rubric:   { label: 'Quản lý tiêu chí chấm điểm' },
    analytics:{ label: 'Thống kê chấm điểm' },
  }
  return map[activeTab.value] || null
})

// ── State ──
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

// ── Computed ──
const aggregateScore = computed(() => {
  const total = rubricItems.value.reduce((s, r) => s + Number(r.score), 0)
  return total / rubricItems.value.length
})

// ── Methods ──
const formatDate = (dt) => {
  if (!dt) return ''
  return new Date(dt).toLocaleDateString('vi-VN')
}

const formatDateTime = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('vi-VN') + ' · ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
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
      feedbackText.value = ''
      gradeSuccess.value = false
      const avg = result.data.score ?? 7
      rubricItems.value.forEach(r => r.score = avg)
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
      notifySuccess(notify ? 'Đã gửi điểm và phản hồi cho học viên!' : 'Đã lưu nháp thành công.')
      stats.value.pending = Math.max(0, stats.value.pending - 1)
      stats.value.graded++
      queue.value = queue.value.filter(s => s.id !== selectedSub.value.id)
    } else notifyError(result.message)
  } catch { notifyError('Lỗi kết nối máy chủ.') }
  finally { isGrading.value = false }
}

// ── Watchers ──
watch(activeTab, (tab) => {
  if (tab === 'history' && history.value.length === 0) loadHistory()
  if (tab === 'queue') loadQueue()
}, { immediate: false })

// ── Lifecycle ──
onMounted(() => {
  loadClasses()
  loadQueue()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');
.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }
.prose :deep(p) { margin-bottom: 1.2em; }
.prose :deep(strong) { font-weight: 800; color: #1e293b; }
.prose :deep(em) { font-style: italic; }
.prose :deep(ul) { list-style: disc; padding-left: 1.5rem; margin-bottom: 1em; }
</style>
