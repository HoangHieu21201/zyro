<template>
  <div class="bouncing-dots-wrapper" :style="{ '--dot-color': color, '--dot-size': size + 'px' }">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
  </div>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    default: '#ffffff'
  },
  size: {
    type: Number,
    default: 8
  }
});
</script>

<style scoped>
.bouncing-dots-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: calc(var(--dot-size) / 1.5);
}

.dot {
  width: var(--dot-size);
  height: var(--dot-size);
  background-color: var(--dot-color);
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}
</style>