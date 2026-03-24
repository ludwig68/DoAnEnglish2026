<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
    
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col transition-all z-20 shrink-0">
      <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/50">
        <span class="w-3 h-3 rounded-full bg-[#7AE582] shadow-[0_0_10px_#7AE582] mr-3"></span>
        <span class="text-white font-bold tracking-wide uppercase text-sm">Admin Panel</span>
      </div>
      <div class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1 px-3">
          <li>
            <router-link to="/admin" exact-active-class="bg-[#7AE582]/10 text-[#7AE582]" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
              <i class="fa-solid fa-chart-pie w-5 text-center"></i> Tổng quan
            </router-link>
          </li>
          <li>
            <router-link to="/admin/users" active-class="bg-[#7AE582]/10 text-[#7AE582]" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors">
              <i class="fa-solid fa-users w-5 text-center"></i> Quản lý học viên
            </router-link>
          </li>
          </ul>
      </div>
      <div class="p-4 border-t border-slate-800">
        <button @click="logout" class="flex items-center gap-3 px-3 py-2.5 w-full rounded-lg hover:bg-red-500/10 hover:text-red-400 transition-colors text-sm">
          <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Đăng xuất
        </button>
      </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-10">
        <h2 class="text-lg font-bold text-slate-800">Hệ thống quản trị</h2>
        <div class="flex items-center gap-3 pl-5 border-l border-slate-200">
          <p class="text-sm font-bold text-slate-700">Admin System</p>
          <img src="https://placehold.co/100x100/7AE582/ffffff?text=AD" class="w-9 h-9 rounded-full border border-slate-200">
        </div>
      </header>

      <div class="flex-1 overflow-y-auto bg-slate-50">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { clearAuthSession } from '../../utils/auth'

const router = useRouter()

const logout = () => {
  clearAuthSession()
  router.push('/login')
}
</script>
