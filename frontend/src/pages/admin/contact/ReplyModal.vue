<template>
  <div class="modal fade glass-modal" id="contactReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <!-- Nâng lên kích thước modal-xl (lớn nhất) -->
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533] overflow-hidden bg-white">
        
        <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3 p-md-4">
          <h5 class="fw-bold text-dark dark:text-white mb-0 font-sans-vn d-flex align-items-center">
            <div class="bg-urban text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-envelope-open fs-5"></i>
            </div>
            Chi Tiết Liên Hệ & Phản Hồi
          </h5>
          <button type="button" class="btn-close dark:filter-invert" @click="closeModal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body p-0 position-relative bg-light dark:bg-[#121416]" v-if="contact">
          <div class="row g-0 h-100">
             
             <!-- CỘT TRÁI: THÔNG TIN KHÁCH HÀNG GỬI -->
             <div class="col-lg-5 p-4 border-end dark:border-gray-700 bg-white dark:bg-[#1a2533] custom-scrollbar-y" style="max-height: 75vh; overflow-y: auto;">
                <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-person-lines-fill me-2"></i>Thông Tin Yêu Cầu</h6>
                
                <div class="d-flex align-items-center mb-4 p-3 bg-light dark:bg-[#212529] rounded-3 border dark:border-gray-600 shadow-sm">
                   <div class="bg-white dark:bg-[#121416] rounded-circle d-flex justify-content-center align-items-center shadow-sm me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                      <i class="bi bi-person text-muted fs-4"></i>
                   </div>
                   <div class="w-100 overflow-hidden">
                      <div class="fw-bold text-dark dark:text-white fs-6 font-sans-vn text-truncate">{{ contact.name }}</div>
                      <div class="text-muted small font-monospace text-truncate"><i class="bi bi-envelope me-1"></i>{{ contact.email }}</div>
                      <div class="text-muted small font-monospace text-truncate" v-if="contact.phone"><i class="bi bi-telephone me-1"></i>{{ contact.phone }}</div>
                   </div>
                </div>

                <div class="mb-4">
                   <span class="small text-muted text-uppercase fw-bold"><i class="bi bi-tag-fill me-1"></i>Chủ đề</span>
                   <div class="fw-bold text-dark dark:text-gray-200 fs-6 mt-1 p-3 bg-light dark:bg-[#212529] rounded border dark:border-gray-600 shadow-sm">{{ contact.subject }}</div>
                </div>
                
                <div>
                   <span class="small text-muted text-uppercase fw-bold"><i class="bi bi-chat-left-quote-fill me-1"></i>Nội dung tin nhắn</span>
                   <!-- Đã ép word-break để không vỡ khung -->
                   <div class="text-dark dark:text-gray-300 p-3 bg-light dark:bg-[#212529] rounded-3 border dark:border-gray-600 mt-2 fst-italic lh-base shadow-sm word-break-all" style="white-space: pre-wrap;">
                      {{ contact.message }}
                   </div>
                </div>
             </div>

             <!-- CỘT PHẢI: KHUNG SOẠN THẢO HOẶC LỊCH SỬ -->
             <div class="col-lg-7 p-4 bg-white dark:bg-[#1a2533] d-flex flex-column custom-scrollbar-y" style="max-height: 75vh; overflow-y: auto;">
                
                <!-- TRƯỜNG HỢP 1: ĐÃ PHẢN HỒI -->
                <div v-if="contact.status === 'replied'" class="h-100 d-flex flex-column">
                   <h6 class="fw-bold text-success text-uppercase mb-4 border-bottom border-success border-opacity-25 pb-2"><i class="bi bi-check-circle-fill me-2"></i>Lịch Sử Phản Hồi</h6>
                   
                   <div class="alert alert-success border border-success border-opacity-25 bg-success bg-opacity-10 rounded-4 p-4 flex-grow-1">
                      <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-success border-opacity-25 pb-2">
                         <span class="fw-bold text-success"><i class="bi bi-send-check-fill me-1"></i>Email đã gửi lúc:</span>
                         <span class="small font-monospace fw-bold">{{ formatDateTime(contact.replied_at) }}</span>
                      </div>
                      <div class="bg-white dark:bg-[#121416] p-3 rounded-3 border border-success border-opacity-25 text-dark dark:text-gray-300 rich-text-display shadow-sm word-break-all" 
                           v-html="contact.reply_message" style="min-height: 200px;">
                      </div>
                   </div>
                </div>

                <!-- TRƯỜNG HỢP 2: CHƯA PHẢN HỒI -> SOẠN THẢO RICH TEXT -->
                <form v-else @submit.prevent="submitReply" class="h-100 d-flex flex-column">
                   <h6 class="fw-bold text-urban text-uppercase mb-3 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-pencil-square me-2"></i>Soạn Thảo Email Phản Hồi</h6>
                   
                   <!-- QUICK REPLY TEMPLATES -->
                   <div class="mb-3">
                      <label class="form-label small fw-bold text-muted text-uppercase mb-2">Chèn nhanh câu trả lời mẫu</label>
                      <div class="d-flex gap-2 flex-wrap">
                         <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('greeting')">
                            <i class="bi bi-hand-thumbs-up me-1"></i> Chào hỏi
                         </button>
                         <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('apology')">
                            <i class="bi bi-emoji-frown me-1"></i> Xin lỗi chậm trễ
                         </button>
                         <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill border-dashed dark:text-gray-300 transition-all hover-urban-outline font-sans-vn fw-medium" @click="insertTemplate('thanks')">
                            <i class="bi bi-heart me-1"></i> Cảm ơn & Kết thúc
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
                      <!-- ĐÃ BỎ NÚT CHÈN ẢNH ĐỂ TIẾT KIỆM KHÔNG GIAN THEO YÊU CẦU -->
                      
                      <div class="vr mx-1 dark:bg-gray-600"></div>
                      
                      <div class="d-flex align-items-center gap-1 px-1 cursor-pointer" title="Đổi màu chữ">
                         <i class="bi bi-palette text-muted small"></i>
                         <input type="color" class="form-control form-control-color border-0 p-0 shadow-none bg-transparent cursor-pointer" style="width: 25px; height: 25px;" @input="formatDoc('foreColor', $event.target.value)">
                      </div>
                   </div>

                   <!-- CONTENT EDITABLE AREA -->
                   <div class="editor-content form-control rounded-0 rounded-bottom-3 shadow-none bg-white dark:bg-[#121416] dark:text-white border-top-0 border-secondary-subtle dark:border-gray-600 flex-grow-1 custom-scrollbar-y p-3" 
                        contenteditable="true" 
                        ref="editorRef"
                        @input="syncEditorContent"
                        @blur="syncEditorContent"
                        placeholder="Soạn nội dung phản hồi tại đây..."
                        style="min-height: 250px; outline: none;">
                   </div>

                   <div class="d-flex justify-content-between align-items-center mt-4">
                      <span class="small font-monospace fw-bold" :class="isReplyValid ? 'text-success' : 'text-danger'">
                         {{ rawTextLength }} / 5000 ký tự <span class="d-none d-sm-inline">(Tối thiểu 20)</span>
                      </span>
                      <div class="d-flex gap-2">
                         <button type="button" class="btn btn-light dark:bg-[#2b3035] border dark:border-gray-600 rounded-pill px-4 fw-bold shadow-sm hover-bg-effect font-sans-vn" @click="closeModal">Hủy</button>
                         
                         <button type="submit" class="btn btn-urban rounded-pill px-5 fw-bold transition-all shadow-sm hover-transform font-sans-vn" :disabled="isReplying || !isReplyValid">
                            <LoadingDots v-if="isReplying" color="#ffffff" :size="6" class="me-2" />
                            <span v-if="!isReplying">Gửi Email <i class="bi bi-send ms-1"></i></span>
                         </button>
                      </div>
                   </div>
                </form>
             </div>
          </div>
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

const emit = defineEmits(['refresh']);

const contact = ref(null);
const editorRef = ref(null);
let modalInstance = null;

const replyHtmlContent = ref('');
const rawTextLength = ref(0);
const isReplying = ref(false);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

// =====================================
// EXPOSE HÀM ĐỂ COMPONENT CHA GỌI
// =====================================
const openModal = async (contactData) => {
  contact.value = contactData;
  replyHtmlContent.value = '';
  rawTextLength.value = 0;
  
  if (!modalInstance) {
    modalInstance = new window.bootstrap.Modal(document.getElementById('contactReplyModal'));
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

// =====================================
// RICH TEXT EDITOR LOGIC
// =====================================
const formatDoc = (cmd, value = null) => {
  if (editorRef.value) {
      document.execCommand(cmd, false, value);
      editorRef.value.focus();
      syncEditorContent();
  }
};

const addLink = () => {
  const url = prompt('Nhập đường dẫn (URL):');
  if (url) formatDoc('createLink', url);
};

const syncEditorContent = () => {
  if (editorRef.value) {
      replyHtmlContent.value = editorRef.value.innerHTML;
      rawTextLength.value = editorRef.value.innerText.trim().length;
  }
};

// =====================================
// QUICK REPLY TEMPLATES
// =====================================
const insertTemplate = (type) => {
  if (!editorRef.value) return;
  
  let text = '';
  if (type === 'greeting') text = `Chào bạn ${contact.value.name},<br><br>Cảm ơn bạn đã liên hệ với ZYRO. Chúng tôi đã nhận được yêu cầu của bạn về vấn đề: <b>"${contact.value.subject}"</b>.<br><br>`;
  if (type === 'apology') text = `Chúng tôi rất xin lỗi vì sự chậm trễ trong việc phản hồi cũng như sự bất tiện mà bạn đang gặp phải.<br><br>`;
  if (type === 'thanks') text = `<br><br>Nếu cần hỗ trợ thêm, vui lòng phản hồi lại email này. Chúc bạn một ngày tốt lành!<br><b>Trân trọng,<br>Đội ngũ CSKH ZYRO</b>`;

  document.execCommand('insertHTML', false, text);
  syncEditorContent();
};

const isReplyValid = computed(() => {
  return rawTextLength.value >= 20 && rawTextLength.value <= 5000;
});

const submitReply = async () => {
  if (!isReplyValid.value) return;
  isReplying.value = true;
  
  try {
     const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts/${contact.value.id}/reply`, {
        reply_message: replyHtmlContent.value
     }, { headers: getHeaders() });
     
     ZyroSwal.toastSuccess(res.data.message);
     closeModal();
     emit('refresh'); // Gọi Component cha tải lại bảng
  } catch (e) {
     ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra khi gửi mail');
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

.hover-bg-effect:hover { background-color: rgba(84, 119, 146, 0.05); }
.hover-bg-gray:hover { background-color: #e9ecef !important; }
html.dark .hover-bg-gray:hover { background-color: #343a40 !important; }
.hover-urban-outline:hover { color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; background: rgba(84, 119, 146, 0.05); }
.border-dashed { border-style: dashed !important; border-color: #adb5bd !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* CHỐNG TRÀN CHỮ BẺ TỪ DÀI */
.word-break-all {
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

.editor-toolbar button { height: 34px; width: 34px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
.editor-content { 
  font-family: 'Segoe UI', Roboto, Arial, sans-serif; 
  font-size: 0.95rem; 
  line-height: 1.6; 
  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  overflow-x: hidden;
}
.editor-content:empty:before { content: attr(placeholder); color: #adb5bd; pointer-events: none; display: block; font-style: italic; }
.rich-text-display :deep(img) { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; }
.rich-text-display :deep(a) { color: var(--color-c-hover, #547792); text-decoration: underline; }

.glass-modal { backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4); }
.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }
</style>