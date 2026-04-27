<template>
  <!-- MÀN HÌNH CHỜ ĐIỆN ẢNH (SPLASH SCREEN) -->
  <transition name="splash-fade">
    <div v-if="showSplash" class="global-splash d-flex align-items-center justify-content-center">
      
      <!-- Hiệu ứng Sóng Nước (Liquid Wave Ribbon) -->
      <svg viewBox="0 0 400 120" class="zyro-svg-logo">
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="zyro-svg-text wave-1">zyro.</text>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="zyro-svg-text wave-2">zyro.</text>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="zyro-svg-text wave-3">zyro.</text>
      </svg>
      
    </div>
  </transition>

  <!-- NỘI DUNG WEB CHÍNH -->
  <router-view></router-view>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const showSplash = ref(true);
const router = useRouter();
const route = useRoute();

onMounted(() => {
  // Lắng nghe Vue Router: Khi nào tải xong các file giao diện thì bắt đầu đếm giờ tắt Splash
  router.isReady().then(() => {
    // Để thời gian 2.5 giây cho hiệu ứng sóng nước chảy mượt mà trước khi fade out
    setTimeout(() => {
      showSplash.value = false;
    }, 2500); 
  });
});

// GLOBAL AUTO SCROLL TO TOP
// Lắng nghe mọi sự thay đổi của đường dẫn để cuộn lên đầu trang
watch(() => route.path, () => {
  nextTick(() => {
    setTimeout(() => {
      window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'smooth'
      });
    }, 100);
  });
});
</script>

<style>
@import "tailwindcss";

@theme {
  --color-c-light: rgb(148, 180, 193);
  --color-c-hover: rgb(84, 119, 146);
  --color-c-dark: rgb(33, 52, 72);
  --color-c-effect: rgb(235, 241, 245);
}

@layer base {
  body {
    @apply bg-c-effect text-c-dark font-sans antialiased;
  }
}

/* =======================================================
   HIỆU ỨNG SPLASH SCREEN (SÓNG NƯỚC - LIQUID WAVE)
======================================================== */
.global-splash {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background-color: var(--color-c-effect);
}
html.dark .global-splash { 
  background-color: #121416; 
}

/* Kích thước SVG */
.zyro-svg-logo {
  width: 250px;
  overflow: visible;
}

/* Base style cho cả 3 layer sóng */
.zyro-svg-text {
  font-family: 'Georgia', serif;
  font-style: italic;
  font-weight: 900;
  font-size: 6rem;
  fill: transparent !important;
  stroke-width: 2.5px;
  
  stroke-dasharray: 120 120; 
  animation: liquidWave 3s infinite linear;
}

/* Phối màu và Delay cho từng layer để tạo độ sâu (Ripple effect) */
.wave-1 {
  stroke: var(--color-c-light);
  opacity: 0.5;
  animation-delay: -1s;
}
.wave-2 {
  stroke: var(--color-c-hover);
  opacity: 0.8;
  animation-delay: -2s;
}
.wave-3 {
  stroke: var(--color-c-dark);
  opacity: 1;
  animation-delay: 0s;
}

/* Tùy chỉnh màu sóng cho Dark Mode */
html.dark .wave-1 { stroke: rgba(255, 255, 255, 0.3); }
html.dark .wave-2 { stroke: rgba(255, 255, 255, 0.6); }
html.dark .wave-3 { stroke: rgba(255, 255, 255, 1); }

/* Chuyển động chạy liên tục vô tận */
@keyframes liquidWave {
  100% { stroke-dashoffset: -240; } 
}

.splash-fade-leave-active {
  transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1), transform 1s cubic-bezier(0.4, 0, 0.2, 1);
}
.splash-fade-leave-to {
  opacity: 0;
  transform: scale(1.15); 
  pointer-events: none;
}

.zyro-container {
  width: 100%;
  max-width: 1310px;
  margin: 0 auto;
  padding-left: 20px;
  padding-right: 20px;
}

@media (min-width: 1400px) {
  .zyro-container {
    padding-left: 0;
    padding-right: 0;
  }
}
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background-color: transparent;
}
::-webkit-scrollbar-thumb {
  @apply bg-c-light rounded-full;
}
::-webkit-scrollbar-thumb:hover {
  @apply bg-c-hover;
}

[data-bs-theme="dark"] body {
    background-color: #121416 !important;
    color: #e0e0e0 !important;
}

[data-bs-theme="dark"] .bg-white,
[data-bs-theme="dark"] .bg-light,
[data-bs-theme="dark"] .card {
    background-color: #1e2125 !important;
    color: #e0e0e0 !important;
}

[data-bs-theme="dark"] .text-dark {
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .text-muted,
[data-bs-theme="dark"] .text-black-50 {
    color: #adb5bd !important;
}

[data-bs-theme="dark"] .border,
[data-bs-theme="dark"] .border-bottom,
[data-bs-theme="dark"] .border-top,
[data-bs-theme="dark"] .border-light-subtle {
    border-color: #2b3035 !important;
}

[data-bs-theme="dark"] .form-control,
[data-bs-theme="dark"] .form-select {
    background-color: #121416 !important;
    border-color: #373b3e !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .form-control:focus,
[data-bs-theme="dark"] .form-select:focus {
    background-color: #1e2125 !important;
    border-color: #009981 !important;
}

[data-bs-theme="dark"] .btn-light {
    background-color: #2b3035 !important;
    border-color: #373b3e !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .btn-light:hover {
    background-color: #343a40 !important;
    border-color: #495057 !important;
    color: #ffffff !important;
}

[data-bs-theme="dark"] .toggle-sidebar-btn {
    background-color: #121416 !important;
    border-color: #373b3e !important;
    color: #e0e0e0 !important;
}
[data-bs-theme="dark"] .toggle-sidebar-btn:hover {
    background-color: #009981 !important;
    border-color: #009981 !important;
    color: #fff !important;
}

[data-bs-theme="dark"] .table {
    --bs-table-bg: transparent;
    --bs-table-color: #e0e0e0;
    --bs-table-border-color: #373b3e;
    --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
    --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
}

[data-bs-theme="dark"] .modal-content {
    background-color: #1e2125 !important;
    border-color: #373b3e !important;
}

[data-bs-theme="dark"] .modal-header,
[data-bs-theme="dark"] .modal-footer {
    border-color: #373b3e !important;
}

.logo-shimmer { 
  font-weight: 900; 
  background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); 
  background-size: 200% auto; 
  color: transparent; 
  -webkit-background-clip: text; 
  background-clip: text; 
}
html.dark .logo-shimmer {
  background: linear-gradient(120deg, #f8f9fa 30%, var(--color-c-light) 50%, #f8f9fa 70%);
  background-size: 200% auto; 
  -webkit-background-clip: text; 
  background-clip: text; 
}
@keyframes shine { 
  to { background-position: 200% center; } 
}
</style>