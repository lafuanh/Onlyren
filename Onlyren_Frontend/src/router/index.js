import { createRouter, createWebHistory } from 'vue-router'
import { getCurrentUser } from '@/api/auth'

// Import views/pages
import HomePage from '@/pages/HomePage.vue'
import SearchPage from '@/pages/SearchPage.vue'
import RoomDetailPage from '@/pages/RoomDetail.vue'
import UserProfilePage from '@/pages/UserProfilePage.vue'
import RenterProfilePage from '@/pages/RenterProfilePage.vue'
import MessagesPage from '@/pages/MessagesPage.vue'

import AuthPages from '@/pages/AuthPages.vue' // Import AuthPages.vue

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


// Renter-specific middleware
const requireRenterAuth = async (to, from, next) => {
  try {
    const user = await getCurrentUser()
    if (user && user.is_renter) {
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
  {
    path: '/',
    name: 'Home',
    component: HomePage // No auth guard needed, accessible by anyone
  },
  {
    path: '/search',
    name: 'Search',
    component: SearchPage // No auth guard needed, accessible by anyone
  },
  {
    path: '/rooms/:id',
    name: 'RoomDetail',
    component: RoomDetailPage,
    props: true
  },
  {
    path: '/login',
    name: 'Login',
    component: AuthPages,
    beforeEnter: requireGuest // Ensure only guest users can access the login page
  },
  {
    path: '/register',
    name: 'Register',
    component: AuthPages,
    beforeEnter: requireGuest // Ensure only guest users can access the register page
  },
  {
    path: '/profile',
    name: 'UserProfile',
    component: UserProfilePage,
    beforeEnter: requireAuth // Protect the profile page from unauthorized users
  },
  {
    path: '/renter/profile',
    name: 'RenterProfile',
    component: RenterProfilePage,
    beforeEnter: requireRenterAuth // Protect the renter profile page from non-renter users
  },
  {
    path: '/messages',
    name: 'Messages',
    component: MessagesPage,
    beforeEnter: requireAuth // Protect messages page from non-authenticated users
  },
  {
    path: '/renter/rooms/new',
    name: 'NewRoom',
    component: () => import('@/pages/NewRoomPage.vue'),
    beforeEnter: requireRenterAuth // Protect this page from non-renter users
  },
  {
    path: '/renter/rooms/:id/edit',
    name: 'EditRoom',
    component: () => import('@/pages/EditRoomPage.vue'),
    beforeEnter: requireRenterAuth,
    props: true
  },

  // {
  //   path: '/reservations',
  //   name: 'Reservations',
  //   component: () => import('@/pages/ReservationsPage.vue'),
  //   beforeEnter: requireAuth // Protect the reservations page from non-authenticated users
  // },
  // 404 route
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/pages/NotFoundPage.vue')
  }



]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return { top: 0 } // Always scroll to top on route change
  }
})

export default router
