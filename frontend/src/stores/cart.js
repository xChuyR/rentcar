// src/stores/cart.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/client'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items   = ref([])
  const loading = ref(false)
  const open    = ref(false)

  const count = computed(() => items.value.length)
  const total = computed(() => items.value.reduce((s, i) => s + Number(i.subtotal), 0))

  async function fetch() {
    const auth = useAuthStore()
    if (!auth.isLoggedIn) return
    try {
      const { data } = await api.get('/cart')
      items.value = data.data.items
    } catch {}
  }

  async function add(autoId, diasRenta = 1, fechaInicio = null, fechaFin = null) {
    const auth = useAuthStore()
    if (!auth.isLoggedIn) return { needsLogin: true }
    loading.value = true
    try {
      await api.post('/cart', { auto_id: autoId, dias_renta: diasRenta, fecha_inicio: fechaInicio, fecha_fin: fechaFin })
      await fetch()
      return { ok: true }
    } finally {
      loading.value = false
    }
  }

  async function remove(autoId) {
    await api.delete('/cart', { data: { auto_id: autoId } })
    await fetch()
  }

  async function clear() {
    await api.delete('/cart')
    items.value = []
  }

  async function checkout() {
    // Aquí se integraría pasarela de pago real
    await clear()
    return true
  }

  return { items, loading, open, count, total, fetch, add, remove, clear, checkout }
})
