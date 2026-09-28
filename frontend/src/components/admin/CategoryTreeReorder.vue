<template>
  <div class="category-tree-reorder">
    <div class="d-flex justify-content-end align-items-center mb-4 gap-2">
      <button class="btn btn-light border dark:border-gray-600 dark:bg-[#2b3035] dark:text-white px-4 fw-bold shadow-sm" @click="$emit('cancel')">Hủy</button>
      <button class="btn text-white px-4 fw-bold shadow-sm d-flex align-items-center" style="background-color: #547792;" @click="handleSave" :disabled="isSaving">
        <LoadingSpinner v-if="isSaving" class="me-2" size="16" />
        <i class="bi bi-floppy-fill me-1" v-else></i> LƯU CẤU TRÚC
      </button>
    </div>
    
    <div class="tree-container">
      <!-- ROOTS DRAGGABLE -->
      <draggable
        v-model="treeData"
        group="roots"
        item-key="id"
        handle=".drag-handle"
        ghost-class="ghost-root"
        animation="200"
      >
        <template #item="{ element, index }">
          <div class="card border mb-3 rounded-4 shadow-sm dark:bg-[#212529] dark:border-gray-700">
            <div class="card-header bg-white dark:bg-[#212529] d-flex align-items-center justify-content-between border-bottom-0 rounded-4 p-3 flex-wrap gap-3">
              
              <div class="d-flex align-items-center flex-grow-1">
                <i class="bi bi-grip-vertical text-warning fs-4 drag-handle cursor-move me-2"></i>
                <div class="d-flex align-items-center">
                  <img :src="getImageUrl(element.thumbnail)" class="rounded-3 object-fit-cover me-3 border" style="width: 50px; height: 50px;">
                  <div>
                    <h6 class="fw-bold mb-0 dark:text-white">{{ element.name }}</h6>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1 border border-secondary border-opacity-25">Danh mục Gốc</span>
                  </div>
                </div>
              </div>

              <div class="d-flex align-items-center gap-2">
                <!-- Move Buttons for Root -->
                <div class="btn-group shadow-sm me-2">
                  <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveToTop(treeData, index)" :disabled="index === 0" title="Lên đầu"><i class="bi bi-chevron-bar-up"></i></button>
                  <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveUp(treeData, index)" :disabled="index === 0" title="Lên trên"><i class="bi bi-chevron-up"></i></button>
                  <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveDown(treeData, index)" :disabled="index === treeData.length - 1" title="Xuống dưới"><i class="bi bi-chevron-down"></i></button>
                  <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveToBottom(treeData, index)" :disabled="index === treeData.length - 1" title="Xuống cuối"><i class="bi bi-chevron-bar-down"></i></button>
                </div>

                <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 d-flex align-items-center shadow-sm" style="min-width: 100px;" @click="element.expanded = !element.expanded">
                  <span class="fw-bold me-2">{{ element.children.length }} mục con</span>
                  <i class="bi" :class="element.expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
              </div>

            </div>

            <!-- CHILDREN DRAGGABLE -->
            <div class="card-body bg-light dark:bg-[#1a2533] rounded-bottom-4 border-top dark:border-gray-700 p-3" v-show="element.expanded && element.children.length > 0">
              <draggable
                v-model="element.children"
                :group="'children-' + element.id"
                item-key="id"
                handle=".drag-handle-child"
                ghost-class="ghost-child"
                animation="200"
              >
                <template #item="{ element: child, index: childIndex }">
                  <div class="d-flex align-items-center justify-content-between bg-white dark:bg-[#212529] border dark:border-gray-600 rounded-3 p-2 mb-2 shadow-sm">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-grip-vertical text-muted fs-5 drag-handle-child cursor-move me-2"></i>
                      <img :src="getImageUrl(child.thumbnail)" class="rounded-2 object-fit-cover me-3 border" style="width: 35px; height: 35px;">
                      <div class="fw-semibold dark:text-gray-200">{{ child.name }}</div>
                    </div>
                    
                    <!-- Move Buttons for Child -->
                    <div class="btn-group shadow-sm">
                      <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveToTop(element.children, childIndex)" :disabled="childIndex === 0" title="Lên đầu"><i class="bi bi-chevron-bar-up"></i></button>
                      <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveUp(element.children, childIndex)" :disabled="childIndex === 0" title="Lên trên"><i class="bi bi-chevron-up"></i></button>
                      <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveDown(element.children, childIndex)" :disabled="childIndex === element.children.length - 1" title="Xuống dưới"><i class="bi bi-chevron-down"></i></button>
                      <button class="btn btn-sm btn-light border dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300" @click="moveToBottom(element.children, childIndex)" :disabled="childIndex === element.children.length - 1" title="Xuống cuối"><i class="bi bi-chevron-bar-down"></i></button>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const props = defineProps({
  categories: { type: Array, required: true },
  isSaving: { type: Boolean, default: false }
});

const emit = defineEmits(['save', 'cancel']);

const treeData = ref([]);

const getImageUrl = (path) => path ? `${import.meta.env.VITE_STORAGE_URL}${path}` : defaultImage;

// Build nested tree from flat list
const buildTree = (flatList) => {
  const rootNodes = flatList.filter(c => !c.parent_id).sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
  return rootNodes.map(root => {
    const children = flatList
      .filter(c => c.parent_id === root.id)
      .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    return { ...root, expanded: true, children: [...children] };
  });
};

watch(() => props.categories, (newVal) => {
  treeData.value = buildTree(newVal);
}, { immediate: true, deep: true });

// Move Handlers
const moveToTop = (list, index) => {
  if (index > 0) {
    const item = list[index];
    list.splice(index, 1);
    list.unshift(item);
  }
};

const moveUp = (list, index) => {
  if (index > 0) {
    const item = list[index];
    list.splice(index, 1);
    list.splice(index - 1, 0, item);
  }
};

const moveDown = (list, index) => {
  if (index < list.length - 1) {
    const item = list[index];
    list.splice(index, 1);
    list.splice(index + 1, 0, item);
  }
};

const moveToBottom = (list, index) => {
  if (index < list.length - 1) {
    const item = list[index];
    list.splice(index, 1);
    list.push(item);
  }
};

const handleSave = () => {
  const flatPayload = [];
  treeData.value.forEach((root, rootIndex) => {
    flatPayload.push({ id: root.id, sort_order: rootIndex + 1 });
    root.children.forEach((child, childIndex) => {
      flatPayload.push({ id: child.id, sort_order: childIndex + 1 });
    });
  });
  emit('save', flatPayload);
};
</script>

<style scoped>
.cursor-move { cursor: move; }
.ghost-root { opacity: 0.4; background: #e9ecef; }
.ghost-child { opacity: 0.4; background: #f8f9fa; }
</style>
