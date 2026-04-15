<template>
  <div class="flex flex-col gap-8 w-full max-w-3xl mx-auto pb-10">
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
      <!-- Question Title / Difficulty -->
      <div v-if="question?.hint" class="px-8 py-5 border-b border-slate-50 bg-slate-50/50">
        <p class="text-[12px] font-bold text-slate-400 italic flex items-center gap-2">
          <i class="fa-regular fa-lightbulb text-amber-400 text-sm block"></i>
          {{ question.hint }}
        </p>
      </div>

      <!-- Passage with inline blanks -->
      <div class="px-8 py-8 text-[17px] text-slate-700 font-headline font-bold leading-[2.2] tracking-tight">
        <template v-for="(segment, sIdx) in parsed.segments" :key="sIdx">
          <span v-if="segment.type === 'text'">{{ segment.text }}</span>
          <span v-else class="inline-flex flex-col items-center mx-1 align-bottom">
            <input
              :ref="el => setInputRef(segment.blankIdx, el)"
              v-model="localAnswers[segment.blankIdx]"
              @input="handleInput"
              @focus="focusedBlankIdx = segment.blankIdx"
              @blur="focusedBlankIdx = null"
              @keydown.tab.prevent="focusNextBlank(segment.blankIdx)"
              class="border-0 border-b-2 bg-transparent outline-none text-center text-[16px] font-black transition-all duration-200 min-w-[80px] max-w-[160px]"
              :class="getBlankClass(segment.blankIdx)"
              :style="{ width: Math.max(80, (localAnswers[segment.blankIdx]?.length || 5) * 11 + 30) + 'px' }"
              :placeholder="isAnswerRevealed ? '' : '.....'"
              :disabled="isAnswerRevealed"
            />
            <span v-if="isAnswerRevealed"
              class="text-[10px] font-black text-emerald-500 mt-1 whitespace-nowrap">
              <i class="fa-solid fa-check mr-1"></i>{{ getCorrectHint(segment.blankIdx) }}
            </span>
          </span>
        </template>
      </div>

      <!-- Vocabulary bank -->
      <div class="px-8 pb-7 border-t border-slate-50 pt-5 bg-slate-50/30">
        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Ngân hàng từ gợi ý</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="word in vocabBank"
            :key="word"
            @click="insertWord(word)"
            :disabled="isAnswerRevealed || isWordUsed(word)"
            class="px-4 py-2 rounded-xl text-[13px] font-bold border transition-all cursor-pointer"
            :class="isWordUsed(word)
              ? 'line-through bg-slate-50 text-slate-300 border-slate-100 cursor-not-allowed'
              : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 shadow-sm'">
            {{ word }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: Array, default: () => [] },
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const BLANK_PATTERN = /\[BLANK\]|_{3,}/g

const parsed = computed(() => {
  const text = props.question?.question_text || ''
  const segments = []
  let blankIdx = 0
  const parts = text.split(BLANK_PATTERN)
  const blankMatches = text.match(BLANK_PATTERN) || []

  parts.forEach((part, idx) => {
    if (part) segments.push({ type: 'text', text: part })
    if (idx < blankMatches.length) {
      segments.push({ type: 'blank', blankIdx: blankIdx++ })
    }
  })

  // Fallback: if question_text has no [BLANK] markers, show the full text + 1 input at end
  if (blankIdx === 0) {
    // Clear and rebuild: text segment + one blank
    return {
      segments: [
        { type: 'text', text: text },
        { type: 'blank', blankIdx: 0 }
      ],
      blankCount: 1
    }
  }

  return { segments, blankCount: blankIdx }
})

const localAnswers = ref([])

watch(() => props.question, () => {
  localAnswers.value = [...(props.savedAnswer || [])]
  while (localAnswers.value.length < parsed.value.blankCount) {
    localAnswers.value.push('')
  }
}, { immediate: true })

watch(() => props.savedAnswer, (newVal) => {
  localAnswers.value = [...(newVal || [])]
  while (localAnswers.value.length < parsed.value.blankCount) {
    localAnswers.value.push('')
  }
})

const handleInput = () => {
  emit('update-answer', [...localAnswers.value])
}

// Stable shuffle once per question change
const vocabBank = ref([])
watch(() => props.question, () => {
  const words = (props.question?.options || []).map(o => o.option_text).filter(Boolean)
  for (let i = words.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [words[i], words[j]] = [words[j], words[i]]
  }
  vocabBank.value = words
}, { immediate: true })

const focusedBlankIdx = ref(null)
const inputRefs = ref({})

const setInputRef = (bIdx, el) => {
  if (el) inputRefs.value[bIdx] = el
}

const focusNextBlank = (bIdx) => {
  const nextEl = inputRefs.value[bIdx + 1]
  if (nextEl) nextEl.focus()
}

const isWordUsed = (word) => {
  return localAnswers.value.some(a => a.toLowerCase().trim() === word.toLowerCase().trim())
}

const insertWord = (word) => {
  if (props.isAnswerRevealed) return
  if (focusedBlankIdx.value !== null) {
    localAnswers.value[focusedBlankIdx.value] = word
    handleInput()
    focusNextBlank(focusedBlankIdx.value)
    return
  }
  const emptyIdx = localAnswers.value.findIndex(a => !a.trim())
  if (emptyIdx !== -1) {
    localAnswers.value[emptyIdx] = word
    handleInput()
    focusNextBlank(emptyIdx)
  }
}

// Only the options marked as correct (PHP returns is_correct as string "0"/"1", use Number() to handle correctly)
const correctOptions = computed(() =>
  (props.question?.options || []).filter(o => Number(o.is_correct) === 1)
)

// For generic use (backward compat) – first correct option per blank
const correctAnswers = computed(() =>
  correctOptions.value.map(o => o.option_text)
)

// For questions with 1 blank but multiple valid alternatives, show them joined
const getCorrectHint = (bIdx) => {
  if (parsed.value.blankCount <= 1 && correctOptions.value.length > 1) {
    return correctOptions.value.map(o => o.option_text).join(' / ')
  }
  return correctOptions.value[bIdx]?.option_text || ''
}

// isBlankCorrect: for single-blank, accept ANY correct option as valid
const isBlankCorrect = (bIdx) => {
  const user = (localAnswers.value[bIdx] || '').toLowerCase().trim()
  if (!user) return false

  if (parsed.value.blankCount <= 1) {
    // Single blank: any correct option is acceptable
    return correctOptions.value.some(o =>
      o.option_text.toLowerCase().trim() === user
    )
  }

  // Multi-blank: position-based match against the Nth correct option
  const correctAtPos = correctOptions.value[bIdx]
  return !!correctAtPos && correctAtPos.option_text.toLowerCase().trim() === user
}

const getBlankClass = (bIdx) => {
  if (focusedBlankIdx.value === bIdx) return 'border-emerald-500 text-emerald-700'
  if (props.isAnswerRevealed) {
    if (isBlankCorrect(bIdx)) return 'border-emerald-200 text-emerald-600'
    if ((localAnswers.value[bIdx] || '').trim()) return 'border-rose-300 text-rose-500 line-through'
    return 'border-slate-200 text-slate-300'
  }
  if ((localAnswers.value[bIdx] || '').trim()) return 'border-slate-800 text-slate-800'
  return 'border-slate-300 text-slate-400'
}
</script>
