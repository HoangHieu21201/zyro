<!-- File: frontend/src/App.vue -->
<template>
  <router-view></router-view>
</template>

<script setup>
// Nơi chứa logic chung cho toàn app (như init Auth, check Token...)
</script>

<style>
/* Nhúng trực tiếp Tailwind v4 */
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
    /* Sử dụng biến màu vừa khai báo ở trên */
    @apply bg-c-effect text-c-dark font-sans antialiased;
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
   (Áp dụng toàn cục, không cần đặt trong file Layout)
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

.logo-shimmer { 
  font-size: 3.5rem; 
  font-weight: 900; 
  letter-spacing: -1.5px; 
  background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); 
  background-size: 200% auto; 
  color: transparent; 
  -webkit-background-clip: text; 
  background-clip: text; 
  animation: shine 1.5s linear infinite; 
}
@keyframes shine { 
  to { background-position: 200% center; } 
}
</style>