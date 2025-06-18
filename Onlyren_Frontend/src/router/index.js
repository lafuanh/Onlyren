import { createRouter, createWebHistory } from 'vue-router'
import { getCurrentUser } from '@/api/auth'

// Import views/pages
import HomePage from '@/pages/HomePage.vue'
import SearchPage from '@/pages/SearchPage.vue'
import RoomDetailPage from '@/pages/RoomDetail.vue'
import UserProfilePage from '@/pages/UserProfilePage.vue'
import RenterProfilePage from '@/pages/RenterProfilePage.vue'
import AdminProfilePage from '@/pages/AdminProfilePage.vue'
import AuthPages from '@/pages/AuthPages.vue'

// Lazy-loaded components for better performance
const PaymentPage = () => import('@/pages/PaymentPage.vue')
const NotFoundPage = () => import('@/pages/NotFoundPage.vue')

// Auth middleware
const requireAuth = async (to, from, next) => {
  try {
    const user = await getCurrentUser()
    if (user) {
      next()
    } else {
      next('/login') // Redirect to login page if the user is not authenticated
    }
  } catch (error) {
    console.error('Authentication failed:', error)
    next('/login') // If there's an error (like no backend), redirect to login
  }
}

// Admin-specific middleware
const requireAdminAuth = async (to, from, next) => {
  try {
    const user = await getCurrentUser()
    if (user && user.role === 'admin') {
      next()
    } else {
      next('/login') // Redirect to login page if user is not an admin
    }
  } catch (error) {
    next('/login') // If error in fetching user, redirect to login
  }
}

// Renter-specific middleware
const requireRenterAuth = async (to, from, next) => {
  try {
    const user = await getCurrentUser()
    if (user && user.role === 'renter') {
      next()
    } else {
      next('/login') // Redirect to login page if user is not a renter
    }
  } catch (error) {
    next('/login') // If error in fetching user, redirect to login
  }
}

// Guest middleware (redirect if authenticated)
const requireGuest = async (to, from, next) => {
  try {
    const user = await getCurrentUser()
    if (!user) {
      next() // Continue to the requested page if the user is not authenticated
    } else {
      next('/') // Redirect to the homepage if the user is already authenticated
    }
  } catch (error) {
    next() // Allow navigation if there's an error in fetching user
  }
}

const routes = [
  // Public routes
  {
    path: '/',
    name: 'Home',
    component: HomePage,
    meta: { title: 'Home' }
  },
  {
    path: '/search',
    name: 'Search',
    component: SearchPage,
    meta: { title: 'Search Rooms' }
  },
  {
    path: '/rooms/:id',
    name: 'RoomDetail',
    component: RoomDetailPage,
    props: true,
    meta: { title: 'Room Details' }
  },

  // Auth routes
  {
    path: '/login',
    name: 'Login',
    component: AuthPages,
    beforeEnter: requireGuest,
    meta: { title: 'Login' }
  },
  {
    path: '/register',
    name: 'Register',
    component: AuthPages,
    beforeEnter: requireGuest,
    meta: { title: 'Register' }
  },

  // Payment & Booking routes
  {
    path: '/payments/:id',
    name: 'Payment',
    component: PaymentPage,
    props: true,
   // beforeEnter: requireAuth,
    meta: { title: 'Payment' }
  },
  // {
  //   path: '/confirmation/:id',
  //   name: 'Confirmation',
  //   component: ConfirmationPage,
  //   props: true,
  //   beforeEnter: requireAuth,
  //   meta: { title: 'Booking Confirmation' }
  // },

  // User routes
  {
    path: '/profile',
    name: 'UserProfile',
    component: UserProfilePage,
    beforeEnter: requireAuth,
    meta: { title: 'My Profile' }
  },
  // {
  //   path: '/reservations',
  //   name: 'Reservations',
  //   component: ReservationsPage,
  //   beforeEnter: requireAuth,
  //   meta: { title: 'My Reservations' }
  // },
  // {
  //   path: '/favorites',
  //   name: 'Favorites',
  //   component: FavoritesPage,
  //   beforeEnter: requireAuth,
  //   meta: { title: 'My Favorites' }
  // },
  // {
  //   path: '/messages',
  //   name: 'Messages',
  //   component: MessagesPage,
  //   beforeEnter: requireAuth,
  //   meta: { title: 'Messages' }
  // },

  // Renter routes
  {
    path: '/renter',
    redirect: '/renter/profile'
  },
  {
    path: '/renter/profile',
    name: 'RenterProfile',
    component: RenterProfilePage,
    beforeEnter: requireRenterAuth,
    meta: { title: 'Renter Dashboard' }
  },
  // {
  //   path: '/renter/rooms',
  //   name: 'RenterRooms',
  //   component: () => import('@/pages/RenterRoomsPage.vue'),
  //   beforeEnter: requireRenterAuth,
  //   meta: { title: 'My Rooms' }
  // },
  // {
  //   path: '/renter/rooms/new',
  //   name: 'NewRoom',
  //   component: NewRoomPage,
  //   beforeEnter: requireRenterAuth,
  //   meta: { title: 'Add New Room' }
  // },
  // {
  //   path: '/renter/rooms/:id/edit',
  //   name: 'EditRoom',
  //   component: EditRoomPage,
  //   beforeEnter: requireRenterAuth,
  //   props: true,
  //   meta: { title: 'Edit Room' }
  // },
  // {
  //   path: '/renter/bookings',
  //   name: 'RenterBookings',
  //   component: () => import('@/pages/RenterBookingsPage.vue'),
  //   beforeEnter: requireRenterAuth,
  //   meta: { title: 'Booking Management' }
  // },
  // {
  //   path: '/renter/analytics',
  //   name: 'RenterAnalytics',
  //   component: () => import('@/pages/RenterAnalyticsPage.vue'),
  //   beforeEnter: requireRenterAuth,
  //   meta: { title: 'Analytics' }
  // },

  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard'
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminProfilePage,
    beforeEnter: requireAdminAuth,
    meta: { title: 'Admin Dashboard' }
  },
  // {
  //   path: '/admin/users',
  //   name: 'AdminUsers',
  //   component: () => import('@/pages/AdminUsersPage.vue'),
  //   beforeEnter: requireAdminAuth,
  //   meta: { title: 'User Management' }
  // },
  // {
  //   path: '/admin/rooms',
  //   name: 'AdminRooms',
  //   component: () => import('@/pages/AdminRoomsPage.vue'),
  //   beforeEnter: requireAdminAuth,
  //   meta: { title: 'Room Management' }
  // },
  // {
  //   path: '/admin/bookings',
  //   name: 'AdminBookings',
  //   component: () => import('@/pages/AdminBookingsPage.vue'),
  //   beforeEnter: requireAdminAuth,
  //   meta: { title: 'Booking Management' }
  // },
  // {
  //   path: '/admin/reports',
  //   name: 'AdminReports',
  //   component: () => import('@/pages/AdminReportsPage.vue'),
  //   beforeEnter: requireAdminAuth,
  //   meta: { title: 'Reports' }
  // },

  // Static pages
  // {
  //   path: '/about',
  //   name: 'About',
  //   component: () => import('@/pages/AboutPage.vue'),
  //   meta: { title: 'About Us' }
  // },
  // {
  //   path: '/contact',
  //   name: 'Contact',
  //   component: () => import('@/pages/ContactPage.vue'),
  //   meta: { title: 'Contact Us' }
  // },
  // {
  //   path: '/terms',
  //   name: 'Terms',
  //   component: () => import('@/pages/TermsPage.vue'),
  //   meta: { title: 'Terms of Service' }
  // },
  // {
  //   path: '/privacy',
  //   name: 'Privacy',
  //   component: () => import('@/pages/PrivacyPage.vue'),
  //   meta: { title: 'Privacy Policy' }
  // },
  // {
  //   path: '/help',
  //   name: 'Help',
  //   component: () => import('@/pages/HelpPage.vue'),
  //   meta: { title: 'Help Center' }
  // },

  // 404 route - must be last
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFoundPage,
    meta: { title: 'Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // If there's a saved position (back/forward navigation), use it
    if (savedPosition) {
      return savedPosition
    }
    // If there's a hash, scroll to that element
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    }
    // Otherwise, scroll to top
    return { top: 0 }
  }
})

// Global navigation guard for setting page titles
router.beforeEach((to, from, next) => {
  // Set page title
  const defaultTitle = 'RoomRent'
  document.title = to.meta.title ? `${to.meta.title} - ${defaultTitle}` : defaultTitle
  
  next()
})

// Global error handler
router.onError((error) => {
  console.error('Router error:', error)
})

export default router