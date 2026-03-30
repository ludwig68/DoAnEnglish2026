<template>
  <Transition name="confirm-fade">
    <div
      v-if="confirmStore.visible"
      class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/55 p-4 backdrop-blur-sm"
      @click.self="cancelConfirm"
    >
      <div class="w-full max-w-md overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
        <div class="flex items-start gap-4 px-6 py-6">
          <div
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl text-xl"
            :class="theme.iconWrap"
          >
            <i :class="theme.icon"></i>
          </div>

          <div class="min-w-0 flex-1">
            <h3 class="text-lg font-black text-slate-900">{{ confirmStore.options.title }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ confirmStore.options.message }}</p>
          </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100"
            @click="cancelConfirm"
          >
            {{ confirmStore.options.cancelText }}
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-bold text-white transition"
            :class="theme.confirmButton"
            @click="acceptConfirm"
          >
            {{ confirmStore.options.confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { acceptConfirm, cancelConfirm, confirmStore } from '../utils/confirm'

const themes = {
  danger: {
    iconWrap: 'bg-red-100 text-red-700',
    icon: 'fa-solid fa-trash-can',
    confirmButton: 'bg-red-600 hover:bg-red-700',
  },
  warning: {
    iconWrap: 'bg-amber-100 text-amber-700',
    icon: 'fa-solid fa-triangle-exclamation',
    confirmButton: 'bg-amber-500 hover:bg-amber-600',
  },
  info: {
    iconWrap: 'bg-sky-100 text-sky-700',
    icon: 'fa-solid fa-circle-info',
    confirmButton: 'bg-sky-600 hover:bg-sky-700',
  },
}

const theme = computed(() => themes[confirmStore.options.tone] ?? themes.danger)
</script>

<style scoped>
.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.18s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}
</style>
