<template>
  <a :href="getFileUrl(mat.file_url)" target="_blank"
    class="group flex items-center gap-4 p-4 rounded-2xl bg-white hover:shadow-[0_4px_20px_rgb(0,0,0,0.06)] hover:bg-emerald-50/30 transition-all duration-300 border border-transparent hover:border-emerald-200 cursor-pointer">
    
    <div class="w-12 h-12 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-105" :class="colorClasses.text">
      <i :class="colorClasses.icon" class="text-[2.2rem]"></i>
    </div>
    
    <div class="flex-1 min-w-0 pr-2">
      <h4 class="font-bold text-slate-800 text-[15px] truncate mb-0.5 group-hover:text-emerald-600 transition-colors" :title="mat.title">{{ mat.title }}</h4>
      <p class="text-slate-400 text-[12px] font-semibold">{{ formatFileSize(mat.file_size) }}</p>
    </div>
  </a>
</template>

<script setup>
import { computed } from 'vue'
import { getFileUrl } from '../../../utils/api'

const props = defineProps({
  mat: { type: Object, required: true }
})

const getFileColors = (type) => {
  switch(type) {
    case 'pdf': return { icon: 'fa-solid fa-file-pdf', text: 'text-red-500' }
    case 'audio': return { icon: 'fa-solid fa-file-audio', text: 'text-indigo-500' }
    case 'video': return { icon: 'fa-solid fa-circle-play', text: 'text-emerald-500' }
    case 'image': return { icon: 'fa-solid fa-image', text: 'text-cyan-500' }
    case 'document': return { icon: 'fa-solid fa-file-word', text: 'text-blue-500' }
    default: return { icon: 'fa-solid fa-file', text: 'text-slate-400' }
  }
}

const colorClasses = computed(() => getFileColors(props.mat.file_type))

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

</script>
