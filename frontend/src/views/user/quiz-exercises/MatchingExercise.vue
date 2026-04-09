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

        <!-- Timer + Submit -->
        <div class="flex items-center gap-5 shrink-0">
          <div class="flex items-center gap-2" :class="timeLeft < 300 ? 'text-red-500' : 'text-slate-600'">
            <div class="w-2 h-2 rounded-full" :class="timeLeft < 300 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"></div>
            <span class="text-[13px] font-black tabular-nums">{{ formattedTime }}</span>
          </div>
          <button @click="revealAll" :disabled="isSubmitting"
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
          class="w-10 h-10 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all"
          :class="activeTool === tool.key
            ? 'bg-emerald-500 text-white shadow-[0_4px_10px_rgba(22,163,74,0.3)]'
            : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'">
          <i :class="tool.icon" class="text-xs"></i>
          <span class="text-[7px] font-black uppercase tracking-wider leading-none">{{ tool.shortLabel }}</span>
        </button>

        <div class="mt-auto">
          <button class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center hover:border-emerald-300 hover:text-emerald-500 transition-all shadow-sm">
            <i class="fa-solid fa-plus text-xs"></i>
          </button>
        </div>
      </aside>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto no-scrollbar">
        <div class="max-w-5xl mx-auto px-8 py-10">

          <!-- Header section -->
          <div class="flex items-start justify-between mb-10 gap-8">
            <div class="flex-1">
              <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100/60 rounded-lg mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[9px] font-black uppercase tracking-[0.25em] text-emerald-600">Focus Mode</span>
                <span class="text-[9px] font-bold text-slate-400">{{ quiz?.title || 'Ghép nối' }} • Từ vựng nâng cao</span>
              </div>
              <h1 class="text-[32px] font-headline font-black text-slate-800 tracking-tight leading-none mb-4">
                Contextual <em class="text-emerald-500 not-italic font-black">Synthesis</em>
              </h1>
              <p class="text-[13px] text-slate-400 font-medium leading-relaxed max-w-lg">
                Ghép từng khái niệm ở cột trái với định nghĩa tương ứng ở cột phải. Chú ý đến sắc thái nghĩa của từng từ.
              </p>
            </div>

            <!-- Progress -->
            <div class="text-right shrink-0 min-w-[160px]">
              <div class="flex items-center justify-end gap-3 mb-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tiến độ</span>
                <span class="text-[22px] font-headline font-black text-emerald-600">{{ progressPercent }}%</span>
              </div>
              <div class="h-2 bg-slate-100 rounded-full overflow-hidden w-40">
                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700"
                  :style="{ width: progressPercent + '%' }"></div>
              </div>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="isLoadingQuestions" class="flex flex-col items-center py-20 gap-4">
            <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Đang tải câu hỏi...</p>
          </div>

          <!-- No questions -->
          <div v-else-if="pairs.length === 0" class="text-center py-20">
            <i class="fa-solid fa-link-slash text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Chưa có cặp ghép nối nào</p>
          </div>

          <!-- Matching UI: Two columns -->
          <template v-else>
            <div class="grid grid-cols-[1fr_48px_1fr] gap-0 items-start">

              <!-- LEFT COLUMN: Terms -->
              <div>
                <div class="flex items-center justify-between mb-5 px-2">
                  <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Khái niệm</p>
                  <p class="text-[9px] font-bold text-emerald-500">Nguồn: Chương trình học</p>
                </div>
                <div class="flex flex-col gap-4">
                  <div
                    v-for="(pair, idx) in pairs"
                    :key="pair.id"
                    @click="!isRevealed && selectLeft(idx)"
                    class="rounded-[1.5rem] border-2 p-5 transition-all duration-300 cursor-pointer select-none"
                    :class="getLeftCardClass(idx)"
                  >
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] mb-1.5"
                      :class="selectedLeft === idx ? 'text-emerald-500' : (userMatches[idx] !== undefined ? 'text-emerald-400' : 'text-slate-400')">
                      Cụm {{ String(idx + 1).padStart(2, '0') }}
                    </p>
                    <div class="flex items-center justify-between gap-3">
                      <h3 class="text-[17px] font-headline font-black leading-snug"
                        :class="selectedLeft === idx ? 'text-emerald-700' : (userMatches[idx] !== undefined ? 'text-slate-700' : 'text-slate-800')">
                        {{ pair.option_text }}
                      </h3>

                      <!-- Match status icon -->
                      <div class="shrink-0">
                        <!-- Verified correct -->
                        <div v-if="isRevealed && userMatches[idx] !== undefined && shuffledRight[userMatches[idx]]?.is_correct"
                          class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center shadow-[0_4px_8px_rgba(22,163,74,0.3)]">
                          <i class="fa-solid fa-check text-white text-xs"></i>
                        </div>
                        <!-- Verified wrong -->
                        <div v-else-if="isRevealed && userMatches[idx] !== undefined && !shuffledRight[userMatches[idx]]?.is_correct"
                          class="w-8 h-8 bg-red-400 rounded-xl flex items-center justify-center">
                          <i class="fa-solid fa-xmark text-white text-xs"></i>
                        </div>
                        <!-- Matched but not verified -->
                        <div v-else-if="!isRevealed && userMatches[idx] !== undefined"
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
                <button @click="shuffleRight"
                  class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 hover:border-emerald-300 hover:text-emerald-500 hover:shadow-md transition-all">
                  <i class="fa-solid fa-shuffle text-xs"></i>
                </button>
              </div>

              <!-- RIGHT COLUMN: Definitions -->
              <div>
                <div class="flex items-center justify-between mb-5 px-2">
                  <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Định nghĩa</p>
                  <p class="text-[9px] font-bold text-slate-400">Chế độ: Ghép chính xác</p>
                </div>
                <div class="flex flex-col gap-4">
                  <div
                    v-for="(def, dIdx) in shuffledRight"
                    :key="def.tempId"
                    @click="!isRevealed && selectRight(dIdx)"
                    class="rounded-[1.5rem] border-2 p-5 transition-all duration-300 select-none min-h-[80px] flex items-center"
                    :class="getRightCardClass(dIdx)"
                  >
                    <div class="flex-1">
                      <p class="text-[14px] font-bold leading-relaxed"
                        :class="getRightTextClass(dIdx)">
                        {{ def.match_text }}
                      </p>
                    </div>

                    <!-- Link icon when selected + active left -->
                    <div v-if="selectedLeft !== null && !isMatched(dIdx)"
                      class="ml-3 w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0 opacity-50">
                      <i class="fa-solid fa-link text-emerald-400 text-[8px]"></i>
                    </div>

                    <!-- Already matched indicator -->
                    <div v-else-if="isMatched(dIdx) && !isRevealed"
                      class="ml-3 w-6 h-6 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center shrink-0">
                      <i class="fa-solid fa-link text-emerald-500 text-[8px]"></i>
                    </div>
                  </div>
                </div>

                <!-- Matching system status -->
                <div class="flex items-center justify-end gap-2 mt-5">
                  <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                  <p class="text-[10px] font-bold text-slate-400">Hệ thống ghép nối đang hoạt động</p>
                </div>
              </div>
            </div>

            <!-- Reveal feedback (after verify) -->
            <transition name="slide-down">
              <div v-if="isRevealed" class="mt-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8">
                <div class="flex items-center gap-4 mb-6">
                  <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-sm"
                    :class="score === totalPairs ? 'bg-emerald-500' : 'bg-amber-400'">
                    <i class="fa-solid fa-star text-white text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Kết quả xác minh</p>
                    <p class="text-[17px] font-headline font-black text-slate-800">
                      {{ score }} / {{ totalPairs }} cặp đúng
                      <span class="text-[13px] ml-1" :class="score === totalPairs ? 'text-emerald-500' : 'text-amber-500'">
                        ({{ Math.round((score / totalPairs) * 100) }}%)
                      </span>
                    </p>
                  </div>
                </div>

                <!-- Correct answers -->
                <div class="space-y-3">
                  <div v-for="(pair, idx) in pairs" :key="pair.id"
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
                        Đáp án của bạn: {{ userMatches[idx] !== undefined ? shuffledRight[userMatches[idx]]?.match_text : 'Chưa trả lời' }}
                      </p>
                    </div>
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
      <div class="max-w-5xl mx-auto px-8 h-16 flex items-center justify-between gap-6">

        <!-- Left: Back + AI Hint -->
        <div class="flex items-center gap-6">
          <button @click="$emit('close')"
            class="flex items-center gap-2 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-700 transition-colors group">
            <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
            Quay lại
          </button>
          <div class="flex items-center gap-3 text-[12px] text-slate-400 font-bold hidden md:flex">
            <img src="https://ui-avatars.com/api/?name=AI&background=111&color=fff&bold=true&size=24&rounded=true" class="w-7 h-7 rounded-full" alt="">
            <img src="https://ui-avatars.com/api/?name=AI+Hint&background=10B981&color=fff&bold=true&size=24&rounded=true" class="w-7 h-7 rounded-full -ml-3 ring-2 ring-white" alt="">
            <span class="italic opacity-70">Gợi ý AI: Hãy tìm gốc Latin của từ khó nhất.</span>
          </div>
        </div>

        <!-- Right: Save + Verify -->
        <div class="flex items-center gap-3 shrink-0">
          <button @click="resetMatches"
            class="px-5 py-2.5 bg-slate-50 text-slate-500 border border-slate-200 rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all shadow-sm">
            Làm lại
          </button>
          <button @click="verifyMatches" :disabled="answeredCount < totalPairs || isRevealed"
            class="flex items-center gap-2 px-7 py-2.5 text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-md"
            :class="answeredCount < totalPairs || isRevealed
              ? 'bg-slate-300 cursor-not-allowed'
              : 'bg-slate-900 hover:bg-emerald-600 hover:-translate-y-0.5 shadow-[0_4px_12px_rgba(15,23,42,0.2)]'">
            {{ isRevealed ? 'Đã xác minh' : 'Xác minh kết quả' }}
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
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
          <button @click="activeTool = null" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <template v-if="activeTool === 'notes'">
            <textarea v-model="personalNote" rows="12"
              class="w-full text-[13px] text-slate-700 bg-slate-50 border border-slate-100 rounded-2xl p-4 resize-none outline-none focus:ring-2 focus:ring-emerald-200 transition-all font-medium leading-relaxed"
              placeholder="Ghi chú của bạn..."></textarea>
          </template>

          <template v-else-if="activeTool === 'drafts'">
            <p class="text-[11px] text-slate-400 font-bold mb-4">Tháp ghép đã thực hiện</p>
            <div class="space-y-3">
              <div v-for="(pair, idx) in pairs" :key="pair.id"
                class="p-3 rounded-xl border text-[11px]"
                :class="userMatches[idx] !== undefined ? 'border-emerald-100 bg-emerald-50' : 'border-slate-100 bg-slate-50'">
                <div class="font-black text-slate-700 truncate">{{ pair.option_text }}</div>
                <div v-if="userMatches[idx] !== undefined" class="text-emerald-600 font-bold mt-1 flex items-center gap-1 truncate">
                  <i class="fa-solid fa-link text-[8px]"></i>
                  {{ shuffledRight[userMatches[idx]]?.match_text?.substring(0, 50) }}...
                </div>
                <div v-else class="text-slate-300 font-bold mt-1">Chưa ghép</div>
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
            :class="score === totalPairs ? 'bg-emerald-50' : 'bg-amber-50'">
            <i :class="score === totalPairs ? 'fa-solid fa-trophy text-emerald-500' : 'fa-solid fa-star-half-stroke text-amber-500'" class="text-3xl"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-2">
            {{ score === totalPairs ? 'Xuất sắc!' : 'Hoàn thành!' }}
          </h3>
          <p class="text-[13px] text-slate-400 font-medium mb-8 leading-relaxed">
            Bạn đã ghép đúng {{ score }}/{{ totalPairs }} cặp từ.
          </p>

          <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100/50">
              <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1">Đúng</p>
              <p class="text-3xl font-headline font-black text-emerald-600">{{ score }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl p-5 border border-red-100/50">
              <p class="text-[9px] font-black uppercase tracking-widest text-red-400 mb-1">Sai</p>
              <p class="text-3xl font-headline font-black text-red-500">{{ totalPairs - score }}</p>
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
 * MatchingExercise.vue
 * Dạng bài ghép nối (matching type).
 * - Hiển thị cột trái (option_text) và cột phải (match_text - đã shuffle)
 * - Click trái → chọn term, click phải → tạo cặp match
 * - Verify: so sánh user match với đáp án đúng (is_correct)
 * - Sidebar: Ghi chú, bản tóm tắt nháp
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
const pairs = ref([])          // raw options from DB: [{ id, option_text, match_text, is_correct }]
const shuffledRight = ref([])  // shuffled definitions by index, each has tempId
const userMatches = ref({})    // userMatches[leftIdx] = rightIdx
const selectedLeft = ref(null) // index of currently selected left term
const isRevealed = ref(false)
const isLoadingQuestions = ref(true)
const isSubmitting = ref(false)
const showSuccess = ref(false)
const activeTool = ref(null)
const personalNote = ref('')

// Timer
const TOTAL_SECONDS = 15 * 60
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null

// ── Constants ──
const sidebarTools = [
  { key: 'drafts', icon: 'fa-solid fa-layer-group', label: 'Nháp đã ghép', shortLabel: 'Nháp' },
  { key: 'notes', icon: 'fa-regular fa-note-sticky', label: 'Ghi chú', shortLabel: 'Chú' },
]

// ── Computed ──
const totalPairs = computed(() => pairs.value.length)

const answeredCount = computed(() => Object.keys(userMatches.value).length)

const progressPercent = computed(() => {
  if (totalPairs.value === 0) return 0
  return Math.round((answeredCount.value / totalPairs.value) * 100)
})

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const score = computed(() => {
  let correct = 0
  pairs.value.forEach((pair, idx) => {
    if (userMatches.value[idx] !== undefined) {
      const selectedDef = shuffledRight.value[userMatches.value[idx]]
      if (selectedDef && selectedDef.id === pair.id) correct++
    }
  })
  return correct
})

// ── Styling helpers ──
const getLeftCardClass = (idx) => {
  if (isRevealed.value) {
    if (userMatches.value[idx] !== undefined) {
      return isCorrectMatch(idx)
        ? 'border-emerald-400 bg-emerald-50/70 cursor-default'
        : 'border-red-300 bg-red-50/60 cursor-default'
    }
    return 'border-slate-100 bg-white opacity-50 cursor-default'
  }
  if (selectedLeft.value === idx) return 'border-emerald-500 bg-emerald-50/60 shadow-[0_0_0_4px_rgba(52,211,153,0.15)]'
  if (userMatches.value[idx] !== undefined) return 'border-emerald-200 bg-emerald-50/40 hover:border-emerald-300'
  return 'border-slate-100 bg-white hover:border-emerald-200 hover:shadow-md'
}

const getRightCardClass = (dIdx) => {
  if (isRevealed.value) {
    const leftIdx = getLeftIdxForRight(dIdx)
    if (leftIdx !== null) {
      return isCorrectMatch(leftIdx)
        ? 'border-emerald-400 bg-emerald-50/70 cursor-default'
        : 'border-red-300 bg-red-50/60 cursor-default'
    }
    return 'border-slate-100 bg-white opacity-40 cursor-default'
  }
  const leftIdx = getLeftIdxForRight(dIdx)
  if (leftIdx !== null) return 'border-emerald-200 bg-emerald-50/40 cursor-pointer hover:border-emerald-300'
  if (selectedLeft.value !== null) return 'border-slate-200 bg-white cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/20 hover:shadow-md'
  return 'border-slate-100 bg-white cursor-pointer hover:border-slate-200'
}

const getRightTextClass = (dIdx) => {
  const leftIdx = getLeftIdxForRight(dIdx)
  if (leftIdx !== null) return 'text-emerald-700 font-bold'
  if (selectedLeft.value !== null) return 'text-slate-600'
  return 'text-slate-500'
}

const isMatched = (dIdx) => getLeftIdxForRight(dIdx) !== null

const getLeftIdxForRight = (dIdx) => {
  for (const [leftIdx, rightIdx] of Object.entries(userMatches.value)) {
    if (parseInt(rightIdx) === dIdx) return parseInt(leftIdx)
  }
  return null
}

const isCorrectMatch = (leftIdx) => {
  if (userMatches.value[leftIdx] === undefined) return false
  const selectedDef = shuffledRight.value[userMatches.value[leftIdx]]
  return selectedDef && selectedDef.id === pairs.value[leftIdx]?.id
}

// ── Methods ──
const selectLeft = (idx) => {
  if (isRevealed.value) return
  selectedLeft.value = selectedLeft.value === idx ? null : idx
}

const selectRight = (dIdx) => {
  if (isRevealed.value || selectedLeft.value === null) return

  const leftIdx = selectedLeft.value

  // Unlink any existing left→this right
  const existingLeft = getLeftIdxForRight(dIdx)
  if (existingLeft !== null) {
    const newMatches = { ...userMatches.value }
    delete newMatches[existingLeft]
    userMatches.value = newMatches
  }

  // Unlink leftIdx from previous right
  const newMatches = { ...userMatches.value }
  newMatches[leftIdx] = dIdx
  userMatches.value = newMatches

  selectedLeft.value = null
}

const verifyMatches = () => {
  if (answeredCount.value < totalPairs.value) return
  isRevealed.value = true
}

const resetMatches = () => {
  userMatches.value = {}
  selectedLeft.value = null
  isRevealed.value = false
  shuffleRightInternal()
}

const shuffleRight = () => {
  if (isRevealed.value) return
  userMatches.value = {}
  selectedLeft.value = null
  shuffleRightInternal()
}

const shuffleRightInternal = () => {
  const arr = pairs.value.map((p, i) => ({
    id: p.id,
    match_text: p.match_text,
    is_correct: p.is_correct,
    tempId: `def-${p.id}-${Date.now()}-${i}`,
  }))
  // Fisher-Yates shuffle
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  shuffledRight.value = arr
}

const revealAll = async () => {
  isSubmitting.value = true
  if (!isRevealed.value) verifyMatches()
  await new Promise(r => setTimeout(r, 800))
  isSubmitting.value = false
  showSuccess.value = true
  stopTimer()
  emit('submitted', {
    quiz_id: props.quiz?.id,
    score: score.value,
    total: totalPairs.value,
    matches: userMatches.value,
  })
}

// ── Data fetching ──
const fetchPairs = async () => {
  const loadFromQuiz = (quiz) => {
    // Flatten all matching options from all questions
    const allOptions = []
    ;(quiz.questions || []).forEach(q => {
      if (q.question_type === 'matching') {
        q.options.forEach(opt => { if (opt.match_text) allOptions.push(opt) })
      }
    })
    pairs.value = allOptions
    shuffleRightInternal()
  }

  if (props.quiz?.questions?.length) {
    loadFromQuiz(props.quiz)
    isLoadingQuestions.value = false
    return
  }

  if (!props.quiz?.id) { isLoadingQuestions.value = false; return }

  try {
    const res = await apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`)
    const data = await res.json()
    if (data.status === 'success') {
      loadFromQuiz(data.data)
    }
  } catch { /* ignore */ } finally {
    isLoadingQuestions.value = false
  }
}

// ── Timer ──
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) { timeLeft.value-- }
    else { stopTimer(); revealAll() }
  }, 1000)
}
const stopTimer = () => {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
}

onMounted(() => { fetchPairs(); startTimer() })
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

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-14px); }

.slide-right-enter-active, .slide-right-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.slide-right-enter-from, .slide-right-leave-to { opacity: 0; transform: translateX(-16px); }
</style>
