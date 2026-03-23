// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/client'

export const useAuthStore = defineStore('auth', () => {
  const token   = ref(localStorage.getItem('rc_token') || null)
  const user    = ref(JSON.parse(localStorage.getItem('rc_user') || 'null'))
  const loading = ref(false)

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin    = computed(() => user.value?.rol === 'admin')
  const initials   = computed(() => {
    if (!user.value) return '?'
    return (user.value.nombre?.[0] || '') + (user.value.apellido?.[0] || '')
  })

  // Paso 1 login — devuelve { temp_token, email_hint }
  async function login(email, password) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/login', { email, password })
      return data.data
    } finally {
      loading.value = false
    }
  }

  // Paso 2 MFA — devuelve { token, user }
  async function verifyMFA(code, tempToken) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/verify-mfa',
        { code },
        { headers: { Authorization: `Bearer ${tempToken}` } }
      )
      setSession(data.data.token, data.data.user)
      return data.data
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/register', payload)
      return data
    } finally {
      loading.value = false
    }
  }

  async function requestReset(email) {
    const { data } = await api.post('/auth/request-reset', { email })
    return data
  }

  async function resetPassword(payload) {
    const { data } = await api.post('/auth/reset-password', payload)
    return data
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch {}
    clearSession()
  }

  function setSession(t, u) {
    token.value = t
    user.value  = u
    localStorage.setItem('rc_token', t)
    localStorage.setItem('rc_user', JSON.stringify(u))
  }

  function clearSession() {
    token.value = null
    user.value  = null
    localStorage.removeItem('rc_token')
    localStorage.removeItem('rc_user')
  }

  return { token, user, loading, isLoggedIn, isAdmin, initials,
           login, verifyMFA, register, requestReset, resetPassword, logout }
})
