<template>
  <div class="floating-widgets-container">

    <!-- NÚT BACK TO TOP (Chỉ hiện khi cuộn xuống) -->
    <transition name="slide-fade">
      <button v-show="showBackToTop" @click="scrollToTop"
        class="btn btn-light floating-btn back-to-top-btn shadow-sm mb-3 border dark:border-gray-600 dark:bg-[#2b3035] dark:text-gray-300"
        title="Lên đầu trang">
        <i class="bi bi-chevron-up"></i>
      </button>
    </transition>

    <!-- NÚT CHATBOT -->
    <!-- <button class="btn floating-btn chatbot-btn shadow-lg position-relative" @click="toggleChat"
      title="Chat với nhân viên ZYRO">
      <i class="bi bi-chat-dots-fill fs-4 text-white"></i>

      <span
        class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light dark:border-gray-800 rounded-circle animation-pulse">
        <span class="visually-hidden">Có tin nhắn mới</span>
      </span>
    </button> -->

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const showBackToTop = ref(false);

const handleScroll = () => {
  showBackToTop.value = window.scrollY > 400;
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

const toggleChat = () => {
  ZyroSwal.toastSuccess('Tính năng đang được ZYRO cập nhật!');
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
  bottom: 30px;
  right: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 1045;
}

@media (max-width: 768px) {
  .floating-widgets-container {
    bottom: 20px;
    right: 20px;
  }
}

.floating-btn {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  padding: 0;
}

.back-to-top-btn {
  color: var(--color-c-hover, #547792);
}

.back-to-top-btn:hover {
  background-color: var(--color-c-hover, #547792) !important;
  color: #ffffff !important;
  transform: translateY(-5px);
}

/* Nút Chatbot với Gradient nổi bật */
.chatbot-btn {
  background: linear-gradient(135deg, var(--color-c-hover, #547792) 0%, var(--color-c-dark, #213448) 100%);
  border: none;
}

.chatbot-btn:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 25px rgba(33, 52, 72, 0.4) !important;
}

/* HIỆU ỨNG ẨN HIỆN NÚT CUỘN LÊN */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}

/* HIỆU ỨNG NHỊP ĐẬP (PULSE) CỦA CHẤM ĐỎ */
.animation-pulse {
  animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
  0% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
  }
}
</style>