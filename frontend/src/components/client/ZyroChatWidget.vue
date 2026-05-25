<template>
  <div class="zyro-chat-container">
    
    <transition name="chat-slide">
      <div v-if="chatStore.isOpen" class="chat-widget-wrapper">
        
        <!-- ======================================================== -->
        <!-- ĐÃ FIX: SEGMENTED CONTROL SIDEBAR (LỒI RA BÊN TRÁI ĐẸP MẮT) -->
        <!-- ======================================================== -->
        <div class="chat-sidebar-segmented">
           <button class="seg-btn bot-btn" 
                   :class="{ active: chatStore.status === 'bot_handling' }" 
                   @click="endHuman" 
                   title="Trợ lý ảo AI">
              <i class="bi bi-robot"></i>
              <span class="seg-text font-sans-vn">AI Bot</span>
           </button>
           
           <button class="seg-btn admin-btn" 
                   :class="{ active: chatStore.status === 'admin_handling' }" 
                   @click="requestHuman" 
                   title="Nhân viên hỗ trợ">
              <i class="bi bi-headset"></i>
              <span class="seg-text font-sans-vn">CSKH</span>
           </button>
        </div>

        <!-- KHUNG CHAT CHÍNH -->
        <div class="chat-window shadow-lg rounded-4 d-flex flex-column border overflow-hidden transition-all"
             :class="chatStore.status === 'admin_handling' ? 'border-success border-opacity-50 dark:border-success dark:border-opacity-50 dark:bg-[#1a2533] bg-white' : 'border-light-subtle dark:border-gray-700 bg-white dark:bg-[#1a2533]'">
          
          <!-- HEADER: Sạch sẽ, Rộng rãi, Đã dời nút chuyển đổi đi -->
          <div class="chat-header text-white p-3 d-flex justify-content-between align-items-center z-index-2 shadow-sm transition-all"
               :class="chatStore.status === 'admin_handling' ? 'bg-success' : 'bg-urban'">
            <div class="d-flex align-items-center gap-2">
              <div class="position-relative">
                <div class="bg-dark rounded-circle d-flex justify-content-center align-items-center shadow-sm p-1 border border-secondary" style="width: 38px; height: 38px;">
                  <i v-if="chatStore.status === 'admin_handling'" class="bi bi-person-workspace text-white fs-5"></i>
                  <img v-else src="@/assets/images/logo/logozyro.png" class="w-100 h-100 object-fit-contain" alt="ZYRO Bot">
                </div>
                <span class="position-absolute bottom-0 end-0 border border-2 border-dark rounded-circle" 
                      :class="chatStore.status === 'admin_handling' ? 'bg-danger animation-pulse-fast' : 'bg-success'" 
                      style="width: 10px; height: 10px;"></span>
              </div>
              <div>
                <h6 class="mb-0 fw-bold font-sans-vn d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                  {{ chatStore.status === 'admin_handling' ? 'Nhân viên ZYRO' : 'ZYRO Trợ lý ảo' }}
                  <span v-if="chatStore.status === 'admin_handling'" class="badge bg-danger rounded-pill px-2" style="font-size: 0.6rem;">LIVE</span>
                </h6>
                <span class="small opacity-75" style="font-size: 0.7rem;">Luôn sẵn sàng hỗ trợ</span>
              </div>
            </div>
            
            <div class="d-flex align-items-center gap-1">
               <button type="button" class="btn btn-link text-white p-0 hover-opacity transition-all d-flex align-items-center justify-content-center ms-2" 
                       @click="resetChat" title="Xóa lịch sử chat" style="width: 30px; height: 30px;">
                 <i class="bi bi-trash3 fs-6"></i>
               </button>

               <button type="button" class="btn btn-link text-white p-0 hover-opacity transition-all d-flex align-items-center justify-content-center ms-1" 
                      @click="chatStore.toggleChat()" title="Đóng chat" style="width: 30px; height: 30px;">
                <i class="bi bi-x-lg fs-5"></i>
              </button>
            </div>
          </div>

          <!-- BODY: DANH SÁCH TIN NHẮN -->
          <div class="chat-body flex-grow-1 p-3 custom-scrollbar-y bg-light dark:bg-[#121416]" ref="chatBodyRef">
            
            <div class="d-flex justify-content-start mb-3 mt-2">
              <div class="msg-bubble bot-bubble shadow-sm font-sans-vn">
                Xin chào! Tớ là Trợ lý ảo ZYRO. Tớ có thể giúp gì cho bạn về sản phẩm, đơn hàng hay tư vấn phong cách không nhỉ? 🥰
              </div>
            </div>

            <div v-for="(msg, index) in chatStore.messages" :key="msg.id || index" class="d-flex mb-3 animation-fade-in" :class="msg.sender_type === 'user' ? 'justify-content-end' : (msg.sender_type === 'system' ? 'justify-content-center' : 'justify-content-start')">
              
              <div v-if="msg.sender_type !== 'user' && msg.sender_type !== 'system'" class="me-2 flex-shrink-0 mt-auto">
                 <div class="bg-dark rounded-circle d-flex justify-content-center align-items-center shadow-sm border dark:border-gray-600" 
                      :class="msg.sender_type === 'admin' ? 'border-success border-2' : ''"
                      style="width: 28px; height: 28px; padding: 3px;">
                   <img v-if="msg.sender_type === 'bot'" src="@/assets/images/logo/logozyro.png" class="w-100 h-100 object-fit-contain" alt="ZYRO">
                   <i v-else-if="msg.sender_type === 'admin'" class="bi bi-person-fill text-white" style="font-size: 1rem;"></i>
                   <i v-else class="bi bi-info-circle text-white"></i>
                 </div>
              </div>

              <div class="msg-bubble shadow-sm font-sans-vn" 
                   :class="[
                     msg.sender_type === 'user' ? 'user-bubble' : 'bot-bubble',
                     msg.sender_type === 'admin' ? 'admin-rep-bubble' : '',
                     msg.sender_type === 'system' ? 'system-bubble fst-italic text-center w-100 shadow-none' : ''
                   ]" 
                   v-html="formatMessage(msg.content)">
              </div>
            </div>
          </div>

          <!-- FOOTER: KHUNG NHẬP -->
          <div class="chat-footer p-2 bg-white dark:bg-[#1a2533] border-top dark:border-gray-700 z-index-2 transition-all"
               :class="chatStore.status === 'admin_handling' ? 'bg-success bg-opacity-10 dark:bg-success dark:bg-opacity-10' : ''">
            
            <div v-if="chatStore.status === 'admin_handling'" class="text-center text-success mb-2 font-sans-vn fw-bold" style="font-size: 0.65rem;">
               <i class="bi bi-shield-check me-1"></i> Đã kết nối với Nhân viên ZYRO
            </div>

            <form @submit.prevent="submitMessage" class="d-flex align-items-center gap-2 m-0 bg-light dark:bg-[#212529] p-1 rounded-pill border dark:border-gray-600 focus-within-ring transition-all"
                  :class="chatStore.status === 'admin_handling' ? 'border-success' : ''">
              
              <input type="text" class="form-control border-0 bg-transparent shadow-none px-3 dark:text-white font-sans-vn" 
                     v-model="inputText" 
                     :placeholder="chatStore.status === 'admin_handling' ? 'Nhắn tin cho nhân viên...' : 'Hỏi trợ lý ảo ZYRO...'" 
                     :disabled="chatStore.isTyping"
                     autocomplete="off"
                     style="font-size: 0.9rem;">
              
              <button type="submit" class="btn rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 transition-transform hover-scale" 
                      :class="chatStore.status === 'admin_handling' ? 'btn-success text-white' : 'btn-urban'"
                      style="width: 36px; height: 36px;" 
                      :disabled="chatStore.isTyping || !inputText.trim()">
                <i class="bi bi-send-fill" style="margin-left: -2px;"></i>
              </button>
            </form>

            <div class="text-center mt-1">
               <span class="small text-muted" style="font-size: 0.65rem;">
                 {{ chatStore.status === 'admin_handling' ? 'Powered by ZYRO CSKH' : 'Powered by ZYRO AI' }}
               </span>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <button v-if="!chatStore.isOpen" type="button" class="btn floating-btn chatbot-btn shadow-lg position-relative" 
              @click="chatStore.toggleChat()"
              title="Chat với ZYRO">
        <i class="bi bi-chat-dots-fill fs-4 text-white transition-all"></i>
        <span v-show="chatStore.unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge bg-danger border border-light dark:border-gray-800 rounded-circle animation-pulse">
          {{ chatStore.unreadCount }}
        </span>
      </button>
    </transition>

  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, watch } from 'vue';
import { useChatStore } from '@/stores/chatStore';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const chatStore = useChatStore();
const inputText = ref('');
const chatBodyRef = ref(null);

const scrollToBottom = () => {
  nextTick(() => {
    if (chatBodyRef.value) {
      chatBodyRef.value.scrollTop = chatBodyRef.value.scrollHeight;
    }
  });
};

watch(() => chatStore.messages.length, () => { if (chatStore.isOpen) scrollToBottom(); });
watch(() => chatStore.isOpen, (val) => { if (val) setTimeout(scrollToBottom, 200); });
watch(() => chatStore.isTyping, () => { if (chatStore.isOpen) scrollToBottom(); });

const formatMessage = (text) => {
  if (!text) return '';
  return text.replace(/\n/g, '<br>');
};

const submitMessage = () => {
  if (!inputText.value.trim()) return;
  chatStore.sendMessage(inputText.value);
  inputText.value = '';
};

const requestHuman = () => {
  chatStore.requestHumanSupport();
  scrollToBottom();
};

const endHuman = () => {
  chatStore.endHumanSupport();
  scrollToBottom();
};

const resetChat = async () => {
  ZyroSwal.confirmDelete('Đoạn chat này').then(async (result) => {
    if (result.isConfirmed) {
      await chatStore.clearHistory();
      ZyroSwal.toastSuccess('Lịch sử đã được xóa sạch.');
    }
  });
};

onMounted(() => {
  chatStore.initSession();
  chatStore.fetchHistory();
});
</script>

<style scoped>
.zyro-chat-container { z-index: 1045; position: relative; }

/* ======================================================== */
/* ĐÃ FIX: Sửa bottom từ 85px thành 25px để bám đáy màn hình */
/* ======================================================== */
.chat-widget-wrapper { 
  width: 360px; height: 550px; max-height: calc(100vh - 100px); 
  position: fixed; bottom: 25px; right: 25px; transform-origin: bottom right; 
}
@media (max-width: 768px) { 
  .chat-widget-wrapper { width: calc(100vw - 75px); height: 70vh; right: 15px; bottom: 25px; } 
}

.chat-window {
  width: 100%; height: 100%; position: relative; z-index: 2;
}

/* ======================================================== */
/* THIẾT KẾ SIDEBAR NAVIGATION LỒI RA BÊN TRÁI              */
/* ======================================================== */
.chat-sidebar-segmented {
  position: absolute;
  left: -56px; /* Đẩy hẳn ra ngoài để không lấn vào khung chat */
  top: 20px; 
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 1; 
}

.seg-btn {
  width: 56px;
  height: 64px;
  border: 1px solid rgba(0,0,0,0.1);
  border-right: none;
  background: white;
  color: #6c757d;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-radius: 16px 0 0 16px;
  box-shadow: -4px 4px 15px rgba(0,0,0,0.05);
  padding: 0 5px 0 0;
}

html.dark .seg-btn {
  background: #1a2533;
  border-color: rgba(255,255,255,0.1);
  color: #adb5bd;
}

.seg-btn i { font-size: 1.3rem; margin-bottom: 2px; }
.seg-btn .seg-text { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; }

.seg-btn:hover:not(.active) {
  width: 60px;
  margin-left: -4px;
  background: #f8f9fa;
  color: var(--color-c-hover, #547792);
}
html.dark .seg-btn:hover:not(.active) { background: #212529; color: white; }

.bot-btn.active {
  width: 64px;
  margin-left: -8px;
  background: var(--color-c-hover, #547792);
  color: white;
  border-color: var(--color-c-hover, #547792);
  box-shadow: -4px 4px 15px rgba(84, 119, 146, 0.3);
  z-index: 2;
}

.admin-btn.active {
  width: 64px;
  margin-left: -8px;
  background: #198754;
  color: white;
  border-color: #198754;
  box-shadow: -4px 4px 15px rgba(25, 135, 84, 0.3);
  z-index: 2;
}

.chatbot-btn { position: fixed; bottom: 25px; right: 25px; width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; padding: 0; background: linear-gradient(135deg, var(--color-c-hover, #547792) 0%, var(--color-c-dark, #213448) 100%); border: none; z-index: 1045;}
.chatbot-btn:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 10px 25px rgba(33, 52, 72, 0.4) !important; }
@media (max-width: 768px) { .chatbot-btn { right: 15px; bottom: 15px; } }

.chat-body { overflow-y: auto !important; overflow-x: hidden; }

.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.hover-bg-light:hover { background-color: rgba(255,255,255,0.2) !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

.msg-bubble { max-width: 85%; padding: 10px 14px; font-size: 0.95rem; line-height: 1.5; word-wrap: break-word; }
.user-bubble { background-color: var(--color-c-hover, #547792); color: #fff; border-radius: 18px 18px 4px 18px; }
.bot-bubble { background-color: #ffffff; color: #212529; border: 1px solid rgba(0,0,0,0.05); border-radius: 18px 18px 18px 4px; }
html.dark .bot-bubble { background-color: #212529; color: #f8f9fa; border-color: rgba(255,255,255,0.05); }

.admin-rep-bubble { background-color: #d1e7dd !important; border-color: #badbcc !important; color: #0f5132 !important; border-radius: 18px 18px 18px 4px; }
html.dark .admin-rep-bubble { background-color: rgba(25, 135, 84, 0.2) !important; border-color: rgba(25, 135, 84, 0.4) !important; color: #a3cfbb !important; }

.system-bubble { background-color: rgba(255,193,7,0.15); color: #856404; font-size: 0.8rem; border-radius: 8px; margin: 0 auto; max-width: 90%; }
html.dark .system-bubble { color: #ffeeba; background-color: rgba(255,193,7,0.2); }

.typing-dot { width: 6px; height: 6px; background-color: #adb5bd; border-radius: 50%; animation: typingBounce 1.4s infinite ease-in-out both; }
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
@keyframes typingBounce { 0%, 80%, 100% { transform: scale(0); opacity: 0.5; } 40% { transform: scale(1); opacity: 1; } }

.animation-pulse { animation: pulse-ring 2s infinite; }
@keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }

.animation-pulse-fast { animation: pulse-ring-fast 1.5s infinite; }
@keyframes pulse-ring-fast { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.9); } 70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

.hover-scale:hover:not(:disabled) { transform: scale(1.1); }
.hover-transform:hover { transform: translateY(-2px); }
.hover-opacity:hover { opacity: 0.7; }
.focus-within-ring:focus-within { border-color: var(--color-c-hover, #547792) !important; box-shadow: 0 0 0 2px rgba(84, 119, 146, 0.2); }
.transition-all { transition: all 0.3s ease; }

.chat-slide-enter-active, .chat-slide-leave-active { transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
.chat-slide-enter-from, .chat-slide-leave-to { opacity: 0; transform: translateY(20px) scale(0.9); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>