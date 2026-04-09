<template>
  <div class="fixed inset-0 z-[200] bg-slate-50 flex flex-col font-body overflow-hidden">

    <!-- ═══ TOP BAR ═══ -->
    <header class="bg-white border-b border-slate-100 shadow-sm shrink-0 z-10">
      <div class="px-6 h-14 flex items-center justify-between gap-4">
        <!-- Left: Back Button -->
        <div class="flex items-center gap-4">
          <button @click="$emit('close')" class="flex items-center gap-2 px-4 py-2 text-[12px] font-black text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            Quay lại
          </button>
        </div>
        <div class="flex items-center gap-5 shrink-0">
          <div class="flex items-center gap-2" :class="timeLeft < 300 ? 'text-red-500' : 'text-slate-600'">
            <div class="w-2 h-2 rounded-full" :class="timeLeft < 300 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"></div>
            <span class="text-[13px] font-black tabular-nums">{{ formattedTime }}</span>
          </div>
          <button @click="handleSubmitAll" :disabled="isSubmitting"
            class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest shadow-[0_4px_10px_rgba(22,163,74,0.25)] hover:bg-emerald-700 hover:-translate-y-0.5 transition-all disabled:opacity-50">
            <span v-if="isSubmitting"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Đang nộp...</span>
            <span v-else>Nộp bài</span>
          </button>
        </div>
      </div>
    </header>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="flex-1 flex overflow-hidden">

      <!-- Left sidebar -->
      <aside class="w-[64px] border-r border-slate-100 bg-white flex flex-col items-center py-5 gap-4 shrink-0">

        <button v-for="tool in sidebarTools" :key="tool.key"
          @click="activeTool = activeTool === tool.key ? null : tool.key"
          class="w-10 h-10 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all relative"
          :class="activeTool === tool.key
            ? 'bg-emerald-500 text-white shadow-[0_4px_10px_rgba(22,163,74,0.3)]'
            : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'">
          <!-- Active left border indicator -->
          <div v-if="activeTool === tool.key" class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-6 bg-emerald-500 rounded-r-full -ml-px"></div>
          <i :class="tool.icon" class="text-xs"></i>
          <span class="text-[7px] font-black uppercase tracking-wider leading-none">{{ tool.shortLabel }}</span>
        </button>

        <div class="mt-auto">
          <button class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center hover:border-emerald-300 hover:text-emerald-500 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
          </button>
        </div>
      </aside>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto no-scrollbar">
        <div class="max-w-4xl mx-auto px-10 py-10">

          <!-- Header -->
          <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-8 h-px bg-slate-300"></div>
              <p class="text-[9px] font-black uppercase tracking-[0.25em] text-slate-400">
                Focus Mode • {{ quiz?.title || 'Assignment Alpha' }}
              </p>
            </div>
            <h1 class="text-[34px] font-headline font-black text-slate-800 leading-tight tracking-tight mb-1">
              Editorial Mastery:
            </h1>
            <h2 class="text-[34px] font-headline font-black text-emerald-500 leading-tight tracking-tight mb-5">
              Điền vào chỗ trống
            </h2>
            <p class="text-[13px] text-slate-400 font-medium leading-relaxed max-w-xl">
              Đọc đoạn văn và điền vào ô trống bằng từ/cụm từ phù hợp. Bạn có thể gõ trực tiếp hoặc chọn từ Ngân hàng từ vựng.
            </p>
          </div>

          <!-- Loading -->
          <div v-if="isLoadingQuestions" class="flex flex-col items-center py-20 gap-4">
            <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Đang tải...</p>
          </div>

          <!-- No questions -->
          <div v-else-if="questions.length === 0" class="text-center py-20">
            <i class="fa-solid fa-spell-check text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Chưa có câu hỏi</p>
          </div>

          <!-- Task cards -->
          <template v-else>
            <div
              v-for="(question, qIdx) in questions"
              :key="question.id"
              class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden mb-8 transition-all duration-300"
              :class="qIdx === activeTaskIdx ? 'ring-2 ring-emerald-100' : 'opacity-70 hover:opacity-90'"
            >
              <!-- Card header -->
              <div class="flex items-center justify-between px-8 pt-7 pb-0">
                <div class="flex items-center gap-3">
                  <span class="px-3 py-1.5 bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-[0.15em] rounded-lg">
                    Task {{ qIdx + 1 }} / {{ questions.length }}
                  </span>
                  <span class="px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.15em] rounded-lg"
                    :class="getDifficultyClass(question)">
                    Độ khó: {{ getDifficultyLabel(question) }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                  <i class="fa-solid fa-cloud-arrow-up text-xs opacity-50"></i>
                  <span>Tự động lưu...</span>
                </div>
              </div>

              <!-- Passage with inline blanks -->
              <div class="px-8 py-7 text-[17px] text-slate-700 font-headline font-bold leading-[2.2] tracking-tight">
                <template v-for="(segment, sIdx) in parsedSegments[qIdx]" :key="sIdx">
                  <!-- Normal text -->
                  <span v-if="segment.type === 'text'">{{ segment.text }}</span>

                  <!-- Blank input -->
                  <span v-else class="inline-flex flex-col items-center mx-1 align-bottom">
                    <input
                      :ref="el => setInputRef(qIdx, segment.blankIdx, el)"
                      v-model="userAnswers[qIdx][segment.blankIdx]"
                      @focus="activeTaskIdx = qIdx; focusedBlankIdx = { q: qIdx, b: segment.blankIdx }"
                      @blur="focusedBlankIdx = null"
                      @keydown.tab.prevent="focusNextBlank(qIdx, segment.blankIdx)"
                      class="border-0 border-b-2 bg-transparent outline-none text-center text-[16px] font-black transition-all duration-200 min-w-[80px] max-w-[160px]"
                      :class="getBlankClass(qIdx, segment.blankIdx)"
                      :style="{ width: Math.max(80, (userAnswers[qIdx][segment.blankIdx]?.length || 5) * 11 + 30) + 'px' }"
                      :placeholder="isRevealed ? '' : '.....'"
                      :disabled="isRevealed"
                      :aria-label="`Ô trống ${segment.blankIdx + 1} câu ${qIdx + 1}`"
                    />
                    <!-- Correct answer hint after reveal if wrong -->
                    <span v-if="isRevealed && !isBlankCorrect(qIdx, segment.blankIdx)"
                      class="text-[9px] font-black text-emerald-500 mt-0.5 whitespace-nowrap">
                      ✓ {{ getCorrectAnswer(qIdx, segment.blankIdx) }}
                    </span>
                  </span>
                </template>
              </div>

              <!-- Vocabulary bank -->
              <div class="px-8 pb-7 border-t border-slate-50 pt-5">
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">
                  Ngân hàng từ vựng
                </p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="word in vocabBanks[qIdx]"
                    :key="word"
                    @click="insertWord(qIdx, word)"
                    :disabled="isRevealed || isWordUsed(qIdx, word)"
                    class="px-4 py-2 rounded-xl text-[12px] font-bold border transition-all cursor-pointer"
                    :class="isWordUsed(qIdx, word)
                      ? 'line-through bg-slate-50 text-slate-300 border-slate-100 cursor-not-allowed'
                      : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 shadow-sm'">
                    {{ word }}
                  </button>
                </div>

                <!-- Course progress -->
                <div class="mt-6 flex items-end justify-between gap-4">
                  <div class="text-[10px] text-slate-300 font-bold italic hidden sm:block">
                    "Làm đúng từng câu nhỏ sẽ dẫn đến thành công lớn!"
                  </div>
                  <div class="text-right shrink-0">
                    <div class="flex items-center justify-end gap-2 mb-1">
                      <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tiến độ</span>
                      <span class="text-[15px] font-headline font-black text-emerald-600">{{ overallProgress }}%</span>
                    </div>
                    <div class="h-1.5 w-32 bg-slate-100 rounded-full overflow-hidden">
                      <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700"
                        :style="{ width: overallProgress + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reveal feedback summary -->
            <transition name="slide-down">
              <div v-if="isRevealed" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 mb-8">
                <div class="flex items-center gap-4 mb-6">
                  <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm"
                    :class="totalScore === totalBlanks ? 'bg-emerald-500' : 'bg-amber-400'">
                    <i class="fa-solid fa-check-double text-white"></i>
                  </div>
                  <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Kết quả bài làm</p>
                    <p class="text-[19px] font-headline font-black text-slate-800">
                      {{ totalScore }}/{{ totalBlanks }} ô trống đúng
                      <span class="text-[14px] ml-2" :class="totalScore === totalBlanks ? 'text-emerald-500' : 'text-amber-500'">
                        ({{ Math.round(totalScore / totalBlanks * 100) }}%)
                      </span>
                    </p>
                  </div>
                </div>
              </div>
            </transition>
          </template>

        </div>
      </div>
    </div>

    <!-- ═══ BOTTOM ACTION BAR ═══ -->
    <div class="bg-white border-t border-slate-100 shadow-sm shrink-0 z-10">
      <div class="max-w-4xl mx-auto px-10 h-16 flex items-center justify-between">
        <button @click="$emit('close')"
          class="flex items-center gap-2 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-700 transition-colors group">
          <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
          Về danh sách
        </button>

        <div class="flex items-center gap-5">
          <div class="text-right hidden sm:block">
            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Tiếp theo</p>
            <p class="text-[11px] font-bold text-slate-600">Kiểm tra đáp án</p>
          </div>
          <button @click="handleSubmitAnswer" :disabled="isRevealed"
            class="flex items-center gap-2 px-8 py-3 text-white rounded-xl text-[12px] font-black uppercase tracking-widest transition-all shadow-lg group"
            :class="isRevealed
              ? 'bg-slate-300 cursor-not-allowed'
              : 'bg-slate-900 hover:bg-emerald-600 hover:-translate-y-0.5 shadow-[0_5px_15px_rgba(15,23,42,0.2)]'">
            {{ isRevealed ? 'Đã kiểm tra' : 'Nộp đáp án' }}
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ TOOL PANEL ═══ -->
    <transition name="slide-right">
      <div v-if="activeTool" class="fixed inset-y-0 left-[64px] w-72 bg-white border-r border-slate-100 shadow-2xl z-40 flex flex-col">
        <div class="p-5 border-b border-slate-50 flex items-center justify-between">
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
            {{ sidebarTools.find(t => t.key === activeTool)?.label }}
          </p>
          <button @click="activeTool = null" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-400">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <template v-if="activeTool === 'notes'">
            <textarea v-model="personalNote" rows="14"
              class="w-full text-[13px] text-slate-700 bg-slate-50 border border-slate-100 rounded-2xl p-4 resize-none outline-none focus:ring-2 focus:ring-emerald-200 transition-all font-medium leading-relaxed"
              placeholder="Ghi chú của bạn..."></textarea>
          </template>
          <template v-else-if="activeTool === 'outline'">
            <p class="text-[10px] font-bold text-slate-400 mb-4">Tóm tắt câu hỏi</p>
            <div class="space-y-3">
              <div v-for="(q, idx) in questions" :key="q.id"
                @click="activeTaskIdx = idx; activeTool = null"
                class="p-3 rounded-xl border cursor-pointer transition-all text-[11px]"
                :class="idx === activeTaskIdx ? 'border-emerald-200 bg-emerald-50 text-emerald-700 font-black' : 'border-slate-100 hover:border-slate-200 text-slate-600 font-bold'">
                <span class="mr-2 opacity-60">{{ idx + 1 }}.</span>
                {{ q.question_text.substring(0, 50).replace(/\[BLANK\]/g, '___') }}...
              </div>
            </div>
          </template>
          <template v-else-if="activeTool === 'refs'">
            <p class="text-[10px] font-bold text-slate-400 mb-4">Đáp án đã điền</p>
            <div class="space-y-2">
              <div v-for="(q, qIdx) in questions" :key="q.id">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Câu {{ qIdx + 1 }}</p>
                <div class="flex flex-wrap gap-1.5 mb-3">
                  <span v-for="(ans, bIdx) in userAnswers[qIdx]" :key="bIdx"
                    class="px-2 py-1 rounded-lg text-[10px] font-bold border"
                    :class="ans ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-400'">
                    Ô {{ bIdx + 1 }}: {{ ans || '—' }}
                  </span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </transition>

    <!-- ═══ SUCCESS OVERLAY ═══ -->
    <transition name="fade">
      <div v-if="showSuccess" class="fixed inset-0 z-[300] bg-black/60 backdrop-blur-md flex items-center justify-center px-6">
        <div class="bg-white rounded-[2.5rem] p-12 max-w-md w-full text-center shadow-2xl">
          <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
            :class="totalScore === totalBlanks ? 'bg-emerald-50' : 'bg-amber-50'">
            <i :class="totalScore === totalBlanks ? 'fa-solid fa-trophy text-emerald-500' : 'fa-solid fa-pen-to-square text-amber-500'" class="text-3xl"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-2">
            {{ totalScore === totalBlanks ? 'Hoàn hảo!' : 'Bài đã nộp!' }}
          </h3>
          <p class="text-[13px] text-slate-400 font-medium mb-8 leading-relaxed">
            Bạn điền đúng {{ totalScore }}/{{ totalBlanks }} ô trống ({{ Math.round(totalScore / totalBlanks * 100) }}%).
          </p>
          <div class="grid grid-cols-3 gap-3 mb-8">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
              <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Tổng ô</p>
              <p class="text-2xl font-headline font-black text-slate-700">{{ totalBlanks }}</p>
            </div>
            <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100/50">
              <p class="text-[8px] font-black uppercase tracking-widest text-emerald-500 mb-1">Đúng</p>
              <p class="text-2xl font-headline font-black text-emerald-600">{{ totalScore }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl p-4 border border-red-100/50">
              <p class="text-[8px] font-black uppercase tracking-widest text-red-400 mb-1">Sai</p>
              <p class="text-2xl font-headline font-black text-red-500">{{ totalBlanks - totalScore }}</p>
            </div>
          </div>
          <button @click="$emit('close')"
            class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[12px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-md">
            Quay về danh sách
          </button>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
/**
 * FillBlankExercise.vue
 * Dạng bài điền vào chỗ trống (fill_blank).
 * - Phân tích question_text theo mẫu [BLANK] hoặc ____
 * - Render inline inputs trong đoạn văn
 * - Ngân hàng từ vựng: click chip → điền vào ô đang focus
 * - Verify: so sánh với đáp án đúng từ options
 */
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { apiFetch } from '../../../utils/api'

// ── Props & Emits ──
const props = defineProps({
  quiz: { type: Object, default: null },
  courseName: { type: String, default: '' }
})
const emit = defineEmits(['close', 'submitted'])

// ── State ──
const questions = ref([])
const userAnswers = ref([])       // userAnswers[qIdx][blankIdx] = string
const parsedSegments = ref([])    // parsedSegments[qIdx] = [{type:'text',text}|{type:'blank',blankIdx}]
const vocabBanks = ref([])        // vocabBanks[qIdx] = string[] (shuffled)
const correctAnswers = ref([])    // correctAnswers[qIdx][blankIdx] = string (from options order)
const isRevealed = ref(false)
const isLoadingQuestions = ref(true)
const isSubmitting = ref(false)
const showSuccess = ref(false)
const activeTool = ref(null)
const personalNote = ref('')
const activeTaskIdx = ref(0)
const focusedBlankIdx = ref(null) // { q: qIdx, b: blankIdx }
const inputRefs = ref({})         // inputRefs[`${qIdx}-${blankIdx}`] = el

// Timer
const TOTAL_SECONDS = 20 * 60
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null

// ── Constants ──
const BLANK_PATTERN = /\[BLANK\]|_{3,}/g

const sidebarTools = [
  { key: 'drafts', icon: 'fa-solid fa-layer-group', label: 'Nháp đã làm', shortLabel: 'Nháp' },
  { key: 'outline', icon: 'fa-solid fa-list-ul', label: 'Danh sách câu', shortLabel: 'DS câu' },
  { key: 'refs', icon: 'fa-solid fa-bookmark', label: 'Đáp án đã điền', shortLabel: 'Đáp án' },
  { key: 'notes', icon: 'fa-regular fa-note-sticky', label: 'Ghi chú', shortLabel: 'Chú' },
]

// ── Computed ──
const totalBlanks = computed(() =>
  userAnswers.value.reduce((acc, a) => acc + a.length, 0)
)

const filledBlanks = computed(() =>
  userAnswers.value.reduce((acc, a) => acc + a.filter(v => v.trim() !== '').length, 0)
)

const overallProgress = computed(() => {
  if (totalBlanks.value === 0) return 0
  return Math.round((filledBlanks.value / totalBlanks.value) * 100)
})

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const totalScore = computed(() => {
  let score = 0
  questions.value.forEach((_, qIdx) => {
    ;(userAnswers.value[qIdx] || []).forEach((ans, bIdx) => {
      if (isBlankCorrect(qIdx, bIdx)) score++
    })
  })
  return score
})

// ── Helpers ──
const isBlankCorrect = (qIdx, bIdx) => {
  const correct = correctAnswers.value[qIdx]?.[bIdx]?.toLowerCase().trim() || ''
  const user = (userAnswers.value[qIdx]?.[bIdx] || '').toLowerCase().trim()
  return correct !== '' && user !== '' && user === correct
}

const getCorrectAnswer = (qIdx, bIdx) => correctAnswers.value[qIdx]?.[bIdx] || ''

const isWordUsed = (qIdx, word) =>
  (userAnswers.value[qIdx] || []).some(a => a.toLowerCase().trim() === word.toLowerCase().trim())

const getBlankClass = (qIdx, bIdx) => {
  if (focusedBlankIdx.value?.q === qIdx && focusedBlankIdx.value?.b === bIdx) {
    return 'border-emerald-500 text-emerald-700'
  }
  if (isRevealed.value) {
    if (isBlankCorrect(qIdx, bIdx)) return 'border-emerald-400 text-emerald-600'
    if ((userAnswers.value[qIdx]?.[bIdx] || '').trim()) return 'border-red-400 text-red-500'
    return 'border-slate-200 text-slate-300'
  }
  if ((userAnswers.value[qIdx]?.[bIdx] || '').trim()) return 'border-slate-800 text-slate-800'
  return 'border-slate-300 text-slate-400'
}

const getDifficultyClass = (q) => {
  const lvl = q.difficulty || 'medium'
  if (lvl === 'hard' || lvl === 'advanced') return 'bg-red-50 text-red-500'
  if (lvl === 'easy') return 'bg-emerald-50 text-emerald-600'
  return 'bg-amber-50 text-amber-600'
}

const getDifficultyLabel = (q) => {
  const lvl = q.difficulty || 'medium'
  if (lvl === 'hard' || lvl === 'advanced') return 'Nâng cao'
  if (lvl === 'easy') return 'Cơ bản'
  return 'Trung bình'
}

// ── Input refs ──
const setInputRef = (qIdx, bIdx, el) => {
  if (el) inputRefs.value[`${qIdx}-${bIdx}`] = el
}

const focusNextBlank = (qIdx, bIdx) => {
  const nextBIdx = bIdx + 1
  const el = inputRefs.value[`${qIdx}-${nextBIdx}`]
  if (el) { el.focus(); return }
  // Next question
  const nextEl = inputRefs.value[`${qIdx + 1}-0`]
  if (nextEl) nextEl.focus()
}

// ── Methods ──
const insertWord = (qIdx, word) => {
  if (isRevealed.value) return

  // If there's a focused blank in this question, fill it
  if (focusedBlankIdx.value?.q === qIdx) {
    const bIdx = focusedBlankIdx.value.b
    const arr = [...(userAnswers.value[qIdx] || [])]
    arr[bIdx] = word
    userAnswers.value[qIdx] = arr
    nextTick(() => {
      const el = inputRefs.value[`${qIdx}-${bIdx}`]
      if (el) el.focus()
    })
    return
  }

  // Otherwise, fill the first empty blank in this question
  const arr = [...(userAnswers.value[qIdx] || [])]
  const emptyIdx = arr.findIndex(a => !a.trim())
  if (emptyIdx !== -1) {
    arr[emptyIdx] = word
    userAnswers.value[qIdx] = arr
    nextTick(() => {
      const el = inputRefs.value[`${qIdx}-${emptyIdx}`]
      if (el) el.focus()
    })
  }
}

const handleSubmitAnswer = () => {
  if (isRevealed.value) return
  isRevealed.value = true
}

const handleSubmitAll = async () => {
  if (!isRevealed.value) handleSubmitAnswer()
  isSubmitting.value = true
  await new Promise(r => setTimeout(r, 900))
  isSubmitting.value = false
  showSuccess.value = true
  stopTimer()
  emit('submitted', {
    quiz_id: props.quiz?.id,
    score: totalScore.value,
    total: totalBlanks.value,
    answers: userAnswers.value,
  })
}

// ── Parse question text ──
const parseQuestion = (questionText) => {
  const segments = []
  let blankIdx = 0
  const parts = questionText.split(BLANK_PATTERN)
  const blankMatches = questionText.match(BLANK_PATTERN) || []

  parts.forEach((part, idx) => {
    if (part) segments.push({ type: 'text', text: part })
    if (idx < blankMatches.length) {
      segments.push({ type: 'blank', blankIdx: blankIdx++ })
    }
  })
  return { segments, blankCount: blankIdx }
}

// ── Data loading ──
const buildFromQuestions = (qs) => {
  const parsed = []
  const banks = []
  const correct = []
  const answers = []

  qs.forEach((q) => {
    const { segments, blankCount } = parseQuestion(q.question_text)
    parsed.push(segments)

    // Correct answers: options where is_correct = true, in order
    const correctOpts = (q.options || []).filter(o => o.is_correct)
    correct.push(correctOpts.map(o => o.option_text))

    // Vocab bank: all option_text values, shuffled
    const all = (q.options || []).map(o => o.option_text)
    for (let i = all.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [all[i], all[j]] = [all[j], all[i]]
    }
    banks.push(all)

    answers.push(new Array(blankCount).fill(''))
  })

  questions.value = qs
  parsedSegments.value = parsed
  vocabBanks.value = banks
  correctAnswers.value = correct
  userAnswers.value = answers
}

const fetchQuestions = async () => {
  const filterFillBlank = (qs) =>
    (qs || []).filter(q => q.question_type === 'fill_blank' || q.question_type === 'fill_in_blank')

  if (props.quiz?.questions?.length) {
    buildFromQuestions(filterFillBlank(props.quiz.questions))
    isLoadingQuestions.value = false
    return
  }
  if (!props.quiz?.id) { isLoadingQuestions.value = false; return }

  try {
    const res = await apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`)
    const data = await res.json()
    if (data.status === 'success') {
      buildFromQuestions(filterFillBlank(data.data.questions))
    }
  } catch { /* ignore */ }
  finally { isLoadingQuestions.value = false }
}

// ── Timer ──
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
    else { stopTimer(); handleSubmitAll() }
  }, 1000)
}
const stopTimer = () => {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
}

onMounted(() => { fetchQuestions(); startTimer() })
onUnmounted(() => stopTimer())
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');
.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }
.no-scrollbar::-webkit-scrollbar { width: 6px; }
.no-scrollbar::-webkit-scrollbar-track { background: transparent; }
.no-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.no-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.no-scrollbar { scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }

input[type="text"], input:not([type]) {
  -webkit-appearance: none;
  appearance: none;
}
input:focus { outline: none; }
input::placeholder { color: #cbd5e1; font-style: italic; font-weight: 600; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-14px); }

.slide-right-enter-active, .slide-right-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.slide-right-enter-from, .slide-right-leave-to { opacity: 0; transform: translateX(-16px); }
</style>
