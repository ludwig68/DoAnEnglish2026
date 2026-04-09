<template>
  <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-10">
    
    <!-- ═ PROMPT CARD ═ -->
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 flex flex-col gap-5">
      <!-- Badge Row -->
      <div class="flex items-center gap-3 text-[10px] font-black tracking-widest">
        <span class="px-3 py-1.5 bg-[#86EFAC]/40 text-[#047857] rounded-full uppercase shadow-sm">
          ESSAY PROMPT
        </span>
        <span class="text-slate-400 flex items-center gap-2">
          <span class="w-1 h-1 rounded-full bg-slate-300"></span> {{ minWords }} Words Minimum
        </span>
      </div>

      <!-- Main Question -->
      <h2 class="text-[22px] font-headline font-black text-slate-800 leading-snug">
        {{ mainPrompt }}
      </h2>

      <!-- Sub Queries / Hints -->
      <div v-if="subPrompts.length > 0" class="text-[14px] text-slate-500 font-medium leading-relaxed flex flex-col gap-4">
        <p v-for="(line, idx) in subPrompts" :key="idx">{{ line }}</p>
      </div>
    </div>

    <!-- ═ EDITOR CARD ═ -->
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm flex flex-col h-[500px] overflow-hidden relative group">
      <!-- Fake Toolbar & Word Count -->
      <div class="px-8 py-5 border-b border-slate-50 flex items-center justify-between bg-white z-10">
        <div class="flex items-center gap-6 text-slate-400">
          <button @click="formatText('bold')" 
            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors cursor-pointer"
            :class="formatState.bold ? 'bg-emerald-50 text-emerald-600' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'">
            <i class="fa-solid fa-bold text-[14px]"></i>
          </button>
          <button @click="formatText('italic')" 
            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors cursor-pointer"
            :class="formatState.italic ? 'bg-emerald-50 text-emerald-600' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'">
            <i class="fa-solid fa-italic text-[14px]"></i>
          </button>
          <button @click="formatText('insertUnorderedList')" 
            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors cursor-pointer"
            :class="formatState.insertUnorderedList ? 'bg-emerald-50 text-emerald-600' : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50'">
            <i class="fa-solid fa-list-ul text-[14px]"></i>
          </button>
        </div>
        <div class="text-[10px] uppercase font-black tracking-widest text-slate-400">
          WORD COUNT: <span class="text-slate-700 ml-1">{{ currentWordCount }} <span class="text-slate-300 mx-0.5">/</span> {{ minWords }}</span>
        </div>
      </div>

      <!-- Editor -->
      <div class="flex-1 relative bg-white">
        <!-- Placeholder overlay if empty -->
        <div v-if="!hasContent" class="absolute inset-0 px-8 pt-8 pointer-events-none text-slate-300 italic text-[15px] font-body">
          Start typing your essay here...
        </div>
        <!-- Contenteditable -->
        <div
          ref="editorRef"
          contenteditable="true"
          @input="handleInput"
          @keyup="updateContentState"
          @mouseup="updateContentState"
          :class="isAnswerRevealed ? 'opacity-70 pointer-events-none' : ''"
          class="absolute inset-0 w-full h-full px-8 pt-8 pb-10 text-[15px] font-body text-slate-700 leading-loose outline-none border-none overflow-y-auto block-editor"
        ></div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  savedAnswer: { type: String, default: '' },
  isAnswerRevealed: { type: Boolean, default: false }
})

const emit = defineEmits(['update-answer'])

const editorRef = ref(null)
const plainTextContext = ref('')

const formatState = ref({
  bold: false,
  italic: false,
  insertUnorderedList: false
})

const hasContent = computed(() => {
  return plainTextContext.value.trim().length > 0
})

const updateContentState = () => {
  if (!editorRef.value) return
  formatState.value.bold = document.queryCommandState('bold')
  formatState.value.italic = document.queryCommandState('italic')
  formatState.value.insertUnorderedList = document.queryCommandState('insertUnorderedList')
}

onMounted(() => {
  if (editorRef.value && props.savedAnswer) {
    editorRef.value.innerHTML = props.savedAnswer
    plainTextContext.value = editorRef.value.innerText
  }
  document.addEventListener('selectionchange', updateContentState)
})

onUnmounted(() => {
  document.removeEventListener('selectionchange', updateContentState)
})

watch(() => props.savedAnswer, (newVal) => {
  if (editorRef.value && newVal !== editorRef.value.innerHTML) {
    editorRef.value.innerHTML = newVal || ''
    plainTextContext.value = editorRef.value.innerText
  }
})

const handleInput = () => {
  if (!editorRef.value) return
  const html = editorRef.value.innerHTML
  plainTextContext.value = editorRef.value.innerText
  emit('update-answer', html)
}

const formatText = (command) => {
  if (props.isAnswerRevealed) return
  // execCommand is deprecated but still the standard way for basic cross-browser simple rich text editors in HTML5
  document.execCommand(command, false, null)
  editorRef.value.focus()
  handleInput()
  updateContentState()
}

const currentWordCount = computed(() => {
  return plainTextContext.value.trim() ? plainTextContext.value.trim().split(/\s+/).length : 0
})

const minWords = computed(() => {
  return 250 // Typical IELTS task 2 minimum
})

const mainPrompt = computed(() => {
  const text = props.question?.question_text || 'Some people believe that the best way to control accidents on our roads is to increase the minimum legal age for driving cars or riding motorbikes.'
  // Splitting purely for UI replication if the DB gives one long string. 
  // We'll treat the first sentence as mainPrompt if it's super long, or just the whole thing if short.
  const parts = text.split('\n').filter(p => p.trim())
  return parts[0] || text
})

const subPrompts = computed(() => {
  const text = props.question?.question_text || ''
  const parts = text.split('\n').filter(p => p.trim())
  if (parts.length > 1) {
    return parts.slice(1)
  }
  // Fallback for mockup representation if question_text is empty or unformatted
  if (!text) {
    return [
      "To what extent do you agree or disagree with this statement?",
      "Give reasons for your answer and include any relevant examples from your own knowledge or experience."
    ]
  }
  return []
})
</script>

<style scoped>
.block-editor :deep(ul) {
  list-style-type: disc;
  padding-left: 2rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}
.block-editor :deep(b), .block-editor :deep(strong) {
  font-weight: 800;
  color: #1e293b; /* slate-800 */
}
.block-editor :deep(i), .block-editor :deep(em) {
  font-style: italic;
}
</style>
