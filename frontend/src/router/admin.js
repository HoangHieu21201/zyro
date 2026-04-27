const admin = [
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('../pages/admin/auth/Login.vue'),
  },
  {
    path: '/admin/register',
    name: 'admin-register',
    component: () => import('../pages/admin/auth/Register.vue'),
  },
  {
    path: '/admin/forgot-password',
    name: 'admin-forgot-password',
    component: () => import('../pages/admin/auth/ForgotPassword.vue'),
  },
  {
    path: '/admin/reset-password',
    name: 'admin-reset-password',
    component: () => import('../pages/admin/auth/ResetPassword.vue'),
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    children: [
      {
        path: '/admin/dashboard',
        name: 'admin-dashboard',
        component: () => import('../pages/admin/index.vue'),
      },
      {
        path: 'roles/create',
        name: 'admin-roles-create',
        component: () => import('../pages/admin/role/Create.vue'),
        meta: { moduleCode: 'admin_roles' },
      },
      {
        path: 'roles/:id/edit',
        name: 'admin-roles-edit',
        component: () => import('../pages/admin/role/Edit.vue'),
        meta: { moduleCode: 'admin_roles' },
      },
      {
        path: 'admins',
        name: 'admin-admins',
        component: () => import('../pages/admin/admin/Index.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        path: 'admins/create',
        name: 'admin-admins-create',
        component: () => import('../pages/admin/admin/Create.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        path: 'admins/:id/edit',
        name: 'admin-admins-edit',
        component: () => import('../pages/admin/admin/Edit.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        path: 'profile',
        name: 'admin-profile',
        component: () => import('../pages/admin/profile/Index.vue'),
      },
      {
        path: 'roles',
        name: 'admin-roles',
        component: () => import('../pages/admin/role/Index.vue'),
        meta: { moduleCode: 'admin_roles' },
      },
      {
        path: 'categories',
        name: 'admin-categories',
        component: () => import('../pages/admin/category/Index.vue'),
        meta: { moduleCode: 'admin_categories' },
      },
      {
        path: 'categories/create',
        name: 'admin-categories-create',
        component: () => import('../pages/admin/category/Create.vue'),
        meta: { moduleCode: 'admin_categories' },
      },
      {
        path: 'categories/:id/edit',
        name: 'admin-categories-edit',
        component: () => import('../pages/admin/category/Edit.vue'),
        meta: { moduleCode: 'admin_categories' },
      },
      {
        path: 'brands',
        name: 'admin-brands',
        component: () => import('../pages/admin/brand/Index.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      {
        path: 'brands/create',
        name: 'admin-brands-create',
        component: () => import('../pages/admin/brand/Create.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      {
        path: 'brands/:id/edit',
        name: 'admin-brands-edit',
        component: () => import('../pages/admin/brand/Edit.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('../pages/admin/user/Index.vue'),
        meta: { moduleCode: 'admin_users' },
      },
      {
        path: 'users/create',
        name: 'admin-users-create',
        component: () => import('../pages/admin/user/Create.vue'),
        meta: { moduleCode: 'admin_users' },
      },
      {
        path: 'users/:id/edit',
        name: 'admin-users-edit',
        component: () => import('../pages/admin/user/Edit.vue'),
        meta: { moduleCode: 'admin_users' },
      },
      {
        path: 'tiers',
        name: 'admin-tiers',
        component: () => import('../pages/admin/tier/Index.vue'),
        meta: { moduleCode: 'admin_tiers' },
      },
      {
        path: 'tiers/create',
        name: 'admin-tiers-create',
        component: () => import('../pages/admin/tier/Create.vue'),
        meta: { moduleCode: 'admin_tiers' },
      },
      {
        path: 'tiers/:id/edit',
        name: 'admin-tiers-edit',
        component: () => import('../pages/admin/tier/Edit.vue'),
        meta: { moduleCode: 'admin_tiers' },
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('../pages/admin/product/Index.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      {
        path: 'products/create',
        name: 'admin-products-create',
        component: () => import('../pages/admin/product/Create.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      {
        path: 'products/:id/edit',
        name: 'admin-products-edit',
        component: () => import('../pages/admin/product/Edit.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      // lookbook
      {
        path: 'lookbooks',
        name: 'admin-lookbooks',
        component: () => import('../pages/admin/lookbook/Index.vue'),
        meta: { moduleCode: 'admin_lookbooks' },
      },
      {
        path: 'lookbooks/create',
        name: 'admin-lookbooks-create',
        component: () => import('../pages/admin/lookbook/Create.vue'),
        meta: { moduleCode: 'admin_lookbooks' },
      },
      {
        path: 'lookbooks/:id/edit',
        name: 'admin-lookbooks-edit',
        component: () => import('../pages/admin/lookbook/Edit.vue'),
        meta: { moduleCode: 'admin_lookbooks' },
      },
      // voucher
      {
        path: 'vouchers',
        name: 'admin-vouchers',
        component: () => import('../pages/admin/voucher/Index.vue'),
        meta: { moduleCode: 'admin_vouchers' },
      },
      {
        path: 'vouchers/create',
        name: 'admin-vouchers-create',
        component: () => import('../pages/admin/voucher/Create.vue'),
        meta: { moduleCode: 'admin_vouchers' },
      },
      {
        path: 'vouchers/:id/edit',
        name: 'admin-vouchers-edit',
        component: () => import('../pages/admin/voucher/Edit.vue'),
        meta: { moduleCode: 'admin_vouchers' },
      },
      {
        path: 'banners',
        name: 'admin-banners',
        component: () => import('../pages/admin/banner/Index.vue'),
        meta: { moduleCode: 'admin_banners' },
      },
      {
        path: 'banners/create',
        name: 'admin-banners-create',
        component: () => import('../pages/admin/banner/Create.vue'),
        meta: { moduleCode: 'admin_banners' },
      },
      {
        path: 'banners/:id/edit',
        name: 'admin-banners-edit',
        component: () => import('../pages/admin/banner/Edit.vue'),
        meta: { moduleCode: 'admin_banners' },
      },

      {
        path: 'reviews',
        name: 'admin-reviews',
        component: () => import('../pages/admin/review/Index.vue'),
        meta: { moduleCode: 'admin_reviews' },
      },
      {
        path: 'wishlists',
        name: 'admin-wishlists',
        component: () => import('../pages/admin/wishlist/Index.vue'),
        meta: { moduleCode: 'admin_wishlists' },
      },
      {
        path: 'returns',
        name: 'admin-returns',
        component: () => import('../pages/admin/return/Index.vue'),
        meta: { moduleCode: 'admin_orders' },
      },
      {
        path: 'returns/:id/edit',
        name: 'admin-returns-edit',
        component: () => import('../pages/admin/return/Edit.vue'),
        meta: { moduleCode: 'admin_orders' },
      },

      {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('../pages/admin/order/Index.vue'),
        meta: { moduleCode: 'admin_orders' },
      },
      {
        path: 'orders/:id',
        name: 'admin-orders-edit',
        component: () => import('../pages/admin/order/Edit.vue'),
        meta: { moduleCode: 'admin_orders' },
      },
      {
        path: 'flash-sales',
        name: 'admin-flash-sales',
        component: () => import('../pages/admin/flash_sales/Index.vue'),
        meta: { moduleCode: 'admin_flash_sales' },
      },
      {
        path: 'flash-sales/create',
        name: 'admin-flash-sales-create',
        component: () => import('../pages/admin/flash_sales/Create.vue'),
        meta: { moduleCode: 'admin_flash_sales' },
      },
      {
        path: 'flash-sales/:id/edit',
        name: 'admin-flash-sales-edit',
        component: () => import('../pages/admin/flash_sales/Edit.vue'),
        meta: { moduleCode: 'admin_flash_sales' },
      },
      {
        path: 'inventory',
        name: 'admin-inventory',
        component: () => import('../pages/admin/inventory/Index.vue'),
        meta: { moduleCode: 'admin_inventory' },
      },

       {
        path: 'contacts',
        name: 'admin-contacts',
        component: () => import('../pages/admin/contact/Index.vue'),
        meta: { title: 'Hộp thư khách hàng' }
      },
    ],
  },
]

export default admin
