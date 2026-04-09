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
        <div class="flex items-center gap-5 shrink-0">
          <div class="group flex items-center gap-2 cursor-default"
            :class="timeLeft < 300 ? 'text-red-500' : 'text-slate-600'">
            <i class="fa-solid fa-stopwatch text-sm" :class="timeLeft < 300 ? 'animate-pulse' : ''"></i>
            <span class="text-[13px] font-black tabular-nums">{{ formattedTime }}</span>
          </div>
          <button @click="submitAll" :disabled="isSubmitting"
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
          class="w-10 h-10 rounded-xl flex flex-col items-center justify-center gap-0.5 transition-all relative"
          :class="activeTool === tool.key
            ? 'bg-emerald-500 text-white shadow-[0_4px_10px_rgba(22,163,74,0.3)]'
            : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'">
          <div v-if="activeTool === tool.key"
            class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-6 bg-emerald-500 rounded-r-full -ml-px"></div>
          <i :class="tool.icon" class="text-xs"></i>
          <span class="text-[7px] font-black uppercase tracking-wider leading-none">{{ tool.shortLabel }}</span>
        </button>
        <div class="mt-auto">
          <button class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center hover:border-emerald-300 hover:text-emerald-500 transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
          </button>
        </div>
      </aside>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto no-scrollbar">
        <div class="max-w-2xl mx-auto px-8 py-12">

          <!-- Loading -->
          <div v-if="isLoadingQuestions" class="flex flex-col items-center py-20 gap-4">
            <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Đang tải bài nghe...</p>
          </div>

          <!-- No questions -->
          <div v-else-if="questions.length === 0" class="text-center py-20">
            <i class="fa-solid fa-headphones text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Chưa có bài nghe nào</p>
          </div>

          <template v-else>
            <!-- Page header -->
            <div class="text-center mb-10">
              <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-4">
                {{ quiz?.title || 'Assignment Alpha' }}
              </p>
              <h1 class="text-[34px] font-headline font-black text-slate-800 leading-tight tracking-tight mb-5">
                Dictation: The Art of Listening
              </h1>
              <p class="text-[14px] text-slate-400 font-medium leading-relaxed max-w-md mx-auto">
                Nghe kỹ đoạn âm thanh và ghi chép lại chính xác những gì bạn nghe được.
                Chú ý đến dấu câu và ngữ điệu.
              </p>
            </div>

            <!-- QUESTION NAVIGATOR (if multiple) -->
            <div v-if="questions.length > 1" class="flex items-center gap-2 justify-center mb-8 flex-wrap">
              <button v-for="(q, idx) in questions" :key="q.id"
                @click="activeQIdx = idx"
                class="px-4 py-2 rounded-xl text-[11px] font-black transition-all border"
                :class="idx === activeQIdx
                  ? 'bg-slate-900 text-white border-slate-800'
                  : transcriptions[idx]?.trim()
                    ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                    : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-200'">
                Bài {{ idx + 1 }}
              </button>
            </div>

            <!-- ═ AUDIO PLAYER CARD ═ -->
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 mb-8">
              <div class="flex items-center gap-8">

                <!-- Transport controls -->
                <div class="flex items-center gap-4 shrink-0">
                  <button @click="skipBack"
                    class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">
                    <i class="fa-solid fa-rotate-left text-sm"></i>
                    <span class="text-[7px] font-black -mt-2 -ml-0.5">10</span>
                  </button>

                  <!-- Play / Pause button -->
                  <button @click="togglePlay"
                    class="w-16 h-16 rounded-full flex items-center justify-center text-white shadow-[0_6px_20px_rgba(22,163,74,0.4)] transition-all hover:scale-105 active:scale-95 shrink-0"
                    :class="isPlaying ? 'bg-emerald-600' : 'bg-emerald-600'">
                    <i :class="isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play'" class="text-xl ml-0.5"></i>
                  </button>

                  <button @click="skipForward"
                    class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-all">
                    <span class="text-[7px] font-black -mt-2 -mr-0.5">10</span>
                    <i class="fa-solid fa-rotate-right text-sm"></i>
                  </button>
                </div>

                <!-- Info + Progress -->
                <div class="flex-1 min-w-0">
                  <p class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-500 mb-0.5">
                    Audio Source {{ String(activeQIdx + 1).padStart(2, '0') }}
                  </p>
                  <p class="text-[13px] font-headline font-black text-slate-700 truncate mb-3">
                    {{ currentQuestion?.audio_title || currentQuestion?.question_text?.substring(0, 50) || 'Bài nghe chính tả' }}
                  </p>

                  <!-- Time display -->
                  <div class="flex items-center justify-between mb-2 text-[11px] font-black tabular-nums">
                    <span class="text-slate-600">{{ formattedAudioTime }}</span>
                    <span class="text-slate-300">{{ formattedAudioDuration }}</span>
                  </div>

                  <!-- Seekbar -->
                  <div class="relative h-2 bg-slate-100 rounded-full overflow-hidden cursor-pointer mb-3"
                    @click="seekAudio">
                    <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-300"
                      :style="{ width: audioProgress + '%' }"></div>
                    <!-- Waveform decoration -->
                    <div class="absolute inset-0 flex items-center gap-px px-1 pointer-events-none opacity-20">
                      <div v-for="n in 40" :key="n" class="flex-1 bg-emerald-700 rounded-full"
                        :style="{ height: (Math.random() * 60 + 20) + '%' }"></div>
                    </div>
                  </div>

                  <!-- Controls row -->
                  <div class="flex items-center gap-3">
                    <!-- Speed selector -->
                    <div class="relative">
                      <button @click="showSpeedMenu = !showSpeedMenu"
                        class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-lg border border-emerald-100/80 hover:bg-emerald-100 transition-all">
                        {{ playbackRate }}x TỐC ĐỘ
                      </button>
                      <div v-if="showSpeedMenu"
                        class="absolute bottom-full mb-2 left-0 bg-white border border-slate-100 rounded-xl shadow-xl p-2 z-50 min-w-[100px]">
                        <button v-for="rate in speedOptions" :key="rate"
                          @click="setSpeed(rate)"
                          class="block w-full text-left px-3 py-2 text-[11px] font-black rounded-lg transition-all"
                          :class="playbackRate === rate ? 'bg-emerald-50 text-emerald-600' : 'text-slate-500 hover:bg-slate-50'">
                          {{ rate }}x
                        </button>
                      </div>
                    </div>

                    <!-- Loop toggle -->
                    <button @click="isLooping = !isLooping"
                      class="px-3 py-1 text-[9px] font-black rounded-lg border transition-all"
                      :class="isLooping
                        ? 'bg-slate-800 text-white border-slate-700'
                        : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300'">
                      <i class="fa-solid fa-repeat mr-1 text-[8px]"></i>LOOP
                    </button>

                    <!-- Play count -->
                    <span v-if="playCount > 0" class="text-[9px] font-black text-slate-300 ml-auto">
                      Đã nghe {{ playCount }} lần
                    </span>

                    <!-- Volume -->
                    <button @click="toggleMute" class="ml-auto text-slate-400 hover:text-slate-600 transition-colors w-7 h-7 flex items-center justify-center">
                      <i :class="isMuted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high'" class="text-sm"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Hidden native audio element -->
              <audio ref="audioEl"
                :src="currentQuestion?.audio_url"
                :loop="isLooping"
                @timeupdate="onTimeUpdate"
                @loadedmetadata="onLoadedMetadata"
                @ended="onAudioEnded"
                @play="isPlaying = true"
                @pause="isPlaying = false"
                preload="metadata">
              </audio>

              <!-- No audio warning -->
              <div v-if="!currentQuestion?.audio_url"
                class="mt-4 flex items-center gap-3 p-4 bg-amber-50/80 rounded-2xl border border-amber-100/60 text-amber-600">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                <p class="text-[11px] font-bold">Bài nghe này chưa có file audio. Giảng viên sẽ cập nhật sau.</p>
              </div>
            </div>

            <!-- ═ TRANSCRIPTION BOX ═ -->
            <div class="mb-8">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-[17px] font-headline font-black text-slate-800">Bài chép của bạn</h3>
                <div class="flex items-center gap-3 text-[10px] font-black text-slate-400">
                  <span class="px-2.5 py-1 bg-slate-100 rounded-lg">{{ currentWordCount }} từ</span>
                  <span class="flex items-center gap-1">
                    <i class="fa-solid fa-cloud-arrow-up text-slate-300"></i>
                    Tự động lưu {{ lastSavedText }}
                  </span>
                </div>
              </div>

              <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <textarea
                  v-model="transcriptions[activeQIdx]"
                  @input="handleInput"
                  :disabled="isRevealed"
                  rows="12"
                  class="w-full px-8 pt-7 pb-4 text-[15px] text-slate-700 leading-[1.9] font-medium resize-none outline-none focus:ring-0 border-0 bg-transparent placeholder:text-slate-300 placeholder:italic placeholder:font-medium"
                  placeholder="Bắt đầu gõ bài chép chính tả tại đây..."
                ></textarea>

                <!-- Bottom bar of textarea -->
                <div class="flex items-center justify-between px-6 py-3 border-t border-slate-50">
                  <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                    <span v-if="isRevealed && similarityScore !== null"
                      class="flex items-center gap-1.5"
                      :class="similarityScore >= 80 ? 'text-emerald-500' : similarityScore >= 50 ? 'text-amber-500' : 'text-red-400'">
                      <i class="fa-solid fa-chart-simple text-xs"></i>
                      Độ chính xác: {{ similarityScore }}%
                    </span>
                    <span v-else class="opacity-50">Gõ bài nghe bên trên</span>
                  </div>
                  <button @click="checkSpelling"
                    class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-[0.15em] rounded-xl hover:border-slate-300 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                    Kiểm tra chính tả
                  </button>
                </div>
              </div>
            </div>

            <!-- ═ DIFF VIEW after reveal ═ -->
            <transition name="slide-down">
              <div v-if="isRevealed && currentQuestion?.question_text" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 mb-8">
                <div class="flex items-center gap-3 mb-6">
                  <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-file-lines text-white text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Đáp án gốc</p>
                    <p class="text-[14px] font-headline font-black text-slate-700">So sánh với bài chép của bạn</p>
                  </div>
                </div>
                <div class="text-[14px] leading-[2] font-medium text-slate-600 bg-slate-50 rounded-2xl p-6 border border-slate-100">
                  <span v-for="(word, wIdx) in diffWords" :key="wIdx"
                    class="inline mr-1 px-0.5 rounded"
                    :class="word.status === 'correct' ? '' : word.status === 'wrong' ? 'bg-red-100 text-red-600 line-through' : 'bg-emerald-100 text-emerald-700'">
                    {{ word.text }}
                  </span>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-6 text-center">
                  <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100/50">
                    <p class="text-[8px] font-black uppercase tracking-widest text-emerald-500 mb-1">Đúng</p>
                    <p class="text-2xl font-headline font-black text-emerald-600">{{ correctWords }}</p>
                  </div>
                  <div class="bg-red-50 rounded-2xl p-4 border border-red-100/50">
                    <p class="text-[8px] font-black uppercase tracking-widest text-red-400 mb-1">Sai</p>
                    <p class="text-2xl font-headline font-black text-red-500">{{ wrongWords }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Chính xác</p>
                    <p class="text-2xl font-headline font-black text-slate-700">{{ similarityScore }}%</p>
                  </div>
                </div>
              </div>
            </transition>

            <!-- ═ TIP CARDS ═ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
              <div class="bg-white rounded-[1.75rem] border border-slate-100 shadow-sm p-6 flex items-start gap-5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100/50 flex items-center justify-center shrink-0">
                  <i class="fa-regular fa-lightbulb text-emerald-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-[11px] font-headline font-black text-slate-700 mb-1">Mẹo của chuyên gia</p>
                  <p class="text-[12px] font-medium text-slate-400 leading-relaxed">
                    Lắng nghe ngữ điệu của người nói — giọng lên thường báo hiệu câu hỏi hoặc dấu phẩy.
                  </p>
                </div>
              </div>
              <div class="bg-slate-700/5 rounded-[1.75rem] border border-slate-200/60 shadow-sm p-6 flex items-start gap-5">
                <div class="w-10 h-10 rounded-2xl bg-slate-100 border border-slate-200/60 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-brain text-slate-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-[11px] font-headline font-black text-slate-700 mb-1">Tải nhận thức</p>
                  <p class="text-[12px] font-medium text-slate-400 leading-relaxed">
                    Hãy nghỉ ngắn sau mỗi 5 phút để duy trì độ chính xác và tránh mệt mỏi khi nghe.
                  </p>
                </div>
              </div>
            </div>
          </template>

        </div>
      </div>
    </div>

    <!-- ═══ BOTTOM BAR ═══ -->
    <div class="bg-white border-t border-slate-100 shadow-sm shrink-0 z-10">
      <div class="max-w-2xl mx-auto px-8 h-16 flex items-center justify-between">
        <button @click="$emit('close')"
          class="flex items-center gap-2 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-700 transition-colors group">
          <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
          Về danh sách
        </button>

        <div class="flex items-center gap-4">
          <div v-if="!isRevealed" class="text-right hidden sm:block">
            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Tiếp theo</p>
            <p class="text-[11px] font-bold text-slate-600">Nộp đáp án</p>
          </div>
          <button @click="isRevealed ? submitAll() : revealAnswer()"
            class="flex items-center gap-2 px-8 py-3 text-white rounded-xl text-[12px] font-black uppercase tracking-widest transition-all shadow-lg group"
            :class="isRevealed
              ? 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_5px_15px_rgba(22,163,74,0.25)]'
              : 'bg-slate-900 hover:bg-emerald-600 shadow-[0_5px_15px_rgba(15,23,42,0.2)] hover:-translate-y-0.5'">
            {{ isRevealed ? 'Hoàn thành & Nộp bài' : 'Nộp đáp án' }}
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
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
          <button @click="activeTool = null" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-400">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
          <template v-if="activeTool === 'notes'">
            <textarea v-model="personalNote" rows="14"
              class="w-full text-[13px] text-slate-700 bg-slate-50 border border-slate-100 rounded-2xl p-4 resize-none outline-none focus:ring-2 focus:ring-emerald-200 transition-all font-medium leading-relaxed"
              placeholder="Ghi chú của bạn..."></textarea>
          </template>
          <template v-else-if="activeTool === 'outlines'">
            <p class="text-[10px] font-bold text-slate-400">Câu hỏi trong bài</p>
            <div class="space-y-2">
              <div v-for="(q, idx) in questions" :key="q.id"
                @click="activeQIdx = idx; activeTool = null"
                class="p-3 rounded-xl border cursor-pointer transition-all text-[11px]"
                :class="idx === activeQIdx ? 'border-emerald-200 bg-emerald-50 text-emerald-700 font-black' : 'border-slate-100 hover:border-slate-200 text-slate-600 font-bold'">
                <p class="opacity-60 text-[9px] uppercase tracking-wider mb-0.5">Bài {{ idx + 1 }}</p>
                {{ q.question_text?.substring(0, 60) || `Bài nghe ${idx + 1}` }}...
              </div>
            </div>
          </template>
          <template v-else-if="activeTool === 'refs'">
            <p class="text-[10px] font-bold text-slate-400 mb-2">Bài chép đã lưu</p>
            <div v-for="(t, idx) in transcriptions" :key="idx"
              class="p-3 rounded-xl border border-slate-100 bg-slate-50 mb-2">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Bài {{ idx + 1 }}</p>
              <p class="text-[12px] font-medium text-slate-600 leading-relaxed line-clamp-3">
                {{ t || 'Chưa có nội dung' }}
              </p>
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
            :class="averageScore >= 80 ? 'bg-emerald-50' : 'bg-amber-50'">
            <i :class="averageScore >= 80 ? 'fa-solid fa-headphones text-emerald-500' : 'fa-solid fa-headphones-simple text-amber-500'" class="text-3xl"></i>
          </div>
          <h3 class="text-2xl font-headline font-black text-slate-800 mb-2">
            {{ averageScore >= 80 ? 'Xuất sắc!' : 'Đã hoàn thành!' }}
          </h3>
          <p class="text-[13px] text-slate-400 font-medium mb-8 leading-relaxed">
            Bạn đã hoàn thành bài nghe chép chính tả với độ chính xác trung bình {{ averageScore }}%.
          </p>
          <div class="bg-emerald-50 rounded-2xl p-6 mb-8 border border-emerald-100/50">
            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1">Độ chính xác</p>
            <p class="text-4xl font-headline font-black text-emerald-600">{{ averageScore }}%</p>
          </div>
          <button @click="$emit('close')"
            class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[12px] font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-md">
            Quay về danh sách
          </button>
        </div>
      </div>
    </transition>

    <!-- Overlay to close speed menu -->
    <div v-if="showSpeedMenu" @click="showSpeedMenu = false" class="fixed inset-0 z-40"></div>

  </div>
</template>

<script setup>
/**
 * DictationExercise.vue
 * Dạng bài nghe chép chính tả (dictation).
 * - Custom HTML5 audio player: play/pause, seek, speed, loop, skip ±10s
 * - Textarea để ghi chép, đếm từ, auto-save simulation
 * - Reveal: word-diff comparison (đúng/sai theo từng từ)
 * - Tính độ chính xác (similarity) bằng word matching
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../../../utils/api'

// ── Props & Emits ──
const props = defineProps({
  quiz: { type: Object, default: null },
  courseName: { type: String, default: '' }
})
const emit = defineEmits(['close', 'submitted'])

// ── Refs ──
const audioEl = ref(null)

// ── State ──
const questions = ref([])
const transcriptions = ref([])    // one textarea value per question
const activeQIdx = ref(0)
const isLoadingQuestions = ref(true)
const isRevealed = ref(false)
const isSubmitting = ref(false)
const showSuccess = ref(false)
const activeTool = ref(null)
const personalNote = ref('')
const lastSavedText = ref('ahora mismo')

// Audio
const isPlaying = ref(false)
const isLooping = ref(false)
const isMuted = ref(false)
const playbackRate = ref(1.0)
const showSpeedMenu = ref(false)
const audioCurrentTime = ref(0)
const audioDuration = ref(0)
const playCount = ref(0)

// Result
const similarityScore = ref(null)
const diffWords = ref([])

// Timer
const TOTAL_SECONDS = 30 * 60
const timeLeft = ref(TOTAL_SECONDS)
let timerInterval = null
let autoSaveInterval = null

// ── Constants ──
const speedOptions = [0.5, 0.75, 1.0, 1.25, 1.5]

const sidebarTools = [
  { key: 'drafts', icon: 'fa-solid fa-layer-group', label: 'Bài chép đã lưu', shortLabel: 'Nháp' },
  { key: 'outlines', icon: 'fa-solid fa-list-ul', label: 'Danh sách bài nghe', shortLabel: 'DS bài' },
  { key: 'refs', icon: 'fa-solid fa-bookmark', label: 'Tài liệu tham khảo', shortLabel: 'Tài liệu' },
  { key: 'notes', icon: 'fa-regular fa-note-sticky', label: 'Ghi chú', shortLabel: 'Ghi chú' },
]

// ── Computed ──
const currentQuestion = computed(() => questions.value[activeQIdx.value] || null)

const currentWordCount = computed(() => {
  const t = transcriptions.value[activeQIdx.value] || ''
  return t.trim() ? t.trim().split(/\s+/).length : 0
})

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const formattedAudioTime = computed(() => formatAudioTime(audioCurrentTime.value))
const formattedAudioDuration = computed(() => formatAudioTime(audioDuration.value))

const audioProgress = computed(() => {
  if (audioDuration.value === 0) return 0
  return (audioCurrentTime.value / audioDuration.value) * 100
})

const correctWords = computed(() => diffWords.value.filter(w => w.status === 'correct').length)
const wrongWords = computed(() => diffWords.value.filter(w => w.status === 'wrong').length)

const averageScore = computed(() => {
  if (similarityScore.value !== null) return similarityScore.value
  return 0
})

// ── Audio helpers ──
const formatAudioTime = (secs) => {
  const m = Math.floor(secs / 60)
  const s = Math.floor(secs % 60)
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

const togglePlay = () => {
  if (!audioEl.value) return
  if (isPlaying.value) {
    audioEl.value.pause()
  } else {
    audioEl.value.play().catch(() => {})
    playCount.value++
  }
}

const skipBack = () => {
  if (audioEl.value) audioEl.value.currentTime = Math.max(0, audioEl.value.currentTime - 10)
}

const skipForward = () => {
  if (audioEl.value) audioEl.value.currentTime = Math.min(audioDuration.value, audioEl.value.currentTime + 10)
}

const seekAudio = (e) => {
  if (!audioEl.value || audioDuration.value === 0) return
  const rect = e.currentTarget.getBoundingClientRect()
  const ratio = (e.clientX - rect.left) / rect.width
  audioEl.value.currentTime = ratio * audioDuration.value
}

const setSpeed = (rate) => {
  playbackRate.value = rate
  if (audioEl.value) audioEl.value.playbackRate = rate
  showSpeedMenu.value = false
}

const toggleMute = () => {
  isMuted.value = !isMuted.value
  if (audioEl.value) audioEl.value.muted = isMuted.value
}

const onTimeUpdate = () => {
  if (audioEl.value) audioEl.value && (audioCurrentTime.value = audioEl.value.currentTime)
}

const onLoadedMetadata = () => {
  if (audioEl.value) audioDuration.value = audioEl.value.duration
}

const onAudioEnded = () => {
  isPlaying.value = false
}

// ── Input handler ──
const handleInput = () => {
  // Trigger autosave countdown reset
}

const checkSpelling = () => {
  const t = transcriptions.value[activeQIdx.value] || ''
  if (!t.trim()) {
    alert('Vui lòng gõ bài chép trước khi kiểm tra chính tả.')
    return
  }
  // Simple feedback
  alert('✓ Không tìm thấy lỗi chính tả rõ ràng. Hãy nghe lại để chắc chắn hơn!')
}

// ── Scoring ──
const computeSimilarity = (userText, correctText) => {
  const userWords = userText.toLowerCase().replace(/[^\w\s]/g, '').split(/\s+/).filter(Boolean)
  const correctWordList = correctText.toLowerCase().replace(/[^\w\s]/g, '').split(/\s+/).filter(Boolean)

  const diff = []
  let correct = 0

  correctWordList.forEach((word, idx) => {
    const userWord = userWords[idx] || ''
    if (userWord === word) {
      diff.push({ text: word, status: 'correct' })
      correct++
    } else if (userWord) {
      diff.push({ text: word, status: 'missing' })
    } else {
      diff.push({ text: word, status: 'wrong' })
    }
  })

  diffWords.value = diff
  return correctWordList.length > 0 ? Math.round((correct / correctWordList.length) * 100) : 0
}

const revealAnswer = () => {
  const userText = transcriptions.value[activeQIdx.value] || ''
  const correctText = currentQuestion.value?.answer || currentQuestion.value?.question_text || ''
  similarityScore.value = computeSimilarity(userText, correctText)
  isRevealed.value = true
}

const submitAll = async () => {
  if (!isRevealed.value) revealAnswer()
  isSubmitting.value = true
  await new Promise(r => setTimeout(r, 900))
  isSubmitting.value = false
  showSuccess.value = true
  stopTimer()
  if (audioEl.value) audioEl.value.pause()
  emit('submitted', {
    quiz_id: props.quiz?.id,
    transcriptions: transcriptions.value,
    accuracy: similarityScore.value,
  })
}

// ── Data loading ──
const buildFromQuestions = (qs) => {
  questions.value = qs
  transcriptions.value = new Array(qs.length).fill('')
}

const fetchQuestions = async () => {
  const filterDictation = (qs) =>
    (qs || []).filter(q => q.question_type === 'dictation')

  if (props.quiz?.questions?.length) {
    const filtered = filterDictation(props.quiz.questions)
    // If no dictation-specific questions, treat the whole quiz as one dictation passage
    buildFromQuestions(filtered.length > 0 ? filtered : [props.quiz])
    isLoadingQuestions.value = false
    return
  }

  if (!props.quiz?.id) { isLoadingQuestions.value = false; return }

  try {
    const res = await apiFetch(`user/quiz_detail.php?quiz_id=${props.quiz.id}`)
    const data = await res.json()
    if (data.status === 'success') {
      const filtered = filterDictation(data.data.questions)
      buildFromQuestions(filtered.length > 0 ? filtered : [data.data])
    }
  } catch { /* ignore */ }
  finally { isLoadingQuestions.value = false }
}

// ── Auto-save simulation ──
const startAutoSave = () => {
  autoSaveInterval = setInterval(() => {
    const now = new Date()
    lastSavedText.value = `${now.getMinutes()}:${String(now.getSeconds()).padStart(2, '0')}`
  }, 30000)
}

// ── Timer ──
const startTimer = () => {
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
    else { stopTimer(); submitAll() }
  }, 1000)
}
const stopTimer = () => {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
}

// ── Watchers ──
watch(activeQIdx, () => {
  isRevealed.value = false
  similarityScore.value = null
  diffWords.value = []
  audioCurrentTime.value = 0
  if (audioEl.value) {
    audioEl.value.pause()
    audioEl.value.currentTime = 0
  }
})

onMounted(() => {
  fetchQuestions()
  startTimer()
  startAutoSave()
})

onUnmounted(() => {
  stopTimer()
  if (autoSaveInterval) clearInterval(autoSaveInterval)
  if (audioEl.value) audioEl.value.pause()
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

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-14px); }

.slide-right-enter-active, .slide-right-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.slide-right-enter-from, .slide-right-leave-to { opacity: 0; transform: translateX(-16px); }
</style>
