<template>
  <div class="dropdown custom-admin-select position-relative" ref="dropdownRef">
    <button
      class="btn btn-sm d-flex justify-content-between align-items-center bg-transparent border-0 shadow-none fw-bold text-secondary dark:text-gray-300 px-0"
      type="button"
      @click="toggleDropdown"
      :class="{ 'show': isOpen }"
      style="min-width: 150px; width: 100%;"
    >
      <span class="text-truncate me-2">{{ selectedLabel }}</span>
      <i class="bi bi-chevron-down small transition-transform" :class="{ 'rotate-180': isOpen }"></i>
    </button>
    
    <ul class="dropdown-menu shadow border-0 rounded-4 mt-2 py-2 dark:bg-[#2b3035] position-absolute z-3" 
        :class="{ 'show': isOpen }" 
        style="min-width: 220px; max-height: 350px; overflow-y: auto; right: auto; left: 0;">
      <template v-for="(item, index) in options" :key="index">
        
        <!-- NẾU LÀ OPTGROUP -->
        <template v-if="item.options">
          <li v-if="index > 0"><hr class="dropdown-divider dark:border-gray-600 my-2"></li>
          <li><h6 class="dropdown-header fw-bold text-uppercase small" style="color: #547792; opacity: 0.9;">{{ item.label }}</h6></li>
          <li v-for="subItem in item.options" :key="subItem.value">
            <a class="dropdown-item d-flex align-items-center py-2 px-3 cursor-pointer dark:text-gray-200 transition-colors" 
               :class="{ 'active-item fw-bold': modelValue === subItem.value }"
               @click.prevent="selectOption(subItem)">
              <i class="bi bi-check2 me-2" :class="modelValue === subItem.value ? 'text-urban opacity-100' : 'opacity-0'"></i>
              {{ subItem.label }}
            </a>
          </li>
        </template>
        
        <!-- NẾU LÀ OPTION THƯỜNG -->
        <template v-else>
          <li>
            <a class="dropdown-item d-flex align-items-center py-2 px-3 cursor-pointer dark:text-gray-200 transition-colors"
               :class="{ 'active-item fw-bold': modelValue === item.value }"
               @click.prevent="selectOption(item)">
              <i class="bi bi-check2 me-2" :class="modelValue === item.value ? 'text-urban opacity-100' : 'opacity-0'"></i>
              {{ item.label }}
            </a>
          </li>
        </template>
        
      </template>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], required: true },
  options: { type: Array, required: true },
});
const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const flatOptions = computed(() => {
  let flat = [];
  props.options.forEach(opt => {
    if (opt.options) flat = flat.concat(opt.options);
    else flat.push(opt);
  });
  return flat;
});

const selectedLabel = computed(() => {
  const found = flatOptions.value.find(o => o.value === props.modelValue);
  return found ? found.label : 'Chọn...';
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectOption = (option) => {
  emit('update:modelValue', option.value);
  emit('change', option.value);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
.transition-colors { transition: background-color 0.15s ease, color 0.15s ease; }
.transition-transform { transition: transform 0.2s ease; }
.rotate-180 { transform: rotate(180deg); }

.dropdown-item:active {
  background-color: transparent;
}
.dropdown-item:hover {
  background-color: #f8f9fa;
}
.dark .dropdown-item:hover {
  background-color: #1a2533 !important;
}

.active-item {
  background-color: rgba(84, 119, 146, 0.1) !important;
  color: #547792 !important;
}
.dark .active-item {
  background-color: rgba(84, 119, 146, 0.2) !important;
  color: #a9c6de !important;
}

/* Custom Scrollbar for dropdown */
.dropdown-menu::-webkit-scrollbar {
  width: 6px;
}
.dropdown-menu::-webkit-scrollbar-track {
  background: transparent;
}
.dropdown-menu::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}
.dark .dropdown-menu::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
}
</style>
