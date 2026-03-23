// src/api/client.js
import axios from 'axios'

// En desarrollo (npm run dev): Vite hace proxy de /api → rentcar.test
// En producción (archivos estáticos en Laragon): /api apunta directo al backend
const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json' },
  withCredentials: false,
  timeout: 15000,
})

// Adjuntar token JWT en cada request automáticamente
api.interceptors.request.use(cfg => {
  const token = localStorage.getItem('rc_token')
  if (token) cfg.headers.Authorization = `Bearer ${token}`
  return cfg
})

// Manejar errores globalmente
api.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      // Token expirado o inválido — limpiar sesión y redirigir
      localStorage.removeItem('rc_token')
      localStorage.removeItem('rc_user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(err)
  }
)

export default api
