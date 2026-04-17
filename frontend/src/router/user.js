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
        name: 'client-stores',
        component: () => import('@/pages/client/StoreLocator.vue')
      },
      {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/pages/NotFound.vue')
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
    ],
  },
]

export default user