<template>
  <div class="flex flex-col gap-6 w-full max-w-5xl mx-auto pb-10">
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8">
      <p class="text-[14px] font-headline font-black text-slate-800 mb-6">
        {{ question?.question_text || 'Ghép các cặp từ tương ứng' }}
      </p>

      <div class="grid grid-cols-[1fr_80px_1fr] gap-4">
        <!-- LEFT COLUMN: Terms -->
        <div>
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-5 px-2">Cụm từ / Điểm neo</p>
          <div class="flex flex-col gap-4">
            <div
              v-for="(pair, idx) in pairs"
              :key="pair.id || idx"
              @click="!isAnswerRevealed && selectLeft(idx)"
              class="rounded-[1.5rem] border-2 p-5 transition-all duration-300 cursor-pointer select-none"
              :class="getLeftCardClass(idx)"
            >
              <p class="text-[9px] font-black uppercase tracking-[0.2em] mb-1.5"
                :class="selectedLeft === idx ? 'text-emerald-500' : (localAnswers[idx] !== undefined ? 'text-emerald-400' : 'text-slate-400')">
                Cụm {{ String(idx + 1).padStart(2, '0') }}
              </p>
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-[15px] font-headline font-black leading-snug"
                  :class="selectedLeft === idx ? 'text-emerald-700' : (localAnswers[idx] !== undefined ? 'text-slate-700' : 'text-slate-800')">
                  {{ pair.option_text }}
                </h3>

                <div class="shrink-0">
                  <div v-if="isAnswerRevealed && localAnswers[idx] !== undefined && isCorrectMatch(idx)"
                    class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center shadow-[0_4px_8px_rgba(22,163,74,0.3)]">
                    <i class="fa-solid fa-check text-white text-xs"></i>
                  </div>
                  <div v-else-if="isAnswerRevealed && localAnswers[idx] !== undefined && !isCorrectMatch(idx)"
                    class="w-8 h-8 bg-red-400 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-white text-xs"></i>
                  </div>
                  <div v-else-if="!isAnswerRevealed && localAnswers[idx] !== undefined"
                    class="w-7 h-7 bg-emerald-100 border border-emerald-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-link text-emerald-500 text-[10px]"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CENTER: Connector icon -->
        <div class="flex items-center justify-center pt-16">
          <button @click="shuffleRight" :disabled="isAnswerRevealed"
            class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 hover:border-emerald-300 hover:text-emerald-500 hover:shadow-md transition-all cursor-pointer disabled:opacity-50">
            <i class="fa-solid fa-shuffle text-xs"></i>
          </button>
        </div>

        <!-- RIGHT COLUMN: Definitions -->
        <div>
          <div class="flex items-center justify-between mb-5 px-2">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Định nghĩa</p>
          </div>
          <div class="flex flex-col gap-4">
            <div
              v-for="(def, dIdx) in shuffledRight"
              :key="def.tempId || dIdx"
              @click="!isAnswerRevealed && selectRight(dIdx)"
              class="rounded-[1.5rem] border-2 p-5 transition-all duration-300 select-none min-h-[80px] flex items-center"
              :class="getRightCardClass(dIdx)"
            >
              <div class="flex-1">
                <p class="text-[13px] font-bold leading-relaxed"
                  :class="getRightTextClass(dIdx)">
                  {{ def.match_text }}
                </p>
              </div>

              <!-- Link icon when selected + active left -->
              <div v-if="selectedLeft !== null && !isMatched(dIdx)"
                class="ml-3 w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0 opacity-50">
                <i class="fa-solid fa-link text-emerald-400 text-[8px]"></i>
              </div>
              <div v-else-if="isMatched(dIdx) && !isAnswerRevealed"
                class="ml-3 w-6 h-6 rounded-xl bg-emerald-500 shadow-[0_2px_8px_rgba(16,185,129,0.3)] flex items-center justify-center shrink-0">
                <span class="text-white text-[10px] font-black">{{ String(getMatchedLeftIdx(dIdx) + 1).padStart(2, '0') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Correct Answers After Submit -->
    <div v-if="isAnswerRevealed" class="mt-4 bg-white rounded-[2rem] border border-slate-100 p-8 shadow-sm">
      <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Kết quả chi tiết</p>
      <div class="space-y-3">
        <div v-for="(pair, idx) in pairs" :key="pair.id || idx"
          class="flex items-start gap-4 p-4 rounded-2xl border text-[12px]"
          :class="isCorrectMatch(idx) ? 'border-emerald-100 bg-emerald-50/60' : 'border-red-100 bg-red-50/50'">
          <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
            :class="isCorrectMatch(idx) ? 'bg-emerald-500' : 'bg-red-400'">
            <i :class="isCorrectMatch(idx) ? 'fa-solid fa-check' : 'fa-solid fa-xmark'" class="text-white text-[8px]"></i>
          </div>
          <div class="flex-1 min-w-0">
            <span class="font-black text-slate-700">{{ pair.option_text }}</span>
            <span class="mx-2 text-slate-300">→</span>
            <span class="font-bold text-slate-500">{{ pair.match_text }}</span>
            <p v-if="!isCorrectMatch(idx)" class="mt-1 text-red-500 font-bold">
              Đáp án của bạn: {{ localAnswers[idx] !== undefined ? shuffledRight[localAnswers[idx]]?.match_text : 'Chưa ghép' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: Object, default: () => ({}) }, // { leftIdx: rightIdx }
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const pairs = computed(() => {
  return (props.question?.options || []).filter(o => o.match_text)
})

const localAnswers = ref({}) // leftIdx -> rightIdx in shuffledRight

// We must securely shuffle right side ONCE per question so it's stable
const shuffledRight = ref([])

const shuffleRight = () => {
  if (props.isAnswerRevealed) return
  let arr = pairs.value.map((p, i) => ({ ...p, originalIdx: i }))
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  shuffledRight.value = arr
  // Remap localAnswers if they existed?
  // Since we don't want to break existing answers, it's safer to just clear answers when shuffling manually
  localAnswers.value = {}
  emit('update-answer', {})
}

onMounted(() => {
  shuffleRight()
  // Hydrate saved answers if any
  if (Object.keys(props.savedAnswer || {}).length > 0) {
    localAnswers.value = { ...props.savedAnswer }
  }
})

const selectedLeft = ref(null)

const selectLeft = (idx) => {
  if (selectedLeft.value === idx) {
    selectedLeft.value = null
  } else {
    selectedLeft.value = idx
    if (localAnswers.value[idx] !== undefined) {
      delete localAnswers.value[idx]
      emit('update-answer', { ...localAnswers.value })
    }
  }
}

const selectRight = (idx) => {
  if (selectedLeft.value === null) {
    // If clicking a right card that is already matched, unmatch it
    const pairEntry = Object.entries(localAnswers.value).find(([_, rIdx]) => rIdx === idx)
    if (pairEntry) {
      delete localAnswers.value[pairEntry[0]]
      emit('update-answer', { ...localAnswers.value })
    }
    return
  }
  
  // Unlink if right side is already linked
  const existingPair = Object.entries(localAnswers.value).find(([_, rIdx]) => rIdx === idx)
  if (existingPair) {
    delete localAnswers.value[existingPair[0]]
  }
  
  localAnswers.value[selectedLeft.value] = idx
  selectedLeft.value = null
  emit('update-answer', { ...localAnswers.value })
}

const isMatched = (rightIdx) => {
  return Object.values(localAnswers.value).includes(rightIdx)
}

const getMatchedLeftIdx = (rightIdx) => {
  const match = Object.entries(localAnswers.value).find(([l, r]) => r === rightIdx)
  if (match) return parseInt(match[0])
  return null
}

const isCorrectMatch = (leftIdx) => {
  const userRIdx = localAnswers.value[leftIdx]
  if (userRIdx === undefined) return false
  return shuffledRight.value[userRIdx]?.originalIdx === leftIdx
}

const getLeftCardClass = (idx) => {
  if (selectedLeft.value === idx) return 'border-emerald-500 shadow-[0_8px_20px_rgba(16,185,129,0.15)] bg-white'
  if (props.isAnswerRevealed) {
    if (localAnswers.value[idx] === undefined) return 'border-red-200 bg-red-50/50'
    return isCorrectMatch(idx) ? 'border-emerald-200 bg-emerald-50/30' : 'border-red-300 bg-red-50'
  }
  if (localAnswers.value[idx] !== undefined) return 'border-emerald-200 bg-emerald-50/30 hover:border-emerald-300'
  return 'border-slate-100 bg-white hover:border-emerald-300 hover:shadow-sm'
}

const getRightCardClass = (idx) => {
  if (selectedLeft.value !== null && !isMatched(idx)) return 'border-slate-200 bg-slate-50 border-dashed cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50'
  if (isMatched(idx)) {
    if (props.isAnswerRevealed) {
      const leftIdx = Object.entries(localAnswers.value).find(e => e[1] === idx)?.[0]
      return isCorrectMatch(leftIdx) ? 'border-emerald-200 bg-emerald-50/30' : 'border-red-300 bg-red-50'
    }
    return 'border-emerald-200 bg-emerald-50/30 cursor-pointer hover:border-emerald-300'
  }
  return 'border-slate-100 bg-white cursor-pointer hover:border-slate-300'
}

const getRightTextClass = (idx) => {
  if (selectedLeft.value !== null && !isMatched(idx)) return 'text-slate-500'
  if (isMatched(idx)) return 'text-emerald-700'
  return 'text-slate-600'
}
</script>
