// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/',            name: 'home',      component: () => import('@/views/HomeView.vue') },
  { path: '/nosotros',    name: 'nosotros',  component: () => import('@/views/NosotrosView.vue') },
  { path: '/servicios',   name: 'servicios', component: () => import('@/views/ServiciosView.vue') },
  { path: '/contacto',    name: 'contacto',  component: () => import('@/views/ContactoView.vue') },
  { path: '/foda',        name: 'foda',      component: () => import('@/views/FodaView.vue') },
  { path: '/login',       name: 'login',     component: () => import('@/views/LoginView.vue'),    meta: { guestOnly: true } },
  { path: '/registro',    name: 'registro',  component: () => import('@/views/RegisterView.vue'), meta: { guestOnly: true } },
  { path: '/reset-password', name: 'reset',  component: () => import('@/views/ResetPasswordView.vue') },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('@/views/AdminView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 })
})

router.beforeEach(to => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'home' }
  }
  if (to.meta.guestOnly && auth.isLoggedIn) {
    return { name: 'home' }
  }
})

export default router
