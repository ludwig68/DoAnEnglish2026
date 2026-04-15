<template>
  <div class="flex flex-col gap-5 w-full max-w-3xl mx-auto pb-10">
    <!-- ═ AUDIO PLAYER CARD (Only if audio_url exists) ═ -->
    <div v-if="question?.audio_url" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-5 sm:p-6 mb-1 flex items-center gap-4 sm:gap-6 animate-in fade-in slide-in-from-top-4 duration-500">
      <!-- Play/Pause Controls -->
      <div class="flex items-center gap-2 shrink-0 relative">
        <button @click="skip(-10)" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
          <i class="fa-solid fa-rotate-left text-lg"></i>
        </button>
        <button @click="togglePlay"
          class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center text-white shadow-[0_5px_15px_rgba(16,185,129,0.25)] hover:scale-105 active:scale-95 transition-all">
          <i :class="isPlaying ? 'fa-solid fa-pause text-lg' : 'fa-solid fa-play text-lg ml-0.5'"></i>
        </button>
        <button @click="skip(10)" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
          <i class="fa-solid fa-rotate-right text-lg"></i>
        </button>
      </div>

      <!-- Info & Progress -->
      <div class="flex-1 min-w-0 flex flex-col gap-2.5">
        <div class="flex items-end justify-between">
          <div class="min-w-0 pr-4">
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-600 mb-0.5">LISTENING TASK</p>
            <p class="text-[11px] font-bold text-slate-500 truncate">{{ question?.audio_title || 'Audio Recording' }}</p>
          </div>
          <div class="text-[11px] font-black tabular-nums tracking-wide text-slate-700 shrink-0">
            {{ formattedAudioTime }} <span class="text-slate-300 mx-1">/</span> <span class="text-slate-500">{{ formattedAudioDuration }}</span>
          </div>
        </div>

        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden cursor-pointer" @click="seekAudio">
          <div class="h-full bg-emerald-600 rounded-full transition-all duration-300 ease-linear" :style="{ width: audioProgress + '%' }"></div>
        </div>

        <div class="flex items-center justify-between">
          <button @click="cycleSpeed" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black tracking-widest rounded-full hover:bg-emerald-100 transition-colors uppercase">
            {{ playbackRate.toFixed(1) }}X Speed
          </button>
          <button @click="isMuted = !isMuted" class="text-slate-400 hover:text-emerald-600 transition-colors">
            <i :class="isMuted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high'" class="text-sm"></i>
          </button>
        </div>
      </div>

      <audio ref="audioEl"
        :src="question.audio_url"
        :muted="isMuted"
        @timeupdate="onTimeUpdate"
        @loadedmetadata="onLoadedMetadata"
        @ended="isPlaying = false"
        @play="isPlaying = true"
        @pause="isPlaying = false"
        preload="metadata">
      </audio>
    </div>

    <!-- Question Card -->
    <div class="bg-white rounded-[2rem] border border-slate-50 shadow-sm p-8 pb-10 relative overflow-hidden mb-2">
      <!-- Decorative green curve -->
      <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-50 rounded-full opacity-60"></div>
      
      <div class="relative z-10">
        <p class="text-[12px] font-headline font-black text-emerald-600 mb-3 tracking-wide flex items-center gap-1.5">
          <i class="fa-solid fa-circle-question text-[10px]"></i> {{ question?.audio_url ? 'Listening Question' : 'Câu hỏi' }}
        </p>
        <p class="text-[19px] sm:text-[22px] font-headline font-black text-slate-800 leading-[1.5]">
          {{ question?.question_text }}
        </p>
        <div v-if="question?.hint" class="mt-5 pt-4 border-t border-slate-50">
          <p class="text-[12px] font-bold text-slate-400 italic flex items-center gap-2">
            <i class="fa-regular fa-lightbulb text-amber-400 text-sm"></i>
            {{ question.hint }}
          </p>
        </div>
      </div>
    </div>

    <!-- Options A B C D -->
    <div class="flex flex-col gap-3">
      <button
        v-for="(option, oIdx) in (question?.options || [])"
        :key="option.id || oIdx"
        @click="selectAnswer(option)"
        :disabled="isAnswerRevealed"
        class="group w-full flex items-center justify-between gap-4 p-5 rounded-[1.25rem] border-2 text-left transition-all duration-200"
        :class="getOptionClass(option)"
      >
        <div class="flex items-center gap-4 flex-1 min-w-0">
          <div class="w-10 h-10 rounded-[0.8rem] flex items-center justify-center text-[14px] font-black shrink-0 transition-colors duration-200 shadow-sm"
            :class="getLetterClass(option)">
            {{ optionLetters[oIdx] }}
          </div>
          <span class="text-[15px] font-bold leading-snug transition-colors duration-200 pr-2"
            :class="getOptionTextClass(option)">
            {{ option.option_text }}
          </span>
        </div>

        <!-- Before Reveal: Selected indicator -->
        <div v-if="!isAnswerRevealed && savedAnswer === option.id" class="shrink-0 flex items-center justify-center w-[22px] h-[22px] rounded-full bg-emerald-600">
           <i class="fa-solid fa-check text-white text-[10px]"></i>
        </div>

        <!-- Revealed indicators -->
        <div v-else-if="isAnswerRevealed && Number(option.is_correct) === 1"
          class="w-[22px] h-[22px] bg-emerald-100 rounded-full flex items-center justify-center shrink-0 border border-emerald-300">
          <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
        </div>
        <div v-else-if="isAnswerRevealed && savedAnswer === option.id && Number(option.is_correct) !== 1"
          class="w-[22px] h-[22px] bg-rose-200 rounded-full flex items-center justify-center shrink-0">
          <i class="fa-solid fa-xmark text-rose-500 text-[10px]"></i>
        </div>
      </button>
    </div>

    <!-- Explanation -->
    <transition name="slide-down">
      <div v-if="isAnswerRevealed && question?.explanation"
        class="bg-blue-50/80 rounded-[1.5rem] border border-blue-100/60 p-6 flex gap-4">
        <div class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
          <i class="fa-solid fa-book-open text-white text-xs"></i>
        </div>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-blue-500 mb-1">Giải thích</p>
          <p class="text-[13px] font-bold text-slate-700 leading-relaxed">{{ question.explanation }}</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: [String, Number], default: null },
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F']

// ── AUDIO LOGIC ──
const audioEl = ref(null)
const isPlaying = ref(false)
const audioTime = ref(0)
const audioDuration = ref(0)
const playbackRate = ref(1)
const isMuted = ref(false)

const togglePlay = () => {
  if (!audioEl.value) return
  if (isPlaying.value) {
    audioEl.value.pause()
  } else {
    audioEl.value.play()
  }
}

const onTimeUpdate = () => {
  if (audioEl.value) audioTime.value = audioEl.value.currentTime
}

const onLoadedMetadata = () => {
  if (audioEl.value) audioDuration.value = audioEl.value.duration
}

const seekAudio = (e) => {
  if (!audioEl.value || !audioDuration.value) return
  const rect = e.currentTarget.getBoundingClientRect()
  const val = (e.clientX - rect.left) / rect.width
  audioEl.value.currentTime = val * audioDuration.value
}

const cycleSpeed = () => {
  const rates = [0.5, 1.0, 1.5, 2.0]
  const idx = rates.indexOf(playbackRate.value)
  const nextRate = rates[(idx + 1) % rates.length]
  playbackRate.value = nextRate
  if (audioEl.value) audioEl.value.playbackRate = nextRate
}

const skip = (secs) => {
  if (audioEl.value) {
    audioEl.value.currentTime = Math.max(0, Math.min(audioDuration.value, audioEl.value.currentTime + secs))
  }
}

const formatTime = (secs) => {
  if (!secs || isNaN(secs)) return '00:00'
  const m = Math.floor(secs / 60)
  const s = Math.floor(secs % 60)
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

const formattedAudioTime = computed(() => formatTime(audioTime.value))
const formattedAudioDuration = computed(() => formatTime(audioDuration.value))
const audioProgress = computed(() => {
  if (!audioDuration.value) return 0
  return (audioTime.value / audioDuration.value) * 100
})

onUnmounted(() => {
  if (audioEl.value) {
    audioEl.value.pause()
  }
})

// ── SELECT LOGIC ──
const selectAnswer = (option) => {
  if (props.isAnswerRevealed) return
  emit('update-answer', option.id)
}

const getOptionClass = (option) => {
  if (!props.isAnswerRevealed) {
    if (props.savedAnswer === option.id) {
      return 'border-emerald-500 bg-white shadow-[0_4px_15px_rgba(16,185,129,0.08)]'
    }
    return 'border-transparent bg-[#F4F5F7] hover:border-emerald-200 hover:bg-[#EDF2F7]' // Unselected Grey Look
  }

  // Revealed
  const isCorrect = Number(option.is_correct) === 1
  if (isCorrect) {
    return 'border-emerald-200 bg-emerald-50/50 border-dashed'
  }

  if (props.savedAnswer === option.id) {
    return 'border-rose-200 bg-rose-50/50'
  }

  return 'border-transparent bg-[#F4F5F7] opacity-60'
}

const getLetterClass = (option) => {
  if (!props.isAnswerRevealed) {
    return props.savedAnswer === option.id
      ? 'bg-emerald-600 text-white shadow-md'
      : 'bg-white text-emerald-600 shadow-sm group-hover:bg-emerald-600 group-hover:text-white'
  }

  const isCorrect = Number(option.is_correct) === 1
  if (isCorrect) {
    return 'bg-emerald-100 text-emerald-700'
  }

  if (props.savedAnswer === option.id) {
    return 'bg-rose-100 text-rose-500'
  }

  return 'bg-white text-slate-400'
}

const getOptionTextClass = (option) => {
  if (!props.isAnswerRevealed) {
    return props.savedAnswer === option.id ? 'text-emerald-700' : 'text-slate-600 group-hover:text-slate-800'
  }

  const isCorrect = Number(option.is_correct) === 1
  if (isCorrect) {
    return 'text-emerald-700'
  }

  if (props.savedAnswer === option.id) {
    return 'text-rose-600'
  }

  return 'text-slate-400 line-through'
}
</script>
