<template>
  <div class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex justify-center px-4 sm:justify-end">
    <TransitionGroup
      name="toast-list"
      tag="div"
      class="flex w-full max-w-sm flex-col gap-3"
    >
      <article
        v-for="item in notificationStore.items"
        :key="item.id"
        class="pointer-events-auto overflow-hidden rounded-2xl border bg-white shadow-2xl shadow-slate-900/10 backdrop-blur"
        :class="toastTheme[item.type]?.card ?? toastTheme.info.card"
      >
        <div class="flex items-start gap-3 px-4 py-3">
          <div
            class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm"
            :class="toastTheme[item.type]?.icon ?? toastTheme.info.icon"
          >
            <i :class="toastTheme[item.type]?.symbol ?? toastTheme.info.symbol"></i>
          </div>

          <div class="min-w-0 flex-1">
            <p class="text-sm font-black text-slate-900">{{ item.title }}</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ item.message }}</p>
          </div>

          <button
            type="button"
            class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
            @click="dismissNotification(item.id)"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </article>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { notificationStore, dismissNotification } from '../utils/notify'

const toastTheme = {
  success: {
    card: 'border-emerald-200',
    icon: 'bg-emerald-100 text-emerald-700',
    symbol: 'fa-solid fa-circle-check',
  },
  error: {
    card: 'border-red-200',
    icon: 'bg-red-100 text-red-700',
    symbol: 'fa-solid fa-circle-exclamation',
  },
  warning: {
    card: 'border-amber-200',
    icon: 'bg-amber-100 text-amber-700',
    symbol: 'fa-solid fa-triangle-exclamation',
  },
  info: {
    card: 'border-sky-200',
    icon: 'bg-sky-100 text-sky-700',
    symbol: 'fa-solid fa-circle-info',
  },
}
</script>

<style scoped>
.toast-list-enter-active,
.toast-list-leave-active {
  transition: all 0.2s ease;
}

.toast-list-enter-from,
.toast-list-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.98);
}

.toast-list-move {
  transition: transform 0.2s ease;
}
</style>
