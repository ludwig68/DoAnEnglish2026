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
              <div class="flex items-center justify-between mb-2">
                <p class="text-[9px] font-black uppercase tracking-[0.2em]"
                  :class="selectedLeft === idx ? 'text-emerald-500' : (localAnswers[pair.id] !== undefined ? 'text-emerald-400' : 'text-slate-400')">
                  Cụm {{ String(idx + 1).padStart(2, '0') }}
                </p>
                <div v-if="isAnswerRevealed" class="flex items-center gap-1.5">
                  <template v-if="localAnswers[pair.id] === undefined">
                     <span class="text-[9px] font-black uppercase text-slate-400">Chưa làm</span>
                     <div class="w-6 h-6 bg-slate-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-minus text-slate-300 text-[10px]"></i>
                     </div>
                  </template>
                  <template v-else>
                    <span v-if="!isCorrectMatch(idx)" class="text-[9px] font-black uppercase text-rose-400">Sai</span>
                    <span v-else class="text-[9px] font-black uppercase text-emerald-500">Đúng</span>
                    <div class="shrink-0">
                      <div v-if="isCorrectMatch(idx)"
                        class="w-6 h-6 bg-emerald-100 border border-emerald-300 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                      </div>
                      <div v-else
                        class="w-6 h-6 bg-rose-200 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-rose-500 text-[10px]"></i>
                      </div>
                    </div>
                  </template>
                </div>
                <div v-else-if="localAnswers[pair.id] !== undefined"
                  class="w-6 h-6 bg-emerald-100 border border-emerald-200 rounded-lg flex items-center justify-center">
                  <i class="fa-solid fa-link text-emerald-500 text-[8px]"></i>
                </div>
              </div>

              <h3 class="text-[15px] font-headline font-black leading-snug"
                :class="selectedLeft === idx ? 'text-emerald-700' : (localAnswers[pair.id] !== undefined ? 'text-slate-700' : 'text-slate-800')">
                {{ pair.option_text }}
              </h3>
              
              <!-- Revealed solution below term -->
              <div v-if="isAnswerRevealed" class="mt-3 pt-3 border-t border-slate-50">
                 <p class="text-[10px] uppercase font-black tracking-widest text-slate-300 mb-1">Đáp án đúng:</p>
                 <p class="text-[13px] font-bold text-emerald-600">{{ pair.match_text }}</p>
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
              <div v-if="!isAnswerRevealed && selectedLeft !== null && !isMatched(dIdx)"
                class="ml-3 w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0 opacity-50">
                <i class="fa-solid fa-link text-emerald-400 text-[8px]"></i>
              </div>
              <div v-else-if="isMatched(dIdx)"
                class="ml-3 w-6 h-6 rounded-xl bg-emerald-600 shadow-[0_2px_8px_rgba(16,185,129,0.3)] flex items-center justify-center shrink-0"
                :class="isAnswerRevealed ? (isCorrectMatchByRight(dIdx) ? 'bg-emerald-500' : 'bg-rose-400') : ''">
                <span class="text-white text-[10px] font-black">{{ String(getMatchedLeftIdx(dIdx) + 1).padStart(2, '0') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed results removed here as they are now integrated into cards -->
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

const localAnswers = ref({}) // qId -> match_text
const shuffledRight = ref([])
const selectedLeft = ref(null) // index in pairs

const isCorrectMatch = (leftIdx) => {
  const pair = pairs.value[leftIdx]
  if (!pair) return false
  const userMatchText = localAnswers.value[pair.id]
  if (userMatchText === undefined) return false
  
  // Lenient comparison
  return String(userMatchText).trim().toLowerCase() === String(pair.match_text).trim().toLowerCase()
}

const isCorrectMatchByRight = (rightIdx) => {
  const leftIdx = getMatchedLeftIdx(rightIdx)
  if (leftIdx === null) return false
  return isCorrectMatch(leftIdx)
}

const shuffleRight = () => {
  let arr = pairs.value.map((p, i) => ({ ...p, originalIdx: i }))
  // Only shuffle if NOT revealed
  if (!props.isAnswerRevealed) {
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[arr[i], arr[j]] = [arr[j], arr[i]]
    }
    // Only clear if not revealed
    localAnswers.value = {}
    emit('update-answer', {})
  }
  shuffledRight.value = arr
}

onMounted(() => {
  shuffleRight()
  if (props.savedAnswer && typeof props.savedAnswer === 'object') {
    localAnswers.value = { ...props.savedAnswer }
  }
})

watch(() => props.question?.id, () => {
  shuffleRight()
  if (props.savedAnswer && typeof props.savedAnswer === 'object') {
    localAnswers.value = { ...props.savedAnswer }
  } else {
    localAnswers.value = {}
  }
})

const selectLeft = (idx) => {
  if (selectedLeft.value === idx) {
    selectedLeft.value = null
  } else {
    selectedLeft.value = idx
    const leftId = pairs.value[idx]?.id
    if (leftId && localAnswers.value[leftId] !== undefined) {
      delete localAnswers.value[leftId]
      emit('update-answer', { ...localAnswers.value })
    }
  }
}

const selectRight = (idx) => {
  const rightItem = shuffledRight.value[idx]
  if (selectedLeft.value === null) {
    // Deselect if already matched
    const pairEntry = Object.entries(localAnswers.value).find(([_, text]) => text === rightItem.match_text)
    if (pairEntry) {
      delete localAnswers.value[pairEntry[0]]
      emit('update-answer', { ...localAnswers.value })
    }
    return
  }
  
  const leftId = pairs.value[selectedLeft.value]?.id
  if (!leftId) return

  // Unlink if this right item was used elsewhere
  const existingPair = Object.entries(localAnswers.value).find(([_, text]) => text === rightItem.match_text)
  if (existingPair) {
    delete localAnswers.value[existingPair[0]]
  }
  
  localAnswers.value[leftId] = rightItem.match_text
  selectedLeft.value = null
  emit('update-answer', { ...localAnswers.value })
}

const isMatched = (rightIdx) => {
  const text = shuffledRight.value[rightIdx]?.match_text
  if (!text) return false
  return Object.values(localAnswers.value).includes(text)
}

const getMatchedLeftIdx = (rightIdx) => {
  const text = shuffledRight.value[rightIdx]?.match_text
  const match = Object.entries(localAnswers.value).find(([_, rText]) => rText === text)
  if (match) {
    const leftId = parseInt(match[0])
    return pairs.value.findIndex(p => p.id === leftId)
  }
  return null
}

const getLeftCardClass = (idx) => {
  const pair = pairs.value[idx]
  if (selectedLeft.value === idx) return 'border-emerald-500 shadow-[0_8px_20px_rgba(16,185,129,0.15)] bg-white'
  if (props.isAnswerRevealed) {
    if (!pair || localAnswers.value[pair.id] === undefined) return 'border-rose-100 bg-rose-50/50'
    return isCorrectMatch(idx) ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50'
  }
  if (pair && localAnswers.value[pair.id] !== undefined) return 'border-emerald-200 bg-emerald-50/30 hover:border-emerald-300'
  return 'border-slate-100 bg-white hover:border-emerald-300 hover:shadow-sm'
}

const getRightCardClass = (idx) => {
  if (selectedLeft.value !== null && !isMatched(idx)) return 'border-slate-200 bg-slate-50 border-dashed cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50'
  if (isMatched(idx)) {
    if (props.isAnswerRevealed) {
      return isCorrectMatchByRight(idx) ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50'
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
