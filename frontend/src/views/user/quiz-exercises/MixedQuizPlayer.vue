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
          <button @click="handleSubmitFinal" :disabled="isSubmitting || isRevealed || isWritingLocked"
            class="px-5 py-2 text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-md group"
            :class="isRevealed || isWritingLocked ? 'bg-slate-300 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
            <span v-if="isSubmitting"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Đang nộp...</span>
            <span v-else-if="isWritingLocked"><i class="fa-solid fa-lock mr-1"></i>Đã nộp</span>
            <span v-else>{{ isRevealed ? 'Đã nộp bài' : 'Nộp bài' }}</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Writing Locked Banner -->
    <div v-if="isWritingLocked"
      class="bg-amber-50 border-b border-amber-200 px-6 py-3 flex items-center justify-between gap-4 shrink-0 z-10">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-lock text-amber-500"></i>
        <span class="text-[13px] font-bold text-amber-700">
          Bài tự luận đã được nộp — chỉ được nộp một lần. Bạn có thể xem lại bài viết của mình.
        </span>
        <span v-if="prevSubmission?.sub_status === 'pending_grading'"
          class="px-3 py-1 bg-amber-200 text-amber-700 rounded-full text-[10px] font-black uppercase tracking-wider">
          <i class="fa-solid fa-hourglass-half mr-1"></i> Chờ giảng viên chấm
        </span>
        <span v-else-if="prevSubmission?.sub_status === 'completed'"
          class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-wider">
          <i class="fa-solid fa-check-circle mr-1"></i> Điểm: {{ prevSubmission.score }}/10
        </span>
      </div>
      <button @click="$emit('close')" class="text-amber-500 hover:text-amber-700 font-black text-[12px] uppercase tracking-widest">
        Quay lại
      </button>
    </div>

    <!-- ═══ MAIN LAYOUT ═══ -->
    <div class="flex-1 flex overflow-hidden relative">

      <!-- Left Sidebar (Question Map) -->
      <aside class="w-[80px] border-r border-slate-100 bg-white flex flex-col items-center py-6 gap-2 shrink-0 overflow-y-auto custom-scrollbar shadow-sm z-20 relative">
        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-2">Bản đồ</p>
        <button
          v-for="(q, idx) in questions"
          :key="q.id || idx"
          @click="jumpToQuestion(idx)"
          class="w-12 h-12 text-[14px] font-black relative"
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

            <!-- ─ Teacher Feedback Box ─ -->
            <transition name="fade">
              <div v-if="isRevealed && prevSubmission?.sub_status === 'completed' && (currentQuestion?.question_type === 'writing' || currentQuestion?.question_type === 'essay') && (prevSubmission?.feedback || parsedRubric)" 
                class="mt-10 bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-emerald-500/5 p-8 relative overflow-hidden group">
                
                <!-- Decorative background elements -->
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50 group-hover:bg-emerald-100 transition-colors duration-700 pointer-events-none"></div>

                <div class="flex flex-col md:flex-row gap-10 relative z-10">
                  <!-- Rubric Details -->
                  <div v-if="parsedRubric" class="flex-1 space-y-6">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-600 flex items-center gap-2 mb-2">
                      <i class="fa-solid fa-chart-pie"></i> Điểm thành phần
                    </h4>
                    
                    <div v-for="(item, idx) in parsedRubric" :key="idx" class="space-y-3">
                      <div class="flex items-end justify-between">
                        <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest">{{ item.label }}</span>
                        <span class="text-xl font-headline font-black transition-colors"
                          :class="item.score >= 7 ? 'text-emerald-500' : item.score >= 5 ? 'text-amber-500' : 'text-red-400'">
                          {{ parseFloat(item.score).toFixed(1) }}
                        </span>
                      </div>
                      <div class="relative h-2 bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                        <div class="h-full rounded-full transition-all duration-1000"
                          :class="item.score >= 7 ? 'bg-emerald-500' : item.score >= 5 ? 'bg-amber-400' : 'bg-red-400'"
                          :style="{ width: `${(item.score / 10) * 100}%` }">
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Text Feedback -->
                  <div class="flex-1 relative">
                    <div v-if="parsedRubric" class="absolute -left-5 top-0 bottom-0 w-px bg-slate-100 hidden md:block"></div>
                    <div class="md:pl-5">
                      <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-600 flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-comment-dots"></i> Nhận xét từ giảng viên
                      </h4>
                      <div class="text-[13px] text-slate-700 leading-[1.8] font-bold whitespace-pre-wrap italic bg-emerald-50/50 p-5 rounded-2xl border border-emerald-100/50 relative">
                        <i class="fa-solid fa-quote-left absolute -top-3 -left-3 text-2xl text-emerald-200"></i>
                         {{ prevSubmission?.feedback || 'Chưa có nhận xét chi tiết bằng văn bản.' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
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
            <!-- Case 1: All objective questions (No writing) -->
            <template v-if="!hasWritingResponse">
              <div class="text-center">
                <div class="text-[36px] font-headline font-black leading-none" :class="submittedScore >= (submittedTotal/2) ? 'text-emerald-500' : 'text-red-500'">
                  {{ Math.round(submittedScore) }}<span class="text-[18px] text-slate-300">/{{ submittedTotal }}</span>
                </div>
                <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">
                  Tổng điểm đạt được
                </div>
              </div>
              <div class="w-px h-10 bg-slate-100"></div>
              <div class="text-center">
                <div class="text-[36px] font-headline font-black text-slate-700 leading-none">{{ submittedCorrect }}<span class="text-[18px] text-slate-300">/{{ submittedTotal }}</span></div>
                <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">Câu đúng</div>
              </div>
            </template>

            <!-- Case 2: Quiz with Writing (Pending) -->
            <template v-else>
              <div class="py-4 text-center">
                <p class="text-[11px] font-black uppercase tracking-widest text-amber-600">Bài thi đang chờ chấm</p>
              </div>
            </template>
          </div>


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
  quiz: { type: Object, default: null },
  forceReview: { type: Boolean, default: false }
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
const hasWritingResponse = ref(false)
const questionResults = ref({}) // qId -> isCorrect (bool or 'pending')

// Previous submission state
const prevSubmission = ref(null)  // { score, sub_status, answers_json, submitted_at, feedback, rubric_data }
const isWritingLocked = ref(false) // true if quiz has essay and already submitted once

const totalQuestions = computed(() => questions.value.length)
const currentQuestion = computed(() => questions.value[currentIndex.value] || null)

const parsedRubric = computed(() => {
  if (!prevSubmission.value?.rubric_data) return null;
  try {
    const data = typeof prevSubmission.value.rubric_data === 'string'
      ? JSON.parse(prevSubmission.value.rubric_data)
      : prevSubmission.value.rubric_data;

    const map = {
      grammar: 'Độ chính xác ngữ pháp',
      cohesion: 'Mạch lạc & Liên kết',
      lexical: 'Tài nguyên từ vựng',
      task: 'Hoàn thành yêu cầu đề bài'
    };

    return Object.keys(data).map(key => ({
      label: map[key] || key,
      score: data[key]
    }));
  } catch(e) {
    return null;
  }
})

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
  if (isRevealed.value || isWritingLocked.value) return
  
  timerInterval = setInterval(() => {
    if (isRevealed.value || isWritingLocked.value) {
      stopTimer()
      return
    }
    
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

onMounted(async () => {
  await fetchQuestions()
  if (!isRevealed.value && !isWritingLocked.value && !props.forceReview) {
    startTimer()
  }
})
onUnmounted(() => {
  stopTimer()
})

const fetchQuestions = async () => {
  if (!props.quiz?.id) { isLoadingQuestions.value = false; return }

  try {
    // Load questions and previous submission in parallel
    const [qRes, subRes] = await Promise.all([
      apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`),
      apiFetch(`user/quiz_submissions.php?quiz_id=${props.quiz.id}`)
    ])
    const [qData, subData] = await Promise.all([qRes.json(), subRes.json()])

    if (qData.status === 'success') {
      questions.value = qData.data.questions || []
    }

    if (subData.status === 'success' && subData.submitted) {
      prevSubmission.value = subData
      submittedScore.value = subData.score ?? 0

      // Check if quiz has writing questions OR we are in force-review mode
      const hasEssay = questions.value.some(q => q.question_type === 'writing' || q.question_type === 'essay')
      if (hasEssay || props.forceReview) {
        isWritingLocked.value = true
        isRevealed.value = true
        // Restore previous answers for review
        try {
          const prevAnswers = typeof subData.answers_json === 'string' 
            ? JSON.parse(subData.answers_json || '{}') 
            : (subData.answers_json || {})
          userAnswers.value = prevAnswers
        } catch(e) {
          console.error("Failed to parse answers", e)
        }
      }
    } else if (props.forceReview) {
      // Force review even if no submission found (shouldn't happen with status='completed' but for safety)
      isRevealed.value = true
      isWritingLocked.value = true
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
  const q = questions.value[idx]
  const isCurrent = currentIndex.value === idx
  const result = (isRevealed.value && q) ? questionResults.value[q.id] : null

  // 1. Base style (colors)
  let baseStyle = ''
  if (isRevealed.value) {
    if (result === 'pending') {
      baseStyle = 'bg-amber-50 text-amber-600 border-amber-200'       // Writing - pending
    } else if (result === 'skipped') {
      baseStyle = 'bg-slate-50 text-slate-300 border-slate-100'        // Not answered
    } else if (result === true) {
      baseStyle = 'bg-emerald-100 text-emerald-700 border-emerald-300' // Correct - soft green
    } else if (result === false) {
      baseStyle = 'bg-rose-100 text-rose-500 border-rose-200'          // Wrong - soft rose
    } else {
      baseStyle = 'bg-slate-50 text-slate-300 border-slate-100'        // Unknown/not graded
    }
  } else {
    // Current taking quiz style
    if (reviewMarked.value.has(idx)) {
      baseStyle = 'border-amber-300 bg-amber-50 text-amber-600'
    } else if (isQuestionAnswered(idx)) {
      baseStyle = 'border-slate-300 bg-slate-100 text-slate-600'
    } else {
      baseStyle = 'border-slate-100 text-slate-400 hover:border-slate-300 hover:bg-slate-50'
    }
  }

  // 2. Active highlight
  const activeStyle = isCurrent 
    ? 'ring-4 ring-slate-900 ring-offset-2 z-10 scale-110 shadow-xl' 
    : 'border-2'

  return `${baseStyle} ${activeStyle} rounded-2xl flex items-center justify-center transition-all duration-300`
}

const handleSubmitFinal = async () => {
  if (isRevealed.value || isWritingLocked.value) return
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

    if (result.locked) {
      // Essay already submitted — force lock
      isWritingLocked.value = true
      isRevealed.value = true
      submitError.value = 'Bài tự luận chỉ được nộp một lần.'
      isSubmitting.value = false
      stopTimer()
      return
    }

    if (result.status === 'success') {
      submittedScore.value = result.score ?? 0
      submittedCorrect.value = result.correct ?? 0
      submittedTotal.value = result.total ?? 0
      hasWritingResponse.value = !!result.has_writing
      questionResults.value = result.results || {}
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

.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
