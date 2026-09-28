<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    :width="size"
    :height="size"
    :class="['lucide-spinner', className]"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
  >
    <g class="spinner-group">
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(0 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(30 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(60 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(90 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(120 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(150 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(180 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(210 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(240 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(270 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(300 12 12)" />
      <line x1="12" y1="2" x2="12" y2="6" transform="rotate(330 12 12)" />
    </g>
  </svg>
</template>

<script setup>
defineProps({
  size: {
    type: [String, Number],
    default: 24
  },
  className: {
    type: String,
    default: ''
  }
});
</script>

<style scoped>
.spinner-group line {
  animation: spinner-fade 1s linear infinite;
}
.spinner-group line:nth-child(1) { animation-delay: -0.916s; }
.spinner-group line:nth-child(2) { animation-delay: -0.833s; }
.spinner-group line:nth-child(3) { animation-delay: -0.75s; }
.spinner-group line:nth-child(4) { animation-delay: -0.666s; }
.spinner-group line:nth-child(5) { animation-delay: -0.583s; }
.spinner-group line:nth-child(6) { animation-delay: -0.5s; }
.spinner-group line:nth-child(7) { animation-delay: -0.416s; }
.spinner-group line:nth-child(8) { animation-delay: -0.333s; }
.spinner-group line:nth-child(9) { animation-delay: -0.25s; }
.spinner-group line:nth-child(10) { animation-delay: -0.166s; }
.spinner-group line:nth-child(11) { animation-delay: -0.083s; }
.spinner-group line:nth-child(12) { animation-delay: 0s; }

@keyframes spinner-fade {
  0% { opacity: 1; }
  100% { opacity: 0.15; }
}
</style>
