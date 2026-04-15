<template>
  <div class="p-6 lg:p-10">
    

    <!-- Stats / Tabs Bar -->
    <div class="flex flex-wrap items-center justify-between gap-6 mb-8">
      <div class="flex items-center p-1 bg-slate-100 rounded-2xl w-fit">
        <button 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="activeTab = tab.value"
          class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300"
          :class="activeTab === tab.value 
            ? 'bg-white text-emerald-600 shadow-sm' 
            : 'text-slate-500 hover:text-slate-700'"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Tìm kiếm học viên..."
            class="pl-11 pr-5 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none w-64 transition-all"
          >
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Student</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Class</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Type</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Reason</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
              <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template v-if="loading">
              <tr v-for="i in 5" :key="i" class="animate-pulse">
                <td colspan="7" class="px-8 py-6">
                  <div class="h-12 bg-slate-50 rounded-xl w-full"></div>
                </td>
              </tr>
            </template>
            <template v-else-if="filteredRequests.length === 0">
              <tr>
                <td colspan="7" class="px-8 py-20 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                      <i class="fa-solid fa-inbox text-3xl text-slate-200"></i>
                    </div>
                    <p class="text-slate-400 font-bold">Không tìm thấy yêu cầu nào.</p>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr 
                v-for="req in filteredRequests" 
                :key="req.type + req.id"
                class="hover:bg-slate-50/50 transition-colors group"
              >
                <td class="px-8 py-6">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-black text-sm shadow-sm">
                      {{ req.student_name.split(' ').pop().charAt(0) }}
                    </div>
                    <div>
                      <h4 class="font-black text-slate-800 text-[15px] leading-tight">{{ req.student_name }}</h4>
                      <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-wider">ID: #ST{{ req.student_id.toString().padStart(5, '0') }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="max-w-[150px]">
                    <h5 class="font-bold text-slate-700 text-sm leading-snug">{{ req.course_title }}</h5>
                    <p class="text-[10px] font-black text-emerald-600 mt-1 uppercase tracking-tight line-clamp-1 opacity-90">{{ req.lesson_title }}</p>
                    <p class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase tracking-tighter line-clamp-1 opacity-70">{{ req.shift_name || req.class_name }}</p>
                  </div>
                </td>
                <td class="px-8 py-6">
                   <span 
                    class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-[0.15em] border"
                    :class="req.type === 'leave' 
                      ? 'bg-amber-50 text-amber-600 border-amber-100' 
                      : 'bg-teal-50 text-teal-600 border-teal-100'"
                  >
                    {{ req.type === 'leave' ? 'Nghỉ học' : 'Học bù' }}
                  </span>
                </td>
                <td class="px-8 py-6">
                  <div class="max-w-[200px] min-w-[120px]">
                    <p v-if="req.type === 'leave'" class="text-xs font-bold text-slate-600 italic leading-relaxed line-clamp-2">"{{ req.reason || 'Không có lý do cụ thể' }}"</p>
                    <p v-else class="text-[11px] font-bold text-slate-400 italic">-- Tự động theo buổi nghỉ --</p>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="text-slate-600">
                    <p class="font-black text-sm">{{ formatDate(req.request_date) }}</p>
                    <p class="text-[10px] text-slate-400 font-bold mt-0.5 uppercase tracking-widest">{{ formatYear(req.request_date) }}</p>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="flex items-center gap-2">
                    <span 
                      class="w-2 h-2 rounded-full"
                      :class="{
                        'bg-amber-400 animate-pulse': req.status === 'pending' || req.status === 'registered',
                        'bg-emerald-500': req.status === 'approved' || req.status === 'attended',
                        'bg-rose-500': req.status === 'rejected' || req.status === 'cancelled'
                      }"
                    ></span>
                    <span class="text-xs font-black uppercase tracking-widest" :class="{
                       'text-amber-600': req.status === 'pending' || req.status === 'registered',
                       'text-emerald-600': req.status === 'approved' || req.status === 'attended',
                       'text-rose-600': req.status === 'rejected' || req.status === 'cancelled',
                       'text-slate-400': !['pending','registered','approved','attended','rejected','cancelled'].includes(req.status)
                    }">
                      {{ formatStatus(req.status) || 'PENDING' }}
                    </span>
                    <!-- Hiển thị admin_note nếu có (khi bị từ chối) -->
                    <div v-if="req.admin_note && (req.status === 'rejected')" class="group/note relative ml-1">
                       <i class="fa-solid fa-circle-info text-[10px] text-rose-300"></i>
                       <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 p-2 bg-slate-800 text-white text-[9px] rounded-lg opacity-0 group-hover/note:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none">
                         {{ req.admin_note }}
                       </div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="flex items-center gap-2" v-if="isPending(req)">
                    <button 
                      @click="approveRequest(req)"
                      class="px-4 py-2 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
                      style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
                    >
                      Duyệt
                    </button>
                    <button 
                      @click="rejectRequest(req)"
                      class="w-9 h-9 flex items-center justify-center bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl transition-all"
                    >
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                  <div v-else class="text-[10px] font-bold text-slate-300 uppercase italic">
                    N/A
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { apiFetch } from '../../utils/api';
import { notifySuccess, notifyError, notifyInfo } from '../../utils/notify';
import { openConfirm } from '../../utils/confirm';

const requests = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const activeTab = ref('all');

const tabs = [
  { label: 'Tất cả', value: 'all' },
  { label: 'Chờ duyệt', value: 'pending' },
  { label: 'Đã duyệt', value: 'approved' },
  { label: 'Đã từ chối', value: 'rejected' },
];

const fetchRequests = async () => {
  loading.value = true;
  try {
    const resp = await apiFetch('/admin/requests.php');
    const data = await resp.json();
    if (data.status === 'success') {
      requests.value = data.data;
    }
  } catch (error) {
    console.error('Failed to fetch requests:', error);
    notifyError('Không thể tải danh sách yêu cầu.');
  } finally {
    loading.value = false;
  }
};

const filteredRequests = computed(() => {
  let filtered = requests.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(r => 
      r.student_name.toLowerCase().includes(query) || 
      r.course_title.toLowerCase().includes(query)
    );
  }

  // Filter by tab
  if (activeTab.value === 'pending') {
    filtered = filtered.filter(r => r.status === 'pending' || r.status === 'registered');
  } else if (activeTab.value === 'approved') {
    filtered = filtered.filter(r => r.status === 'approved' || r.status === 'attended');
  } else if (activeTab.value === 'rejected') {
    filtered = filtered.filter(r => r.status === 'rejected' || r.status === 'cancelled');
  }

  return filtered;
});

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
};

const formatYear = (dateStr) => {
  const date = new Date(dateStr);
  return date.getFullYear();
};

const formatStatus = (status) => {
  if (!status) return 'PENDING';
  const s = status.toLowerCase();
  switch (s) {
    case 'pending': 
    case 'registered': return 'Pending';
    case 'approved': 
    case 'attended': return 'Approved';
    case 'rejected': return 'Rejected';
    case 'cancelled': return 'Cancelled';
    default: return status.charAt(0).toUpperCase() + status.slice(1);
  }
};

const isPending = (req) => {
  const s = req.status ? req.status.toLowerCase() : 'registered';
  return s === 'pending' || s === 'registered';
};

const approveRequest = async (req) => {
  const ok = await openConfirm({
    title: 'Xác nhận duyệt?',
    message: `Bạn có chắc chắn muốn duyệt đơn ${req.type === 'leave' ? 'nghỉ học' : 'học bù'} này?`,
    tone: 'info',
    confirmText: 'Duyệt ngay'
  });

  if (!ok) return;

  try {
    const res = await apiFetch('admin/requests.php', {
      method: 'POST',
      body: JSON.stringify({ type: req.type, id: req.id, action: 'approve' })
    })
    const result = await res.json()
    
    if (result.status === 'success') {
      notifySuccess(result.message || 'Đã phê duyệt yêu cầu thành công.')
      fetchRequests()
    } else {
      notifyError(result.message || 'Lỗi không xác định từ máy chủ.')
    }
  } catch (err) {
    notifyError('Mất kết nối máy chủ hoặc lỗi hệ thống.')
  }
};

const rejectRequest = async (req) => {
  const note = prompt('Lý do từ chối (không bắt buộc):')
  if (note === null) return

  try {
    const res = await apiFetch('admin/requests.php', {
      method: 'POST',
      body: JSON.stringify({ type: req.type, id: req.id, action: 'reject', admin_note: note })
    })
    const result = await res.json()

    if (result.status === 'success') {
      notifyInfo('Đã từ chối yêu cầu.')
      fetchRequests()
    } else {
      notifyError(result.message || 'Lỗi không xác định từ máy chủ.')
    }
  } catch (err) {
    notifyError('Mất kết nối máy chủ hoặc lỗi hệ thống.')
  }
};

onMounted(() => {
  fetchRequests();
});
</script>

<style scoped>
.divide-y > tr:last-child {
  border-bottom: none;
}
</style>
