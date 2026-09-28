<template>
  <div class="d-flex flex-column align-items-center justify-content-center gap-2">
    <select
      class="form-select form-select-sm border shadow-sm fw-semibold dark:bg-[#212529] dark:text-gray-200"
      style="width: 120px; font-size: 0.8rem; height: 32px;"
      :class="selectClass"
      :value="modelValue"
      @change="onChange($event.target.value)"
      :disabled="isUpdating || disabled"
    >
      <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>

    <div class="d-flex align-items-center justify-content-center w-100" style="min-height: 28px;">
      <LoadingSpinner v-if="isUpdating" class="text-urban" size="16" />
      <div v-else-if="isChanged" class="d-flex align-items-center justify-content-center gap-2 w-100">
        <button @click="$emit('save')" class="btn btn-success d-flex align-items-center justify-content-center shadow-sm px-2 py-1 flex-grow-1" style="font-size: 0.8rem; border-radius: 6px;" title="Xác nhận">
          <i class="bi bi-send-check-fill me-1"></i> Xác nhận
        </button>
        <button @click="onCancel" class="btn btn-light text-danger border dark:border-gray-600 d-flex align-items-center justify-content-center shadow-sm px-2 py-1" style="font-size: 0.8rem; border-radius: 6px;" title="Hủy">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';

const props = defineProps({
  modelValue: { type: String, required: true },
  originalStatus: { type: String, required: true },
  isUpdating: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  options: {
    type: Array,
    default: () => [
      { value: 'active', label: 'Hiển thị' },
      { value: 'hidden', label: 'Đang ẩn' }
    ]
  },
  selectClass: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue', 'change', 'save', 'cancel']);

const isChanged = computed(() => props.modelValue !== props.originalStatus);

const onChange = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
};

const onCancel = () => {
  emit('update:modelValue', props.originalStatus);
  emit('cancel');
};
</script>
