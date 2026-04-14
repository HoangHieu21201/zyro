<!-- File: frontend/src/pages/admin/product/Index.vue -->
<template>
  <div class="product-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải kho sản phẩm...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản Lý Sản Phẩm</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
          <router-link :to="{ name: 'admin-products-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all">
            <i class="bi bi-plus-circle-fill me-1"></i> Thêm Sản Phẩm
          </router-link>
        </div>
      </div>

      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ products.filter(c => !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'published' }" @click.prevent="switchTab('published')">
              <i class="bi bi-eye-fill me-2 text-success"></i> Đang xuất bản
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'published'}">{{ products.filter(c => c.status === 'published' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'draft' }" @click.prevent="switchTab('draft')">
              <i class="bi bi-file-earmark-text me-2 text-secondary"></i> Bản nháp
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'draft'}">{{ products.filter(c => c.status === 'draft' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-warning"></i> Đang ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'hidden'}">{{ products.filter(c => c.status === 'hidden' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ products.filter(c => c.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-3 px-4 rounded-top-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi bi-box-seam me-2 text-urban fs-5"></i> Kho Sản Phẩm
              <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
            <div class="search-box position-relative" style="width: 280px; max-width: 100%;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, mã sản phẩm...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>

          <div class="d-flex align-items-center flex-wrap gap-2 bg-light dark:bg-[#212529] p-2 rounded-3 border dark:border-gray-700">
            <div class="d-flex align-items-center small fw-semibold text-muted me-2"><i class="bi bi-funnel me-1"></i> Bộ lọc:</div>
            
            <!-- ĐÃ NÂNG CẤP: DROPDOWN ĐA CẤP DỄ NHÌN HƠN -->
            <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 180px;" v-model="filterCategory">
              <option value="">Tất cả Danh mục</option>
              <option v-for="c in hierarchicalCategories" :key="c.id" :value="c.id" :class="{'fw-bold text-dark dark:text-white': c.level === 0}">
                {{ c.displayName }}
              </option>
            </select>
            
            <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 160px;" v-model="filterBrand">
              <option value="">Tất cả Thương hiệu</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            
            <div class="input-group input-group-sm shadow-sm" style="width: 280px;">
              <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-muted"><i class="bi bi-calendar-range"></i></span>
              <input type="date" class="form-control border-0 bg-white dark:bg-[#1a2533] dark:text-white" v-model="filterDateFrom" title="Từ ngày">
              <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-muted">-</span>
              <input type="date" class="form-control border-0 bg-white dark:bg-[#1a2533] dark:text-white" v-model="filterDateTo" title="Đến ngày">
            </div>
            
            <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 shadow-sm" @click="clearFilters" v-if="hasActiveFilters">
              <i class="bi bi-x-circle"></i> Xóa lọc
            </button>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 35%;">Sản phẩm</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 15%;">Phân loại</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Thông tin phụ</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="displayProducts.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu phù hợp.
                  </td>
                </tr>
                <tr v-else v-for="product in displayProducts" :key="product.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': product.deleted_at || product.status === 'hidden'}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getImageUrl(product.thumbnail_image)" @error="handleImageError" 
                           class="rounded-3 object-fit-cover me-3 border shadow-sm dark:border-gray-600 img-zoomable" 
                           style="width: 60px; height: 60px;" 
                           @click.stop="openImageZoom(getImageUrl(product.thumbnail_image))">
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate" :title="product.name">
                          <i v-if="product.is_featured" class="bi bi-star-fill text-warning me-1" title="Nổi bật"></i>
                          {{ product.name }}
                        </h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace"><i class="bi bi-link-45deg"></i> {{ product.slug }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4">
                    <div class="text-dark dark:text-gray-300 small fw-bold text-truncate mb-1"><i class="bi bi-diagram-3 text-urban me-1"></i>{{ product.category?.name || 'N/A' }}</div>
                    <div class="text-muted dark:text-gray-400 small text-truncate"><i class="bi bi-tag text-urban me-1"></i>{{ product.brand?.name || 'No Brand' }}</div>
                  </td>
                  
                  <td class="px-4">
                    <div class="text-dark dark:text-gray-300 small mb-1"><span class="text-muted">Giá từ:</span> <strong class="text-danger dark:text-red-400">{{ formatCurrency(product.base_price) }}</strong></div>
                    <div class="text-muted dark:text-gray-400 small"><i class="bi bi-layers text-urban me-1"></i>{{ product.variants_count || 0 }} biến thể kho</div>
                  </td>

                  <td class="px-4 text-center">
                    <span v-if="product.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200" 
                              style="width: 115px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(product.localStatus || product.status)"
                              v-model="product.localStatus"
                              @change="checkStatusChange(product)"
                              :disabled="product.isUpdatingStatus">
                        <option value="published">Xuất bản</option>
                        <option value="draft">Bản nháp</option>
                        <option value="hidden">Đang ẩn</option>
                      </select>
                      
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="product.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;" role="status"></div>
                        <template v-else-if="product.isStatusChanged">
                          <button @click="saveProductStatus(product)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(product)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border" title="Xem nhanh" @click="openQuickView(product)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!product.deleted_at">
                        <router-link :to="{ name: 'admin-products-edit', params: { id: product.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Cập nhật">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(product.id, product.name)" title="Đưa vào thùng rác">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreProduct(product.id)" title="Khôi phục">
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
            <div v-if="displayProducts.length === 0" class="text-center py-5 text-muted">Không có dữ liệu phù hợp.</div>
            <div v-else v-for="product in displayProducts" :key="product.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': product.deleted_at || product.status === 'hidden'}">
              <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3 border-bottom dark:border-gray-700 pb-3">
                  <img :src="getImageUrl(product.thumbnail_image)" @error="handleImageError" 
                       class="rounded-3 object-fit-cover me-3 border shadow-sm dark:border-gray-600 img-zoomable" 
                       style="width: 60px; height: 60px;"
                       @click.stop="openImageZoom(getImageUrl(product.thumbnail_image))">
                  <div class="overflow-hidden w-100">
                    <h6 class="mb-1 fw-bold dark:text-gray-200 text-truncate">
                      <i v-if="product.is_featured" class="bi bi-star-fill text-warning me-1"></i>{{ product.name }}
                    </h6>
                    <div class="text-muted dark:text-gray-400 small text-truncate"><i class="bi bi-diagram-3 me-1"></i>{{ product.category?.name || 'N/A' }}</div>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                   <strong class="text-danger dark:text-red-400 fs-5">{{ formatCurrency(product.base_price) }}</strong>
                   
                   <span v-if="product.deleted_at" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                   <span v-else-if="product.status === 'published'" class="text-success small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Xuất bản</span>
                   <span v-else-if="product.status === 'draft'" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Bản nháp</span>
                   <span v-else class="text-warning small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Đang ẩn</span>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border flex-grow-1 shadow-sm" @click="openQuickView(product)"><i class="bi bi-eye"></i></button>
                  <template v-if="!product.deleted_at">
                    <router-link :to="{ name: 'admin-products-edit', params: { id: product.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm"><i class="bi bi-pencil-square"></i></router-link>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border flex-grow-1 shadow-sm" @click="confirmDelete(product.id, product.name)"><i class="bi bi-trash"></i></button>
                  </template>
                  <template v-else>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold shadow-sm" @click="restoreProduct(product.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
        <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedProducts.length) }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage--"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
              <button class="page-link dark:border-gray-600" :class="currentPage === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="currentPage = page">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage++"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- POPUP QUICK VIEW (ĐÃ QUY HOẠCH LẠI BỐ CỤC TRÊN/DƯỚI) -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-box-seam text-urban me-2"></i>Chi Tiết Sản Phẩm</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4" v-if="selectedProduct">
            
            <!-- NỬA TRÊN: THÔNG TIN & HÌNH ẢNH -->
            <div class="row mb-4">
              <!-- Avatar & Status -->
              <div class="col-md-3 text-center mb-4 mb-md-0">
                <img :src="getImageUrl(selectedProduct.thumbnail_image)" @error="handleImageError" 
                     class="rounded-4 shadow-sm border border-2 border-light dark:border-gray-600 object-fit-cover mb-3 img-zoomable" 
                     style="width: 100%; max-height: 200px;" @click="openImageZoom(getImageUrl(selectedProduct.thumbnail_image))">
                <h5 class="fw-bold mb-1 dark:text-white">{{ selectedProduct.name }}</h5>
                <p class="text-muted dark:text-gray-400 small mb-3 font-monospace">/{{ selectedProduct.slug }}</p>
                <span class="badge px-3 py-2 rounded-pill shadow-sm" :class="selectedProduct.deleted_at ? 'bg-secondary text-white' : (selectedProduct.status === 'published' ? 'bg-success text-white' : (selectedProduct.status === 'draft' ? 'bg-secondary text-white' : 'bg-warning text-dark'))">
                  {{ selectedProduct.deleted_at ? 'Đã xóa' : (selectedProduct.status === 'published' ? 'Đang xuất bản' : (selectedProduct.status === 'draft' ? 'Bản nháp' : 'Đang ẩn')) }}
                </span>
              </div>

              <!-- Chi tiết Grid -->
              <div class="col-md-9">
                <div class="bg-light dark:bg-[#212529] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100 d-flex flex-column">
                  <div class="row g-3 mb-3">
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-diagram-3 text-urban me-1"></i>Danh mục:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.category?.name || 'N/A' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-tag text-urban me-1"></i>Thương hiệu:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.brand?.name || 'N/A' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-cash-stack text-urban me-1"></i>Giá cơ sở tham khảo:</span>
                       <span class="fw-bold text-danger dark:text-red-400">{{ formatCurrency(selectedProduct.base_price) }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-gender-ambiguous text-urban me-1"></i>Giới tính:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.gender || 'Unisex' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-scissors text-urban me-1"></i>Kiểu dáng (Fit):</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.fit_type || 'N/A' }}</span>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-star-fill text-warning me-1"></i>Nổi bật:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedProduct.is_featured ? 'Có' : 'Không' }}</span>
                    </div>
                    <div class="col-12">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-info-circle text-urban me-1"></i>Bảo quản:</span>
                       <span class="fw-bold text-dark dark:text-gray-200 fst-italic">{{ selectedProduct.care_instructions || 'Không có hướng dẫn bảo quản' }}</span>
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
                  <span class="text-dark dark:text-white fw-bold fs-5"><i class="bi bi-layers text-urban me-2"></i>Danh Sách Biến Thể Kho ({{ selectedProduct.variants?.length || 0 }})</span>
                  <span v-if="isQuickViewLoading" class="spinner-border spinner-border-sm text-urban"></span>
                  <button v-if="!selectedProduct.deleted_at" type="button" @click="goToEditProduct(selectedProduct.id)" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-pencil-square me-1"></i> Sửa chi tiết
                  </button>
                </div>
                
                <div v-if="!isQuickViewLoading && (!selectedProduct.variants || selectedProduct.variants.length === 0)" class="text-muted small fst-italic bg-light dark:bg-[#212529] p-4 text-center rounded-4 border dark:border-gray-700">
                  Sản phẩm này chưa có biến thể kho nào.
                </div>
                
                <div v-else-if="!isQuickViewLoading" class="table-responsive custom-scrollbar-y rounded-3 border dark:border-gray-700" style="max-height: 350px;">
                  <table class="table table-bordered table-hover mb-0 align-middle small text-center dark:border-gray-700">
                    <thead class="bg-light dark:bg-[#2b3035] sticky-top" style="z-index: 10;">
                      <tr>
                        <th class="dark:text-gray-300">Ảnh</th>
                        <th class="dark:text-gray-300">Mã SKU</th>
                        <th class="dark:text-gray-300 text-start">Phân loại (Thuộc tính)</th>
                        <th class="text-secondary">Giá Nhập (Vốn)</th>
                        <th class="text-urban">Giá Bán</th>
                        <th class="text-danger">Khuyến Mãi</th>
                        <th class="dark:text-gray-300">Tồn kho</th>
                      </tr>
                    </thead>
                    <tbody class="dark:bg-[#1a2533]">
                      <tr v-for="v in selectedProduct.variants" :key="v.id">
                        <td>
                          <img :src="getImageUrl(v.image_url)" class="rounded border object-fit-cover dark:border-gray-600 mx-auto img-zoomable" style="width: 35px; height: 35px;" @error="handleImageError" @click="openImageZoom(getImageUrl(v.image_url))">
                        </td>
                        <td class="font-monospace fw-bold text-muted dark:text-gray-400">
                           {{ v.sku }} <span v-if="v.is_default" class="badge bg-danger ms-1" style="font-size: 0.55rem;">Mặc định</span>
                        </td>
                        <td class="text-start">
                          <span v-if="!v.attribute_values || v.attribute_values.length === 0" class="text-muted fst-italic">Không có</span>
                          <span v-else v-for="(attr, i) in v.attribute_values" :key="i" class="badge bg-light text-dark dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 me-1 mb-1 shadow-sm">{{ attr.value }}</span>
                        </td>
                        <td class="fw-bold text-secondary">{{ formatCurrency(v.cost_price) }}</td>
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
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/placeholder.png'; 

const route = useRoute();
const router = useRouter();

const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const systemModules = ref([]);
const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 
const isRefreshing = ref(false);
const isQuickViewLoading = ref(false);

// Bộ Lọc
const searchQuery = ref('');
const activeTab = ref('all');
const filterCategory = ref('');
const filterBrand = ref('');
const filterDateFrom = ref('');
const filterDateTo = ref('');

const currentPage = ref(1);
const itemsPerPage = 10;

const selectedProduct = ref(null);
let quickViewModalInstance = null;

// Biến Zoom ảnh
const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
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

// ĐÃ THÊM LẠI LOGIC TẠO CÂY ĐA CẤP (Nesting) CHO SELECT CATEGORY ĐỂ GIAO DIỆN ĐẸP HƠN
const hierarchicalCategories = computed(() => {
  const buildTree = (parentId = null, level = 0) => {
    let res = [];
    const children = categories.value.filter(c => (c.parent_id || null) === (parentId || null));
    children.forEach(child => {
      res.push({
        ...child,
        displayName: (level > 0 ? '\u00A0\u00A0\u00A0\u00A0'.repeat(level) + '↳ ' : '') + child.name,
        level: level
      });
      res = res.concat(buildTree(child.id, level + 1));
    });
    return res;
  };
  return buildTree(null);
});

// Hàm giúp lấy tất cả ID của 1 danh mục và các con, cháu của nó (Recursive)
const getAllCategoryIds = (id) => {
  let ids = [id];
  const children = categories.value.filter(c => c.parent_id === id);
  children.forEach(child => {
    ids = ids.concat(getAllCategoryIds(child.id));
  });
  return ids;
};

const fetchData = async (isSilent = false) => {
  if (isSilent) isRefreshing.value = true;
  else if (!isFirstLoad.value) isLoading.value = true;
  
  try {
    const [resProducts, resModules, resCats, resBrands] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/products', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/brands', { headers: getHeaders() })
    ]);

    const payloadData = resProducts.data.data;
    const rawData = Array.isArray(payloadData?.data) ? payloadData.data : (Array.isArray(payloadData) ? payloadData : []);
    
    products.value = rawData.map(p => ({
      ...p, localStatus: p.status, isStatusChanged: false, isUpdatingStatus: false
    }));
    
    const allCats = Array.isArray(resCats.data?.data) ? resCats.data.data : [];
    categories.value = allCats.filter(c => (c.status === 'active' || c.status === 'published') && !c.deleted_at);
    
    const allBrands = Array.isArray(resBrands.data?.data) ? resBrands.data.data : [];
    brands.value = allBrands.filter(b => b.status === 'active' && !b.deleted_at);

    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_products'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
    isRefreshing.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.products').listen('.ProductEvent', () => { fetchData(true); });
  }
};

const getStatusSelectClass = (status) => {
  const map = { 
    'published': 'text-success border-success bg-success bg-opacity-10', 
    'draft': 'text-secondary border-secondary bg-secondary bg-opacity-10',
    'hidden': 'text-warning border-warning bg-warning bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const checkStatusChange = (p) => { p.isStatusChanged = (p.localStatus !== p.status); };
const cancelStatusChange = (p) => { p.localStatus = p.status; p.isStatusChanged = false; };

const saveProductStatus = async (p) => {
  p.isUpdatingStatus = true;
  try {
    await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${p.id}/status`, { status: p.localStatus }, { headers: getHeaders() });
    p.status = p.localStatus; 
    p.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật trạng thái thành công', showConfirmButton: false, timer: 1500 });
  } catch (error) { 
    cancelStatusChange(p); 
    Swal.fire('Lỗi', error.response?.data?.message || 'Lỗi cập nhật trạng thái', 'error');
  } finally { p.isUpdatingStatus = false; }
};

const switchTab = (tabId) => { activeTab.value = tabId; currentPage.value = 1; };

const hasActiveFilters = computed(() => {
  return filterCategory.value !== '' || filterBrand.value !== '' || filterDateFrom.value !== '' || filterDateTo.value !== '';
});

const clearFilters = () => {
  filterCategory.value = '';
  filterBrand.value = '';
  filterDateFrom.value = '';
  filterDateTo.value = '';
  currentPage.value = 1;
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const openQuickView = async (p) => {
  selectedProduct.value = p;
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
  
  isQuickViewLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${p.id}`, { headers: getHeaders() });
    selectedProduct.value = res.data.data;
  } catch (err) {
    console.error("Lỗi tải chi tiết", err);
  } finally { isQuickViewLoading.value = false; }
};

const openImageZoom = (url) => {
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  imageZoomModalInstance.show();
};

const goToEditProduct = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = '';
    document.body.style = '';
    router.push({ name: 'admin-products-edit', params: { id } });
  }, 300);
};

const processedProducts = computed(() => {
  let result = products.value;
  if (activeTab.value === 'deleted') { result = result.filter(c => c.deleted_at); } 
  else {
    result = result.filter(c => !c.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(c => c.status === activeTab.value);
  }
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(c => (c.name?.toLowerCase().includes(q)) || (c.slug?.toLowerCase().includes(q)));
  }

  // ĐÃ FIX BỘ LỌC DANH MỤC: Gom toàn bộ ID của các danh mục con nếu danh mục đang chọn có danh mục con
  if (filterCategory.value) {
    const targetCategoryIds = getAllCategoryIds(filterCategory.value);
    result = result.filter(c => targetCategoryIds.includes(c.category_id));
  }

  if (filterBrand.value) {
    result = result.filter(c => c.brand_id === filterBrand.value);
  }

  if (filterDateFrom.value) {
    const fromDate = new Date(filterDateFrom.value).setHours(0,0,0,0);
    result = result.filter(c => new Date(c.created_at).getTime() >= fromDate);
  }
  
  if (filterDateTo.value) {
    const toDate = new Date(filterDateTo.value).setHours(23,59,59,999);
    result = result.filter(c => new Date(c.created_at).getTime() <= toDate);
  }

  return result;
});

const totalPages = computed(() => Math.ceil(processedProducts.value.length / itemsPerPage) || 1);

const displayProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage; 
  return processedProducts.value.slice(start, start + itemsPerPage);
});

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Đưa vào thùng rác?', text: `Sản phẩm "${name}" sẽ bị vô hiệu hóa!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        fetchData();
      } catch(e) { isLoading.value = false; Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xóa', 'error'); }
    }
  });
};

const restoreProduct = (id) => {
  Swal.fire({ title: 'Khôi phục sản phẩm?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover, #547792)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
        fetchData();
      } catch(e) { isLoading.value = false; Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi khôi phục', 'error'); }
    }
  });
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.products'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* CSS cho ảnh phóng to */
.img-zoomable { cursor: zoom-in; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.img-zoomable:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

/* Class bọc ngoài Modal để làm mờ nền */
.glass-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.4);
}

.transition-all { transition: all 0.3s ease; }
</style>