<template>
  <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-10">
    <!-- ═ AUDIO PLAYER CARD ═ -->
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8 flex items-center gap-4 sm:gap-8">
      <!-- Controls -->
      <div class="flex items-center gap-1 sm:gap-3 shrink-0 relative">
        <button @click="skip(-10)" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-300 hover:text-[#008035] hover:bg-green-50 transition-colors relative">
          <i class="fa-solid fa-arrow-rotate-left text-xl"></i>
          <span class="absolute text-[8px] font-black mt-1 text-slate-400">10</span>
        </button>
        <button @click="togglePlay"
          class="w-14 h-14 sm:w-16 sm:h-16 bg-[#008035] rounded-full flex items-center justify-center text-white shadow-[0_8px_20px_rgba(0,128,53,0.3)] hover:scale-105 active:scale-95 transition-all">
          <i :class="isPlaying ? 'fa-solid fa-pause text-xl' : 'fa-solid fa-play text-xl ml-1'"></i>
        </button>
        <button @click="skip(10)" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-300 hover:text-[#008035] hover:bg-green-50 transition-colors relative">
          <i class="fa-solid fa-arrow-rotate-right text-xl"></i>
          <span class="absolute text-[8px] font-black mt-1 text-slate-400">10</span>
        </button>
      </div>

      <!-- Info & Progress -->
      <div class="flex-1 min-w-0 flex flex-col gap-3">
        <div class="flex items-start justify-between">
          <div class="min-w-0 pr-4">
            <p class="text-[13px] sm:text-[14px] font-headline font-black uppercase tracking-widest text-[#008035] mb-0.5 truncate">
              AUDIO SOURCE 01
            </p>
            <p class="text-[12px] sm:text-[13px] font-medium text-slate-500 truncate">
              {{ question?.audio_title || question?.question_text || 'Linguistic Nuances in Modern English' }}
            </p>
          </div>
          <div class="text-[11px] sm:text-[12px] font-black tabular-nums tracking-wide text-slate-700 shrink-0">
            {{ formattedAudioTime }} <span class="text-slate-300 mx-1">/</span> <span class="text-slate-500">{{ formattedAudioDuration }}</span>
          </div>
        </div>

        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden cursor-pointer" @click="seekAudio">
          <div class="h-full bg-[#008035] rounded-full transition-all duration-300 ease-linear" :style="{ width: audioProgress + '%' }"></div>
        </div>

        <div class="flex items-center justify-between mt-1">
          <div class="flex items-center gap-2">
            <button @click="cycleSpeed" class="px-3 py-1.5 bg-[#86EFAC] text-[#047857] text-[9px] font-black tracking-widest rounded-full hover:bg-green-300 transition-colors">
              {{ playbackRate.toFixed(1) }}X SPEED
            </button>
            <button @click="isLooping = !isLooping" class="px-3 py-1.5 text-[9px] font-black tracking-widest rounded-full transition-colors"
                :class="isLooping ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">
              LOOP MODE
            </button>
          </div>
          <button @click="isMuted = !isMuted" class="text-slate-400 hover:text-slate-600 transition-colors">
            <i :class="isMuted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high'" class="text-lg"></i>
          </button>
        </div>
      </div>

      <audio ref="audioEl"
        :src="question?.audio_url"
        :loop="isLooping"
        :muted="isMuted"
        @timeupdate="onTimeUpdate"
        @loadedmetadata="onLoadedMetadata"
        @ended="isPlaying = false"
        @play="isPlaying = true"
        @pause="isPlaying = false"
        preload="metadata">
      </audio>
    </div>

    <!-- ═ TRANSCRIPTION BOX ═ -->
    <div class="mt-2">
      <!-- Title row -->
      <div class="flex flex-wrap items-center justify-between mb-4 px-2 gap-4">
        <h3 class="text-[20px] font-headline font-black text-slate-800 tracking-tight">Your Transcription</h3>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1.5 bg-slate-100/80 text-[11px] font-bold text-slate-600 rounded-full">{{ currentWordCount }} Words</span>
          <span class="px-3 py-1.5 bg-slate-100/80 text-[11px] font-bold text-slate-600 rounded-full">Autosaved just now</span>
        </div>
      </div>

      <!-- Editor Card -->
      <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden group">
        <textarea
          v-model="transcription"
          @input="handleInput"
          :disabled="isAnswerRevealed"
          rows="14"
          class="w-full px-8 pt-8 pb-20 text-[16px] text-slate-700 leading-relaxed font-medium resize-none outline-none focus:ring-0 border-0 bg-transparent placeholder:text-slate-300 placeholder:font-medium"
          placeholder="Start typing your dictation here..."
        ></textarea>
        
        <!-- Bottom right action -->
        <div class="absolute bottom-6 right-6 flex items-center justify-end">
          <button class="flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-700 rounded-full text-[11px] font-black tracking-widest hover:bg-slate-200 transition-colors">
            <i class="fa-solid fa-spell-check text-slate-500"></i>
            CHECK SPELLING
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: String, default: '' },
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const transcription = ref(props.savedAnswer || '')
watch(() => props.savedAnswer, (newVal) => {
  transcription.value = newVal || ''
})

const audioEl = ref(null)
const isPlaying = ref(false)
const audioTime = ref(0)
const audioDuration = ref(0)
const playbackRate = ref(1)
const showSpeedMenu = ref(false)

const togglePlay = () => {
  if (!audioEl.value || !props.question?.audio_url) return
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
  if (!audioEl.value) return
  const rect = e.currentTarget.getBoundingClientRect()
  const val = (e.clientX - rect.left) / rect.width
  audioEl.value.currentTime = val * audioDuration.value
}

const isLooping = ref(false)
const isMuted = ref(false)

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

const currentWordCount = computed(() => {
  return transcription.value.trim() ? transcription.value.trim().split(/\s+/).length : 0
})

const handleInput = () => {
  emit('update-answer', transcription.value)
}

// Basic diff simply highlights changes; for brevity, we do a word diff or just show original
const diffHtml = computed(() => {
  if (!props.question?.answer) return ''
  const tArr = transcription.value.trim().split(/\s+/)
  const oArr = props.question.answer.trim().split(/\s+/)
  
  let html = ''
  let i = 0
  while (i < oArr.length) {
    if (tArr[i]?.toLowerCase().replace(/[.,!?;:]/g, '') === oArr[i].toLowerCase().replace(/[.,!?;:]/g, '')) {
      html += `<span class="text-slate-600">${oArr[i]} </span>`
    } else {
      if (tArr[i]) {
        html += `<span class="bg-red-100 text-red-600 line-through px-1 rounded mx-0.5">${tArr[i]}</span>`
      }
      html += `<span class="bg-emerald-100 text-emerald-700 px-1 rounded mx-0.5">${oArr[i]}</span> `
    }
    i++
  }
  return html
})
</script>
