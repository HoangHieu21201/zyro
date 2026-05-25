<template>
  <div class="admin-chat-wrapper pb-3 d-flex flex-column" style="height: calc(100vh - 80px); overflow: hidden;">
    
    <div class="d-flex align-items-center justify-content-between mb-3 px-3 flex-shrink-0">
      <h4 class="fw-bold text-dark dark:text-white mb-0 font-sans-vn">Hỗ Trợ Trực Tuyến (Real-time)</h4>
      <span class="badge bg-urban px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-broadcast me-1"></i> Echo Reverb Connected</span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden bg-white dark:bg-[#1a2533] mx-3" style="min-height: 0;">
      <div class="row g-0 h-100">
        
        <!-- CỘT 1: DANH SÁCH KHÁCH HÀNG -->
        <div class="col-md-5 col-lg-3 border-end dark:border-gray-700 d-flex flex-column h-100 bg-light dark:bg-[#212529]">
          <div class="p-3 border-bottom dark:border-gray-700 flex-shrink-0">
            <div class="input-group shadow-sm-hover bg-white dark:bg-[#121416] rounded-pill overflow-hidden">
               <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
               <input type="text" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="searchQuery" placeholder="Tìm khách hàng...">
            </div>
          </div>
          
          <div class="flex-grow-1 overflow-auto custom-scrollbar-y" style="height: 0;">
            <div v-if="isLoading" class="p-4 text-center text-muted">
               <span class="spinner-border text-urban mb-2"></span><br>Đang tải...
            </div>
            <div v-else-if="filteredConversations.length === 0" class="p-5 text-center text-muted small">
               <i class="bi bi-chat-square-text fs-1 opacity-25 d-block mb-3"></i>Không có đoạn chat nào.
            </div>
            <div v-else class="list-group list-group-flush rounded-0">
              <a href="#" v-for="conv in filteredConversations" :key="conv.id" 
                 class="list-group-item list-group-item-action p-3 border-bottom dark:border-gray-700 transition-all border-0"
                 :class="{'active-conv': activeConvId === conv.id, 'bg-transparent dark:text-gray-300': activeConvId !== conv.id}"
                 @click.prevent="selectConversation(conv.id)">
                
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <h6 class="fw-bold mb-0 text-truncate font-sans-vn" :class="{'text-urban': activeConvId === conv.id}">
                     {{ conv.user ? conv.user.full_name : 'Khách vãng lai' }}
                  </h6>
                  <small class="text-muted font-monospace" style="font-size: 0.7rem;">{{ formatTime(conv.last_message_at) }}</small>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <p class="mb-0 small text-truncate flex-grow-1 font-sans-vn" :class="{'text-muted': activeConvId !== conv.id, 'fw-bold text-dark dark:text-white': conv.has_unread}">
                     {{ conv.last_message_snippet || 'Bắt đầu trò chuyện' }}
                  </p>
                  <span v-if="conv.status === 'bot_handling'" class="badge bg-secondary ms-2 shadow-sm" style="font-size: 0.6rem;"><i class="bi bi-robot"></i> Bot</span>
                  <span v-if="conv.has_unread" class="badge bg-danger rounded-circle p-1 ms-2 shadow-sm" style="width: 10px; height: 10px;"></span>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- CỘT 2: KHUNG CHAT CHÍNH -->
        <div class="col-md-7 col-lg-6 d-flex flex-column h-100 position-relative">
          <template v-if="activeConv">
            
            <div class="p-3 border-bottom dark:border-gray-700 bg-white dark:bg-[#1a2533] d-flex justify-content-between align-items-center shadow-sm z-index-2 flex-shrink-0">
               <div class="d-flex align-items-center">
                 <div class="bg-light dark:bg-[#212529] rounded-circle d-flex justify-content-center align-items-center me-3 border shadow-sm" style="width: 45px; height: 45px;">
                   <i class="bi bi-person-fill text-muted fs-5"></i>
                 </div>
                 <div>
                   <h6 class="fw-bold mb-0 text-dark dark:text-white font-sans-vn">{{ activeConv.user ? activeConv.user.full_name : 'Khách vãng lai' }}</h6>
                   <span class="badge mt-1 shadow-sm" :class="activeConv.status === 'bot_handling' ? 'bg-secondary' : 'bg-success'">
                     <i class="bi" :class="activeConv.status === 'bot_handling' ? 'bi-robot' : 'bi-person-workspace'"></i>
                     {{ activeConv.status === 'bot_handling' ? 'Bot đang tự động tiếp' : 'Nhân viên đang hỗ trợ' }}
                   </span>
                 </div>
               </div>
               <div>
                  <button v-if="activeConv.status === 'bot_handling'" class="btn btn-urban btn-sm fw-bold shadow-sm rounded-pill px-3 hover-transform" @click="takeoverChat(activeConv.id)">
                    <i class="bi bi-person-workspace me-1"></i> Nhân viên Tiếp quản
                  </button>
                  <button v-else class="btn btn-outline-secondary btn-sm fw-bold shadow-sm rounded-pill px-3 hover-transform" @click="resolveChat(activeConv.id)">
                    <i class="bi bi-check2-all me-1"></i> Kết Thúc Phiên
                  </button>

                  <button class="btn btn-outline-danger btn-sm fw-bold shadow-sm rounded-pill px-3 ms-2 hover-transform" @click="deleteConversation(activeConv.id)" title="Xóa toàn bộ lịch sử">
                    <i class="bi bi-trash3-fill"></i> Xóa Chat
                  </button>
               </div>
            </div>

            <div class="flex-grow-1 p-3 overflow-auto custom-scrollbar-y bg-light dark:bg-[#121416]" ref="chatBodyRef" style="height: 0;">
               
               <template v-for="item in groupedMessages" :key="item.isDateLabel ? item.date : item.id">
                 
                 <div v-if="item.isDateLabel" class="text-center my-4">
                   <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-1 fw-medium font-sans-vn" style="font-size: 0.75rem;">
                      {{ item.date }}
                   </span>
                 </div>

                 <div v-else class="d-flex mb-3 animation-fade-in" :class="item.sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start'">
                   
                   <div v-if="item.sender_type !== 'admin' && item.sender_type !== 'system'" class="me-2 flex-shrink-0 mt-auto">
                      <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm border dark:border-gray-600" style="width: 32px; height: 32px;">
                        <i v-if="item.sender_type === 'bot'" class="bi bi-robot text-urban"></i>
                        <i v-else class="bi bi-person-fill text-secondary"></i>
                      </div>
                   </div>

                   <div class="msg-bubble shadow-sm font-sans-vn d-flex flex-column" 
                        :class="[
                          item.sender_type === 'admin' ? 'admin-bubble' : 'user-bubble',
                          item.sender_type === 'system' ? 'system-bubble mx-auto fst-italic text-center shadow-none' : ''
                        ]">
                      
                      <div v-html="formatMessage(item.content)"></div>
                      
                      <div v-if="item.sender_type !== 'system'" class="d-flex align-items-center mt-1" :class="item.sender_type === 'admin' ? 'justify-content-end text-light opacity-75' : 'justify-content-end text-muted opacity-75'" style="font-size: 0.65rem;">
                         <span>{{ formatTime(item.created_at) }}</span>
                         <i v-if="item.sender_type === 'admin'" class="bi bi-check2-all ms-1 fs-6 lh-1"></i>
                      </div>

                   </div>
                 </div>
               </template>

            </div>

            <div class="p-3 border-top dark:border-gray-700 bg-white dark:bg-[#1a2533] z-index-2 shadow-sm-top flex-shrink-0">
              <div v-if="activeConv.status === 'bot_handling'" class="text-center text-danger fw-bold fst-italic small py-2 bg-danger bg-opacity-10 rounded-pill">
                <i class="bi bi-lock-fill me-1"></i> Vui lòng bấm "Nhân viên Tiếp quản" ở trên để tắt Bot và tự nhắn tin.
              </div>
              <form v-else @submit.prevent="sendMessage" class="d-flex align-items-center gap-2 m-0 bg-light dark:bg-[#212529] p-1 rounded-pill border dark:border-gray-600 shadow-sm-hover">
                <input type="text" class="form-control border-0 bg-transparent shadow-none px-3 dark:text-white font-sans-vn" 
                       v-model="inputText" placeholder="Nhập tin nhắn hỗ trợ khách hàng..." autocomplete="off" :disabled="isSending">
                <button type="submit" class="btn btn-urban rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 transition-all hover-scale" style="width: 40px; height: 40px;" :disabled="!inputText.trim() || isSending">
                  <span v-if="isSending" class="spinner-border spinner-border-sm text-white"></span>
                  <i v-else class="bi bi-send-fill" style="margin-left: -2px;"></i>
                </button>
              </form>
            </div>
          </template>
          
          <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-light dark:bg-[#121416]">
             <i class="bi bi-chat-heart fs-1 opacity-25 mb-3"></i>
             <h5 class="fw-normal font-sans-vn">Chọn một khách hàng để bắt đầu tư vấn</h5>
          </div>
        </div>

        <!-- CỘT 3: THÔNG TIN CRM DÀNH CHO NHÂN VIÊN CHỐT SALE -->
        <div class="col-lg-3 d-none d-lg-flex flex-column border-start dark:border-gray-700 bg-white dark:bg-[#1a2533]">
           <div class="p-3 border-bottom dark:border-gray-700 shadow-sm z-index-2 flex-shrink-0">
             <h6 class="fw-bold m-0 text-urban text-uppercase tracking-wide font-sans-vn"><i class="bi bi-person-vcard me-2"></i>CRM Hồ Sơ Khách</h6>
           </div>
           
           <div class="p-3 overflow-auto custom-scrollbar-y flex-grow-1 bg-light dark:bg-[#121416]" style="height: 0;" v-if="activeConv">
              <template v-if="activeConv.user">
                
                
                <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700 font-sans-vn">
                   <div class="mb-3">
                     <small class="text-muted fw-bold text-uppercase d-block mb-1">Email</small>
                     <div class="text-dark dark:text-gray-300 font-monospace small bg-light dark:bg-[#212529] p-2 rounded border dark:border-gray-600">{{ activeConv.user.email }}</div>
                   </div>
                   <div class="mb-0">
                     <small class="text-muted fw-bold text-uppercase d-block mb-1">Điện thoại</small>
                     <div class="text-dark dark:text-gray-300 font-monospace small bg-light dark:bg-[#212529] p-2 rounded border dark:border-gray-600">{{ activeConv.user.phone || 'Chưa cập nhật' }}</div>
                   </div>
                </div>
              </template>
              <template v-else>
                <div class="text-center text-muted py-5 mt-5">
                   <i class="bi bi-incognito fs-1 opacity-25 mb-3 d-block"></i>
                   <h6 class="font-sans-vn">Khách Vãng Lai</h6>
                   <p class="small fst-italic">Khách hàng chưa đăng nhập, không có dữ liệu mua sắm trước đây.</p>
                </div>
              </template>
           </div>
           
           <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-light dark:bg-[#121416]">
             <i class="bi bi-database-exclamation fs-1 opacity-25 mb-3"></i>
             <p class="small text-center px-4 font-sans-vn">Dữ liệu khách hàng sẽ hiển thị khi bạn bắt đầu trò chuyện.</p>
           </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import api from '@/utils/axios';
import Swal from 'sweetalert2';

const conversations = ref([]);
const activeConvId = ref(null);
const activeConv = ref(null);
const messages = ref([]);
const inputText = ref('');
const chatBodyRef = ref(null);
const isLoading = ref(true);
const searchQuery = ref('');
const isSending = ref(false);

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value;
  const q = searchQuery.value.toLowerCase();
  return conversations.value.filter(c => {
     const name = c.user ? c.user.full_name.toLowerCase() : 'khách vãng lai';
     return name.includes(q) || (c.last_message_snippet && c.last_message_snippet.toLowerCase().includes(q));
  });
});

const groupedMessages = computed(() => {
  let groups = [];
  let lastDate = null;
  const today = new Date().toLocaleDateString('vi-VN');
  const yesterday = new Date(Date.now() - 86400000).toLocaleDateString('vi-VN');

  messages.value.forEach(msg => {
    if (!msg.created_at) return;
    const msgDate = new Date(msg.created_at).toLocaleDateString('vi-VN');
    
    if (msgDate !== lastDate) {
      let label = msgDate;
      if (msgDate === today) label = 'Hôm nay';
      else if (msgDate === yesterday) label = 'Hôm qua';
      
      groups.push({ isDateLabel: true, date: label });
      lastDate = msgDate;
    }
    groups.push({ isDateLabel: false, ...msg });
  });

  return groups;
});

const fetchConversations = async () => {
  try {
    const res = await api.get('/admin/chats', { headers: getHeaders() });
    conversations.value = res.data.data;
  } catch (e) {
  } finally {
    isLoading.value = false;
  }
};

const selectConversation = async (id) => {
  activeConvId.value = id;
  activeConv.value = conversations.value.find(c => c.id === id);
  try {
    const res = await api.get(`/admin/chats/${id}/messages`, { headers: getHeaders() });
    messages.value = res.data.data;
    scrollToBottom();
    if(activeConv.value) activeConv.value.has_unread = false;
  } catch(e) {}
};

const takeoverChat = async (id) => {
  try {
    const res = await api.post(`/admin/chats/${id}/takeover`, {}, { headers: getHeaders() });
    if (res.data.success) {
      activeConv.value.status = 'admin_handling';
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã nhận hỗ trợ', showConfirmButton: false, timer: 1500 });
    }
  } catch(e) {}
};

const resolveChat = async (id) => {
  try {
    await api.post(`/admin/chats/${id}/resolve`, {}, { headers: getHeaders() });
    activeConv.value.status = 'resolved';
    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Phiên chat kết thúc', showConfirmButton: false, timer: 1500 });
  } catch(e) {}
};

const deleteConversation = (id) => {
  Swal.fire({
    title: 'Xóa phiên chat?',
    text: 'Toàn bộ tin nhắn của khách này sẽ bị xóa vĩnh viễn khỏi Database.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Đồng ý xóa',
    cancelButtonText: 'Đóng',
    confirmButtonColor: '#dc3545'
  }).then(async (result) => {
    if(result.isConfirmed) {
      try {
        await api.delete(`/admin/chats/${id}`, { headers: getHeaders() });
        activeConvId.value = null;
        activeConv.value = null;
        messages.value = [];
        fetchConversations();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã xóa toàn bộ nội dung', showConfirmButton: false, timer: 1500 });
      } catch(e) {}
    }
  });
};

const sendMessage = async () => {
  if (!inputText.value.trim() || !activeConvId.value || isSending.value) return;
  const text = inputText.value;
  inputText.value = '';
  isSending.value = true;
  
  try {
    const res = await api.post(`/admin/chats/${activeConvId.value}/messages`, { message: text }, { headers: getHeaders() });
    
    if (res.data.success) {
       messages.value.push(res.data.data);
       scrollToBottom();
    }

    if (activeConv.value) {
       activeConv.value.last_message_snippet = text;
       activeConv.value.last_message_at = new Date().toISOString();
       conversations.value.sort((a, b) => new Date(b.last_message_at) - new Date(a.last_message_at));
    }
  } catch (e) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Gửi thất bại', showConfirmButton: false, timer: 1500 });
  } finally {
    isSending.value = false;
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatBodyRef.value) {
      chatBodyRef.value.scrollTop = chatBodyRef.value.scrollHeight;
    }
  });
};

const formatMessage = (text) => {
  if (!text) return '';
  return text.replace(/\n/g, '<br>');
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return `${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
};

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.channel('admin.chats')
      .listen('.MessageSentEvent', (e) => {
         
         if (e.sender_type === 'admin') return;

         if (activeConvId.value === e.conversation_id) {
            const exists = messages.value.some(m => m.id === e.id);
            if (!exists) {
               messages.value.push(e);
               scrollToBottom();
            }
         } else {
            const conv = conversations.value.find(c => c.id === e.conversation_id);
            if (conv) {
               // ĐÃ FIX: Chỉ báo đỏ Unread nếu Admin đang phải tiếp, Bot tiếp thì im lặng
               if (conv.status === 'admin_handling') {
                   conv.has_unread = true;
               }
               conv.last_message_snippet = e.content;
               conv.last_message_at = e.created_at;
               conversations.value.sort((a, b) => new Date(b.last_message_at) - new Date(a.last_message_at));
            } else {
               fetchConversations();
            }
         }
      });
  }
};

onMounted(() => {
  fetchConversations();
  setupRealtime();
});

onUnmounted(() => {
  if (window.Echo) window.Echo.leave('admin.chats');
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.btn-outline-danger:hover { color: #ffffff !important; background-color: #dc3545 !important; }
.btn-outline-danger:hover i { color: #ffffff !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

.active-conv {
  background-color: var(--color-c-effect, #EBF1F5) !important;
  border-left: 4px solid var(--color-c-hover, #547792) !important;
}
html.dark .active-conv { background-color: rgba(255,255,255,0.05) !important; border-left-color: #fff !important; }

.hover-transform:hover { transform: translateY(-2px); }
.hover-scale:hover { transform: scale(1.1); }
.shadow-sm-top { box-shadow: 0 -4px 15px rgba(0,0,0,0.03); }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.15) !important; border-color: var(--color-c-hover, #547792) !important; }

/* BUBBLES */
.msg-bubble { max-width: 80%; padding: 12px 16px; font-size: 0.95rem; line-height: 1.5; word-wrap: break-word; }

.admin-bubble {
  background-color: var(--color-c-hover, #547792);
  color: #fff;
  border-radius: 20px 20px 4px 20px;
}

.user-bubble {
  background-color: #ffffff;
  color: #212529;
  border: 1px solid rgba(0,0,0,0.05);
  border-radius: 20px 20px 20px 4px;
}
html.dark .user-bubble { background-color: #2b3035; color: #f8f9fa; border-color: rgba(255,255,255,0.05); }

.system-bubble {
  background-color: rgba(0,0,0,0.05);
  color: #6c757d;
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 8px 12px;
}
html.dark .system-bubble { background-color: rgba(255,255,255,0.05); color: #adb5bd; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>