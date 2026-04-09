<template>
  <div class="fixed inset-0 z-[200] bg-slate-50 flex flex-col font-body overflow-hidden">

    <!-- ═══ TOP NAVIGATION BAR ═══ -->
    <header class="bg-white border-b border-slate-100 shadow-sm shrink-0 relative z-10">
      <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between gap-6">

        <!-- Left: Back Button -->
        <div class="flex items-center gap-4">
          <button @click="$emit('close')" class="flex items-center gap-2 px-4 py-2 text-[12px] font-black text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            Quay lại
          </button>
        </div>

        <!-- Center: Assignment Info -->
        <div class="text-center hidden sm:block">
          <p class="text-[13px] font-headline font-black text-slate-800 tracking-tight leading-none">
            {{ quiz?.title || 'Bài tập viết luận' }}
          </p>
          <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
            {{ quiz?.category ? categoryLabel[quiz.category] : 'Writing Module' }}
          </p>
        </div>

        <!-- Right: Submit -->
        <button @click="handleSubmit" :disabled="isSubmitting"
          class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-[12px] font-black uppercase tracking-widest shadow-[0_4px_12px_rgba(22,163,74,0.3)] hover:bg-emerald-700 hover:shadow-[0_6px_18px_rgba(22,163,74,0.4)] hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
          <span v-if="isSubmitting" class="flex items-center gap-2"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang nộp...</span>
          <span v-else>Nộp bài</span>
        </button>
      </div>

      <!-- Progress sub-bar -->
      <div class="border-t border-slate-50 px-6 py-3 flex items-center gap-6 max-w-5xl mx-auto w-full">
        <!-- Timer -->
        <div class="flex items-center gap-2 shrink-0">
          <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
            :class="timeLeft < 300 ? 'border-red-400' : 'border-emerald-500'">
            <div class="w-2 h-2 rounded-full"
              :class="timeLeft < 300 ? 'bg-red-400 animate-pulse' : 'bg-emerald-500'"></div>
          </div>
          <span class="text-[12px] font-black tabular-nums"
            :class="timeLeft < 300 ? 'text-red-500 animate-pulse' : 'text-slate-700'">
            {{ formattedTime }} còn lại
          </span>
        </div>

        <!-- Progress bar -->
        <div class="flex-1 flex items-center gap-3">
          <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-500"
              :style="{ width: sessionProgress + '%' }"></div>
          </div>
          <span class="text-[11px] font-black text-slate-400 shrink-0">Session: {{ sessionProgress }}%</span>
        </div>
      </div>
    </header>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="flex-1 overflow-y-auto no-scrollbar">
      <div class="max-w-5xl mx-auto px-6 py-10 flex gap-8">

        <!-- Content Column -->
        <div class="flex-1 min-w-0 flex flex-col gap-8">

          <!-- Essay Prompt Card -->
          <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-10">
            <div class="flex items-center gap-4 mb-6">
              <span class="px-3 py-1.5 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-[0.2em] rounded-lg shadow-sm">
                Đề bài
              </span>
              <span class="text-[11px] font-bold text-slate-400">
                <i class="fa-solid fa-pen-to-square mr-1 text-slate-300"></i>
                {{ minWords }} từ tối thiểu
              </span>
            </div>

            <h2 class="text-[22px] font-headline font-black text-slate-800 leading-[1.3] mb-6 tracking-tight">
              {{ quiz?.description || 'Viết một bài luận về chủ đề được giao.' }}
            </h2>

            <div class="space-y-3 text-[14px] text-slate-500 font-medium leading-relaxed border-t border-slate-50 pt-6">
              <p>Hãy trình bày quan điểm của bạn một cách rõ ràng và thuyết phục.</p>
              <p>Đưa ra các lý do và ví dụ cụ thể từ kiến thức hoặc kinh nghiệm của bạn.</p>
            </div>
          </div>

          <!-- Editor Card -->
          <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm flex flex-col overflow-hidden">
            <!-- Toolbar -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-50">
              <div class="flex items-center gap-1">
                <button @click="applyFormat('bold')"
                  class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all font-black text-sm">
                  <strong>B</strong>
                </button>
                <button @click="applyFormat('italic')"
                  class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all text-sm italic">
                  <em>I</em>
                </button>
                <button @click="applyFormat('insertUnorderedList')"
                  class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all text-sm">
                  <i class="fa-solid fa-list-ul text-xs"></i>
                </button>
                <div class="w-px h-5 bg-slate-100 mx-2"></div>
                <button @click="clearEditor"
                  class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-400 transition-all text-xs">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </div>

              <!-- Word Count -->
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">SỐ TỪ:</span>
                <span class="text-[12px] font-black" :class="wordCount >= minWords ? 'text-emerald-600' : 'text-slate-500'">
                  {{ wordCount }}
                </span>
                <span class="text-[10px] text-slate-300 font-bold">/ {{ minWords }}</span>
              </div>
            </div>

            <!-- Contenteditable Editor -->
            <div
              ref="editorRef"
              contenteditable="true"
              @input="handleEditorInput"
              class="flex-1 min-h-[420px] px-8 py-6 text-[15px] text-slate-700 leading-[1.85] outline-none font-body focus:ring-0"
              :class="{ 'text-slate-300': !editorContent }"
              data-placeholder="Bắt đầu viết bài luận của bạn tại đây...">
            </div>

            <!-- Word count progress bar -->
            <div class="px-6 py-3 border-t border-slate-50">
              <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="wordCount >= minWords ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : 'bg-gradient-to-r from-blue-400 to-emerald-400'"
                  :style="{ width: Math.min((wordCount / minWords) * 100, 100) + '%' }"></div>
              </div>
            </div>
          </div>

          <!-- Vocabulary Suggestions -->
          <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8">
            <div class="flex items-center gap-2 mb-5">
              <div class="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center">
                <i class="fa-solid fa-wand-magic-sparkles text-emerald-500 text-xs"></i>
              </div>
              <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-600">Gợi ý từ vựng</h4>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="word in vocabularySuggestions"
                :key="word"
                @click="insertVocab(word)"
                class="px-4 py-2 bg-slate-50 text-slate-600 text-[12px] font-bold rounded-xl border border-slate-100 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 transition-all cursor-pointer">
                {{ word }}
              </button>
            </div>
          </div>

        </div>

        <!-- Right Sidebar -->
        <div class="w-14 flex flex-col gap-3 shrink-0 pt-2">
          <!-- Hint -->
          <button @click="showHint = !showHint"
            class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center gap-1 transition-all shadow-sm"
            :class="showHint ? 'bg-emerald-500 text-white shadow-[0_4px_12px_rgba(22,163,74,0.3)]' : 'bg-white border border-slate-100 text-slate-400 hover:border-emerald-200 hover:text-emerald-500'">
            <i class="fa-regular fa-lightbulb text-sm"></i>
          </button>

          <!-- Grammar Check -->
          <button
            class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-slate-400 flex items-center justify-center hover:border-blue-200 hover:text-blue-500 transition-all shadow-sm">
            <i class="fa-solid fa-spell-check text-sm"></i>
          </button>

          <!-- Translate -->
          <button
            class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-slate-400 flex items-center justify-center hover:border-purple-200 hover:text-purple-500 transition-all shadow-sm">
            <i class="fa-solid fa-language text-sm"></i>
          </button>

          <!-- Fullscreen -->
          <button @click="toggleFullscreen"
            class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-slate-400 flex items-center justify-center hover:border-slate-300 hover:text-slate-700 transition-all shadow-sm">
            <i :class="isFullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" class="text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ HINT PANEL ═══ -->
    <transition name="slide-up">
      <div v-if="showHint" class="fixed bottom-0 left-0 right-0 z-50">
        <div class="max-w-5xl mx-auto px-6 pb-6">
          <div class="bg-emerald-600 rounded-[2rem] p-8 shadow-xl text-white flex items-start gap-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0 backdrop-blur-sm">
              <i class="fa-regular fa-lightbulb text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[11px] font-black uppercase tracking-widest opacity-70 mb-1">Gợi ý</p>
              <p class="text-[15px] font-bold leading-relaxed">
                {{ currentHint }}
              </p>
              <button @click="nextHint" class="mt-3 text-[10px] font-black uppercase tracking-widest opacity-70 hover:opacity-100 transition-opacity flex items-center gap-1.5">
                Gợi ý khác <i class="fa-solid fa-arrow-right text-[8px]"></i>
              </button>
            </div>
            <button @click="showHint = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 transition-colors shrink-0">
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ═══ SUCCESS OVERLAY ═══ -->
    <transition name="fade">
      <div v-if="showSuccess" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-md flex items-center justify-center px-6">
        <div class="bg-white rounded-[2.5rem] p-12 max-w-sm w-full text-center shadow-2xl animate-in zoom-in duration-300">
          <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-3">Nộp bài thành công!</h3>
          <p class="text-sm text-slate-400 font-medium mb-8 leading-relaxed">
            Bài viết của bạn đã được gửi đi. Giảng viên sẽ chấm điểm và phản hồi sớm nhất có thể.
          </p>
          <div class="bg-emerald-50 rounded-2xl px-6 py-4 mb-8 border border-emerald-100/50">
            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-1">Số từ đã viết</p>
            <p class="text-3xl font-headline font-black text-slate-800">{{ wordCount }}</p>
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
 * WritingExercise.vue
 * Component làm bài tập viết luận (writing type).
 * Hiển thị đề bài, editor có đếm từ, toolbar định dạng cơ bản,
 * gợi ý từ vựng, hint panel, và nộp bài.
 */
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

// ── Props ──
const props = defineProps({
  quiz: {
    type: Object,
    default: null
  },
  courseName: {
    type: String,
    default: ''
  }
})

// ── Emits ──
const emit = defineEmits(['close', 'submitted'])

// ── State ──
const editorRef = ref(null)
const editorContent = ref('')
const showHint = ref(false)
const showSuccess = ref(false)
const isSubmitting = ref(false)
const isFullscreen = ref(false)
const hintIndex = ref(0)

// Timer (45 min by default)
const TOTAL_SECONDS = 45 * 60
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null

// ── Constants ──
const minWords = 250

const categoryLabel = {
  writing: 'Writing Module',
  speaking: 'Speaking Module',
  grammar: 'Grammar Module',
  vocabulary: 'Vocabulary Module',
  reading: 'Reading Module',
  listening: 'Listening Module',
  summary: 'Summary Module',
}

const hints = [
  'Hãy bắt đầu bằng một câu mở đầu mạnh mẽ để giới thiệu quan điểm của bạn.',
  'Cấu trúc bài viết gồm: Giới thiệu → Luận điểm 1 → Luận điểm 2 → Kết luận.',
  'Hãy sử dụng các từ nối (connectives) như "Furthermore", "However", "In contrast"...',
  'Đưa ra ví dụ thực tế để củng cố lập luận của bạn.',
  'Kết bài nên tóm tắt lại các luận điểm chính và đưa ra kết luận dứt khoát.',
]

const vocabularySuggestions = [
  'Inadvertent', 'Congestion', 'Mitigate', 'Legislative framework',
  'Prevalent', 'Consequently', 'Substantially', 'Furthermore',
  'In contrast', 'Nevertheless', 'Crucial', 'Implement',
]

// ── Computed ──
const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const wordCount = computed(() => {
  const text = editorContent.value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
  if (!text) return 0
  return text.split(' ').filter(w => w.length > 0).length
})

const sessionProgress = computed(() => {
  const ratio = (TOTAL_SECONDS - timeLeft.value) / TOTAL_SECONDS
  return Math.round(ratio * 100)
})

const currentHint = computed(() => hints[hintIndex.value % hints.length])

// ── Methods ──
const handleEditorInput = () => {
  editorContent.value = editorRef.value?.innerHTML || ''
}

const applyFormat = (command) => {
  document.execCommand(command, false, null)
  editorRef.value?.focus()
}

const clearEditor = () => {
  if (confirm('Xóa toàn bộ nội dung bài viết?')) {
    if (editorRef.value) editorRef.value.innerHTML = ''
    editorContent.value = ''
  }
}

const insertVocab = (word) => {
  const sel = window.getSelection()
  if (sel && sel.rangeCount > 0 && editorRef.value?.contains(sel.anchorNode)) {
    const range = sel.getRangeAt(0)
    range.deleteContents()
    range.insertNode(document.createTextNode(word + ' '))
    range.collapse(false)
    sel.removeAllRanges()
    sel.addRange(range)
  } else {
    editorRef.value?.focus()
    document.execCommand('insertText', false, word + ' ')
  }
  editorContent.value = editorRef.value?.innerHTML || ''
}

const nextHint = () => {
  hintIndex.value = (hintIndex.value + 1) % hints.length
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

const handleSubmit = async () => {
  if (wordCount.value < minWords) {
    alert(`Bài viết cần tối thiểu ${minWords} từ. Hiện tại bạn mới viết ${wordCount.value} từ.`)
    return
  }
  isSubmitting.value = true
  // Simulate submit (replace with real API call later)
  await new Promise(resolve => setTimeout(resolve, 1200))
  isSubmitting.value = false
  showSuccess.value = true
  stopTimer()
  emit('submitted', {
    quiz_id: props.quiz?.id,
    content: editorContent.value,
    word_count: wordCount.value,
  })
}

const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      stopTimer()
      handleSubmit()
    }
  }, 1000)
}

const stopTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
}

// ── Lifecycle ──
onMounted(() => {
  startTimer()
  nextTick(() => {
    if (editorRef.value) {
      editorRef.value.focus()
    }
  })
})

onUnmounted(() => {
  stopTimer()
})
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

/* Placeholder for contenteditable */
[contenteditable][data-placeholder]:empty::before {
  content: attr(data-placeholder);
  color: #cbd5e1;
  pointer-events: none;
  font-style: italic;
}

/* Slide up transition for hint panel */
.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

/* Fade transition for success overlay */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
