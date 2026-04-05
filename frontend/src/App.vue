<template>
  <router-view></router-view>
</template>

<script setup>
// Nơi chứa logic chung cho toàn app
</script>

<style>
/* Nhúng trực tiếp Tailwind v4 */
@import "tailwindcss";

/* Định nghĩa bộ màu Xanh Urban bằng cú pháp v4 */
@theme {
  --color-c-light: #94B4C1;
  --color-c-hover: #547792;
  --color-c-dark: #213448;
  --color-c-effect: #EBF1F5;
}

@layer base {
  body {
    /* Sử dụng biến màu vừa khai báo ở trên */
    @apply bg-c-effect text-c-dark font-sans antialiased;
  }
}

/* Tùy chỉnh thanh cuộn (Scrollbar) */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-thumb {
  @apply bg-c-light rounded;
}
::-webkit-scrollbar-thumb:hover {
  @apply bg-c-hover;
}
</style>