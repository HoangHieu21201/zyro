import { defineStore } from 'pinia';
import api from '@/utils/axios';

export const useChatStore = defineStore('chat', {
  state: () => ({
    isOpen: false,
    isTyping: false,
    messages: [],
    conversationId: null,
    status: 'bot_handling',
    unreadCount: 0,
    sessionId: localStorage.getItem('chat_session_id') || null,
    isEchoConnected: false
  }),

  actions: {
    initSession() {
      if (!this.sessionId) {
        this.sessionId = 'guest_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        localStorage.setItem('chat_session_id', this.sessionId);
      }
    },

    clearSession() {
      localStorage.removeItem('chat_session_id');
      this.sessionId = null;
      this.messages = [];
      this.conversationId = null;
      this.status = 'bot_handling';
      if (window.Echo && this.isEchoConnected) {
        window.Echo.leave(`chat.conversation.${this.conversationId}`);
        this.isEchoConnected = false;
      }
      this.initSession();
      this.fetchHistory();
    },

    async clearHistory() {
      try {
        await api.delete('/client/chat/clear', { data: { session_id: this.sessionId } });
        this.messages = [];
        this.conversationId = null;
        this.status = 'bot_handling';
        if (window.Echo && this.isEchoConnected) {
          window.Echo.leave(`chat.conversation.${this.conversationId}`);
          this.isEchoConnected = false;
        }
      } catch (err) {
        console.error("Lỗi xóa chat:", err);
      }
    },

    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) this.unreadCount = 0; 
    },

    async fetchHistory() {
      try {
        const res = await api.get('/client/chat/history', {
          params: { session_id: this.sessionId }
        });
        if (res.data.success) {
          this.messages = res.data.data;
          this.conversationId = res.data.conversation_id;
          this.status = res.data.status; 
          this.connectEcho();
        }
      } catch (err) {
        console.error("Lỗi lấy lịch sử chat:", err);
      }
    },

    async requestHumanSupport() {
      this.messages.push({ id: Date.now(), sender_type: 'system', content: 'Đang kết nối với nhân viên hỗ trợ...' });
      try {
         const res = await api.post('/client/chat/request-human', { session_id: this.sessionId });
         if(res.data.success) {
            this.status = 'admin_handling'; 
            this.messages.push({ id: Date.now()+1, sender_type: 'system', content: '<i class="bi bi-shield-check text-success"></i> Đã kết nối thành công! Bạn có thể nhắn tin trực tiếp với nhân viên ZYRO ngay bây giờ.' });
            
            if (!this.isEchoConnected) {
                this.fetchHistory();
            }
         }
      } catch(e) {
         this.messages.push({ id: Date.now()+2, sender_type: 'system', content: '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Hệ thống đang bận, không thể kết nối tới nhân viên lúc này.' });
      }
    },

    async endHumanSupport() {
      this.messages.push({ id: Date.now(), sender_type: 'system', content: 'Đang ngắt kết nối với nhân viên...' });
      try {
         const res = await api.post('/client/chat/end-human', { session_id: this.sessionId });
         if(res.data.success) {
             this.status = 'bot_handling'; 
             this.messages.push({ id: Date.now()+1, sender_type: 'system', content: '<i class="bi bi-robot text-urban"></i> Đã kết thúc trò chuyện với nhân viên. Trợ lý ảo ZYRO đã quay lại!' });
         }
      } catch(e) {
         this.status = 'bot_handling'; 
      }
    },

    async sendMessage(text) {
      if (!text.trim()) return;

      const tempId = Date.now();
      this.messages.push({ id: tempId, sender_type: 'user', content: text });
      
      if (this.status === 'bot_handling') {
         this.isTyping = true;
      }

      try {
        const endpoint = this.status === 'admin_handling' 
                         ? '/client/chat/send' 
                         : '/client/chatbot/chat';

        const res = await api.post(endpoint, {
          message: text,
          session_id: this.sessionId
        });

        if (res.data.success && res.data.data && res.data.data.conversation_id) {
           if (!this.conversationId) {
               this.conversationId = res.data.data.conversation_id;
               this.connectEcho();
           }
        }
        
        if (this.status === 'bot_handling' && res.data.success && res.data.data.sender_type === 'bot') {
            const exists = this.messages.some(m => m.id === res.data.data.id);
            if (!exists) {
                this.messages.push(res.data.data);
            }
        }
      } catch (error) {
        // ĐÃ XÓA HOÀN TOÀN LOGIC TỰ ĐỘNG CHUYỂN SANG ADMIN Ở ĐÂY
        // Nếu Bot lỗi, báo thẳng lỗi trên màn hình Bot.
        this.messages.push({ id: Date.now()+4, sender_type: 'system', content: '<span class="text-danger">AI đang bảo trì hoặc mất kết nối. Vui lòng thử lại sau.</span>' });
      } finally {
        this.isTyping = false; 
      }
    },

    connectEcho() {
      if (this.isEchoConnected || !this.conversationId || !window.Echo) return;

      window.Echo.channel(`chat.conversation.${this.conversationId}`)
        .listen('.MessageSentEvent', (e) => {
          if (e.sender_type === 'user') return; 
          if (e.sender_type === 'bot' && this.status === 'bot_handling') return;

          const exists = this.messages.some(m => m.id === e.id);
          if (!exists) {
              this.messages.push(e);
              this.isTyping = false;
              if (!this.isOpen) {
                  this.unreadCount++;
              }
          }
        });
      this.isEchoConnected = true;
    }
  }
});