<template>
  <div class="fixed inset-0 z-[200] bg-slate-50 flex flex-col font-body overflow-hidden">

    <!-- ═══ TOP NAVIGATION BAR ═══ -->
    <header class="bg-white border-b border-slate-100 shadow-sm shrink-0 relative z-10">
      <div class="px-6 h-14 flex items-center justify-between gap-4">

        <!-- Left: Back Button -->
        <div class="flex items-center gap-4">
          <button @click="$emit('close')" class="flex items-center gap-2 px-4 py-2 text-[12px] font-black text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            Quay lại
          </button>
        </div>

        <!-- Center: Progress -->
        <div class="flex items-center gap-5 flex-1 justify-center max-w-sm">
          <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest shrink-0 whitespace-nowrap">
            Câu {{ currentIndex + 1 }} / {{ totalQuestions }}
          </span>
          <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700"
              :style="{ width: progressPercent + '%' }"></div>
          </div>
          <span class="text-[10px] font-black text-emerald-600 shrink-0 whitespace-nowrap">
            {{ Math.round(progressPercent) }}% Hoàn thành
          </span>
        </div>

        <!-- Right: Timer + Submit -->
        <div class="flex items-center gap-4 shrink-0">
          <div class="flex items-center gap-2"
            :class="timeLeft < 300 ? 'text-red-500' : 'text-slate-600'">
            <div class="w-2 h-2 rounded-full"
              :class="timeLeft < 300 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"></div>
            <span class="text-[13px] font-black tabular-nums">{{ formattedTime }}</span>
          </div>
          <button @click="handleSubmitAll" :disabled="isSubmitting"
            class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest shadow-[0_4px_10px_rgba(22,163,74,0.25)] hover:bg-emerald-700 hover:-translate-y-0.5 transition-all disabled:opacity-50">
            <span v-if="isSubmitting"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Đang nộp...</span>
            <span v-else>Nộp bài</span>
          </button>
        </div>
      </div>
    </header>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="flex-1 flex overflow-hidden">

      <!-- Left Sidebar -->
      <aside class="w-[72px] border-r border-slate-100 bg-white flex flex-col items-center py-6 gap-5 shrink-0">

        <!-- Sidebar tools -->
        <button v-for="tool in sidebarTools" :key="tool.key"
          @click="activeTool = activeTool === tool.key ? null : tool.key"
          class="w-10 h-10 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all"
          :class="activeTool === tool.key
            ? 'bg-emerald-500 text-white shadow-[0_4px_10px_rgba(22,163,74,0.3)]'
            : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
          :title="tool.label">
          <i :class="tool.icon" class="text-xs"></i>
          <span class="text-[7px] font-black uppercase tracking-wider leading-none">{{ tool.label }}</span>
        </button>
      </aside>

      <!-- Main content -->
      <div class="flex-1 overflow-y-auto no-scrollbar">
        <div class="max-w-3xl mx-auto px-8 py-10 flex flex-col gap-8">

          <!-- Section Badge + Title -->
          <div>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-100/60 rounded-xl mb-5">
              <i class="fa-solid fa-crosshairs text-emerald-500 text-xs"></i>
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600">
                {{ categoryLabel[quiz?.category] || 'Trắc nghiệm' }}
              </span>
            </div>
            <h1 class="text-[28px] font-headline font-black text-slate-800 leading-tight tracking-tight">
              Focus Mode: <span class="text-emerald-500">{{ quiz?.title || 'Bài tập' }}</span>
            </h1>
            <p class="text-[13px] text-slate-400 font-medium mt-3 max-w-lg leading-relaxed">
              Đọc kỹ câu hỏi và chọn đáp án đúng nhất. Mỗi câu chỉ có một đáp án chính xác.
            </p>
          </div>

          <!-- Loading state -->
          <div v-if="isLoadingQuestions" class="flex flex-col items-center py-20 gap-4">
            <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Đang tải câu hỏi...</p>
          </div>

          <!-- No questions -->
          <div v-else-if="questions.length === 0" class="text-center py-20">
            <i class="fa-solid fa-face-rolling-eyes text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Chưa có câu hỏi nào</p>
          </div>

          <template v-else>
            <!-- Question Card -->
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-10 transition-all duration-300"
              :key="currentIndex">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 mb-4">
                Câu hỏi {{ currentIndex + 1 }}
              </p>
              <h2 class="text-[20px] font-headline font-black text-slate-800 leading-[1.45] tracking-tight">
                {{ currentQuestion?.question_text }}
              </h2>
              <p v-if="currentQuestion?.hint" class="mt-4 text-[12px] font-bold text-slate-400 italic flex items-center gap-1.5">
                <i class="fa-regular fa-lightbulb text-amber-400"></i>
                {{ currentQuestion.hint }}
              </p>
            </div>

            <!-- Options A B C D -->
            <div class="flex flex-col gap-4">
              <button
                v-for="(option, oIdx) in currentOptions"
                :key="option.id"
                @click="selectAnswer(option)"
                :disabled="isAnswerRevealed"
                class="group w-full flex items-center gap-5 p-6 rounded-[1.75rem] border-2 text-left transition-all duration-300"
                :class="getOptionClass(option, oIdx)"
              >
                <!-- Letter badge -->
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-[13px] font-black shrink-0 transition-all duration-300"
                  :class="getLetterClass(option, oIdx)">
                  {{ optionLetters[oIdx] }}
                </div>

                <!-- Text -->
                <span class="flex-1 text-[15px] font-bold leading-snug transition-colors duration-300"
                  :class="getOptionTextClass(option, oIdx)">
                  {{ option.option_text }}
                </span>

                <!-- Correct check icon -->
                <div v-if="isAnswerRevealed && selectedAnswer?.id === option.id && option.is_correct"
                  class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center shrink-0 shadow-[0_4px_12px_rgba(22,163,74,0.4)]">
                  <i class="fa-solid fa-check text-white text-xs"></i>
                </div>
                <div v-else-if="isAnswerRevealed && selectedAnswer?.id === option.id && !option.is_correct"
                  class="w-8 h-8 bg-red-400 rounded-xl flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-xmark text-white text-xs"></i>
                </div>
                <div v-else-if="isAnswerRevealed && option.is_correct && selectedAnswer?.id !== option.id"
                  class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0 border border-emerald-200">
                  <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                </div>
              </button>
            </div>

            <!-- Explanation (after answer) -->
            <transition name="slide-down">
              <div v-if="isAnswerRevealed && currentQuestion?.explanation"
                class="bg-blue-50/80 rounded-[1.5rem] border border-blue-100/60 p-6 flex gap-4">
                <div class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                  <i class="fa-solid fa-book-open text-white text-xs"></i>
                </div>
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-blue-500 mb-1">Giải thích</p>
                  <p class="text-[13px] font-bold text-slate-700 leading-relaxed">{{ currentQuestion.explanation }}</p>
                </div>
              </div>
            </transition>

            <!-- Navigation -->
            <div class="flex items-center justify-between pt-4">
              <button @click="prevQuestion" :disabled="currentIndex === 0"
                class="flex items-center gap-2 text-[12px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all group">
                <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
                Câu trước
              </button>

              <button @click="markForReview"
                class="px-6 py-2.5 border-2 border-slate-200 text-slate-400 text-[11px] font-black uppercase tracking-widest rounded-xl hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-all"
                :class="reviewMarked.has(currentIndex) ? 'border-amber-300 text-amber-500 bg-amber-50' : ''">
                <i class="fa-regular fa-bookmark mr-1.5"></i>
                {{ reviewMarked.has(currentIndex) ? 'Đã đánh dấu' : 'Xem lại sau' }}
              </button>

              <button v-if="currentIndex < totalQuestions - 1"
                @click="nextQuestion"
                class="flex items-center gap-2 px-8 py-3 bg-slate-900 text-white text-[12px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-md group">
                Câu tiếp
                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
              </button>
              <button v-else @click="handleSubmitAll"
                class="flex items-center gap-2 px-8 py-3 bg-emerald-600 text-white text-[12px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-[0_4px_12px_rgba(22,163,74,0.3)] group">
                <i class="fa-solid fa-flag-checkered text-xs"></i>
                Hoàn thành
              </button>
            </div>

            <!-- Bottom panels -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <!-- Question map -->
              <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 mb-4">Bản đồ câu hỏi</p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="(_, qIdx) in questions"
                    :key="qIdx"
                    @click="jumpToQuestion(qIdx)"
                    class="w-9 h-9 rounded-xl text-[11px] font-black transition-all border"
                    :class="getMapDotClass(qIdx)">
                    {{ qIdx + 1 }}
                  </button>
                </div>
              </div>

              <!-- Hint panel -->
              <div class="bg-emerald-700 rounded-[1.5rem] p-6 text-white relative overflow-hidden shadow-[0_8px_24px_rgba(22,163,74,0.25)]">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3 backdrop-blur-sm">
                  <i class="fa-regular fa-lightbulb text-sm"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-widest opacity-70 mb-1">Hint Available</p>
                <p class="text-[12px] font-bold opacity-90 leading-relaxed mb-4">
                  Dùng hint sẽ trừ 5% điểm tiềm năng của câu hỏi này.
                </p>
                <button @click="revealHint"
                  class="w-full py-2.5 bg-white/20 hover:bg-white/30 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl transition-all border border-white/20">
                  Xem gợi ý
                </button>
              </div>
            </div>
          </template>

        </div>
      </div>
    </div>

    <!-- ═══ TOOL PANEL (Slide from left) ═══ -->
    <transition name="slide-right">
      <div v-if="activeTool" class="fixed inset-y-0 left-[72px] w-80 bg-white border-r border-slate-100 shadow-2xl z-40 flex flex-col">
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-600">
            {{ sidebarTools.find(t => t.key === activeTool)?.label }}
          </p>
          <button @click="activeTool = null" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
          <!-- Notes tool -->
          <template v-if="activeTool === 'notes'">
            <p class="text-[11px] text-slate-400 font-bold mb-3">Ghi chú cá nhân (chỉ bạn thấy)</p>
            <textarea v-model="personalNote" rows="12"
              class="w-full text-[13px] text-slate-700 bg-slate-50 border border-slate-100 rounded-2xl p-4 resize-none outline-none focus:ring-2 focus:ring-emerald-200 transition-all font-medium leading-relaxed"
              placeholder="Ghi chú của bạn..."></textarea>
          </template>
          <!-- Drafts tool: answered questions summary -->
          <template v-else-if="activeTool === 'drafts'">
            <p class="text-[11px] text-slate-400 font-bold mb-4">Tổng kết câu đã trả lời</p>
            <div class="space-y-3">
              <div v-for="(ans, qIdx) in answers" :key="qIdx"
                class="flex items-center gap-3 p-3 rounded-xl border"
                :class="ans !== null ? 'border-emerald-100 bg-emerald-50' : 'border-slate-100 bg-slate-50'">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-black"
                  :class="ans !== null ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'">
                  {{ qIdx + 1 }}
                </div>
                <p class="text-[11px] font-bold text-slate-600 truncate flex-1">
                  {{ ans !== null ? 'Đã chọn: ' + optionLetters[currentOptions.findIndex(o => o?.id === ans)] : 'Chưa trả lời' }}
                </p>
              </div>
            </div>
          </template>
          <!-- Outline: questions list -->
          <template v-else-if="activeTool === 'outline'">
            <p class="text-[11px] text-slate-400 font-bold mb-4">Danh sách câu hỏi</p>
            <div class="space-y-2">
              <button v-for="(q, qIdx) in questions" :key="q.id"
                @click="jumpToQuestion(qIdx)"
                class="w-full text-left p-3 rounded-xl border transition-all text-[12px] font-bold"
                :class="qIdx === currentIndex
                  ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                  : 'border-slate-100 hover:border-slate-200 text-slate-600'">
                <span class="text-xs opacity-60 block mb-0.5">Câu {{ qIdx + 1 }}</span>
                {{ q.question_text.substring(0, 60) }}...
              </button>
            </div>
          </template>
        </div>
      </div>
    </transition>

    <!-- ═══ SUCCESS OVERLAY ═══ -->
    <transition name="fade">
      <div v-if="showSuccess" class="fixed inset-0 z-[300] bg-black/60 backdrop-blur-md flex items-center justify-center px-6">
        <div class="bg-white rounded-[2.5rem] p-12 max-w-md w-full text-center shadow-2xl">
          <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-flag-checkered text-3xl text-emerald-500"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-2">Hoàn thành bài thi!</h3>
          <p class="text-[13px] text-slate-400 font-medium mb-8 leading-relaxed">
            Bạn đã trả lời {{ answeredCount }}/{{ totalQuestions }} câu hỏi. Kết quả sẽ được xử lý và phản hồi sớm.
          </p>

          <!-- Score summary -->
          <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Tổng câu</p>
              <p class="text-2xl font-headline font-black text-slate-800">{{ totalQuestions }}</p>
            </div>
            <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100/50">
              <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1">Đã làm</p>
              <p class="text-2xl font-headline font-black text-emerald-600">{{ answeredCount }}</p>
            </div>
            <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100/50">
              <p class="text-[9px] font-black uppercase tracking-widest text-amber-500 mb-1">Đánh dấu</p>
              <p class="text-2xl font-headline font-black text-amber-600">{{ reviewMarked.size }}</p>
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
 * MultipleChoiceExercise.vue
 * Dạng bài trắc nghiệm (multiple_choice).
 * - Fetch câu hỏi từ API theo quiz_id
 * - Hiển thị từng câu với options A/B/C/D
 * - Điều hướng Previous/Next, đánh dấu xem lại
 * - Timer, sidebar tools (Ghi chú, Nháp, Outline)
 * - Nộp bài và hiển thị kết quả
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../../../utils/api'

// ── Props & Emits ──
const props = defineProps({
  quiz: { type: Object, default: null },
  courseName: { type: String, default: '' }
})
const emit = defineEmits(['close', 'submitted'])

// ── State ──
const questions = ref([])
const answers = ref([])           // answers[i] = selected option id or null
const reviewMarked = ref(new Set())
const currentIndex = ref(0)
const isLoadingQuestions = ref(true)
const isAnswerRevealed = ref(false)
const selectedAnswer = ref(null)
const showSuccess = ref(false)
const isSubmitting = ref(false)
const activeTool = ref(null)
const personalNote = ref('')

// Timer
const TOTAL_SECONDS = 30 * 60
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null

// ── Constants ──
const optionLetters = ['A', 'B', 'C', 'D', 'E']

const categoryLabel = {
  grammar: 'Ngữ pháp · Trắc nghiệm',
  vocabulary: 'Từ vựng · Trắc nghiệm',
  reading: 'Đọc hiểu · Trắc nghiệm',
  listening: 'Nghe hiểu · Trắc nghiệm',
  speaking: 'Nói · Trắc nghiệm',
  writing: 'Viết · Trắc nghiệm',
  summary: 'Tổng hợp · Trắc nghiệm',
  multiple_choice: 'Trắc nghiệm',
}

const sidebarTools = [
  { key: 'drafts', icon: 'fa-solid fa-layer-group', label: 'Nháp' },
  { key: 'outline', icon: 'fa-solid fa-list-ul', label: 'DS câu' },
  { key: 'notes', icon: 'fa-regular fa-note-sticky', label: 'Ghi chú' },
]

// ── Computed ──
const totalQuestions = computed(() => questions.value.length)

const currentQuestion = computed(() => questions.value[currentIndex.value] || null)

const currentOptions = computed(() => currentQuestion.value?.options || [])

const progressPercent = computed(() => {
  if (totalQuestions.value === 0) return 0
  return ((currentIndex.value + 1) / totalQuestions.value) * 100
})

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const answeredCount = computed(() => answers.value.filter(a => a !== null).length)

// ── Option styling ──
const getOptionClass = (option, idx) => {
  if (!isAnswerRevealed.value) {
    const isSelected = answers.value[currentIndex.value] === option.id
    return isSelected
      ? 'border-emerald-500 bg-emerald-50/60 shadow-[0_0_0_3px_rgba(52,211,153,0.15)]'
      : 'border-slate-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/20 cursor-pointer'
  }
  if (option.is_correct) return 'border-emerald-500 bg-emerald-50/80'
  if (selectedAnswer.value?.id === option.id && !option.is_correct) return 'border-red-400 bg-red-50/80'
  return 'border-slate-100 bg-white opacity-50'
}

const getLetterClass = (option, idx) => {
  if (!isAnswerRevealed.value) {
    const isSelected = answers.value[currentIndex.value] === option.id
    return isSelected
      ? 'bg-emerald-500 text-white shadow-[0_4px_10px_rgba(22,163,74,0.3)]'
      : 'bg-slate-50 text-slate-500 border border-slate-200 group-hover:bg-emerald-50 group-hover:text-emerald-600 group-hover:border-emerald-200'
  }
  if (option.is_correct) return 'bg-emerald-500 text-white'
  if (selectedAnswer.value?.id === option.id) return 'bg-red-400 text-white'
  return 'bg-slate-100 text-slate-400'
}

const getOptionTextClass = (option, idx) => {
  if (!isAnswerRevealed.value) {
    const isSelected = answers.value[currentIndex.value] === option.id
    return isSelected ? 'text-emerald-700 font-black' : 'text-slate-700'
  }
  if (option.is_correct) return 'text-emerald-700 font-black'
  if (selectedAnswer.value?.id === option.id && !option.is_correct) return 'text-red-600 font-black'
  return 'text-slate-400'
}

const getMapDotClass = (qIdx) => {
  if (qIdx === currentIndex.value) return 'bg-slate-900 text-white border-slate-800'
  if (reviewMarked.value.has(qIdx)) return 'bg-amber-50 text-amber-600 border-amber-200'
  if (answers.value[qIdx] !== null) return 'bg-emerald-50 text-emerald-600 border-emerald-200'
  return 'bg-white text-slate-400 border-slate-200 hover:border-emerald-200 hover:text-emerald-500'
}

// ── Methods ──
const selectAnswer = (option) => {
  if (isAnswerRevealed.value) return
  selectedAnswer.value = option
  answers.value[currentIndex.value] = option.id
  isAnswerRevealed.value = true
}

const nextQuestion = () => {
  if (currentIndex.value < totalQuestions.value - 1) {
    currentIndex.value++
    restoreAnswerState()
  }
}

const prevQuestion = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
    restoreAnswerState()
  }
}

const jumpToQuestion = (idx) => {
  currentIndex.value = idx
  activeTool.value = null
  restoreAnswerState()
}

const restoreAnswerState = () => {
  const savedOptionId = answers.value[currentIndex.value]
  if (savedOptionId !== null) {
    const opts = currentOptions.value
    selectedAnswer.value = opts.find(o => o.id === savedOptionId) || null
    isAnswerRevealed.value = true
  } else {
    selectedAnswer.value = null
    isAnswerRevealed.value = false
  }
}

const markForReview = () => {
  const set = new Set(reviewMarked.value)
  if (set.has(currentIndex.value)) {
    set.delete(currentIndex.value)
  } else {
    set.add(currentIndex.value)
  }
  reviewMarked.value = set
}

const revealHint = () => {
  alert(currentQuestion.value?.hint || 'Hãy đọc lại câu hỏi cẩn thận và loại bỏ dần các đáp án sai.')
}

const handleSubmitAll = async () => {
  isSubmitting.value = true
  await new Promise(r => setTimeout(r, 1000))
  isSubmitting.value = false
  showSuccess.value = true
  stopTimer()
  emit('submitted', {
    quiz_id: props.quiz?.id,
    answers: answers.value,
    answered_count: answeredCount.value,
  })
}

// ── Data fetching ──
const fetchQuestions = async () => {
  if (!props.quiz?.id) {
    // Dùng questions từ prop nếu đã có
    if (props.quiz?.questions?.length) {
      questions.value = props.quiz.questions
      answers.value = new Array(props.quiz.questions.length).fill(null)
    }
    isLoadingQuestions.value = false
    return
  }
  try {
    const res = await apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`)
    const data = await res.json()
    if (data.status === 'success' && data.data?.questions) {
      questions.value = data.data.questions
      answers.value = new Array(data.data.questions.length).fill(null)
    } else if (props.quiz?.questions?.length) {
      questions.value = props.quiz.questions
      answers.value = new Array(props.quiz.questions.length).fill(null)
    }
  } catch {
    // Fallback to prop data
    if (props.quiz?.questions?.length) {
      questions.value = props.quiz.questions
      answers.value = new Array(props.quiz.questions.length).fill(null)
    }
  } finally {
    isLoadingQuestions.value = false
  }
}

// ── Timer ──
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) { timeLeft.value-- }
    else { stopTimer(); handleSubmitAll() }
  }, 1000)
}

const stopTimer = () => {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
}

// ── Lifecycle ──
onMounted(() => {
  fetchQuestions()
  startTimer()
})
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

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-12px); }

.slide-right-enter-active, .slide-right-leave-active { transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-right-enter-from, .slide-right-leave-to { opacity: 0; transform: translateX(-16px); }
</style>
