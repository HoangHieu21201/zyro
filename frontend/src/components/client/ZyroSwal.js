import Swal from 'sweetalert2';

if (!document.getElementById('zyro-swal-global-styles')) {
  const style = document.createElement('style');
  style.id = 'zyro-swal-global-styles';
  style.innerHTML = `
    .swal2-container.swal2-bottom-end {
      bottom: 20px !important; 
      right: 20px !important;
      z-index: 100000 !important;
      pointer-events: none;
    }
    .swal2-container.swal2-bottom-end .swal2-popup { pointer-events: auto; }
    
    /* GLOBAL SAFETY NET CHO TOAST MẶC ĐỊNH */
    .swal2-popup.swal2-toast {
      background-color: #ffffff !important;
      padding: 12px 18px !important;
      border: none !important;
      border-radius: 10px !important;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0,0,0,0.05) !important;
      width: auto !important;
    }
    html.dark .swal2-popup.swal2-toast {
      background-color: #1a2533 !important;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
    }
    
    .swal2-toast .swal2-title { font-family: 'Segoe UI', Roboto, Arial, sans-serif !important; font-size: 0.95rem !important; font-weight: 600 !important; color: var(--color-c-hover, #547792) !important; margin: 0 0 4px 0 !important; text-align: left !important; }
    html.dark .swal2-toast .swal2-title { color: var(--color-c-light, #94b4c1) !important; }
    
    .swal2-toast .swal2-html-container { font-family: 'Segoe UI', Roboto, Arial, sans-serif !important; font-size: 0.85rem !important; color: #6c757d !important; margin: 0 !important; text-align: left !important; }
    html.dark .swal2-toast .swal2-html-container { color: #adb5bd !important; }

    .swal2-toast.swal2-icon-error .swal2-title { color: #dc3545 !important; }
    html.dark .swal2-toast.swal2-icon-error .swal2-title { color: #ef4444 !important; }

    /* ZYRO CUSTOM TOAST */
    .zyro-custom-toast { background-color: #ffffff !important; padding: 12px 18px !important; border: none !important; border-radius: 10px !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0,0,0,0.05) !important; width: auto !important; max-width: 400px !important; opacity: 1 !important; animation: fadeInRightToast 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    html.dark .zyro-custom-toast { background-color: #1a2533 !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important; }
    .zyro-toast-html-wrap { margin: 0 !important; padding: 0 !important; overflow: visible !important; }
    .zyro-toast-container { display: flex !important; align-items: center !important; gap: 12px !important; text-align: left !important; }

    .zyro-toast-icon { width: 28px !important; height: 28px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; font-size: 1rem !important; color: #ffffff !important; }
    .toast-success-mode .zyro-toast-icon { background-color: var(--color-c-hover, #547792) !important; } 
    .toast-error-mode .zyro-toast-icon { background-color: #dc3545 !important; }

    .zyro-toast-msg { font-family: 'Segoe UI', Roboto, Arial, sans-serif !important; font-size: 0.95rem !important; font-weight: 600 !important; line-height: 1.4 !important; word-break: break-word !important; margin: 0 !important; }
    .toast-success-mode .zyro-toast-msg { color: var(--color-c-hover, #547792) !important; }
    html.dark .toast-success-mode .zyro-toast-msg { color: var(--color-c-light, #94b4c1) !important; }
    .toast-error-mode .zyro-toast-msg { color: #dc3545 !important; }
    html.dark .toast-error-mode .zyro-toast-msg { color: #ef4444 !important; }

    .swal2-timer-progress-bar { height: 3px !important; opacity: 1 !important; }
    .toast-success-mode .swal2-timer-progress-bar { background-color: var(--color-c-hover, #547792) !important; }
    .toast-error-mode .swal2-timer-progress-bar { background-color: #dc3545 !important; }
    @keyframes fadeInRightToast { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }

    .swal-font-script { font-family: inherit; font-style: normal; }

    /* CSS Hủy đơn hàng */
    .custom-cancel-radio-box { transition: all 0.2s ease; }
    .custom-cancel-radio-box:hover { border-color: var(--color-c-hover, #547792) !important; }
    .swal-custom-radio-input:checked { background-color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }
    .swal-custom-textarea:focus { border-color: var(--color-c-hover, #547792) !important; box-shadow: 0 0 0 3px rgba(84, 119, 146, 0.2) !important; outline: none; }

    /* ==========================================
       HIỆU ỨNG LOADING MỚI (BOUNCING DOTS) CHO SWEETALERT
    ========================================== */
    .zyro-swal-bouncing-dots {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-bottom: 24px;
      height: 48px;
    }
    .zyro-swal-dot {
      width: 12px;
      height: 12px;
      background-color: var(--color-c-hover, #547792);
      border-radius: 50%;
      animation: zyroSwalBounce 1.4s infinite ease-in-out both;
    }
    html.dark .zyro-swal-dot {
      background-color: var(--color-c-light, #94b4c1);
    }
    .zyro-swal-dot:nth-child(1) { animation-delay: -0.32s; }
    .zyro-swal-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes zyroSwalBounce {
      0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
      40% { transform: scale(1); opacity: 1; }
    }
  `;
  document.head.appendChild(style);
}

export const ZyroSwal = {
  
  confirmDelete(itemName) {
    return Swal.fire({
      html: `
        <div class="text-center mb-3 mt-2">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 80px; height: 80px;">
            <i class="bi bi-trash3 text-danger" style="font-size: 2.5rem;"></i>
          </div>
        </div>
        <h4 class="fw-bold text-dark dark:text-white mb-2 swal-font-script">Xóa sản phẩm?</h4>
        <p class="text-muted small px-3">Bạn có chắc chắn muốn bỏ <b class="text-dark dark:text-gray-300">${itemName}</b> khỏi danh sách?</p>
      `,
      showCancelButton: true,
      confirmButtonText: 'Đồng ý xóa',
      cancelButtonText: 'Giữ lại',
      buttonsStyling: false,
      customClass: {
        popup: 'rounded-4 shadow-lg border-0 p-4 dark:bg-[#1a2533]',
        confirmButton: 'btn btn-danger rounded-pill px-4 py-2 mx-1 fw-bold flex-grow-1 shadow-sm',
        cancelButton: 'btn btn-light rounded-pill px-4 py-2 mx-1 fw-bold flex-grow-1 text-muted border',
        actions: 'd-flex w-100 mt-4 gap-2 px-3'
      },
      width: '380px',
    });
  },

  confirmLogout() {
    return Swal.fire({
      html: `
        <div class="text-center mb-3 mt-2">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark bg-opacity-10 dark:bg-white dark:bg-opacity-10" style="width: 80px; height: 80px;">
            <i class="bi bi-box-arrow-right text-dark dark:text-white" style="font-size: 2.5rem;"></i>
          </div>
        </div>
        <h4 class="fw-bold text-dark dark:text-white mb-2 swal-font-script">Đăng xuất?</h4>
        <p class="text-muted small px-3">Bạn có chắc chắn muốn kết thúc phiên đăng nhập này không?</p>
      `,
      showCancelButton: true,
      confirmButtonText: 'Đăng xuất ngay',
      cancelButtonText: 'Ở lại',
      buttonsStyling: false,
      customClass: {
        popup: 'rounded-4 shadow-lg border-0 p-4 dark:bg-[#1a2533]',
        confirmButton: 'btn btn-dark dark:btn-light rounded-pill px-4 py-2 mx-1 fw-bold flex-grow-1 shadow-sm',
        cancelButton: 'btn btn-light dark:bg-gray-700 dark:text-white rounded-pill px-4 py-2 mx-1 fw-bold flex-grow-1 text-muted border-0',
        actions: 'd-flex w-100 mt-4 gap-2 px-3'
      },
      width: '360px',
    });
  },

  confirmCancelOrder() {
    return Swal.fire({
      html: `
        <div class="text-start mt-2">
          <h4 class="fw-bold text-dark dark:text-white mb-3 swal-font-script text-center">Lý do hủy đơn?</h4>
          <p class="text-muted small mb-4 px-1 text-center">Vui lòng chọn lý do hủy đơn hàng để ZYRO có thể phục vụ bạn tốt hơn:</p>
          
          <div class="d-flex flex-column gap-2 px-1">
            <label class="form-check custom-cancel-radio-box p-3 border dark:border-gray-600 rounded-3 cursor-pointer d-flex align-items-center mb-0">
              <input class="form-check-input mt-0 me-3 swal-custom-radio-input" type="radio" name="cancelReason" value="Muốn thay đổi địa chỉ nhận hàng" checked style="cursor: pointer;">
              <span class="fw-medium text-dark dark:text-gray-200" style="font-size: 0.95rem;">Muốn thay đổi địa chỉ nhận hàng</span>
            </label>
            <label class="form-check custom-cancel-radio-box p-3 border dark:border-gray-600 rounded-3 cursor-pointer d-flex align-items-center mb-0">
              <input class="form-check-input mt-0 me-3 swal-custom-radio-input" type="radio" name="cancelReason" value="Muốn đổi sản phẩm/phân loại khác" style="cursor: pointer;">
              <span class="fw-medium text-dark dark:text-gray-200" style="font-size: 0.95rem;">Muốn đổi sản phẩm/phân loại khác</span>
            </label>
            <label class="form-check custom-cancel-radio-box p-3 border dark:border-gray-600 rounded-3 cursor-pointer d-flex align-items-center mb-0">
              <input class="form-check-input mt-0 me-3 swal-custom-radio-input" type="radio" name="cancelReason" value="Tìm thấy nơi khác giá rẻ hơn" style="cursor: pointer;">
              <span class="fw-medium text-dark dark:text-gray-200" style="font-size: 0.95rem;">Tìm thấy nơi khác giá rẻ hơn</span>
            </label>
            <label class="form-check custom-cancel-radio-box p-3 border dark:border-gray-600 rounded-3 cursor-pointer d-flex align-items-center mb-0">
              <input class="form-check-input mt-0 me-3 swal-custom-radio-input" type="radio" name="cancelReason" value="Đổi ý không muốn mua nữa" style="cursor: pointer;">
              <span class="fw-medium text-dark dark:text-gray-200" style="font-size: 0.95rem;">Đổi ý không muốn mua nữa</span>
            </label>
            <label class="form-check custom-cancel-radio-box p-3 border dark:border-gray-600 rounded-3 cursor-pointer d-flex align-items-center mb-0">
              <input class="form-check-input mt-0 me-3 swal-custom-radio-input" type="radio" name="cancelReason" value="Khác" style="cursor: pointer;">
              <span class="fw-medium text-dark dark:text-gray-200" style="font-size: 0.95rem;">Lý do khác...</span>
            </label>
          </div>
          
          <div class="px-1">
            <textarea id="customCancelReason" class="form-control swal-custom-textarea mt-3 d-none shadow-none border dark:border-gray-600 dark:bg-[#1a2533] dark:text-white transition-all" rows="3" placeholder="Vui lòng nhập lý do cụ thể..."></textarea>
          </div>
        </div>
      `,
      didOpen: () => {
        const radios = document.querySelectorAll('input[name="cancelReason"]');
        const customInput = document.getElementById('customCancelReason');
        const radioBoxes = document.querySelectorAll('.custom-cancel-radio-box');

        const updateBoxes = () => {
           radios.forEach((r, idx) => {
              if(r.checked) {
                 radioBoxes[idx].style.borderColor = 'var(--color-c-hover, #547792)';
                 radioBoxes[idx].style.backgroundColor = 'rgba(84, 119, 146, 0.08)';
              } else {
                 radioBoxes[idx].style.borderColor = '';
                 radioBoxes[idx].style.backgroundColor = 'transparent';
              }
           });
        };
        
        updateBoxes();
        radios.forEach(radio => {
          radio.addEventListener('change', (e) => {
            updateBoxes();
            if (e.target.value === 'Khác') { customInput.classList.remove('d-none'); customInput.focus(); } 
            else { customInput.classList.add('d-none'); }
          });
        });
      },
      showCancelButton: true,
      confirmButtonText: 'Xác nhận hủy',
      cancelButtonText: 'Giữ lại đơn',
      buttonsStyling: false,
      customClass: {
        popup: 'rounded-4 shadow-lg border-0 p-3 dark:bg-[#1a2533]',
        confirmButton: 'btn btn-danger rounded-pill px-4 py-2.5 mx-1 fw-bold shadow-sm flex-grow-1',
        cancelButton: 'btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 border rounded-pill px-4 py-2.5 mx-1 fw-bold flex-grow-1',
        actions: 'd-flex w-100 mt-4 px-1 gap-2'
      },
      width: '500px',
      preConfirm: () => {
        const selectedOption = document.querySelector('input[name="cancelReason"]:checked').value;
        if (selectedOption === 'Khác') {
          const customReason = document.getElementById('customCancelReason').value.trim();
          if (!customReason) { Swal.showValidationMessage('Vui lòng nhập rõ lý do hủy của bạn!'); return false; }
          return customReason;
        }
        return selectedOption;
      }
    });
  },

  toastSuccess(message) {
    return Swal.fire({
      toast: true,
      position: 'bottom-end',
      timerProgressBar: true,
      html: `
        <div class="zyro-toast-container">
          <div class="zyro-toast-icon shadow-sm">
            <i class="bi bi-check-lg"></i>
          </div>
          <div class="zyro-toast-msg">${message}</div>
        </div>
      `,
      showConfirmButton: false,
      timer: 3000,
      background: 'transparent',
      customClass: {
        popup: 'zyro-custom-toast toast-success-mode',
        htmlContainer: 'zyro-toast-html-wrap'
      }
    });
  },

  toastError(message) {
    return Swal.fire({
      toast: true,
      position: 'bottom-end',
      timerProgressBar: true,
      html: `
        <div class="zyro-toast-container">
          <div class="zyro-toast-icon shadow-sm">
            <i class="bi bi-x-lg"></i>
          </div>
          <div class="zyro-toast-msg">${message}</div>
        </div>
      `,
      showConfirmButton: false,
      timer: 4000,
      background: 'transparent',
      customClass: {
        popup: 'zyro-custom-toast toast-error-mode',
        htmlContainer: 'zyro-toast-html-wrap'
      }
    });
  },

  showLoading(title = 'Đang xử lý') {
    return Swal.fire({
      html: `
        <div class="d-flex flex-column align-items-center py-4">
          <div class="zyro-swal-bouncing-dots">
            <div class="zyro-swal-dot"></div>
            <div class="zyro-swal-dot"></div>
            <div class="zyro-swal-dot"></div>
          </div>
          <h5 class="fw-bold text-dark dark:text-white swal-font-script tracking-widest text-uppercase">${title}</h5>
          <span class="text-muted small">Vui lòng đợi trong giây lát...</span>
        </div>
      `,
      showConfirmButton: false,
      allowOutsideClick: false,
      customClass: { popup: 'rounded-4 shadow-lg border-0 dark:bg-[#1a2533]' },
      width: '320px'
    });
  },

  close() { Swal.close(); }
};