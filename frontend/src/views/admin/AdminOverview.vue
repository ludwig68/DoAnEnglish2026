<template>
  <div class="p-6 animate__animated animate__fadeIn">
    
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-800">Chào mừng trở lại! 👋</h1>
      <p class="text-sm text-slate-500 mt-1">Dưới đây là tổng quan tình hình hoạt động của hệ thống hôm nay.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shrink-0">
          <i class="fa-solid fa-users"></i>
        </div>
        <div>
          <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Tổng học viên</p>
          <h3 class="text-2xl font-black text-slate-800 mt-0.5">1,254</h3>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
          <i class="fa-solid fa-book-open"></i>
        </div>
        <div>
          <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Khóa học</p>
          <h3 class="text-2xl font-black text-slate-800 mt-0.5">48</h3>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center text-xl shrink-0">
          <i class="fa-solid fa-clone"></i>
        </div>
        <div>
          <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Flashcards</p>
          <h3 class="text-2xl font-black text-slate-800 mt-0.5">3,820</h3>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl shrink-0">
          <i class="fa-solid fa-clipboard-question"></i>
        </div>
        <div>
          <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Bài tập (Quiz)</p>
          <h3 class="text-2xl font-black text-slate-800 mt-0.5">156</h3>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
        <h3 class="font-bold text-slate-800">Học viên mới đăng ký</h3>
        <button class="text-sm font-medium text-[#16a34a] hover:underline">Xem tất cả</button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-xs uppercase font-medium text-slate-500">
            <tr>
              <th class="px-6 py-3">Học viên</th>
              <th class="px-6 py-3">Email</th>
              <th class="px-6 py-3">Ngày đăng ký</th>
              <th class="px-6 py-3 text-right">Trạng thái</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-medium text-slate-800">Nguyễn Văn A</td>
              <td class="px-6 py-4">nguyenvana@gmail.com</td>
              <td class="px-6 py-4">19/03/2026</td>
              <td class="px-6 py-4 text-right">
                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-md text-xs font-bold">Hoạt động</span>
              </td>
            </tr>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-medium text-slate-800">Trần Thị B</td>
              <td class="px-6 py-4">tranthib@gmail.com</td>
              <td class="px-6 py-4">18/03/2026</td>
              <td class="px-6 py-4 text-right">
                <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-md text-xs font-bold">Hoạt động</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
// Trang này bây giờ chỉ làm nhiệm vụ hiển thị "Ruột", 
// không cần xử lý Đăng xuất hay giao diện Menu nữa.
</script>