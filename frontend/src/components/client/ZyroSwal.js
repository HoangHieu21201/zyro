import Swal from 'sweetalert2';

if (!document.getElementById('zyro-swal-global-styles')) {
  const style = document.createElement('style');
  style.id = 'zyro-swal-global-styles';
  style.innerHTML = `
    .swal2-container.swal2-top-end {
      top: 100px !important; 
      right: 20px !important;
      z-index: 100000 !important;
      pointer-events: none;
    }
    .swal2-container.swal2-top-end .swal2-popup { pointer-events: auto; }
    
    .swal2-timer-progress-bar { 
      background-color: var(--color-c-hover, #547792) !important;
      height: 3px !important;
      opacity: 0.8 !important;
    }
    html.dark .swal2-timer-progress-bar {
      background-color: #ffffff !important;
    }

    .zyro-custom-toast {
      padding: 0.6rem 1.2rem !important;
      border: none !important;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
      animation: fadeInRightToast 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
      overflow: hidden !important; 
    }
    
    @keyframes fadeInRightToast {
      from { opacity: 0; transform: translateX(50px); }
      to { opacity: 1; transform: translateX(0); }
    }
    
    .swal-font-script { font-family: inherit; font-style: normal; }

    /* CSS cho hộp thoại Hủy đơn hàng (Shopee Style) */
    .custom-cancel-radio-box { transition: all 0.2s ease; }
    .custom-cancel-radio-box:hover { border-color: var(--color-c-hover, #547792) !important; }
    .swal-custom-radio-input:checked {
       background-color: var(--color-c-hover, #547792) !important;
       border-color: var(--color-c-hover, #547792) !important;
    }
    .swal-custom-textarea:focus {
       border-color: var(--color-c-hover, #547792) !important;
       box-shadow: 0 0 0 3px rgba(84, 119, 146, 0.2) !important;
       outline: none;
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

  // ========================================================
  // HÀM MỚI: HỘP THOẠI HỦY ĐƠN HÀNG (SHOPEE STYLE)
  // ========================================================
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
            if (e.target.value === 'Khác') {
              customInput.classList.remove('d-none');
              customInput.focus();
            } else {
              customInput.classList.add('d-none');
            }
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
          if (!customReason) {
            Swal.showValidationMessage('Vui lòng nhập rõ lý do hủy của bạn!');
            return false;
          }
          return customReason;
        }
        return selectedOption;
      }
    });
  },

  toastSuccess(message) {
    return Swal.fire({
      toast: true,
      position: 'top-end',
      timerProgressBar: true,
      html: `
        <div class="d-flex align-items-center gap-3">
          <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">
            <i class="bi bi-check2 text-white" style="font-size: 1rem;"></i>
          </div>
          <span class="fw-semibold text-dark dark:text-white tracking-wide" style="font-size: 0.9rem; white-space: nowrap;">${message}</span>
        </div>
      `,
      showConfirmButton: false,
      timer: 2500,
      background: 'transparent',
      customClass: {
        popup: 'zyro-custom-toast rounded-pill shadow-lg border border-light-subtle px-3 py-2 bg-white dark:bg-[#212529]',
      }
    });
  },

  toastError(message) {
    return Swal.fire({
      toast: true,
      position: 'top-end',
      timerProgressBar: true,
      html: `
        <div class="d-flex align-items-center gap-3">
          <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">
            <i class="bi bi-x-lg text-white" style="font-size: 0.9rem;"></i>
          </div>
          <span class="fw-semibold text-dark dark:text-white tracking-wide" style="font-size: 0.9rem; white-space: nowrap;">${message}</span>
        </div>
      `,
      showConfirmButton: false,
      timer: 3000,
      background: 'transparent',
      customClass: {
        popup: 'zyro-custom-toast rounded-pill shadow-lg border border-light-subtle px-3 py-2 bg-white dark:bg-[#212529]',
      }
    });
  },

  showLoading(title = 'Đang xử lý') {
    return Swal.fire({
      html: `
        <div class="d-flex flex-column align-items-center py-4">
          <div class="spinner-border text-urban mb-4" style="width: 3rem; height: 3rem;" role="status"></div>
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