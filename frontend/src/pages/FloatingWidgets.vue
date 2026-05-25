<template>
  <div class="floating-widgets-container z-index-highest">
    <transition name="slide-fade">
      <!-- Đã xóa class mb-3 vì chúng ta sẽ dùng flex gap -->
      <button v-show="showBackToTop" @click="scrollToTop"
        class="btn btn-light floating-btn back-to-top-btn shadow-sm border dark:border-gray-600 dark:bg-[#2b3035] dark:text-gray-300"
        title="Lên đầu trang">
        <i class="bi bi-chevron-up fs-5"></i>
      </button>
    </transition>

    <ZyroChatWidget />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ZyroChatWidget from '@/components/client/ZyroChatWidget.vue';

const showBackToTop = ref(false);

const handleScroll = () => {
  showBackToTop.value = window.scrollY > 400;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.floating-widgets-container {
  position: fixed;
  bottom: 25px;
  right: 25px;
  z-index: 1045; 
  display: flex;
  flex-direction: column;
  align-items: center; /* BẮT BUỘC: Ép tất cả widget nằm giữa trục dọc để không bị lệch */
  gap: 15px; /* Dùng gap để tạo khoảng cách đều đặn thay vì margin-bottom */
}

@media (max-width: 768px) {
  .floating-widgets-container {
    bottom: 15px;
    right: 15px;
  }
}

.floating-btn {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-to-top-btn {
  color: var(--color-c-hover, #547792);
}
.back-to-top-btn:hover {
  background-color: var(--color-c-hover, #547792) !important;
  color: #ffffff !important;
  transform: translateY(-5px);
}

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(20px) scale(0.8); opacity: 0; }

/* --- BẮT ĐẦU FIX LỆCH NÚT CHAT BẰNG VUE DEEP SELECTOR --- */

/* 1. Ép kích thước khung chứa nút chat bằng đúng 55px để giữ chỗ trong flexbox */
.floating-widgets-container :deep(.zyro-chat-container) {
  width: 55px;
  height: 55px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* 2. Ép nút chat (bên trong ZyroChatWidget) tuân thủ flexbox thay vì fixed trôi nổi */
.floating-widgets-container :deep(.chatbot-btn) {
  position: static !important;
  margin: 0 !important;
}

/* 3. Đảm bảo khi mở khung chat to lên, khung đó vẫn bám đúng góc màn hình */
.floating-widgets-container :deep(.chat-widget-wrapper) {
  position: fixed !important;
  bottom: 25px !important;
  right: 25px !important;
}

@media (max-width: 768px) {
  .floating-widgets-container :deep(.chat-widget-wrapper) {
    bottom: 15px !important;
    right: 15px !important;
  }
}
/* --- KẾT THÚC FIX --- */
</style>