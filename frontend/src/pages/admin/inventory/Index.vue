<template>
  <div class="inventory-index-wrapper pb-5 mb-5" style="padding-bottom: 100px !important;">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu kho hàng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản lý Kho (Inventory)</h3>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-200 dark:border-gray-600 border shadow-sm fw-bold px-4 py-2 hover-urban w-100 w-md-auto" @click="fetchData(true)">
            <i class="bi bi-arrow-clockwise me-1"></i> Đồng bộ kho
          </button>
        </div>
      </div>

      <!-- TABS -->
      <div class="mb-3">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1" style="flex-wrap: wrap !important; gap: 8px;">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all_variants' }" @click.prevent="switchTab('all_variants')">
              <i class="bi bi-box-seam me-2"></i> Tất cả Biến thể
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all_variants'}">{{ counts.all_variants }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'low_stock' }" @click.prevent="switchTab('low_stock')">
              <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i> Sắp hết hàng
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'low_stock'}">{{ counts.low_stock }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'lookbooks' }" @click.prevent="switchTab('lookbooks')">
              <i class="bi bi-images me-2 text-urban"></i> Lookbook
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'lookbooks'}">{{ counts.lookbooks }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533]">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 rounded-top-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi bi-layers-fill me-2 text-urban fs-5"></i> {{ tableTitle }}
              <span v-if="isSilentLoading" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
            <div class="search-box position-relative w-100 w-md-auto" style="max-width: 100%; width: 300px;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:border-gray-700 dark:text-white border-0 py-2" v-model="searchQuery" placeholder="Tìm kiếm mã SKU, Tên...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>

          <!-- BỘ LỌC ĐA CHIỀU (Đã quy hoạch chung 1 dòng gọn gàng) -->
          <div class="d-flex align-items-center flex-wrap gap-2 bg-light dark:bg-[#212529] p-2 rounded-3 border dark:border-gray-700 mb-2">
            <div class="d-flex align-items-center small fw-semibold text-muted me-2"><i class="bi bi-funnel me-1"></i> Bộ lọc:</div>
            
            <template v-if="['all_variants', 'low_stock'].includes(activeTab)">
              <!-- Danh mục đa cấp -->
              <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 180px;" v-model="filterCategory">
                <option value="">Tất cả Danh mục</option>
                <option v-for="c in hierarchicalCategories" :key="c.id" :value="c.id" :class="{'fw-bold text-dark dark:text-white': c.level === 0}">
                  {{ c.displayName }}
                </option>
              </select>
              
              <!-- Thương hiệu -->
              <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 180px;" v-model="filterBrand">
                <option value="">Tất cả Thương hiệu</option>
                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>

              <!-- Sắp xếp số lượng tồn -->
              <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 180px;" v-model="sortStock">
                <option value="">Sắp xếp Tồn kho</option>
                <option value="asc">Số lượng tăng dần</option>
                <option value="desc">Số lượng giảm dần</option>
              </select>
              
              <!-- Setup cảnh báo -->
              <div class="d-flex align-items-center ms-auto px-2 py-1">
                <span class="text-muted dark:text-gray-400 small fw-semibold me-2"><i class="bi bi-bell-fill text-warning"></i> Cảnh báo dưới:</span>
                <div class="input-group input-group-sm shadow-sm" style="width: 85px;">
                  <button class="btn btn-light dark:bg-[#1a2533] dark:text-white border-0 fw-bold px-2 py-0" @click="lowStockThreshold = Math.max(0, lowStockThreshold - 1)">-</button>
                  <input type="text" class="form-control text-center fw-bold text-danger border-0 px-0 py-0 bg-white dark:bg-[#1a2533]" v-model.number="lowStockThreshold">
                  <button class="btn btn-light dark:bg-[#1a2533] dark:text-white border-0 fw-bold px-2 py-0" @click="lowStockThreshold++">+</button>
                </div>
              </div>
            </template>
            
            <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 shadow-sm" @click="clearFilters" v-if="hasActiveFilters">
              <i class="bi bi-x-circle"></i> Xóa lọc
            </button>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          
          <!-- ============================================== -->
          <!-- GIAO DIỆN DESKTOP (PC) -->
          <!-- ============================================== -->
          <div class="table-responsive custom-scrollbar-x d-none d-lg-block">
            
            <!-- BẢNG BIẾN THỂ -->
            <table v-if="['all_variants', 'low_stock'].includes(activeTab)" class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-center border-0" style="width: 5%;">
                    <input type="checkbox" class="form-check-input cursor-pointer" style="width: 1.2rem; height: 1.2rem;" :checked="isAllSelected" @change="toggleSelectAll">
                  </th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 8%;">Ảnh</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 42%;">Thông tin Biến thể</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-center cursor-pointer hover-text-urban" style="width: 10%;" @click="toggleSort">
                     Tồn kho <i class="bi" :class="sortStock === 'asc' ? 'bi-sort-numeric-up' : (sortStock === 'desc' ? 'bi-sort-numeric-down-alt' : 'bi-arrow-down-up')"></i>
                  </th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 35%;">Điều chỉnh (Cộng/Trừ)</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700" :class="{'pe-none': isSilentLoading}">
                <tr v-if="displayVariants.length === 0 && !isSilentLoading">
                  <td colspan="5" class="text-center py-5 text-muted dark:text-gray-500">Không có dữ liệu.</td>
                </tr>
                <tr v-else v-for="variant in displayVariants" :key="'v'+variant.id" :class="{'bg-danger bg-opacity-10 dark:bg-opacity-20': variant.stock_quantity <= lowStockThreshold, 'bg-urban bg-opacity-10': selectedVariants.includes(variant.id)}">
                  <td class="px-4 py-3 text-center">
                     <input type="checkbox" class="form-check-input cursor-pointer" style="width: 1.2rem; height: 1.2rem;" :value="variant.id" v-model="selectedVariants">
                  </td>
                  <td class="px-2 py-3">
                    <img :src="getThumbnail(variant.image_url || variant.product_thumbnail)" 
                         @error="handleImageError"
                         @click.stop="openImageZoom(getThumbnail(variant.image_url || variant.product_thumbnail))"
                         class="rounded border dark:border-gray-600 object-fit-cover shadow-sm bg-white img-zoomable" 
                         style="width: 50px; height: 50px;">
                  </td>
                  <td class="px-2 overflow-hidden">
                    <div class="font-monospace fw-bold text-urban mb-1">SKU: {{ variant.sku }}</div>
                    <div class="text-muted dark:text-gray-400 small text-truncate" :title="variant.product_name">
                        <span class="fw-medium text-dark dark:text-gray-300">{{ variant.product_name }}</span>
                    </div>
                  </td>
                  <td class="px-2 text-center">
                    <div class="fw-bold fs-5" :class="variant.stock_quantity <= lowStockThreshold ? 'text-danger' : 'text-dark dark:text-white'">
                        {{ variant.stock_quantity }}
                    </div>
                  </td>
                  <td class="px-4">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <input type="number" class="form-control text-center fw-bold shadow-sm dark:bg-[#212529] dark:text-white" 
                               style="width: 100px; border-color: #ced4da !important;"
                               v-model.number="variant.changeValue"
                               placeholder="VD: 10" :disabled="variant.isUpdating" min="1" max="10000">
                        <div class="d-flex gap-1" style="min-width: 150px;">
                            <button v-if="!variant.isUpdating" @click="submitStockChange(variant, 'add')" class="btn btn-success fw-bold flex-grow-1 shadow-sm px-2 d-flex align-items-center justify-content-center" :disabled="!variant.changeValue || variant.changeValue < 1"><i class="bi bi-plus-lg"></i></button>
                            <button v-if="!variant.isUpdating" @click="submitStockChange(variant, 'subtract')" class="btn btn-danger fw-bold flex-grow-1 shadow-sm px-2 d-flex align-items-center justify-content-center" :disabled="!variant.changeValue || variant.changeValue < 1"><i class="bi bi-dash-lg"></i></button>
                            <div v-if="variant.isUpdating" class="w-100 text-center"><span class="spinner-border spinner-border-sm text-urban"></span></div>
                        </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- BẢNG LOOKBOOK -->
            <table v-else class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 900px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 12%;">Ảnh bìa</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 43%;">Thông tin Lookbook</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 45%;">Tiến độ & Giới hạn bán (Limit)</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700" :class="{'pe-none': isSilentLoading}">
                <tr v-if="displayLookbooks.length === 0 && !isSilentLoading">
                  <td colspan="3" class="text-center py-5 text-muted dark:text-gray-500">Không có dữ liệu Lookbook.</td>
                </tr>
                <tr v-else v-for="lb in displayLookbooks" :key="'lb'+lb.id">
                  <td class="px-4 py-3">
                    <img :src="getThumbnail(lb.main_image)" 
                         @error="handleImageError"
                         @click.stop="openImageZoom(getThumbnail(lb.main_image))"
                         class="rounded border dark:border-gray-600 object-fit-cover shadow-sm bg-white img-zoomable" 
                         style="width: 60px; height: 80px;">
                  </td>
                  <td class="px-4 overflow-hidden">
                    <div class="fw-bold text-dark dark:text-gray-200 text-truncate mb-1 fs-6">{{ lb.name }}</div>
                    <div class="small text-muted dark:text-gray-400 text-truncate">{{ lb.description || 'Không có mô tả' }}</div>
                  </td>
                  <td class="px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <!-- Cột hiển thị Bar tiến độ -->
                        <div class="bg-white dark:bg-[#121416] p-2 rounded-3 border border-light-subtle dark:border-gray-700 shadow-sm flex-grow-1 me-3">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-cart-check text-urban me-1"></i>Đã bán:</span>
                              <span class="fw-bold" style="font-size: 0.75rem;" :class="lb.usage_limit && (lb.usage_count || 0) >= lb.usage_limit ? 'text-danger' : 'text-success'">
                                  {{ lb.usage_count || 0 }} <span v-if="lb.usage_limit" class="text-muted fw-normal">/ {{ lb.usage_limit }}</span>
                                  <span v-else class="text-muted fw-normal">/ Vô hạn</span>
                              </span>
                          </div>
                          <div class="progress" style="height: 4px;" v-if="lb.usage_limit">
                              <div class="progress-bar" 
                                   :class="((lb.usage_count || 0) / lb.usage_limit * 100) >= 90 ? 'bg-danger' : 'bg-success'" 
                                   role="progressbar" 
                                   :style="{ width: Math.min(((lb.usage_count || 0) / lb.usage_limit * 100), 100) + '%' }"></div>
                          </div>
                        </div>

                        <!-- Cột Nhập Số Lượng Thay Đổi -->
                        <div class="d-flex align-items-center flex-shrink-0">
                            <input type="number" class="form-control text-center fw-bold shadow-sm dark:bg-[#212529] dark:text-white me-2" 
                                   style="width: 100px; border-color: #ced4da !important;"
                                   v-model.number="lb.localLimit"
                                   placeholder="Vô hạn"
                                   @input="checkLookbookLimitChange(lb)"
                                   :disabled="lb.isUpdating"
                                   min="0">
                            <div style="width: 80px;" class="d-flex justify-content-start">
                              <div v-if="lb.isUpdating" class="spinner-border text-urban" style="width: 1.5rem; height: 1.5rem;"></div>
                              <template v-else-if="lb.isChanged">
                                <button @click="saveLookbookLimit(lb)" class="btn btn-success shadow-sm px-3 py-1 me-1" title="Lưu"><i class="bi bi-check-lg fw-bold"></i></button>
                                <button @click="cancelLookbookLimitChange(lb)" class="btn btn-light border text-danger shadow-sm px-3 py-1" title="Hủy"><i class="bi bi-x-lg fw-bold"></i></button>
                              </template>
                            </div>
                        </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ============================================== -->
          <!-- GIAO DIỆN MOBILE (CARD) -->
          <!-- ============================================== -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            
            <!-- BIẾN THỂ (MOBILE) -->
            <template v-if="['all_variants', 'low_stock'].includes(activeTab)">
              <!-- Thanh chọn tất cả trên mobile -->
              <div class="d-flex align-items-center justify-content-between mb-3 px-1" v-if="displayVariants.length > 0">
                 <label class="d-flex align-items-center cursor-pointer mb-0">
                    <input type="checkbox" class="form-check-input me-2" style="width: 1.3rem; height: 1.3rem;" :checked="isAllSelected" @change="toggleSelectAll">
                    <span class="fw-bold text-dark dark:text-white small">Chọn tất cả trang này</span>
                 </label>
              </div>

              <div v-if="displayVariants.length === 0 && !isSilentLoading" class="text-center py-5 text-muted">Không có dữ liệu.</div>
              <div v-else v-for="variant in displayVariants" :key="'m-v'+variant.id" class="card border border-light-subtle shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'border-danger dark:border-danger': variant.stock_quantity <= lowStockThreshold, 'border-urban dark:border-urban': selectedVariants.includes(variant.id)}">
                <div class="card-body p-3">
                  <div class="d-flex mb-3 border-bottom dark:border-gray-700 pb-3 align-items-center">
                    <div class="me-3">
                       <input type="checkbox" class="form-check-input cursor-pointer shadow-sm" style="width: 1.5rem; height: 1.5rem;" :value="variant.id" v-model="selectedVariants">
                    </div>
                    <img :src="getThumbnail(variant.image_url || variant.product_thumbnail)" 
                         @error="handleImageError" 
                         @click.stop="openImageZoom(getThumbnail(variant.image_url || variant.product_thumbnail))"
                         class="rounded border dark:border-gray-600 object-fit-cover shadow-sm bg-white me-3 img-zoomable" 
                         style="width: 70px; height: 70px;">
                    <div class="overflow-hidden w-100">
                      <h6 class="mb-1 fw-bold text-dark dark:text-gray-200 text-truncate" :title="variant.product_name">{{ variant.product_name }}</h6>
                      <div class="font-monospace text-urban small mb-1">SKU: {{ variant.sku }}</div>
                      <div class="small"><span class="text-muted dark:text-gray-400">Tồn kho:</span> <strong class="fs-6" :class="variant.stock_quantity <= lowStockThreshold ? 'text-danger' : 'text-dark dark:text-white'">{{ variant.stock_quantity }}</strong></div>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold text-muted dark:text-gray-400">Điều chỉnh:</span>
                    <div class="d-flex align-items-center gap-2">
                      <input type="number" class="form-control text-center fw-bold shadow-sm dark:bg-[#1a2533] dark:text-white border border-light-subtle" 
                             style="width: 80px;" v-model.number="variant.changeValue" placeholder="SL" :disabled="variant.isUpdating" min="1" max="10000">
                      <button v-if="!variant.isUpdating" @click="submitStockChange(variant, 'add')" class="btn btn-sm btn-success fw-bold shadow-sm px-3" :disabled="!variant.changeValue || variant.changeValue < 1"><i class="bi bi-plus-lg"></i></button>
                      <button v-if="!variant.isUpdating" @click="submitStockChange(variant, 'subtract')" class="btn btn-sm btn-danger fw-bold shadow-sm px-3" :disabled="!variant.changeValue || variant.changeValue < 1"><i class="bi bi-dash-lg"></i></button>
                      <div v-if="variant.isUpdating" class="ms-2"><span class="spinner-border spinner-border-sm text-urban"></span></div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- LOOKBOOK (MOBILE) -->
            <template v-else>
              <div v-if="displayLookbooks.length === 0 && !isSilentLoading" class="text-center py-5 text-muted">Không có dữ liệu Lookbook.</div>
              <div v-else v-for="lb in displayLookbooks" :key="'m-lb'+lb.id" class="card border border-light-subtle shadow-sm mb-3 rounded-4 dark:bg-[#212529]">
                <div class="card-body p-3">
                  <div class="d-flex mb-3 border-bottom dark:border-gray-700 pb-3 align-items-center">
                    <img :src="getThumbnail(lb.main_image)" 
                         @error="handleImageError" 
                         @click.stop="openImageZoom(getThumbnail(lb.main_image))"
                         class="rounded border dark:border-gray-600 object-fit-cover shadow-sm bg-white me-3 img-zoomable" 
                         style="width: 60px; height: 80px;">
                    <div class="overflow-hidden w-100">
                      <h6 class="mb-1 fw-bold text-dark dark:text-gray-200 text-truncate">{{ lb.name }}</h6>
                      <div class="text-muted dark:text-gray-400 small line-clamp-2 mb-2">{{ lb.description || 'Không có mô tả' }}</div>
                      
                      <!-- THANH TIẾN ĐỘ BÁN MOBILE -->
                      <div class="bg-white dark:bg-[#1a2533] p-2 rounded-3 border border-light-subtle dark:border-gray-700 mt-2 shadow-sm">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-cart-check text-urban me-1"></i>Đã bán:</span>
                              <span class="fw-bold" style="font-size: 0.7rem;" :class="lb.usage_limit && (lb.usage_count || 0) >= lb.usage_limit ? 'text-danger' : 'text-success'">
                                  {{ lb.usage_count || 0 }} <span v-if="lb.usage_limit" class="text-muted fw-normal">/ {{ lb.usage_limit }}</span>
                                  <span v-else class="text-muted fw-normal">/ ∞</span>
                              </span>
                          </div>
                          <div class="progress" style="height: 3px;" v-if="lb.usage_limit">
                              <div class="progress-bar" 
                                   :class="((lb.usage_count || 0) / lb.usage_limit * 100) >= 90 ? 'bg-danger' : 'bg-success'" 
                                   role="progressbar" 
                                   :style="{ width: Math.min(((lb.usage_count || 0) / lb.usage_limit * 100), 100) + '%' }"></div>
                          </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold text-muted dark:text-gray-400">Cập nhật Limit:</span>
                    <div class="d-flex align-items-center gap-2">
                      <input type="number" class="form-control text-center fw-bold shadow-sm dark:bg-[#1a2533] dark:text-white border border-light-subtle" 
                             style="width: 100px;" v-model.number="lb.localLimit" placeholder="Vô hạn" @input="checkLookbookLimitChange(lb)" :disabled="lb.isUpdating" min="0">
                      
                      <div v-if="lb.isUpdating"><span class="spinner-border spinner-border-sm text-urban mx-2"></span></div>
                      <template v-else-if="lb.isChanged">
                        <button @click="saveLookbookLimit(lb)" class="btn btn-sm btn-success shadow-sm px-2"><i class="bi bi-check-lg fw-bold"></i></button>
                        <button @click="cancelLookbookLimitChange(lb)" class="btn btn-sm btn-light dark:bg-[#2b3035] border dark:border-gray-600 text-danger shadow-sm px-2"><i class="bi bi-x-lg fw-bold"></i></button>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- THANH CÔNG CỤ BULK ACTION (HIỂN THỊ TRÊN CẢ PC LẪN MOBILE) -->
    <transition name="slide-up">
      <div v-if="selectedVariants.length > 0 && ['all_variants', 'low_stock'].includes(activeTab)" 
           class="position-fixed bottom-0 start-0 w-100 bg-white dark:bg-[#212529] shadow-lg border-top dark:border-gray-700 py-3 px-3 px-md-4 z-index-floating d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" 
           style="box-shadow: 0 -10px 30px rgba(0,0,0,0.15) !important;">
         
         <div class="d-flex align-items-center justify-content-between w-100 w-md-auto">
            <div class="d-flex align-items-center">
                <span class="badge bg-urban fs-6 px-3 py-2 rounded-pill me-2 shadow-sm d-flex align-items-center"><i class="bi bi-check2-square me-1"></i> {{ selectedVariants.length }}</span>
                <span class="text-dark dark:text-white fw-medium d-none d-sm-inline">sản phẩm. Mức điều chỉnh:</span>
                <span class="text-dark dark:text-white fw-medium d-inline d-sm-none small">Điều chỉnh:</span>
            </div>
         </div>
         
         <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
            <input type="number" class="form-control text-center fw-bold shadow-inner dark:bg-[#1a2533] dark:text-white flex-shrink-0" 
                   style="width: 80px; border: 2px solid var(--color-c-hover);"
                   v-model.number="bulkChangeValue"
                   placeholder="SL" :disabled="isBulkUpdating" min="1" max="10000">
                   
            <button @click="submitBulkStockChange('add')" class="btn btn-success fw-bold flex-grow-1 shadow-sm d-flex align-items-center justify-content-center" :disabled="!bulkChangeValue || bulkChangeValue < 1 || isBulkUpdating">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline ms-1">NHẬP VÀO</span> <span class="d-inline d-sm-none ms-1">NHẬP</span>
            </button>
            <button @click="submitBulkStockChange('subtract')" class="btn btn-danger fw-bold flex-grow-1 shadow-sm d-flex align-items-center justify-content-center" :disabled="!bulkChangeValue || bulkChangeValue < 1 || isBulkUpdating">
                <i class="bi bi-dash-lg"></i> <span class="d-none d-sm-inline ms-1">TRỪ HAO</span> <span class="d-inline d-sm-none ms-1">TRỪ</span>
            </button>
         </div>
      </div>
    </transition>

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
import { ref, onMounted, onUnmounted, onBeforeUnmount, computed, watch } from 'vue';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import api from '@/utils/axios';
import defaultPlaceholder from '@/assets/images/defaults/client_placeholder.png';

const isFirstLoad = ref(true);
const isSilentLoading = ref(false); 
const isBulkUpdating = ref(false);

const activeTab = ref('all_variants');
const searchQuery = ref('');
const lowStockThreshold = ref(10);

// Thêm State lọc chuyên sâu
const filterCategory = ref('');
const filterBrand = ref('');
const sortStock = ref(''); // '', 'asc', 'desc'
const categories = ref([]);
const brands = ref([]);

const allVariantsData = ref([]);
const allLookbooksData = ref([]);

const counts = ref({ all_variants: 0, low_stock: 0, lookbooks: 0 });

const selectedVariants = ref([]);
const bulkChangeValue = ref('');

// Biến Zoom ảnh
const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

// --- XỬ LÝ ẢNH MẠNH MẼ NHẤT ---
const handleImageError = (event) => {
    event.target.src = defaultPlaceholder;
};

const getThumbnail = (path) => {
  if (!path || String(path).trim() === '') return defaultPlaceholder;
  let cleanPath = String(path).trim();
  if (cleanPath.startsWith('http') || cleanPath.startsWith('/')) return cleanPath;
  
  cleanPath = cleanPath.replace(/^\/+/, '');
  if (cleanPath.startsWith('storage/')) cleanPath = cleanPath.replace('storage/', '');

  let baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';
  baseUrl = baseUrl.replace('/api/v1', '');
  return `${baseUrl}/storage/${cleanPath}`;
};

const openImageZoom = (url) => {
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  imageZoomModalInstance.show();
};

onBeforeUnmount(() => {
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

// LOGIC CÂY DANH MỤC TRỰC QUAN CHO SELECT
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

const getAllCategoryIds = (id) => {
  let ids = [id];
  const children = categories.value.filter(c => c.parent_id === id);
  children.forEach(child => { ids = ids.concat(getAllCategoryIds(child.id)); });
  return ids;
};

// --- LOGIC BULK ACTION ---
const isAllSelected = computed(() => {
    return displayVariants.value.length > 0 && selectedVariants.value.length === displayVariants.value.length;
});

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedVariants.value = displayVariants.value.map(v => v.id);
    } else {
        selectedVariants.value = [];
    }
};

const submitBulkStockChange = async (type) => {
    if (!bulkChangeValue.value || bulkChangeValue.value <= 0 || selectedVariants.value.length === 0) return;
    
    isBulkUpdating.value = true;
    try {
        const payload = {
            variant_ids: selectedVariants.value,
            type: type,
            quantity: bulkChangeValue.value
        };

        const res = await api.put('/admin/inventory/variants/bulk-stock', payload);
        
        const updatedVariants = res.data.data;
        updatedVariants.forEach(updatedV => {
            const idx = allVariantsData.value.findIndex(v => v.id === updatedV.id);
            if (idx !== -1) {
                allVariantsData.value[idx].stock_quantity = updatedV.stock_quantity;
            }
        });

        bulkChangeValue.value = '';
        selectedVariants.value = []; 
        updateCounts();
        ZyroSwal.toastSuccess(res.data.message);

    } catch (error) {
        let errorMsg = 'Lỗi cập nhật đồng loạt';
        if (error.response?.data?.message) errorMsg = error.response.data.message;
        ZyroSwal.toastError(errorMsg);
    } finally {
        isBulkUpdating.value = false;
    }
};

// --- LOGIC LOOKBOOK ---
const checkLookbookLimitChange = (lb) => { 
  const currentVal = lb.usage_limit === null || lb.usage_limit === undefined ? '' : lb.usage_limit;
  lb.isChanged = String(lb.localLimit) !== String(currentVal); 
};
const cancelLookbookLimitChange = (lb) => { 
  lb.localLimit = lb.usage_limit === null || lb.usage_limit === undefined ? '' : lb.usage_limit; 
  lb.isChanged = false; 
};

const saveLookbookLimit = async (lb) => {
  lb.isUpdating = true;
  try {
    const payload = { usage_limit: lb.localLimit === '' ? null : lb.localLimit };
    await api.put(`/admin/inventory/lookbooks/${lb.id}/limit`, payload);
    lb.usage_limit = payload.usage_limit;
    lb.isChanged = false;
    ZyroSwal.toastSuccess('Đã cập nhật giới hạn');
  } catch (error) {
    cancelLookbookLimitChange(lb);
    ZyroSwal.toastError(error.response?.data?.message || 'Lỗi khi lưu dữ liệu');
  } finally {
    lb.isUpdating = false;
  }
};

// --- LOGIC VARIANT SINGLE ---
const submitStockChange = async (variant, type) => {
  if (!variant.changeValue || variant.changeValue <= 0) return;
  variant.isUpdating = true;
  try {
    const payload = { type: type, quantity: variant.changeValue };
    const res = await api.put(`/admin/inventory/variants/${variant.id}/stock`, payload);
    
    variant.stock_quantity = res.data.data.stock_quantity;
    variant.changeValue = ''; 
    updateCounts();
    ZyroSwal.toastSuccess(res.data.message);
  } catch (error) {
    let errorMsg = 'Không thể lưu thay đổi';
    if (error.response?.data?.errors?.quantity) errorMsg = error.response.data.errors.quantity[0];
    else if (error.response?.data?.message) errorMsg = error.response.data.message;
    ZyroSwal.toastError(errorMsg);
  } finally {
    variant.isUpdating = false;
  }
};

// --- BỘ LỌC VÀ SẮP XẾP ---
const hasActiveFilters = computed(() => {
  return filterCategory.value !== '' || filterBrand.value !== '' || sortStock.value !== '';
});

const clearFilters = () => {
  filterCategory.value = '';
  filterBrand.value = '';
  sortStock.value = '';
};

const toggleSort = () => {
  if (sortStock.value === '') sortStock.value = 'asc';
  else if (sortStock.value === 'asc') sortStock.value = 'desc';
  else sortStock.value = '';
};

// --- DATA FETCHING & SYNC ---
const fetchData = async (silent = false) => {
  if (silent) isSilentLoading.value = true;
  
  try {
    // Tải thêm Danh mục và Thương hiệu để lọc
    const [resVariants, resLookbooks, resCats, resBrands] = await Promise.all([
      api.get('/admin/inventory/variants'),
      api.get('/admin/inventory/lookbooks'),
      api.get('/admin/categories'),
      api.get('/admin/brands')
    ]);

    const allCats = Array.isArray(resCats.data?.data) ? resCats.data.data : [];
    categories.value = allCats.filter(c => (c.status === 'active' || c.status === 'published') && !c.deleted_at);
    
    const allBrands = Array.isArray(resBrands.data?.data) ? resBrands.data.data : [];
    brands.value = allBrands.filter(b => b.status === 'active' && !b.deleted_at);

    let vArray = [];
    const variantsData = resVariants.data.data || [];
    variantsData.forEach(v => {
        if (v.product) {
            vArray.push({
                ...v,
                product_name: v.product.name,
                product_status: v.product.status,
                product_thumbnail: v.product.thumbnail_image,
                category_id: v.product.category_id, // Lưu lại ID để lọc
                brand_id: v.product.brand_id,       // Lưu lại ID để lọc
                changeValue: '', 
                isUpdating: false
            });
        }
    });
    allVariantsData.value = vArray;

    const lookbooksData = resLookbooks.data.data || [];
    allLookbooksData.value = lookbooksData.map(lb => ({
        ...lb,
        localLimit: lb.usage_limit === null || lb.usage_limit === undefined ? '' : lb.usage_limit,
        isChanged: false,
        isUpdating: false
    }));

    updateCounts();
    selectedVariants.value = []; 

  } catch (error) { 
    ZyroSwal.toastError('Không thể tải dữ liệu kho.');
  } finally { 
    isFirstLoad.value = false;
    isSilentLoading.value = false;
  }
};

const updateCounts = () => {
    counts.value.all_variants = allVariantsData.value.length;
    counts.value.low_stock = allVariantsData.value.filter(v => v.stock_quantity <= lowStockThreshold.value).length;
    counts.value.lookbooks = allLookbooksData.value.length;
};

const switchTab = (tabId) => { 
    activeTab.value = tabId; 
    searchQuery.value = '';
    selectedVariants.value = []; 
};

watch(lowStockThreshold, updateCounts);

const tableTitle = computed(() => {
    if (activeTab.value === 'low_stock') return 'Cảnh báo sắp hết hàng';
    if (activeTab.value === 'lookbooks') return 'Quản lý Lookbook';
    return 'Danh sách Biến thể Sản phẩm';
});

const displayVariants = computed(() => {
    let result = allVariantsData.value;
    
    // Tab filtering
    if (activeTab.value === 'low_stock') {
       result = result.filter(v => v.stock_quantity <= lowStockThreshold.value);
    }
    
    // Search
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(v => v.sku.toLowerCase().includes(q) || v.product_name.toLowerCase().includes(q));
    }

    // Category Filter
    if (filterCategory.value) {
        const targetCategoryIds = getAllCategoryIds(filterCategory.value);
        result = result.filter(v => targetCategoryIds.includes(v.category_id));
    }

    // Brand Filter
    if (filterBrand.value) {
        result = result.filter(v => v.brand_id === filterBrand.value);
    }

    // Sorting Logic
    if (sortStock.value === 'asc') {
        result.sort((a, b) => a.stock_quantity - b.stock_quantity);
    } else if (sortStock.value === 'desc') {
        result.sort((a, b) => b.stock_quantity - a.stock_quantity);
    }

    return result;
});

const displayLookbooks = computed(() => {
    let result = allLookbooksData.value;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(lb => lb.name.toLowerCase().includes(q));
    }
    return result;
});

// LẮNG NGHE SỰ KIỆN THỜI GIAN THỰC (REAL-TIME ECHO)
onMounted(() => { 
    fetchData(); 
    
    if (window.Echo) {
        window.Echo.private('admin.inventory')
            .listen('.InventoryUpdatedEvent', (e) => {
                if (e.type === 'variant') {
                    const idx = allVariantsData.value.findIndex(v => v.id === e.data.id);
                    if (idx !== -1) {
                        allVariantsData.value[idx].stock_quantity = e.data.stock_quantity;
                        updateCounts(); 
                    }
                } else if (e.type === 'lookbook') {
                    const idx = allLookbooksData.value.findIndex(lb => lb.id === e.data.id);
                    if (idx !== -1) {
                        allLookbooksData.value[idx].usage_limit = e.data.usage_limit;
                        if (!allLookbooksData.value[idx].isUpdating && !allLookbooksData.value[idx].isChanged) {
                            allLookbooksData.value[idx].localLimit = e.data.usage_limit === null ? '' : e.data.usage_limit;
                        }
                    }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('admin.inventory');
    }
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; color: white; }
.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: var(--color-c-hover, #547792); }
.custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; border-bottom: 2px solid var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

/* SHIMMER LOGO ZYRO */
.logo-shimmer { 
  font-weight: 900; 
  background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); 
  background-size: 200% auto; 
  color: transparent; 
  -webkit-background-clip: text; 
  background-clip: text; 
  animation: shine 1.5s linear infinite;
}
html.dark .logo-shimmer {
  background: linear-gradient(120deg, #f8f9fa 30%, var(--color-c-light) 50%, #f8f9fa 70%);
  background-size: 200% auto; 
  -webkit-background-clip: text; 
  background-clip: text; 
}
@keyframes shine { to { background-position: 200% center; } }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.z-index-floating { z-index: 1040; }
.shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }

/* CSS cho ảnh phóng to */
.img-zoomable { cursor: zoom-in; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.img-zoomable:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.cursor-pointer { cursor: pointer; }

/* Class bọc ngoài Modal để làm mờ nền */
.glass-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.4);
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
</style>