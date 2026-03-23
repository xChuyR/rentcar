<template>
  <main>
    <!-- Breadcrumb -->
    <div class="bc"><div class="bc-inner">
      <RouterLink to="/" class="bc a">{{ $t('home') }}</RouterLink>
      <span class="bc-sep">›</span><span class="bc-current">{{ $t('services') }}</span>
    </div></div>

    <section class="section" style="padding-top:3rem">
      <div class="section-inner">
        <span class="section-label">{{ $t('catalogTitle') }}</span>
        <h1 class="section-title">{{ $t('ourServices') }}</h1>
        <div class="divider"></div>
        <p class="section-sub">{{ $t('catalogSub') }}</p>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center justify-between gap-1 mt-3 mb-2">
          <div class="flex flex-wrap gap-1">
            <button v-for="f in filters" :key="f.value"
              class="btn btn-sm" :class="activeFilter === f.value ? 'btn-primary' : 'btn-outline'"
              @click="activeFilter = f.value">
              {{ f.label }}
            </button>
          </div>
          
          <div v-if="searchQuery" class="search-info flex items-center gap-1">
            <span class="text-muted" style="font-size:.85rem">
              {{ $t('resultsFor') }}: <strong>"{{ searchQuery }}"</strong>
            </span>
            <RouterLink to="/servicios" class="btn btn-xs btn-outline" @click="activeFilter = 'all'">
              ✕ {{ $t('clear') }}
            </RouterLink>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" style="text-align:center;padding:4rem;color:var(--muted)">
          <div style="font-size:2rem;margin-bottom:.5rem">🔄</div>
          <p>{{ $t('loadingVehicles') }}</p>
        </div>

        <!-- Grid -->
        <div v-else class="grid-auto mt-3">
          <article v-for="car in filteredCars" :key="car.id" class="car-card">
            <div class="car-img" :style="{ background: bgMap[car.tipo] || '#1e3a5f' }">
              <img v-if="car.imagen_url" :src="car.imagen_url" :alt="car.nombre" />
              <div v-else class="no-img">
                <svg viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="20" y="50" width="160" height="50" rx="10" fill="white" fill-opacity=".18"/>
                  <path d="M40 50 Q60 25 100 22 Q140 25 160 50Z" fill="white" fill-opacity=".28"/>
                  <circle cx="55" cy="100" r="16" fill="white" fill-opacity=".3"/>
                  <circle cx="145" cy="100" r="16" fill="white" fill-opacity=".3"/>
                  <circle cx="55" cy="100" r="8" fill="white" fill-opacity=".5"/>
                  <circle cx="145" cy="100" r="8" fill="white" fill-opacity=".5"/>
                </svg>
              </div>
              <span class="car-type-badge badge badge-accent">{{ car.tipo }}</span>
            </div>
            <div class="car-body">
              <h3 class="car-name">{{ car.nombre }}</h3>
              <p class="car-meta">{{ car.año }} · {{ car.transmision }} · {{ car.motor }}</p>
              <div class="car-price">${{ Number(car.precio_dia).toLocaleString() }} <span>/ {{ $t('priceDay').toLowerCase() }}</span></div>
              <div class="car-feats">
                <span v-for="f in (car.caracteristicas || []).slice(0,4)" :key="f" class="car-feat-tag">{{ f }}</span>
              </div>
              <div class="car-actions">
                <button class="btn btn-primary btn-sm" @click="addToCart(car)">
                  {{ addingId === car.id ? $t('added') : $t('addToCart') }}
                </button>
                <button class="btn btn-outline btn-sm" @click="toggleExtra(car.id)">
                  {{ expandedId === car.id ? $t('hide') : $t('moreInfo') }}
                </button>
              </div>
              <div class="car-extra" :class="{ open: expandedId === car.id }">
                <p>{{ car.descripcion }}</p>
                <p style="margin-top:.5rem;font-size:.75rem">
                  👥 {{ car.pasajeros }} {{ $t('passengers') }} &nbsp;|&nbsp;
                  🛡️ {{ $t('insuranceIncluded') }} &nbsp;|&nbsp;
                  📍 {{ $t('deliveryAvailable') }}
                </p>
              </div>
            </div>
          </article>

          <div v-if="filteredCars.length === 0 && !loading"
            style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted)">
            {{ $t('noVehicles') }}
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla comparativa -->
    <section class="section" style="background:var(--surface);padding-top:2rem">
      <div class="section-inner">
        <span class="section-label">Comparación</span>
        <h2 class="section-title">{{ $t('comparativeTable') }}</h2>
        <div class="divider"></div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>{{ $t('vehicle') }}</th><th>{{ $t('type') }}</th><th>{{ $t('priceDay') }}</th>
              <th>GPS</th><th>A/C</th><th>4×4</th><th>Autopilot</th><th>{{ $t('insuranceIncluded').split(' ')[0] }}</th>
            </tr></thead>
            <tbody>
              <tr v-for="car in allCars.slice(0,6)" :key="'cmp-'+car.id">
                <td><strong>{{ car.nombre }}</strong></td>
                <td>{{ car.tipo }}</td>
                <td style="font-family:var(--font-d);font-weight:700">${{ Number(car.precio_dia).toLocaleString() }}</td>
                <td>{{ hasFeat(car,'GPS') ? '✔' : '✘' }}</td>
                <td>{{ hasFeat(car,'A/C') ? '✔' : '✘' }}</td>
                <td>{{ hasFeat(car,'4x4') || hasFeat(car,'4X4') ? '✔' : '✘' }}</td>
                <td>{{ hasFeat(car,'Autopilot') ? '✔' : '✘' }}</td>
                <td>✔</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/api/client'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { useRouter } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'

const i18n = useI18nStore()
const { t } = i18n

const cart   = useCartStore()
const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()
const { success, error, info } = useToast()

const allCars      = ref([])
const loading      = ref(true)
const activeFilter = ref('all')
const expandedId   = ref(null)
const addingId     = ref(null)

const filters = computed(() => [
  { value: 'all', label: t('all') },
  { value: 'SUV', label: 'SUV' },
  { value: 'Sedan', label: i18n.lang === 'en' ? 'Sedan' : 'Sedán' },
  { value: 'Deportivo', label: i18n.lang === 'en' ? 'Sports' : 'Deportivo' },
  { value: 'Pickup', label: 'Pickup' },
  { value: 'Eléctrico', label: i18n.lang === 'en' ? 'Electric' : 'Eléctrico' },
])

const bgMap = {
  SUV:       'linear-gradient(135deg,#1e3a5f,#0d2240)',
  Sedan:     'linear-gradient(135deg,#2d5016,#0f2008)',
  Deportivo: 'linear-gradient(135deg,#5f1e1e,#1a0606)',
  Pickup:    'linear-gradient(135deg,#4a3800,#1a1200)',
  Eléctrico: 'linear-gradient(135deg,#003f5f,#001828)',
  Minivan:   'linear-gradient(135deg,#3d1a6e,#1a0830)',
}

const searchQuery = computed(() => route.query.q || '')

const filteredCars = computed(() => {
  let list = allCars.value

  // Filtrar por categoría
  if (activeFilter.value !== 'all') {
    list = list.filter(c => c.tipo === activeFilter.value)
  }

  // Filtrar por búsqueda
  const q = searchQuery.value.toString().toLowerCase()
  if (q) {
    list = list.filter(c => 
      c.nombre.toLowerCase().includes(q) || 
      c.marca.toLowerCase().includes(q) || 
      c.modelo.toLowerCase().includes(q) ||
      (c.descripcion || '').toLowerCase().includes(q)
    )
  }

  return list
})

async function loadCars() {
  loading.value = true
  try {
    const { data } = await api.get('/cars')
    allCars.value = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
  } catch { error('Error al cargar vehículos') }
  finally { loading.value = false }
}

function toggleExtra(id) { expandedId.value = expandedId.value === id ? null : id }

async function addToCart(car) {
  if (!auth.isLoggedIn) {
    info('🔒 Inicia sesión para agregar al carrito')
    router.push('/login')
    return
  }
  addingId.value = car.id
  try {
    const res = await cart.add(car.id, 1)
    if (res?.needsLogin) { router.push('/login'); return }
    success(`🛒 "${car.nombre}" agregado al carrito`)
    cart.open = true
  } catch { error('Error al agregar al carrito') }
  finally { setTimeout(() => { addingId.value = null }, 1800) }
}

function hasFeat(car, feat) {
  return (car.caracteristicas || []).some(f => f.toLowerCase().includes(feat.toLowerCase()))
}

// Limpiar filtros cuando cambia la búsqueda si es necesario
watch(searchQuery, (newQ) => {
  if (newQ) activeFilter.value = 'all'
})

onMounted(loadCars)
</script>
