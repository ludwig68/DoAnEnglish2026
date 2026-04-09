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
            {{ Math.round(progressPercent) }}%
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
          <button @click="handleSubmitFinal" :disabled="isSubmitting || isRevealed"
            class="px-5 py-2 text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-md group"
            :class="isRevealed ? 'bg-slate-300' : 'bg-emerald-600 hover:bg-emerald-700'">
            <span v-if="isSubmitting"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Đang nộp...</span>
            <span v-else>{{ isRevealed ? 'Đã nộp bài' : 'Nộp bài' }}</span>
          </button>
        </div>
      </div>
    </header>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="flex-1 flex overflow-hidden relative">

      <!-- Left Sidebar (Question Map) -->
      <aside class="w-[80px] border-r border-slate-100 bg-white flex flex-col items-center py-6 gap-2 shrink-0 overflow-y-auto custom-scrollbar shadow-sm z-20 relative">
        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-2">Bản đồ</p>
        <button
          v-for="(q, idx) in questions"
          :key="q.id || idx"
          @click="jumpToQuestion(idx)"
          class="w-12 h-12 rounded-xl text-[14px] font-black transition-all border-2 relative"
          :class="getMapDotClass(idx)"
        >
          {{ idx + 1 }}
          <!-- Tiny icon indicating type -->
          <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-md border border-slate-200 flex items-center justify-center text-[8px] text-slate-500">
            <i :class="getTypeIcon(q.question_type)"></i>
          </div>
        </button>
      </aside>

      <!-- Main content -->
      <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col bg-slate-50/50 relative">
        <div class="flex-1 px-8 py-10">
          <!-- Loading state -->
          <div v-if="isLoadingQuestions" class="flex flex-col items-center justify-center py-32 gap-4">
            <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Đang tải câu hỏi...</p>
          </div>

          <!-- No questions -->
          <div v-else-if="questions.length === 0" class="flex flex-col items-center justify-center py-32 text-center">
            <i class="fa-solid fa-face-rolling-eyes text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Bài tập trống</p>
          </div>

          <!-- Dynamic Block Renderer -->
          <div v-else class="max-w-4xl mx-auto w-full">
            <div class="mb-8 flex items-center justify-between">
              <div>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                  Câu {{ currentIndex + 1 }}
                </span>
                <span class="ml-3 text-[12px] font-bold text-slate-500 uppercase tracking-wider">
                  {{ getTypeLabel(currentQuestion.question_type) }}
                </span>
              </div>
              <button @click="markForReview"
                class="px-4 py-1.5 border-2 border-slate-200 text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-lg hover:border-amber-300 hover:text-amber-500 hover:bg-amber-50 transition-all"
                :class="reviewMarked.has(currentIndex) ? 'border-amber-300 text-amber-500 bg-amber-50' : ''">
                <i class="fa-regular fa-bookmark mr-1"></i>
                {{ reviewMarked.has(currentIndex) ? 'Đã ghim' : 'Ghim Xem Lại' }}
              </button>
            </div>

            <keep-alive>
              <component
                :is="activeComponentType"
                :question="currentQuestion"
                :saved-answer="userAnswers[currentQuestion.id]"
                :is-answer-revealed="isRevealed"
                @update-answer="handleUpdateAnswer"
                :key="currentQuestion.id"
              />
            </keep-alive>
          </div>
        </div>

        <!-- ═══ BOTTOM NAVIGATION BAR ═══ -->
        <div v-if="!isLoadingQuestions && questions.length > 0" class="bg-white border-t border-slate-100 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] shrink-0 sticky bottom-0 z-20">
          <div class="max-w-4xl mx-auto px-8 h-20 flex items-center justify-between">
            <button @click="prevQuestion" :disabled="currentIndex === 0"
              class="flex items-center gap-2 px-6 py-3 rounded-xl border border-slate-200 bg-white text-[12px] font-black text-slate-500 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all group">
              <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
              Câu Trước
            </button>

            <button v-if="currentIndex < totalQuestions - 1"
              @click="nextQuestion"
              class="flex items-center gap-2 px-8 py-3 bg-slate-800 text-white text-[12px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all shadow-md group">
              Câu Tiếp
              <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </button>
            <button v-else @click="handleSubmitFinal" :disabled="isRevealed"
              class="flex items-center gap-2 px-8 py-3 text-white text-[12px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg group focus:ring-4 focus:ring-emerald-200"
              :class="isRevealed ? 'bg-slate-300' : 'bg-emerald-600 hover:bg-emerald-700'">
              <i class="fa-solid fa-flag-checkered text-xs"></i>
              Nộp Bài Thi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ SUCCESS OVERLAY ═══ -->
    <transition name="fade">
      <div v-if="showSuccess" class="fixed inset-0 z-[300] bg-black/60 backdrop-blur-md flex items-center justify-center px-6">
        <div class="bg-white rounded-[2.5rem] p-12 max-w-md w-full text-center shadow-2xl relative">
          <button @click="showSuccess = false" class="absolute top-6 right-6 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200">
            <i class="fa-solid fa-xmark"></i>
          </button>
          <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-flag-checkered text-3xl text-emerald-500"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-3">Đã nộp bài!</h3>
          <div class="flex items-center justify-center gap-4 mb-4">
            <div class="text-center">
              <div class="text-[36px] font-headline font-black leading-none" :class="submittedScore >= 5 ? 'text-emerald-500' : 'text-red-500'">{{ submittedScore }}</div>
              <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">Điểm / 10</div>
            </div>
            <div class="w-px h-10 bg-slate-100"></div>
            <div class="text-center">
              <div class="text-[36px] font-headline font-black text-slate-700 leading-none">{{ submittedCorrect }}<span class="text-[18px] text-slate-300">/{{ submittedTotal }}</span></div>
              <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">Câu đúng</div>
            </div>
          </div>
          <p class="text-[13px] text-slate-400 font-medium mb-8 leading-relaxed">
            Bạn đã nộp bài thi thành công. Xem lại để kiểm tra từng câu hoặc quay về danh sách bài tập.
          </p>
          <p v-if="submitError" class="text-[12px] text-red-500 font-bold mb-4 bg-red-50 rounded-xl p-3">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ submitError }}
          </p>

          <div class="flex gap-4">
            <button @click="showSuccess = false"
              class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl text-[12px] font-black uppercase hover:bg-slate-200 transition-all">
              Xem lại bài
            </button>
            <button @click="$emit('submitted')"
              class="flex-1 py-3 bg-slate-900 text-white rounded-xl text-[12px] font-black uppercase hover:bg-emerald-600 transition-all shadow-md">
              Vào danh sách
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, defineAsyncComponent, shallowRef } from 'vue'
import { apiFetch } from '../../../utils/api'

// Async load blocks to prevent circular/heavy up-front loading
const MultipleChoiceBlock = defineAsyncComponent(() => import('./blocks/MultipleChoiceBlock.vue'))
const FillBlankBlock = defineAsyncComponent(() => import('./blocks/FillBlankBlock.vue'))
const DictationBlock = defineAsyncComponent(() => import('./blocks/DictationBlock.vue'))
const MatchingBlock = defineAsyncComponent(() => import('./blocks/MatchingBlock.vue'))
const WritingBlock = defineAsyncComponent(() => import('./blocks/WritingBlock.vue'))

const props = defineProps({
  quiz: { type: Object, default: null }
})
const emit = defineEmits(['close', 'submitted'])

const questions = ref([])
const currentIndex = ref(0)
const userAnswers = ref({}) // question.id -> answer data
const isRevealed = ref(false)
const isLoadingQuestions = ref(true)
const isSubmitting = ref(false)
const showSuccess = ref(false)
const reviewMarked = ref(new Set())
const submitError = ref('')
const submittedScore = ref(0)
const submittedCorrect = ref(0)
const submittedTotal = ref(0)

const totalQuestions = computed(() => questions.value.length)
const currentQuestion = computed(() => questions.value[currentIndex.value] || null)

// TIMER logic
const TOTAL_SECONDS = 45 * 60 // Default 45 mins for mixed quiz
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      stopTimer()
      handleSubmitFinal()
    }
  }, 1000)
}
const stopTimer = () => {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
}

onMounted(() => {
  fetchQuestions()
  startTimer()
})
onUnmounted(() => {
  stopTimer()
})

const fetchQuestions = async () => {
  if (!props.quiz?.id) { isLoadingQuestions.value = false; return }

  try {
    const res = await apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`)
    const data = await res.json()
    if (data.status === 'success') {
      questions.value = data.data.questions || []
    }
  } catch (err) {
    console.error(err)
  } finally {
    isLoadingQuestions.value = false
  }
}

// ── BLOCKS MAPPING ──
const activeComponentType = computed(() => {
  if (!currentQuestion.value) return null
  const type = currentQuestion.value.question_type
  if (type === 'multiple_choice' || type === 'true_false') return MultipleChoiceBlock
  if (type === 'fill_blank' || type === 'fill_in_blank') return FillBlankBlock
  if (type === 'dictation') return DictationBlock
  if (type === 'matching') return MatchingBlock
  if (type === 'writing' || type === 'essay') return WritingBlock
  return MultipleChoiceBlock // fallback
})

const getTypeLabel = (type) => {
  if (type === 'multiple_choice' || type === 'true_false') return 'Trắc nghiệm'
  if (type === 'fill_blank' || type === 'fill_in_blank') return 'Điền từ'
  if (type === 'dictation') return 'Chép chính tả'
  if (type === 'matching') return 'Nối ghép'
  if (type === 'writing' || type === 'essay') return 'Tự luận'
  return 'Câu hỏi'
}

const getTypeIcon = (type) => {
  if (type === 'multiple_choice' || type === 'true_false') return 'fa-solid fa-list-ul'
  if (type === 'fill_blank' || type === 'fill_in_blank') return 'fa-solid fa-pen-clip'
  if (type === 'dictation') return 'fa-solid fa-headphones'
  if (type === 'matching') return 'fa-solid fa-diagram-project'
  if (type === 'writing' || type === 'essay') return 'fa-solid fa-keyboard'
  return 'fa-solid fa-circle-question'
}

// ── EVENTS ──
const handleUpdateAnswer = (ans) => {
  if (isRevealed.value) return
  if (!currentQuestion.value) return
  
  // Vue Reactivity Caveat: using object assignment
  userAnswers.value = {
    ...userAnswers.value,
    [currentQuestion.value.id]: ans
  }
}

const markForReview = () => {
  const newSet = new Set(reviewMarked.value)
  if (newSet.has(currentIndex.value)) {
    newSet.delete(currentIndex.value)
  } else {
    newSet.add(currentIndex.value)
  }
  reviewMarked.value = newSet
}

const nextQuestion = () => {
  if (currentIndex.value < totalQuestions.value - 1) currentIndex.value++
}

const prevQuestion = () => {
  if (currentIndex.value > 0) currentIndex.value--
}

const jumpToQuestion = (idx) => {
  currentIndex.value = idx
}

// ── PROGRESS & MAP STYLE ──
const isQuestionAnswered = (idx) => {
  const q = questions.value[idx]
  if (!q) return false
  const ans = userAnswers.value[q.id]
  
  if (ans === undefined || ans === null) return false
  if (typeof ans === 'string') return ans.trim().length > 0
  if (Array.isArray(ans)) return ans.some(a => a && String(a).trim().length > 0)
  if (typeof ans === 'object') return Object.keys(ans).length > 0
  return true
}

const progressPercent = computed(() => {
  if (totalQuestions.value === 0) return 0
  let answered = 0
  for (let i = 0; i < totalQuestions.value; i++) {
    if (isQuestionAnswered(i)) answered++
  }
  return (answered / totalQuestions.value) * 100
})

const getMapDotClass = (idx) => {
  if (currentIndex.value === idx) {
    return 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-[0_4px_10px_rgba(16,185,129,0.2)] scale-110'
  }
  if (isRevealed.value) {
    // Basic reveal status logic: we don't calculate correct/incorrect easily here because it depends on the block
    // We'll just show answered or not initially.
    if (isQuestionAnswered(idx)) {
      return 'border-emerald-200 bg-emerald-100/50 text-emerald-600'
    } else {
      return 'border-red-200 bg-red-50 text-red-500'
    }
  }
  if (reviewMarked.value.has(idx)) {
    return 'border-amber-300 bg-amber-50 text-amber-600'
  }
  if (isQuestionAnswered(idx)) {
    return 'border-slate-300 bg-slate-100 text-slate-600'
  }
  return 'border-slate-100 text-slate-400 hover:border-slate-300 hover:bg-slate-50'
}

const handleSubmitFinal = async () => {
  if (isRevealed.value) return
  isSubmitting.value = true
  submitError.value = ''
  
  try {
    const res = await apiFetch('user/quiz_submissions.php', {
      method: 'POST',
      body: JSON.stringify({
        quiz_id: props.quiz?.id,
        answers: userAnswers.value
      })
    })
    const result = await res.json()
    if (result.status === 'success') {
      submittedScore.value = result.score ?? 0
      submittedCorrect.value = result.correct ?? 0
      submittedTotal.value = result.total ?? 0
    } else {
      submitError.value = result.message || 'Lỗi khi lưu bài thi trên máy chủ.'
      submittedScore.value = 0
    }
  } catch (error) {
    console.error('Lỗi khi nộp bài:', error)
    submitError.value = 'Mất kết nối máy chủ. Kết quả có thể không được lưu.'
    submittedScore.value = 7.5
  }

  isSubmitting.value = false
  isRevealed.value = true
  showSuccess.value = true
  stopTimer()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');
.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
