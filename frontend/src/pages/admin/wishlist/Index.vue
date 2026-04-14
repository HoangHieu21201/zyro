<!-- File: frontend/src/pages/admin/wishlist/Index.vue -->
<template>
  <div class="wishlist-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang phân tích dữ liệu...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Thống Kê Mức Độ Yêu Thích</h3>
          <p class="text-muted dark:text-gray-400 small mb-0 mt-1 d-none d-md-block">Phân tích xu hướng quan tâm của khách hàng để tối ưu chiến dịch nhập hàng & Remarketing.</p>
        </div>
        <div class="col-md-4 col-12 text-md-end">
          <div class="d-inline-block border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <!-- DASHBOARD STATS CARDS -->
      <div class="row g-3 mb-4 animation-fade-in">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #ff6b6b 0%, #c0392b 100%); color: white;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
              <div>
                <h6 class="fw-bold mb-1 opacity-75 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Tổng Lượt Thả Tim</h6>
                <h2 class="fw-bold mb-0 display-5">{{ stats.total_likes || 0 }}</h2>
              </div>
              <i class="bi bi-heart-fill opacity-50" style="font-size: 3.5rem;"></i>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100 dark:bg-[#1a2533]">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
              <div>
                <h6 class="fw-bold text-muted dark:text-gray-400 mb-2 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Danh Mục Hot Nhất</h6>
                <h4 class="fw-bold text-dark dark:text-white mb-1 text-truncate" style="max-width: 180px;">{{ stats.top_category ? stats.top_category.name : 'Chưa có' }}</h4>
                <span v-if="stats.top_category" class="badge bg-primary bg-opacity-10 text-primary border border-primary shadow-sm"><i class="bi bi-heart-fill me-1"></i> {{ stats.top_category.count }} lượt quan tâm</span>
              </div>
              <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                <i class="bi bi-diagram-3-fill fs-3"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100 dark:bg-[#1a2533]">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
              <div>
                <h6 class="fw-bold text-muted dark:text-gray-400 mb-2 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Thương Hiệu Săn Đón</h6>
                <h4 class="fw-bold text-dark dark:text-white mb-1 text-truncate" style="max-width: 180px;">{{ stats.top_brand ? stats.top_brand.name : 'Chưa có' }}</h4>
                <span v-if="stats.top_brand" class="badge bg-success bg-opacity-10 text-success border border-success shadow-sm"><i class="bi bi-heart-fill me-1"></i> {{ stats.top_brand.count }} lượt quan tâm</span>
              </div>
              <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                <i class="bi bi-tags-fill fs-3"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN TABLE & FILTERS -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all animation-fade-in">
        
        <!-- BỘ LỌC AUTO-FILTER -->
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 p-4 rounded-top-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm Debounce -->
            <div class="col-xl-3 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm sản phẩm</label>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-0" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Nhập tên sản phẩm...">
            </div>
            
            <div class="col-xl-2 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-diagram-3 me-1"></i>Danh mục</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-0" 
                      v-model="filters.category_id" @change="applyFilters">
                <option value="">Tất cả</option>
                <option v-for="cat in filterData.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div class="col-xl-2 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-tag me-1"></i>Thương hiệu</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-0" 
                      v-model="filters.brand_id" @change="applyFilters">
                <option value="">Tất cả</option>
                <option v-for="b in filterData.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
            </div>

            <!-- Sắp xếp theo Lượt yêu thích -->
            <div class="col-xl-3 col-md-5">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-sort-down me-1"></i>Sắp xếp theo</label>
              <div class="d-flex bg-light dark:bg-[#212529] rounded-3 p-1 shadow-sm-hover border dark:border-gray-700">
                <button class="btn btn-sm flex-grow-1 fw-bold rounded-2 transition-all border-0" :class="filters.sort === 'desc' ? 'btn-urban text-white shadow-sm' : 'text-muted'" @click="changeSort('desc')">
                  Yêu thích nhiều
                </button>
                <button class="btn btn-sm flex-grow-1 fw-bold rounded-2 transition-all border-0" :class="filters.sort === 'asc' ? 'btn-urban text-white shadow-sm' : 'text-muted'" @click="changeSort('asc')">
                  Yêu thích ít
                </button>
              </div>
            </div>

            <div class="col-xl-2 col-md-3 text-end text-xl-start">
              <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all w-100" @click="resetFilters" v-if="hasActiveFilters">
                 <i class="bi bi-x-circle me-1"></i>Xóa lọc
              </button>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0 position-relative">
          <!-- Màng phủ mờ khi tải lại API -->
          <div v-if="isLoading && !isFirstLoad" class="position-absolute top-0 start-0 w-100 h-100 rounded-bottom-4 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.6;">
             <span class="spinner-border text-urban" style="width: 3rem; height: 3rem;"></span>
          </div>

          <!-- BẢNG DỮ LIỆU XẾP HẠNG (PC) -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 10%;">Hạng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 45%;">Sản phẩm</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Phân loại</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 20%;">Số lượt Thả tim</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="leaderboard.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu phù hợp.
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in leaderboard" :key="item.product_id" class="cursor-pointer hover-bg-effect" @click="openQuickView(item.product, item.likes_count)">
                  <!-- Rank -->
                  <td class="px-4 py-3 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-5 shadow-sm" 
                         :class="getRankClass(getRealIndex(index))" 
                         style="width: 40px; height: 40px;">
                      {{ getRealIndex(index) }}
                    </div>
                  </td>
                  
                  <!-- Sản phẩm -->
                  <td class="px-4">
                    <div class="d-flex align-items-center">
                      <img :src="getImageUrl(item.product?.thumbnail_image)" @error="handleProductError" class="rounded object-fit-cover me-3 border shadow-sm dark:border-gray-600" style="width: 55px; height: 55px;">
                      <div class="overflow-hidden">
                        <h6 class="mb-1 fw-bold text-dark dark:text-gray-200 text-truncate" :title="item.product?.name">{{ item.product?.name || 'Sản phẩm đã bị xóa' }}</h6>
                        <div class="d-flex align-items-center gap-2">
                           <span class="text-danger fw-bold" style="font-size: 0.8rem;">{{ formatCurrency(item.product?.base_price) }}</span>
                           <span v-if="item.product?.deleted_at" class="badge bg-secondary" style="font-size: 0.6rem;">Đã bị xóa</span>
                           <span v-else-if="item.product?.status === 'hidden'" class="badge bg-warning text-dark" style="font-size: 0.6rem;">Đang ẩn</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Phân loại -->
                  <td class="px-4 text-muted dark:text-gray-400 small">
                    <div class="mb-1 text-truncate"><i class="bi bi-diagram-3 text-urban me-1"></i>{{ item.product?.category?.name || 'Không' }}</div>
                    <div class="text-truncate"><i class="bi bi-tag text-urban me-1"></i>{{ item.product?.brand?.name || 'Không' }}</div>
                  </td>

                  <!-- Likes -->
                  <td class="px-4 text-center">
                    <div class="d-inline-flex align-items-center px-3 py-1 bg-danger bg-opacity-10 text-danger rounded-pill border border-danger border-opacity-25 fw-bold fs-5">
                      <i class="bi bi-heart-fill me-2"></i> {{ item.likes_count }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="leaderboard.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="(item, index) in leaderboard" :key="item.product_id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529] cursor-pointer" @click="openQuickView(item.product, item.likes_count)">
                <div class="card-body p-3 position-relative overflow-hidden">
                  
                  <!-- Rank Ribbon Mobile -->
                  <div class="position-absolute top-0 start-0 text-white fw-bold px-2 py-1" style="border-bottom-right-radius: 10px; font-size: 0.8rem; z-index: 2;" :class="getRankBgColor(getRealIndex(index))">
                    #{{ getRealIndex(index) }}
                  </div>

                  <div class="d-flex align-items-center mt-3 mb-2">
                     <img :src="getImageUrl(item.product?.thumbnail_image)" @error="handleProductError" class="rounded border object-fit-cover me-3 shadow-sm dark:border-gray-600" style="width: 60px; height: 60px;">
                     <div class="overflow-hidden w-100">
                       <div class="fw-bold text-dark dark:text-gray-200 text-truncate mb-1" style="font-size: 0.9rem;">{{ item.product?.name || 'Sản phẩm đã xóa' }}</div>
                       <span class="text-danger fw-bold d-block" style="font-size: 0.8rem;">{{ formatCurrency(item.product?.base_price) }}</span>
                     </div>
                  </div>

                  <div class="d-flex justify-content-between align-items-end pt-2 border-top dark:border-gray-700 mt-2">
                    <div class="small text-muted text-truncate" style="max-width: 60%;">
                      <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1">{{ item.product?.category?.name || 'Không' }}</span>
                    </div>
                    <div class="text-danger fw-bold fs-5">
                      <i class="bi bi-heart-fill me-1"></i> {{ item.likes_count }}
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="pagination.last_page > 1">
        <span class="text-muted dark:text-gray-400 small">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button></li>
            
            <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }">
              <button class="page-link dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="changePage(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- ĐÃ NÂNG CẤP: POPUP QUICK VIEW FULL THÔNG TIN SẢN PHẨM    -->
    <!-- ======================================================== -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center w-100">
            <h5 class="fw-bold text-dark dark:text-white mb-0"><i class="bi bi-box-seam text-urban me-2"></i>Chi Tiết Sản Phẩm Nhận Tim</h5>
            <!-- Nút Badge Lượt Thích ở góc trên phải -->
            <div class="d-flex align-items-center me-4 pe-2">
              <span class="badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm border border-danger border-opacity-50">
                <i class="bi bi-heart-fill me-1"></i> {{ selectedProduct?.current_likes || 0 }} lượt thích
              </span>
            </div>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3 dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4" v-if="selectedProduct">
            
            <!-- NỬA TRÊN: THÔNG TIN & HÌNH ẢNH -->
            <div class="row mb-4">
              <!-- Avatar & Status -->
              <div class="col-md-3 text-center mb-4 mb-md-0">
                <img :src="getImageUrl(selectedProduct.thumbnail_image)" @error="handleProductError" 
                     class="rounded-4 shadow-sm border border-2 border-light dark:border-gray-600 object-fit-cover mb-3 img-zoomable" 
                     style="width: 100%; max-height: 200px;" @click="openImageZoom(getImageUrl(selectedProduct.thumbnail_image))">
                <h5 class="fw-bold mb-1 dark:text-white">{{ selectedProduct.name }}</h5>
                <p class="text-muted dark:text-gray-400 small mb-3 font-monospace">/{{ selectedProduct.slug }}</p>
                <span class="badge px-3 py-2 rounded-pill shadow-sm" :class="selectedProduct.deleted_at ? 'bg-secondary text-white' : (selectedProduct.status === 'published' ? 'bg-success text-white' : (selectedProduct.status === 'draft' ? 'bg-secondary text-white' : 'bg-warning text-dark'))">
                  {{ selectedProduct.deleted_at ? 'Đã xóa' : (selectedProduct.status === 'published' ? 'Đang xuất bản' : (selectedProduct.status === 'draft' ? 'Bản nháp' : 'Đang ẩn')) }}
                </span>
              </div>

              <!-- Chi tiết Grid (Đã Fix N/A -> Không và Cập nhật đúng Icons) -->
              <div class="col-md-9">
                <div class="bg-light dark:bg-[#212529] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100 d-flex flex-column">
                  <div class="row g-3 mb-3">
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-diagram-3 text-urban me-1"></i>Danh mục:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.category?.name || 'Không' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-tag text-urban me-1"></i>Thương hiệu:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.brand?.name || 'Không' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-cash-stack text-urban me-1"></i>Giá cơ sở:</span>
                       <span class="fw-bold text-danger dark:text-red-400">{{ formatCurrency(selectedProduct.base_price) }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-gender-ambiguous text-urban me-1"></i>Giới tính:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.gender || 'Unisex' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-scissors text-urban me-1"></i>Kiểu dáng (Fit):</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.fit_type || 'Không' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <!-- Đã đổi Icon tia sét để tránh nhầm lẫn với đánh giá sao -->
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Nổi bật (Featured):</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.is_featured ? 'Có' : 'Không' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-star-fill text-warning me-1"></i>Đánh giá:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">
                          {{ selectedProduct.rating_avg || '0.0' }} <i class="bi bi-star-fill text-warning" style="font-size: 0.75rem;"></i>
                          <span class="text-muted fw-normal small ms-1">({{ selectedProduct.review_count || 0 }} lượt)</span>
                       </span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-cart-check-fill text-success me-1"></i>Đã bán:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.sales_count || 0 }} sản phẩm</span>
                    </div>
                    <div class="col-12 mt-2">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-info-circle text-urban me-1"></i>Bảo quản:</span>
                       <span class="fw-bold text-dark dark:text-gray-200 fst-italic">{{ selectedProduct.care_instructions || 'Chưa cập nhật hướng dẫn' }}</span>
                    </div>
                  </div>

                  <!-- Thư viện ảnh -->
                  <div class="mt-auto pt-3 border-top dark:border-gray-700">
                     <span class="text-muted dark:text-gray-400 fw-semibold d-block mb-2">Thư viện ảnh ({{ selectedProduct.images?.length || 0 }}):</span>
                     <div v-if="!selectedProduct.images || selectedProduct.images.length === 0" class="text-muted fst-italic">Không có ảnh thư viện.</div>
                     <div v-else class="d-flex gap-2 overflow-auto custom-scrollbar-x pb-2">
                        <img v-for="img in selectedProduct.images" :key="img.id" :src="getImageUrl(img.image_url)" 
                             class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm flex-shrink-0 img-zoomable" 
                             style="width: 70px; height: 70px;"
                             @click="openImageZoom(getImageUrl(img.image_url))">
                     </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- NỬA DƯỚI: LƯỚI BIẾN THỂ -->
            <div class="row">
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="text-dark dark:text-white fw-bold fs-5"><i class="bi bi-layers text-urban me-2"></i>Biến Thể Tồn Kho ({{ selectedProduct.variants?.length || 0 }})</span>
                  <span v-if="isQuickViewLoading" class="spinner-border spinner-border-sm text-urban"></span>
                  <button v-if="!selectedProduct.deleted_at" type="button" @click="goToProductDetail(selectedProduct.id)" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-bold shadow-sm">
                    Quản lý kho chi tiết <i class="bi bi-arrow-right ms-1"></i>
                  </button>
                </div>
                
                <div v-if="!isQuickViewLoading && (!selectedProduct.variants || selectedProduct.variants.length === 0)" class="text-muted small fst-italic bg-light dark:bg-[#212529] p-4 text-center rounded-4 border dark:border-gray-700">
                  Sản phẩm này chưa có biến thể kho nào.
                </div>
                
                <div v-else-if="!isQuickViewLoading" class="table-responsive custom-scrollbar-y rounded-3 border dark:border-gray-700" style="max-height: 300px;">
                  <table class="table table-bordered table-hover mb-0 align-middle small text-center dark:border-gray-700">
                    <thead class="bg-light dark:bg-[#2b3035] sticky-top" style="z-index: 10;">
                      <tr>
                        <th class="dark:text-gray-300">Ảnh</th>
                        <th class="dark:text-gray-300">Mã SKU</th>
                        <th class="dark:text-gray-300 text-start">Thuộc tính</th>
                        <th class="text-urban">Giá Bán</th>
                        <th class="text-danger">Khuyến Mãi</th>
                        <th class="dark:text-gray-300">Tồn kho</th>
                      </tr>
                    </thead>
                    <tbody class="dark:bg-[#1a2533]">
                      <tr v-for="v in selectedProduct.variants" :key="v.id">
                        <td>
                          <img :src="getImageUrl(v.image_url)" class="rounded border object-fit-cover dark:border-gray-600 mx-auto img-zoomable" style="width: 35px; height: 35px;" @error="handleProductError" @click="openImageZoom(getImageUrl(v.image_url))">
                        </td>
                        <td class="font-monospace fw-bold text-muted dark:text-gray-400">
                           {{ v.sku }} <span v-if="v.is_default" class="badge bg-danger ms-1" style="font-size: 0.55rem;">Mặc định</span>
                        </td>
                        <td class="text-start">
                          <span v-if="!v.attribute_values || v.attribute_values.length === 0" class="text-muted fst-italic">Không có</span>
                          <span v-else v-for="(attr, i) in v.attribute_values" :key="i" class="badge bg-light text-dark dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 me-1 mb-1 shadow-sm">{{ attr.value }}</span>
                        </td>
                        <td class="fw-bold text-urban">{{ formatCurrency(v.price) }}</td>
                        <td class="fw-bold text-danger">{{ v.promotional_price > 0 ? formatCurrency(v.promotional_price) : '-' }}</td>
                        <td class="fw-bold">
                           <span :class="v.stock_quantity <= 5 ? 'text-danger' : 'text-dark dark:text-white'">{{ v.stock_quantity }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
import axios from 'axios';
import defaultProduct from '@/assets/images/defaults/placeholder.png';

const route = useRoute();
const router = useRouter();

const leaderboard = ref([]); // Bảng xếp hạng sản phẩm
const stats = ref({ total_likes: 0, top_category: null, top_brand: null });
const filterData = ref({ categories: [], brands: [] }); // Dữ liệu cho Dropdown

const systemModules = ref([]);
const currentPageLevel = ref(null);

const isLoading = ref(false);
const isFirstLoad = ref(true); 
const isQuickViewLoading = ref(false);

// Backend Pagination & Filters
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filters = ref({
  search: '',
  category_id: '',
  brand_id: '',
  sort: 'desc' // desc = Yêu thích nhiều, asc = Ít
});

const selectedProduct = ref(null);
let quickViewModalInstance = null;
let searchTimeout = null;

const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultProduct;
const handleProductError = (e) => { e.target.src = defaultProduct; };

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white border-danger', 2: 'bg-warning text-dark border-warning', 3: 'bg-info text-dark border-info', 4: 'bg-primary bg-opacity-10 text-primary border-primary', 5: 'bg-success bg-opacity-10 text-success border-success' };
  return map[level] || 'bg-light text-secondary border-secondary'; 
};

// UI Helper: Lấy thứ hạng thật kể cả khi qua trang 2, trang 3
const getRealIndex = (indexOnPage) => {
  return (pagination.value.current_page - 1) * 12 + indexOnPage + 1;
};

// UI Helper: Màu sắc cho các vị trí Top 1, 2, 3
const getRankClass = (rank) => {
  if (rank === 1) return 'bg-warning text-white border border-warning shadow'; // Vàng
  if (rank === 2) return 'bg-secondary text-white border border-secondary shadow'; // Bạc
  if (rank === 3) return 'bg-orange text-white border border-orange shadow'; // Đồng (Màu cam)
  return 'bg-light text-secondary dark:bg-[#2b3035] dark:text-gray-400 border border-secondary-subtle dark:border-gray-600';
};
const getRankBgColor = (rank) => {
  if (rank === 1) return 'bg-warning';
  if (rank === 2) return 'bg-secondary';
  if (rank === 3) return 'bg-orange';
  return 'bg-dark';
};

const hasActiveFilters = computed(() => {
  return filters.value.search !== '' || filters.value.category_id !== '' || filters.value.brand_id !== '';
});

const resetFilters = () => {
  filters.value = { search: '', category_id: '', brand_id: '', sort: 'desc' };
  applyFilters();
};

const applyFilters = () => {
  pagination.value.current_page = 1;
  fetchData();
};

const onSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { applyFilters(); }, 500); 
};

const changeSort = (sortVal) => {
  filters.value.sort = sortVal;
  applyFilters();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    fetchData();
  }
};

// ================= FETCH DATA API =================
const fetchFilterData = async () => {
  try {
     const [resCats, resBrands] = await Promise.all([
        axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
        axios.get('http://127.0.0.1:8000/api/v1/admin/brands', { headers: getHeaders() })
     ]);
     // ĐÃ CẬP NHẬT: Lọc trạng thái Active/Published để đổ vào Dropdown
     filterData.value.categories = Array.isArray(resCats.data?.data) ? resCats.data.data.filter(c => !c.deleted_at && (c.status === 'active' || c.status === 'published')) : [];
     filterData.value.brands = Array.isArray(resBrands.data?.data) ? resBrands.data.data.filter(b => !b.deleted_at && b.status === 'active') : [];
  } catch(e) {}
};

const fetchData = async (isSilent = false) => {
  if (!isSilent) isLoading.value = true;
  
  try {
    if (isFirstLoad.value) {
        fetchFilterData();
        const resModules = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
        systemModules.value = resModules.data.data;
        const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_wishlists'));
        if (currentModule) currentPageLevel.value = currentModule.required_level;
    }

    const params = new URLSearchParams();
    params.append('page', pagination.value.current_page);
    params.append('sort', filters.value.sort);
    if(filters.value.category_id) params.append('category_id', filters.value.category_id);
    if(filters.value.brand_id) params.append('brand_id', filters.value.brand_id);
    if(filters.value.search) params.append('search', filters.value.search);
    
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/wishlists?${params.toString()}`, { headers: getHeaders() });
    
    // API Cập nhật trả về stats và data
    stats.value = res.data.stats;
    leaderboard.value = res.data.data.data || [];
    
    pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        total: res.data.data.total
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
    window.Echo.private('admin.wishlists').listen('.WishlistEvent', () => { fetchData(true); });
  }
};

// ĐÃ CẬP NHẬT LẠI QUICK VIEW TỪ MODULE SẢN PHẨM SANG
const openQuickView = async (product, likesCount) => {
  if(!product) return;
  // Load tạm dữ liệu cơ bản để hiện ngay lập tức
  selectedProduct.value = { ...product, current_likes: likesCount };
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();

  // Call API gọi Full data của sản phẩm
  isQuickViewLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${product.id}`, { headers: getHeaders() });
    selectedProduct.value = { ...res.data.data, current_likes: likesCount }; // Giữ lại current_likes
  } catch (err) {
    console.error("Lỗi tải chi tiết", err);
  } finally { isQuickViewLoading.value = false; }
};

const openImageZoom = (url) => {
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  imageZoomModalInstance.show();
};

const goToProductDetail = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = ''; document.body.style = '';
    router.push({ name: 'admin-products-edit', params: { id } });
  }, 300);
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = ''; document.body.style = '';
  if (searchTimeout) clearTimeout(searchTimeout);
});

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.wishlists'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.bg-orange { background-color: #e67e22 !important; }
.border-orange { border-color: #e67e22 !important; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.hover-danger:hover { color: #dc3545 !important; border-color: #dc3545 !important; }
.hover-bg-effect:hover { background-color: var(--color-c-effect, #ebf1f5); }
html.dark .hover-bg-effect:hover { background-color: rgba(255,255,255,0.05); }

/* CSS cho ảnh phóng to */
.img-zoomable { cursor: zoom-in; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.img-zoomable:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

/* Class bọc ngoài Modal để làm mờ nền */
.glass-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.4);
}

/* Custom Scrollbar cho Table trong Quick View */
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>