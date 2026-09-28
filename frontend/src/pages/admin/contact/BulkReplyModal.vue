<template>
  <div class="modal fade glass-modal" id="bulkReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533] overflow-hidden bg-white">
        
        <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3 p-md-4">
          <h5 class="fw-bold text-dark dark:text-white mb-0 font-sans-vn d-flex align-items-center">
            <div class="bg-urban text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-reply-all-fill fs-5"></i>
            </div>
            Trả Lời Hàng Loạt ({{ contactIds.length }} liên hệ)
          </h5>
          <button type="button" class="btn-close dark:filter-invert" @click="closeModal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body p-4 bg-light dark:bg-[#121416]">
          <form @submit.prevent="submitBulkReply" class="h-100 d-flex flex-column">
             <div class="alert alert-info border-info border-opacity-25 bg-info bg-opacity-10 d-flex gap-3 align-items-center rounded-3 p-3 mb-4">
                 <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                 <div>
                     <div class="fw-bold text-info">Phản hồi hàng loạt</div>
                     <span class="small text-dark dark:text-gray-300">Nội dung soạn thảo bên dưới sẽ được gửi tự động qua Email cho tất cả {{ contactIds.length }} khách hàng đã chọn.</span>
                 </div>
             </div>

             <h6 class="fw-bold text-urban text-uppercase mb-3 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-pencil-square me-2"></i>Soạn Thảo Email Phản Hồi</h6>
             
             <!-- QUICK REPLY TEMPLATES -->
             <div class="mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Chèn mẫu thông dụng</label>
                <div class="d-flex gap-2 flex-wrap">
                   <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('greeting')">
                      <i class="bi bi-hand-thumbs-up me-1"></i> Chào hỏi chung
                   </button>
                   <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('apology')">
                      <i class="bi bi-emoji-frown me-1"></i> Cáo lỗi hệ thống
                   </button>
                   <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('thanks')">
                      <i class="bi bi-heart me-1"></i> Lời cảm ơn
                   </button>
                </div>
             </div>

             <!-- EDITOR TOOLBAR -->
             <div class="editor-toolbar bg-light dark:bg-[#212529] border dark:border-gray-600 rounded-top-3 p-2 d-flex gap-2 flex-wrap shadow-sm align-items-center">
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="formatDoc('bold')" title="In đậm"><i class="bi bi-type-bold"></i></button>
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="formatDoc('italic')" title="In nghiêng"><i class="bi bi-type-italic"></i></button>
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="formatDoc('underline')" title="Gạch chân"><i class="bi bi-type-underline"></i></button>
                <div class="vr mx-1 dark:bg-gray-600"></div>
                
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="formatDoc('justifyLeft')" title="Căn trái"><i class="bi bi-text-left"></i></button>
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="formatDoc('justifyCenter')" title="Căn giữa"><i class="bi bi-text-center"></i></button>
                <div class="vr mx-1 dark:bg-gray-600"></div>
                <button type="button" class="btn btn-sm btn-light border-0 dark:bg-transparent dark:text-gray-300 hover-bg-gray" @click="addLink" title="Chèn Link"><i class="bi bi-link-45deg"></i></button>
             </div>

             <!-- CONTENT EDITABLE AREA -->
             <div class="editor-content form-control rounded-0 rounded-bottom-3 shadow-none bg-white dark:bg-[#121416] dark:text-white border-top-0 border-secondary-subtle dark:border-gray-600 flex-grow-1 custom-scrollbar-y p-3" 
                  contenteditable="true" 
                  ref="editorRef"
                  @input="syncEditorContent"
                  @blur="syncEditorContent"
                  placeholder="Soạn nội dung phản hồi chung cho tất cả các khách hàng này..."
                  style="min-height: 250px; outline: none;">
             </div>

             <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="small font-monospace fw-bold" :class="isReplyValid ? 'text-success' : 'text-danger'">
                   {{ rawTextLength }} / 5000 ký tự <span class="d-none d-sm-inline">(Tối thiểu 10)</span>
                </span>
                <div class="d-flex gap-2">
                   <button type="button" class="btn btn-light dark:bg-[#2b3035] border dark:border-gray-600 rounded-pill px-4 fw-bold shadow-sm hover-bg-effect font-sans-vn" @click="closeModal">Hủy</button>
                   
                   <button type="submit" class="btn btn-urban rounded-pill px-5 fw-bold transition-all shadow-sm hover-transform font-sans-vn" :disabled="isReplying || !isReplyValid">
                      <LoadingDots v-if="isReplying" color="#ffffff" :size="6" class="me-2" />
                      <span v-if="!isReplying">Gửi Hàng Loạt <i class="bi bi-send-check ms-1"></i></span>
                   </button>
                </div>
             </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import LoadingDots from '@/components/admin/LoadingDots.vue';

const props = defineProps({
    contactIds: {
        type: Array,
        required: true,
        default: () => []
    }
});

const emit = defineEmits(['refresh']);

const editorRef = ref(null);
let modalInstance = null;

const replyHtmlContent = ref('');
const rawTextLength = ref(0);
const isReplying = ref(false);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const openModal = async () => {
  replyHtmlContent.value = '';
  rawTextLength.value = 0;
  
  if (!modalInstance) {
    modalInstance = new window.bootstrap.Modal(document.getElementById('bulkReplyModal'));
  }
  modalInstance.show();
  
  await nextTick();
  if (editorRef.value) {
     editorRef.value.innerHTML = '';
     editorRef.value.focus();
  }
};

defineExpose({ openModal });

const closeModal = () => {
  if (modalInstance) modalInstance.hide();
};

const formatDoc = (cmd, value = null) => {
  if (editorRef.value) {
      document.execCommand(cmd, false, value);
      editorRef.value.focus();
      syncEditorContent();
  }
};

const addLink = () => {
  const url = prompt('Nhập đường dẫn URL (Bao gồm http:// hoặc https://):');
  if (url) formatDoc('createLink', url);
};

const insertTemplate = (type) => {
    let text = '';
    if(type === 'greeting') text = '<p>Chào bạn,</p><p>Cảm ơn bạn đã liên hệ với Zyro. Chúng tôi đã nhận được thông tin của bạn và đang tiến hành xử lý.</p>';
    if(type === 'apology') text = '<p>Chào bạn,</p><p>Zyro thành thật xin lỗi về sự bất tiện bạn đang gặp phải. Bộ phận kỹ thuật của chúng tôi đang khắc phục vấn đề này trong thời gian sớm nhất.</p>';
    if(type === 'thanks') text = '<p>Một lần nữa, Zyro cảm ơn bạn đã đồng hành và đóng góp ý kiến. Chúc bạn một ngày làm việc vui vẻ!</p><p>Trân trọng,<br><strong>Đội ngũ CSKH Zyro</strong></p>';
    
    if (editorRef.value) {
        editorRef.value.innerHTML += text;
        syncEditorContent();
        editorRef.value.focus();
        
        // Đặt cursor xuống cuối
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(editorRef.value);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
    }
};

const syncEditorContent = () => {
  if (editorRef.value) {
      replyHtmlContent.value = editorRef.value.innerHTML;
      rawTextLength.value = editorRef.value.innerText.trim().length;
  }
};

const isReplyValid = computed(() => {
  return rawTextLength.value >= 10 && rawTextLength.value <= 5000;
});

const submitBulkReply = async () => {
  if (!isReplyValid.value) return;
  if (props.contactIds.length === 0) {
      ZyroSwal.toastError('Chưa chọn liên hệ nào!');
      return;
  }
  
  isReplying.value = true;
  
  try {
     const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts/bulk-reply`, {
        contact_ids: props.contactIds,
        reply_message: replyHtmlContent.value
     }, { headers: getHeaders() });
     
     if(res.data.success) {
         ZyroSwal.toastSuccess(res.data.message);
         closeModal();
         emit('refresh');
     } else {
         ZyroSwal.toastError(res.data.message);
     }
  } catch (e) {
     ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra khi gửi mail hàng loạt');
  } finally {
     isReplying.value = false;
  }
};
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.hover-bg-effect:hover { background-color: #e2e8f0; color: #1e293b; }
.hover-bg-gray:hover { background-color: rgba(0,0,0,0.05) !important; }
.font-sans-vn { font-family: 'Inter', sans-serif; }

.editor-toolbar button { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }

[data-bs-theme="dark"] .hover-bg-effect:hover { background-color: #334155; color: #f8fafc; }
[data-bs-theme="dark"] .hover-bg-gray:hover { background-color: rgba(255,255,255,0.1) !important; }
</style>
