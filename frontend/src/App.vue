<template>
  <!-- MÀN HÌNH CHỜ ĐIỆN ẢNH (SPLASH SCREEN) -->
  <transition name="splash-fade">
    <div v-if="showSplash" class="global-splash d-flex align-items-center justify-content-center">
      
      <!-- Hiệu ứng dải băng vẽ chữ zyro. -->
      <svg viewBox="0 0 400 120" class="zyro-svg-logo">
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="zyro-svg-text">zyro.</text>
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
    // Để thời gian 2.2 giây cho hiệu ứng dải băng vẽ xong và đổ màu
    setTimeout(() => {
      showSplash.value = false;
    }, 2200); 
  });
});

// ========================================================
// ĐÃ FIX: GLOBAL AUTO SCROLL TO TOP
// Lắng nghe mọi sự thay đổi của đường dẫn để cuộn lên đầu trang
// ========================================================
watch(() => route.path, () => {
  nextTick(() => {
    // Đợi 100ms để DOM của component mới kịp render và có chiều cao thực tế
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

/* Định nghĩa bộ màu Xanh Urban bằng cú pháp v4 */
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
   HIỆU ỨNG SPLASH SCREEN (ĐIỆN ẢNH)
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

/* Chữ zyro dải băng uốn lượn */
.zyro-svg-text {
  font-family: 'Georgia', serif;
  font-style: italic;
  font-weight: 900;
  font-size: 6rem;
  fill: transparent;
  stroke: var(--color-c-hover);
  stroke-width: 1.5px;
  /* Thuộc tính dasharray và dashoffset tạo nên viền chạy (Ribbon) */
  stroke-dasharray: 600;
  stroke-dashoffset: 600;
  /* Vẽ viền trong 1.5s, sau đó đổ màu đầy đặn trong 0.8s */
  animation: drawRibbon 1.5s cubic-bezier(0.25, 0.1, 0.25, 1) forwards,
             fillColor 0.8s 1.2s ease forwards;
}
html.dark .zyro-svg-text {
  stroke: #f8f9fa;
  animation: drawRibbon 1.5s cubic-bezier(0.25, 0.1, 0.25, 1) forwards,
             fillColorDark 0.8s 1.2s ease forwards;
}

@keyframes drawRibbon {
  to { stroke-dashoffset: 0; }
}
@keyframes fillColor {
  from { fill: transparent; }
  to { fill: var(--color-c-dark); stroke: transparent; }
}
@keyframes fillColorDark {
  from { fill: transparent; }
  to { fill: #f8f9fa; stroke: transparent; text-shadow: 0 0 15px rgba(255,255,255,0.2); }
}

/* Hiệu ứng Fade-out mượt mà, phóng to nhẹ để mở không gian */
.splash-fade-leave-active {
  transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1), transform 1s cubic-bezier(0.4, 0, 0.2, 1);
}
.splash-fade-leave-to {
  opacity: 0;
  transform: scale(1.1); /* Phóng to 10% khi mờ đi tạo cảm giác "mở rèm" */
  pointer-events: none;
}

/* =======================================================
   ZYRO GLOBAL CONTAINER (TỶ LỆ VÀNG 1310px)
======================================================== */
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

/* =======================================================
   TÙY CHỈNH THANH CUỘN (SCROLLBAR) TOÀN HỆ THỐNG
======================================================== */
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

/* =======================================================
   GLOBAL CSS: CHUẨN HÓA GIAO DIỆN DARK MODE CHO BOOTSTRAP
======================================================== */
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

/* BỘ MÀU SHIMMER CHUẨN ZYRO */
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