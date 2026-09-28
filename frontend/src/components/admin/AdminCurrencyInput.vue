<template>
  <div class="input-group">
    <div class="form-floating flex-grow-1">
      <input 
        type="text" 
        class="form-control fw-bold text-dark dark:text-gray-200" 
        :class="inputClass"
        :id="id" 
        :placeholder="label"
        :value="displayValue"
        @input="onInput"
        @blur="onBlur"
        @focus="onFocus"
        :disabled="disabled"
      >
      <label :for="id" class="text-muted fw-semibold" style="font-size: 0.85rem;">{{ label }}</label>
    </div>
    <span class="input-group-text fw-semibold bg-light text-muted" v-if="suffix">{{ suffix }}</span>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: [Number, String], default: '' },
  label: { type: String, default: '' },
  suffix: { type: String, default: '' },
  id: { type: String, required: true },
  inputClass: { type: String, default: '' },
  disabled: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

const displayValue = ref('');

// Parse a raw string back to integer
const parseNumber = (val) => {
  if (val === null || val === undefined || val === '') return null;
  const numStr = String(val).replace(/\D/g, ''); // strip everything except digits
  if (!numStr) return null;
  return parseInt(numStr, 10);
};

// Format an integer to VNĐ style string (e.g., 1000 -> 1.000)
const formatNumber = (val) => {
  if (val === null || val === undefined || val === '') return '';
  // Convert incoming DB values like '10.00' to float first, then integer
  const num = parseInt(parseFloat(val), 10);
  if (isNaN(num)) return '';
  return new Intl.NumberFormat('vi-VN').format(num);
};

// Sync incoming v-model
watch(() => props.modelValue, (newVal) => {
  // If the actual underlying number is different from what we are displaying, update display
  const currentPure = parseNumber(displayValue.value);
  const newPure = parseNumber(newVal ? parseInt(parseFloat(newVal), 10) : null);
  
  if (currentPure !== newPure) {
    displayValue.value = formatNumber(newVal);
  }
}, { immediate: true });

const onInput = (event) => {
  const raw = event.target.value;
  const pureNum = parseNumber(raw);
  
  if (pureNum === null) {
    displayValue.value = '';
    emit('update:modelValue', '');
  } else {
    const formatted = formatNumber(pureNum);
    displayValue.value = formatted;
    
    // Forcing cursor update is complex in Vue without a directive,
    // but Vue's reactivity usually handles it mostly fine for simple dot insertions.
    // If we update event.target.value directly, it helps.
    event.target.value = formatted;
    
    emit('update:modelValue', pureNum);
  }
};

const onBlur = () => {
  displayValue.value = formatNumber(props.modelValue);
};

const onFocus = (event) => {
  // Select all on focus for quick replacing
  setTimeout(() => event.target.select(), 10);
};
</script>

<style scoped>
.form-floating > .form-control:focus ~ label {
  color: var(--color-c-hover, #547792);
}
.form-control:focus {
  border-color: var(--color-c-hover, #547792);
  box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.25);
}
.input-group > .form-floating:not(:last-child) > .form-control {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
</style>
