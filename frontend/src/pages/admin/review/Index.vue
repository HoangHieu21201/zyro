<template>
  <div class="review-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu đánh giá...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Đánh Giá Của Khách Hàng</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <!-- TRẠNG THÁI LÊN THÀNH TABS -->
      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-chat-square-text me-2"></i> Tất cả
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'active' }" @click.prevent="switchTab('active')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đã duyệt (Hiển thị)
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-warning"></i> Bị ẩn
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
            </a>
          </li>
        </ul>
      </div>

      <!-- BỘ LỌC NÂNG CAO -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] animation-fade-in">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">
            <!-- Ô Tìm Kiếm tích hợp Debounce -->
            <div class="col-xl-3 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Tên khách, email, nội dung...">
            </div>
            
            <div class="col-xl-2 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-star me-1"></i>Đánh giá</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 fw-semibold" 
                      v-model="filters.rating" @change="applyFilters">
                <option value="">Tất cả số sao</option>
                <option value="5">5 Sao (Tuyệt vời)</option>
                <option value="4">4 Sao (Tốt)</option>
                <option value="3">3 Sao (Bình thường)</option>
                <option value="2">2 Sao (Tệ)</option>
                <option value="1">1 Sao (Rất tệ)</option>
              </select>
            </div>

            <div class="col-xl-2 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-diagram-3 me-1"></i>Danh mục</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                      v-model="filters.category_id" @change="applyFilters">
                <option value="">Tất cả</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div class="col-xl-2 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-box-seam me-1"></i>Sản phẩm</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                      v-model="filters.product_id" @change="applyFilters">
                <option value="">Tất cả</option>
                <option v-for="p in productsList" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>

            <div class="col-xl-3 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-calendar-range me-1"></i>Thời gian</label>
              <div class="input-group input-group-sm shadow-sm-hover" style="height: 38px;">
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_from" @change="applyFilters">
                <span class="input-group-text border-secondary-subtle dark:border-gray-700 bg-white dark:bg-[#1a2533]">-</span>
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_to" @change="applyFilters">
              </div>
            </div>

            <!-- Tình trạng Phản hồi -->
            <div class="col-12 mt-3 d-flex justify-content-between align-items-center pt-3 border-top dark:border-gray-700">
               <div class="d-flex gap-2 flex-wrap align-items-center">
                 <span class="small text-muted fw-bold text-uppercase me-2"><i class="bi bi-reply-all me-1"></i>Trạng thái phản hồi:</span>
                 <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all border shadow-sm" :class="filters.replied === '' ? 'btn-urban text-white border-urban' : 'btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600'" @click="toggleReplied('')">Tất cả</button>
                 <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all border shadow-sm" :class="filters.replied === 'yes' ? 'bg-info text-dark border-info' : 'btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600'" @click="toggleReplied('yes')">Đã phản hồi</button>
                 <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all border shadow-sm" :class="filters.replied === 'no' ? 'bg-danger text-white border-danger' : 'btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600'" @click="toggleReplied('no')">Chưa phản hồi (Cần xử lý)</button>
               </div>
               
               <!-- ĐÃ CẬP NHẬT: Thêm spinner nhỏ, tinh tế vào góc phải Bộ lọc thay vì chèn ép giao diện -->
               <div class="d-flex gap-2 align-items-center">
                 <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban me-2" title="Đang tải dữ liệu..."></span>
                 <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all" @click="resetFilters" v-if="hasActiveFilters">
                   <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                 </button>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BẢNG DỮ LIỆU -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all position-relative">
        
        <div v-if="isLoading && !isFirstLoad" 
        class="position-absolute top-0 start-0 w-100 h-100 rounded-4 bg-white
         dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.6;">
        </div>

        <div class="card-body p-0">
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Sản phẩm & Phân loại</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Đánh giá</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 10%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 10%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="reviews.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu phù hợp.
                  </td>
                </tr>
                <tr v-else v-for="review in reviews" :key="review.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': review.deleted_at || review.status === 'hidden'}">
                  <!-- Cột Khách Hàng -->
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getImageUrl(review.user?.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-3 border shadow-sm dark:border-gray-600" style="width: 45px; height: 45px;">
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ review.user?.full_name || 'Khách Vô Danh' }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace">{{ maskEmail(review.user?.email) }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Cột Sản Phẩm -->
                  <td class="px-4">
                    <div class="d-flex align-items-center bg-white dark:bg-[#212529] p-2 border border-light-subtle dark:border-gray-700 rounded-3 shadow-sm">
                      <img :src="getImageUrl(review.product?.thumbnail_image)" @error="handleProductImageError" class="rounded object-fit-cover me-2 dark:border-gray-600 img-zoomable" style="width: 40px; height: 40px;" @click.stop="openImageZoom(getImageUrl(review.product?.thumbnail_image))">
                      <div class="overflow-hidden">
                        <div class="fw-bold text-urban small text-truncate" :title="review.product?.name">{{ review.product?.name || 'Sản phẩm bị xóa' }}</div>
                        <div class="text-muted dark:text-gray-400 mt-1 d-flex align-items-center" style="font-size: 0.7rem;">
                           <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1 text-truncate" style="max-width: 120px;" :title="review.variant_name">{{ review.variant_name || 'Mặc định' }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Cột Số sao & Nhận xét -->
                  <td class="px-4">
                    <div class="d-flex align-items-center mb-1">
                      <div class="text-warning me-2" style="font-size: 0.8rem;">
                        <i v-for="n in 5" :key="n" class="bi" :class="n <= review.rating ? 'bi-star-fill' : 'bi-star'"></i>
                      </div>
                      <span v-if="review.images && review.images.length > 0" class="badge bg-urban text-white" style="font-size: 0.65rem;"><i class="bi bi-images me-1"></i>{{ review.images.length }} ảnh</span>
                    </div>
                    <div class="text-dark dark:text-gray-300 small text-truncate fw-medium fst-italic" style="max-width: 250px;" :title="review.comment">
                      "{{ review.comment || 'Khách hàng không để lại nhận xét.' }}"
                    </div>
                    <div v-if="review.admin_reply" class="text-success small fw-bold mt-1"><i class="bi bi-reply-all-fill me-1"></i>Đã phản hồi</div>
                  </td>

                  <!-- Cột Trạng thái -->
                  <td class="px-4 text-center">
                    <span v-if="review.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <div class="form-check form-switch m-0" title="Bật để hiển thị trên web">
                        <input class="form-check-input cursor-pointer fs-4 m-0" type="checkbox" 
                               :checked="review.status === 'active'" 
                               @change="toggleStatus(review)">
                      </div>
                    </div>
                  </td>

                  <!-- Cột Thao tác -->
                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-urban fw-bold shadow-sm border" title="Chi tiết & Trả lời" @click="openDetailModal(review)">
                        <i class="bi bi-chat-square-text-fill"></i>
                      </button>
                      <template v-if="!review.deleted_at">
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border hover-danger" @click="confirmDelete(review.id)" title="Xóa rác">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreReview(review.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="reviews.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else v-for="review in reviews" :key="review.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': review.deleted_at || review.status === 'hidden'}">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                   <div class="d-flex align-items-center">
                      <img :src="getImageUrl(review.user?.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-2 border shadow-sm dark:border-gray-600" style="width: 35px; height: 35px;">
                      <span class="fw-bold dark:text-gray-200 small">{{ review.user?.full_name || 'Khách' }}</span>
                   </div>
                   <div class="text-warning small">
                      <i v-for="n in 5" :key="n" class="bi" :class="n <= review.rating ? 'bi-star-fill' : 'bi-star'"></i>
                   </div>
                </div>

                <div class="bg-light dark:bg-[#1a2533] p-2 rounded mb-2 border dark:border-gray-700 d-flex">
                   <img :src="getImageUrl(review.product?.thumbnail_image)" @error="handleProductImageError" class="rounded object-fit-cover me-2 img-zoomable" style="width: 40px; height: 40px;" @click.stop="openImageZoom(getImageUrl(review.product?.thumbnail_image))">
                   <div class="overflow-hidden">
                     <div class="fw-bold text-urban text-truncate" style="font-size: 0.8rem;">{{ review.product?.name }}</div>
                     <span class="badge bg-secondary bg-opacity-10 text-secondary border mt-1" style="font-size: 0.65rem;">{{ review.variant_name || 'Mặc định' }}</span>
                   </div>
                </div>
                
                <p class="small text-dark dark:text-gray-300 fst-italic mb-3 text-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;">
                   "{{ review.comment || 'Không có nhận xét' }}"
                </p>

                <div class="d-flex gap-2">
                  <button class="btn btn-urban text-white flex-grow-1 shadow-sm fw-bold btn-sm" @click="openDetailModal(review)"><i class="bi bi-reply-fill"></i> Chi tiết</button>
                  <template v-if="!review.deleted_at">
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border shadow-sm btn-sm" style="width: 45px;" @click="confirmDelete(review.id)"><i class="bi bi-trash"></i></button>
                  </template>
                  <template v-else>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border shadow-sm btn-sm" @click="restoreReview(review.id)"><i class="bi bi-arrow-counterclockwise"></i></button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
        <span class="text-muted dark:text-gray-400 small">Trang {{ pagination.current_page }} / {{ totalPages }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button></li>
            
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: pagination.current_page === page }">
              <button class="page-link dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="changePage(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: pagination.current_page === totalPages }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- POPUP XỬ LÝ ĐÁNH GIÁ (QUICK VIEW & REPLY)                -->
    <!-- ======================================================== -->
    <div class="modal fade" id="reviewDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-chat-left-quote-fill text-urban me-2"></i>Chi Tiết Đánh Giá</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4" v-if="selectedReview">
            <div class="row g-4">
              <!-- NỬA TRÁI: THÔNG TIN KHÁCH HÀNG & REVIEW -->
              <div class="col-lg-7 border-end dark:border-gray-700 pe-lg-4">
                
                <!-- Info Khách & Sản phẩm -->
                <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom dark:border-gray-700">
                  <div class="d-flex align-items-center">
                    <img :src="getImageUrl(selectedReview.user?.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-3 border border-2 border-white shadow-sm dark:border-gray-600" style="width: 55px; height: 55px;">
                    <div>
                      <h6 class="fw-bold mb-1 dark:text-white">{{ selectedReview.user?.full_name || 'Khách Vô Danh' }}</h6>
                      <small class="text-muted font-monospace">{{ maskEmail(selectedReview.user?.email) }}</small>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="text-warning fs-5">
                      <i v-for="n in 5" :key="n" class="bi" :class="n <= selectedReview.rating ? 'bi-star-fill' : 'bi-star'"></i>
                    </div>
                    <small class="text-muted">{{ formatDateTime(selectedReview.created_at) }}</small>
                  </div>
                </div>

                <div class="bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 mb-4">
                  <div class="d-flex align-items-center mb-3 pb-3 border-bottom dark:border-gray-600">
                    <img :src="getImageUrl(selectedProductOfReview?.thumbnail_image)" @error="handleProductImageError" class="rounded object-fit-cover me-3 border dark:border-gray-600 img-zoomable" style="width: 50px; height: 50px;" @click.stop="openImageZoom(getImageUrl(selectedProductOfReview?.thumbnail_image))">
                    <div>
                      <div class="fw-bold text-dark dark:text-gray-200 small">{{ selectedProductOfReview?.name || 'Sản phẩm đã bị xóa' }}</div>
                      <span class="badge bg-white dark:bg-[#1a2533] text-secondary border mt-1">{{ selectedReview.variant_name || 'Phân loại mặc định' }}</span>
                    </div>
                  </div>
                  
                  <div class="d-flex gap-3 flex-wrap">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info" v-if="selectedReview.reviewer_height"><i class="bi bi-arrows-vertical"></i> Cao: {{ selectedReview.reviewer_height }} cm</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary" v-if="selectedReview.reviewer_weight"><i class="bi bi-speedometer2"></i> Nặng: {{ selectedReview.reviewer_weight }} kg</span>
                    <span class="badge border" :class="getFitBadgeClass(selectedReview.fit_feedback)" v-if="selectedReview.fit_feedback"><i class="bi bi-scissors"></i> Form: {{ selectedReview.fit_feedback }}</span>
                  </div>
                </div>

                <!-- Nội dung Review -->
                <div class="mb-4">
                  <h6 class="fw-bold text-muted text-uppercase small mb-2"><i class="bi bi-quote me-1"></i>Khách hàng nhận xét:</h6>
                  <div class="p-3 bg-white dark:bg-[#1a2533] border dark:border-gray-700 rounded-3 text-dark dark:text-gray-200 fst-italic" style="font-size: 0.95rem;">
                    {{ selectedReview.comment || '(Khách hàng không để lại nội dung chữ)' }}
                  </div>
                </div>

                <!-- Ảnh khách chụp -->
                <div v-if="selectedReview.images && selectedReview.images.length > 0">
                  <h6 class="fw-bold text-muted text-uppercase small mb-2"><i class="bi bi-images me-1"></i>Ảnh đính kèm ({{ selectedReview.images.length }}):</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <img v-for="(img, idx) in selectedReview.images" :key="idx" :src="getImageUrl(img)" @error="handleProductImageError" 
                         class="rounded border dark:border-gray-600 object-fit-cover shadow-sm img-zoomable" 
                         style="width: 80px; height: 80px;"
                         @click="openImageZoom(getImageUrl(img))">
                  </div>
                </div>

              </div>
              
              <!-- NỬA PHẢI: ADMIN REPLY & CÀI ĐẶT -->
              <div class="col-lg-5 d-flex flex-column">
                <h6 class="fw-bold text-urban text-uppercase mb-3 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-person-workspace me-2"></i>Khu Vực Quản Trị</h6>
                
                <!-- Status Toggle -->
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-4 d-flex justify-content-between align-items-center shadow-sm mb-4">
                  <div>
                    <h6 class="mb-1 fw-bold text-dark dark:text-white fs-6">Hiển thị công khai</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Bật để hiện review này lên web</small>
                  </div>
                  <div class="form-check form-switch m-0 fs-4">
                    <input class="form-check-input cursor-pointer" type="checkbox" :checked="selectedReview.status === 'active'" @change="toggleStatusModal">
                  </div>
                </div>

                <!-- Form Reply -->
                <div class="flex-grow-1 d-flex flex-column">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 mb-2">
                    Cửa hàng phản hồi <span v-if="selectedReview.admin_reply" class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Đã trả lời</span>
                  </label>
                  <textarea class="form-control flex-grow-1 bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 shadow-sm-hover p-3" 
                            v-model="replyContent" 
                            placeholder="Nhập nội dung cảm ơn hoặc giải quyết khiếu nại của khách hàng tại đây..." 
                            style="min-height: 150px; resize: none;"></textarea>
                  <small class="text-muted d-block mt-2 text-end" :class="{'text-danger': replyContent.length > 1000}">{{ replyContent.length }}/1000 ký tự</small>
                </div>

                <div class="mt-4 pt-3 border-top dark:border-gray-700 text-end">
                  <button type="button" class="btn btn-urban text-white px-5 py-2 fw-bold shadow-sm rounded-pill w-100" @click="saveReply" :disabled="isSavingReply">
                    <span v-if="isSavingReply" class="spinner-border spinner-border-sm me-2"></span> 
                    <i class="bi bi-send-fill me-1" v-else></i> LƯU PHẢN HỒI
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL PHÓNG TO ẢNH (KÈM HIỆU ỨNG BLUR KÍNH) -->
    <div class="modal fade glass-modal" id="imageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
          <div class="modal-header border-0 pb-0 justify-content-end position-absolute top-0 end-0 w-100" style="z-index: 2;">
            <button type="button" class="btn-close btn-close-white m-3" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
          </div>
          <div class="modal-body text-center p-0 position-relative">
            <img :src="zoomedImageUrl" class="img-fluid rounded-4 shadow-lg border border-secondary-subtle dark:border-gray-600" style="max-height: 85vh; object-fit: contain; background-color: var(--color-c-effect);">
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';
import defaultProduct from '@/assets/images/defaults/placeholder.png';

const route = useRoute();
const router = useRouter();

const reviews = ref([]);
const categories = ref([]);
const productsList = ref([]); 
const systemModules = ref([]);
const currentPageLevel = ref(null);

const isLoading = ref(false);
const isFirstLoad = ref(true); 

// Backend Pagination & Filters
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filters = ref({
  search: '',
  rating: '',
  category_id: '',
  product_id: '',
  date_from: '',
  date_to: '',
  replied: '' // yes, no
});

// Trạng thái được chọn ở Tabs
const activeTab = ref('all'); // all, active, hidden, deleted

const selectedReview = ref(null);
const selectedProductOfReview = ref(null);
const replyContent = ref('');
const isSavingReply = ref(false);
let quickViewModalInstance = null;

const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

// Biến lưu trữ Timeout cho Debounce chức năng Tìm Kiếm
let searchTimeout = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const handleImageError = (e) => { e.target.src = defaultAvatar; };
const handleProductImageError = (e) => { e.target.src = defaultProduct; };

const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  return (parts[0].length <= 2 ? parts[0].charAt(0) : parts[0].substring(0, 3)) + '***@' + parts[1];
};

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getLevelColor = (level) => {
  if(!level) return 'bg-secondary';
  const l = parseInt(level);
  switch (l) {
    case 1: return 'bg-danger text-white border-danger shadow-sm';         
    case 2: return 'bg-warning text-dark border-warning';                  
    case 3: return 'bg-info text-dark border-info';                        
    case 4: return 'bg-primary bg-opacity-10 text-primary border-primary'; 
    case 5: return 'bg-success bg-opacity-10 text-success border-success'; 
    default: return 'bg-light dark:bg-gray-700 text-secondary dark:text-gray-300 border-secondary'; 
  }
};

const getFitBadgeClass = (fit) => {
  if (fit === 'Chật') return 'bg-warning text-dark border-warning';
  if (fit === 'Vừa') return 'bg-success text-white border-success';
  if (fit === 'Rộng') return 'bg-danger text-white border-danger';
  return 'bg-secondary text-white';
};

const hasActiveFilters = computed(() => {
  return filters.value.search !== '' || filters.value.rating !== '' || filters.value.replied !== '' || filters.value.category_id !== '' || filters.value.product_id !== '' || filters.value.date_from !== '' || filters.value.date_to !== '';
});

const resetFilters = () => {
  filters.value = { search: '', rating: '', replied: '', category_id: '', product_id: '', date_from: '', date_to: '' };
  activeTab.value = 'all';
  applyFilters();
};

// Hàm tự động gọi khi người dùng thao tác vào các ô select/input (Auto-filter)
const applyFilters = () => {
  pagination.value.current_page = 1;
  fetchData();
};

// Hàm Tìm kiếm tích hợp Debounce để không gọi API liên tục
const onSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500); // Đợi 0.5s sau khi gõ xong mới gọi
};

// Chuyển Tab Trạng Thái
const switchTab = (tab) => {
  activeTab.value = tab;
  applyFilters();
};

// Toggle Phản hồi (Yes/No/All)
const toggleReplied = (val) => {
  filters.value.replied = val;
  applyFilters();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    fetchData();
  }
};

const totalPages = computed(() => pagination.value.last_page || 1);

// ================= FETCH DATA API =================
const fetchData = async (isSilent = false) => {
  if (!isSilent) isLoading.value = true;
  
  try {
    // Gọi các Master Data 1 lần
    if (isFirstLoad.value) {
        const [resModules, resCats, resProds] = await Promise.all([
            axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() }),
            axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
            axios.get('http://127.0.0.1:8000/api/v1/admin/products', { headers: getHeaders() })
        ]);
        
        systemModules.value = resModules.data.data;
        const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_reviews'));
        if (currentModule) currentPageLevel.value = currentModule.required_level;
        
        categories.value = Array.isArray(resCats.data?.data) ? resCats.data.data : [];
        const prods = Array.isArray(resProds.data?.data?.data) ? resProds.data.data.data : [];
        productsList.value = prods.map(p => ({ id: p.id, name: p.name }));
    }

    // Gắn Query Parameters
    const params = new URLSearchParams();
    params.append('page', pagination.value.current_page);
    
    // API backend đang hỗ trợ rating và status
    if(filters.value.rating) params.append('rating', filters.value.rating);
    if(filters.value.search) params.append('search', filters.value.search);
    if(filters.value.category_id) params.append('category_id', filters.value.category_id);
    if(filters.value.product_id) params.append('product_id', filters.value.product_id);
    if(filters.value.date_from) params.append('date_from', filters.value.date_from);
    if(filters.value.date_to) params.append('date_to', filters.value.date_to);
    
    // Gắn status lấy từ Active Tab để gửi cho API
    if(activeTab.value === 'active') params.append('status', 'active');
    if(activeTab.value === 'hidden') params.append('status', 'hidden');
    // Lưu ý: Nếu là Tab Thùng rác (deleted) ta sẽ lọc bằng Frontend dưới đây do Backend API chưa xử lý query 'deleted'

    const resReviews = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews?${params.toString()}`, { headers: getHeaders() });
    let dataList = resReviews.data.data.data || [];
    
    // LỌC Ở FRONTEND NHỮNG CÁI BACKEND CHƯA XỬ LÝ (Tạm thời)
    if (filters.value.replied === 'yes') dataList = dataList.filter(r => r.admin_reply !== null && r.admin_reply !== '');
    if (filters.value.replied === 'no') dataList = dataList.filter(r => r.admin_reply === null || r.admin_reply === '');

    // Xử lý Tab Thùng rác
    if (activeTab.value === 'deleted') {
        dataList = dataList.filter(r => r.deleted_at);
    } else {
        // Mặc định API withTrashed sẽ trả về cả xóa, ta cần loại bỏ các item đã xóa mềm ra khỏi các tab khác
        dataList = dataList.filter(r => !r.deleted_at);
    }

    reviews.value = dataList;
    pagination.value = {
        current_page: resReviews.data.data.current_page,
        last_page: resReviews.data.data.last_page,
        total: resReviews.data.data.total
    };
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.reviews').listen('.ReviewEvent', () => { fetchData(true); });
  }
};

// ================= THAO TÁC CƠ BẢN =================
const toggleStatus = async (review) => {
    const newStatus = review.status === 'active' ? 'hidden' : 'active';
    try {
        await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews/${review.id}/status`, { status: newStatus }, { headers: getHeaders() });
        review.status = newStatus;
        // Nếu đang ở Tab khác Tất cả thì filter lại
        if(activeTab.value === 'active' || activeTab.value === 'hidden') applyFilters();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật hiển thị thành công', showConfirmButton: false, timer: 1500 });
    } catch (e) {
        Swal.fire('Lỗi', 'Không thể cập nhật trạng thái', 'error');
    }
};

const toggleStatusModal = async (e) => {
    const newStatus = e.target.checked ? 'active' : 'hidden';
    try {
        await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews/${selectedReview.value.id}/status`, { status: newStatus }, { headers: getHeaders() });
        selectedReview.value.status = newStatus;
        // Update mảng ngoài
        const target = reviews.value.find(r => r.id === selectedReview.value.id);
        if(target) target.status = newStatus;
        
        // Nếu đang ở Tab khác Tất cả thì reload
        if(activeTab.value === 'active' || activeTab.value === 'hidden') applyFilters();

        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu', showConfirmButton: false, timer: 1500 });
    } catch (err) {
        e.target.checked = !e.target.checked;
        Swal.fire('Lỗi', 'Lỗi kết nối', 'error');
    }
};

const confirmDelete = (id) => {
  Swal.fire({ title: 'Gỡ Đánh giá?', text: `Đánh giá này sẽ bị chuyển vào thùng rác.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xóa', 'error'); }
    }
  });
};

const restoreReview = (id) => {
  Swal.fire({ title: 'Khôi phục đánh giá?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover, #547792)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi khôi phục', 'error'); }
    }
  });
};

// ================= MODAL XỬ LÝ =================
const openDetailModal = async (review) => {
  selectedReview.value = JSON.parse(JSON.stringify(review));
  selectedProductOfReview.value = review.product;
  replyContent.value = review.admin_reply || '';

  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('reviewDetailModal'));
  quickViewModalInstance.show();
};

const saveReply = async () => {
    if (replyContent.value.length > 1000) {
        Swal.fire('Lỗi', 'Nội dung phản hồi không được vượt quá 1000 ký tự', 'warning');
        return;
    }

    isSavingReply.value = true;
    try {
        await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/reviews/${selectedReview.value.id}/reply`, { admin_reply: replyContent.value }, { headers: getHeaders() });
        
        // Update mảng
        const target = reviews.value.find(r => r.id === selectedReview.value.id);
        if(target) target.admin_reply = replyContent.value;
        selectedReview.value.admin_reply = replyContent.value;

        // Nếu người dùng đang lọc theo "Chưa phản hồi", thì sau khi trả lời cần giật lại bảng
        if (filters.value.replied === 'no') applyFilters();

        Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã gửi phản hồi', timer: 1500, showConfirmButton: false });
    } catch (e) {
        Swal.fire('Lỗi', e.response?.data?.message || 'Không thể phản hồi', 'error');
    } finally {
        isSavingReply.value = false;
    }
};

const openImageZoom = (url) => {
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  imageZoomModalInstance.show();
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = ''; document.body.style = '';
  if (searchTimeout) clearTimeout(searchTimeout);
});

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.reviews'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

/* TABS STYLING */
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.hover-danger:hover { color: #dc3545 !important; border-color: #dc3545 !important; }

/* CSS cho ảnh phóng to */
.img-zoomable { cursor: zoom-in; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.img-zoomable:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

/* Class bọc ngoài Modal để làm mờ nền */
.glass-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.4);
}

.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>