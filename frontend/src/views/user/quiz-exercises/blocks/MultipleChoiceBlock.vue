<template>
  <div class="flex flex-col gap-5 w-full max-w-3xl mx-auto pb-10">
    <!-- Question Card -->
    <div class="bg-white rounded-[2rem] border border-slate-50 shadow-sm p-8 pb-10 relative overflow-hidden mb-2">
      <!-- Decorative green curve -->
      <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-50 rounded-full opacity-60"></div>
      
      <div class="relative z-10">
        <p class="text-[12px] font-headline font-black text-[#008035] mb-3 tracking-wide flex items-center gap-1.5">
          <i class="fa-solid fa-circle-question text-[10px]"></i> Câu hỏi
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
        <div v-if="!isAnswerRevealed && savedAnswer === option.id" class="shrink-0 flex items-center justify-center w-[22px] h-[22px] rounded-full bg-[#008035]">
           <i class="fa-solid fa-check text-white text-[10px]"></i>
        </div>

        <!-- Revealed indicators -->
        <div v-else-if="isAnswerRevealed && savedAnswer === option.id && option.is_correct"
          class="w-[22px] h-[22px] bg-[#008035] rounded-full flex items-center justify-center shrink-0">
          <i class="fa-solid fa-check text-white text-[10px]"></i>
        </div>
        <div v-else-if="isAnswerRevealed && savedAnswer === option.id && !option.is_correct"
          class="w-[22px] h-[22px] bg-red-400 rounded-full flex items-center justify-center shrink-0">
          <i class="fa-solid fa-xmark text-white text-[10px]"></i>
        </div>
        <div v-else-if="isAnswerRevealed && option.is_correct && savedAnswer !== option.id"
          class="w-[22px] h-[22px] bg-emerald-100 rounded-full flex items-center justify-center shrink-0 border border-emerald-200">
          <i class="fa-solid fa-check text-[#008035] text-[10px]"></i>
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
import { computed } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: [String, Number], default: null },
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F']

const selectAnswer = (option) => {
  if (props.isAnswerRevealed) return
  emit('update-answer', option.id)
}

const getOptionClass = (option) => {
  if (!props.isAnswerRevealed) {
    if (props.savedAnswer === option.id) {
      return 'border-[#008035] bg-white shadow-[0_4px_15px_rgba(0,128,53,0.08)]'
    }
    return 'border-transparent bg-[#F4F5F7] hover:border-emerald-200 hover:bg-[#EDF2F7]' // Unselected Grey Look
  }

  // Revealed
  if (option.is_correct) {
    return props.savedAnswer === option.id
      ? 'border-[#008035] bg-white shadow-[0_4px_15px_rgba(0,128,53,0.08)]'
      : 'border-emerald-200 bg-emerald-50/50 border-dashed'
  }

  if (props.savedAnswer === option.id) {
    return 'border-red-300 bg-red-50/80'
  }

  return 'border-transparent bg-[#F4F5F7] opacity-60'
}

const getLetterClass = (option) => {
  if (!props.isAnswerRevealed) {
    return props.savedAnswer === option.id
      ? 'bg-[#008035] text-white shadow-md'
      : 'bg-white text-[#008035] shadow-sm group-hover:bg-[#008035] group-hover:text-white'
  }

  if (option.is_correct) {
    return props.savedAnswer === option.id
      ? 'bg-[#008035] text-white shadow-md'
      : 'bg-emerald-100 text-[#008035]'
  }

  if (props.savedAnswer === option.id) {
    return 'bg-red-400 text-white shadow-md'
  }

  return 'bg-white text-slate-400'
}

const getOptionTextClass = (option) => {
  if (!props.isAnswerRevealed) {
    return props.savedAnswer === option.id ? 'text-[#008035]' : 'text-slate-600 group-hover:text-slate-800'
  }

  if (option.is_correct) {
    return props.savedAnswer === option.id ? 'text-[#008035]' : 'text-emerald-700'
  }

  if (props.savedAnswer === option.id) {
    return 'text-red-700'
  }

  return 'text-slate-400 line-through'
}
</script>
