const user = [
  {
    path: '/login',
    name: 'client-login',
    component: () => import('@/pages/client/auth/Login.vue')
  },
  {
    path: '/register',
    name: 'client-register',
    component: () => import('@/pages/client/auth/Register.vue')
  },
  {
    path: '/password/reset',
    name: 'forgot-password',
    component: () => import('@/pages/client/auth/ForgotPassword.vue')
  },

  {
    path: '/',
    component: () => import('../layouts/UserLayout.vue'),
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('../pages/user/Index.vue'),
      },
      {
        path: '/category/:slug?',
        name: 'client-category',
        component: () => import('@/pages/client/category/Index.vue')
      },
      {
        path: '/product/:id',
        name: 'client-product-detail',
        component: () => import('@/pages/client/product/Detail.vue')
      },
      {
        path: '/cart',
        name: 'client-cart',
        component: () => import('@/pages/client/cart/Index.vue')
      },
      {
        path: '/checkout',
        name: 'client-checkout',
        component: () => import('@/pages/client/checkout/Index.vue')
      },
      {
        path: '/checkout/success',
        name: 'client-checkout-success',
        component: () => import('@/pages/client/checkout/Success.vue')
      },
      {
        path: '/checkout/failed',
        name: 'client-checkout-failed',
        component: () => import('@/pages/client/checkout/Failed.vue')
      },
      {
        path: '/user/profile',
        name: 'client-profile',
        component: () => import('@/pages/client/user/Profile.vue')
      },
      {
        path: '/user/orders',
        name: 'client-orders',
        component: () => import('@/pages/client/user/Orders.vue')
      },
      {
        path: '/user/wishlist',
        name: 'client-wishlist',
        component: () => import('@/pages/client/user/Wishlist.vue')
      },
      {
        path: '/user/address',
        name: 'client-address',
        component: () => import('@/pages/client/user/Address.vue')
      },
      {
        path: '/user/password',
        name: 'client-password',
        component: () => import('@/pages/client/user/Password.vue')
      },
      {
        path: '/track-order',
        name: 'client-track-order',
        component: () => import('@/pages/client/TrackOrder.vue')
      },
      {
        path: '/stores',
        name: 'client-stores', // Đã map với "Cửa hàng hệ thống" trong Footer
        component: () => import('@/pages/client/StoreLocator.vue')
      },
      {
        path: '/user/review',
        name: 'client-review',
        component: () => import('@/pages/client/review/Index.vue')
      },
      {
        path: '/flash-sale',
        name: 'client-flash-sale',
        component: () => import('@/pages/client/flash-sale/Index.vue')
      },

      // ==========================================
      // NHÓM STATIC PAGES (Thư mục info)
      // Tổ chức theo dạng Table để dễ scale
      // ==========================================
      {
        path: '/about-us',
        name: 'client-info-about',
        component: () => import('@/pages/client/info/AboutUs.vue')
      },
      {
        path: '/return-policy',
        name: 'client-info-return',
        component: () => import('@/pages/client/info/ReturnPolicy.vue')
      },
      {
        path: '/privacy-policy',
        name: 'client-info-privacy',
        component: () => import('@/pages/client/info/PrivacyPolicy.vue')
      },
      {
        path: '/terms-of-service',
        name: 'client-info-terms',
        component: () => import('@/pages/client/info/TermsOfService.vue')
      },
      {
        path: '/shipping-policy',
        name: 'client-info-shipping',
        component: () => import('@/pages/client/info/ShippingPolicy.vue')
      },
      // Các trang này có thể tạo sau, tạm thời tạo component rỗng hoặc dùng chung template
      {
        path: '/careers',
        name: 'client-info-careers',
        component: () => import('@/pages/client/info/AboutUs.vue')
      },
      {
        path: '/social-responsibility',
        name: 'client-info-social',
        component: () => import('@/pages/client/info/AboutUs.vue')
      },
      {
        path: '/faq',
        name: 'client-info-faq',
        component: () => import('@/pages/client/info/Faq.vue')
      },

      {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/pages/NotFound.vue')
      },

      {
        path: '/lookbook',
        name: 'LookbookIndex',
        component: () => import('@/pages/client/lookbook/Index.vue'),
        meta: { title: 'Bộ sưu tập & Combo' }
      },
      {
        path: '/lookbook/:slug',
        name: 'LookbookDetail',
        component: () => import('@/pages/client/lookbook/Detail.vue'),
        meta: { title: 'Chi tiết Bộ sưu tập' }
      }
    ],
  },
]

export default user