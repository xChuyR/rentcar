<template>
  <div>

    <!-- ── Barra sesión ── -->
    <div v-if="auth.isLoggedIn" class="session-bar">
      <div class="s-avatar">{{ auth.initials }}</div>
      <span class="s-name">{{ auth.user.nombre }} {{ auth.user.apellido }}</span>
      <span class="s-role" :class="auth.user.rol">
        {{ auth.user.rol === 'admin' ? '⚙️ Admin' : '👤 Cliente' }}
      </span>
      <button class="btn-logout" @click="handleLogout">{{ t('logout') }}</button>
    </div>

    <!-- ── Barra accesibilidad ── -->
    <div class="a11y-bar">
      <span class="label">♿</span>
      <button class="nav-btn" @click="toggleContrast">
        {{ highContrast ? '⬛ ON' : t('contrast') }}
      </button>
      <button class="nav-btn" @click="changeFontSize(2)">A+</button>
      <button class="nav-btn" @click="changeFontSize(-2)">A−</button>
      <button class="nav-btn" @click="toggleTTS">
        {{ ttsActive ? t('stop') : t('read') }}
      </button>
    </div>

    <!-- ── Header ── -->
    <header class="site-header">
      <div class="nav-inner">
        <RouterLink to="/" class="nav-logo">Rent<span>Car</span></RouterLink>

        <!-- Búsqueda -->
        <div class="search-wrap" ref="searchWrap">
          <span class="search-icon">🔍</span>
          <input
            v-model="searchQ"
            type="text"
            class="search-input"
            :placeholder="t('searchPlaceholder')"
            @input="onSearch"
            @keydown.escape="searchOpen = false"
          />
          <div class="search-results" :class="{ open: searchOpen }">
            <template v-if="searchResults.length">
              <RouterLink
                v-for="r in searchResults" :key="r.url"
                :to="r.url" class="sr-item"
                @click="searchOpen = false"
              >
                {{ r.type === 'car' ? '🚗' : '🔍' }} {{ r.title }}
              </RouterLink>
            </template>
            <div v-else class="sr-item text-muted">{{ t('noResults') }}</div>
          </div>
        </div>

        <!-- Nav links -->
        <nav>
          <ul class="nav-links" :class="{ 'mobile-open': mobileOpen }">
            <li><RouterLink to="/" @click="mobileOpen=false">{{ t('home') }}</RouterLink></li>
            <li><RouterLink to="/nosotros" @click="mobileOpen=false">{{ t('about') }}</RouterLink></li>
            <li><RouterLink to="/servicios" @click="mobileOpen=false">{{ t('services') }}</RouterLink></li>
            <li><RouterLink to="/foda" @click="mobileOpen=false">{{ t('foda') }}</RouterLink></li>
            <li><RouterLink to="/contacto" @click="mobileOpen=false">{{ t('contact') }}</RouterLink></li>
            <li v-if="auth.isAdmin">
              <RouterLink to="/admin" @click="mobileOpen=false" style="color:var(--accent)">{{ t('admin') }}</RouterLink>
            </li>
          </ul>
        </nav>

        <div class="nav-actions">
          <button class="nav-btn" @click="toggleLang">{{ lang === 'es' ? '🇺🇸 EN' : '🇲🇽 ES' }}</button>
          <button class="nav-btn" @click="toggleDark">{{ isDark ? t('darkOff') : t('darkOn') }}</button>

          <!-- Carrito -->
          <div class="cart-btn-wrap">
            <button class="btn btn-primary btn-sm" @click="cart.open = !cart.open">
              🛒 {{ t('cart') }}
            </button>
            <span v-if="cart.count > 0" class="cart-badge">{{ cart.count }}</span>
          </div>

          <RouterLink v-if="!auth.isLoggedIn" to="/login" class="btn btn-primary btn-sm">
            {{ t('login') }}
          </RouterLink>

          <!-- Hamburger -->
          <button class="nav-btn" @click="mobileOpen = !mobileOpen" id="hambBtn">☰</button>
        </div>
      </div>
    </header>

    <!-- ── Panel Carrito ── -->
    <aside class="cart-panel" :class="{ open: cart.open }">
      <div class="cart-ph">
        <h2 style="font-family:var(--font-d);font-size:1.1rem">{{ t('cart') }}</h2>
        <button class="modal-close" @click="cart.open = false">✕</button>
      </div>

      <div class="cart-body">
        <p v-if="!cart.items.length" class="text-muted" style="text-align:center;padding:2rem 0">
          {{ t('cartEmpty') }}
        </p>
        <div v-for="item in cart.items" :key="item.auto_id" class="cart-item">
          <div style="width:60px;height:45px;border-radius:.4rem;overflow:hidden;flex-shrink:0;background:var(--border)">
            <img v-if="item.imagen_url" :src="item.imagen_url" :alt="item.nombre" style="width:100%;height:100%;object-fit:cover" />
            <div v-else style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.2rem">🚗</div>
          </div>
          <div class="cart-item-info">
            <strong>{{ item.nombre }}</strong>
            <p class="cart-item-meta">{{ item.tipo }} · ${{ item.precio_dia }}/día × {{ item.dias_renta }} días</p>
            <p class="cart-item-price">${{ Number(item.subtotal).toLocaleString() }}</p>
          </div>
          <button class="btn btn-xs btn-danger" @click="cart.remove(item.auto_id)">✕</button>
        </div>
      </div>

      <div class="cart-pf">
        <div class="cart-total-row">
          <span class="text-muted" style="font-size:.85rem">{{ t('cartTotal') }}</span>
          <span class="cart-total-amt">${{ cart.total.toLocaleString() }}</span>
        </div>
        <div class="flex gap-1">
          <button class="btn btn-outline btn-sm" @click="onClear">{{ t('cartClear') }}</button>
          <button class="btn btn-primary btn-sm" @click="onCheckout" style="flex:1">{{ t('cartConfirm') }}</button>
        </div>
      </div>
    </aside>

    <!-- ── Contenido ── -->
    <RouterView v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <component :is="Component" />
      </Transition>
    </RouterView>

    <!-- ── Footer ── -->
    <footer class="site-footer">
      <div class="footer-inner">
        <div class="footer-top">
          <div class="footer-brand">
            <RouterLink to="/" class="nav-logo">Rent<span>Car</span></RouterLink>
            <p>{{ t('footerDesc') }}</p>
          </div>
          <nav class="footer-col">
            <h4>{{ t('footerSite') }}</h4>
            <ul>
              <li><RouterLink to="/">{{ t('home') }}</RouterLink></li>
              <li><RouterLink to="/nosotros">{{ t('about') }}</RouterLink></li>
              <li><RouterLink to="/servicios">{{ t('services') }}</RouterLink></li>
              <li><RouterLink to="/foda">{{ t('foda') }}</RouterLink></li>
              <li><RouterLink to="/contacto">{{ t('contact') }}</RouterLink></li>
            </ul>
          </nav>
          <div class="footer-col">
            <h4>{{ t('footerContact') }}</h4>
            <ul>
              <li><a href="mailto:info@rentcar.mx">info@rentcar.mx</a></li>
              <li><a href="tel:+528611000000">+52 (861) 100-0000</a></li>
              <li><a>314 Bertha B. de La Peña</a></li>
              <li><a>Piedras Negras, Coahuila</a></li>
              <li><a>{{ t('footerSchedule') }}</a></li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <!-- Jesus estuvo aqui -->
          <span>{{ t('rights') }}</span>
          <span>{{ t('tech') }}</span>
        </div>
      </div>
    </footer>

    <!-- ── Toasts ── -->
    <div class="toast-container">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="toast show"
        :class="t.type"
      >{{ t.message }}</div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useToast } from '@/composables/useToast'
import { useI18nStore } from '@/stores/i18n'
import api from '@/api/client'

const i18n   = useI18nStore()
const { t, toggleLang } = i18n
const lang   = computed(() => i18n.lang)

const auth   = useAuthStore()
const cart   = useCartStore()
const router = useRouter()
const { toasts, success, error } = useToast()

// ── Dark mode — aplica a body para que el CSS funcione ──
const isDark = ref(localStorage.getItem('rc_dark') === 'true')

function applyDark(val) {
  document.body.classList.toggle('dark', val)
  localStorage.setItem('rc_dark', val)
  isDark.value = val
}

const toggleDark = () => applyDark(!isDark.value)

// ── High contrast ──
const highContrast = ref(localStorage.getItem('rc_contrast') === 'true')
const toggleContrast = () => {
  highContrast.value = !highContrast.value
  document.body.classList.toggle('high-contrast', highContrast.value)
  localStorage.setItem('rc_contrast', highContrast.value)
}

// ── Font size ──
let fontSize = parseInt(localStorage.getItem('rc_fontsize') || '16')
document.documentElement.style.fontSize = fontSize + 'px'
const changeFontSize = (delta) => {
  fontSize = Math.max(12, Math.min(24, fontSize + delta))
  document.documentElement.style.fontSize = fontSize + 'px'
  localStorage.setItem('rc_fontsize', fontSize)
}

// ── TTS ──
const ttsActive = ref(false)
const toggleTTS = () => {
  if (ttsActive.value) { speechSynthesis.cancel(); ttsActive.value = false; return }
  const main = document.querySelector('main') || document.body
  const utt  = new SpeechSynthesisUtterance(main.innerText.slice(0, 3000))
  utt.lang = 'es-MX'; utt.rate = 0.95
  utt.onend = () => { ttsActive.value = false }
  speechSynthesis.speak(utt)
  ttsActive.value = true
}

// ── Mobile nav ──
const mobileOpen = ref(false)

// ── Search ──
const searchQ    = ref('')
const searchOpen = ref(false)
const searchWrap = ref(null)

const INDEX = computed(() => [
  { title: t('home'),      url: '/' },
  { title: t('about'),     url: '/nosotros' },
  { title: t('services'),  url: '/servicios' },
  { title: t('contact'),   url: '/contacto' },
  { title: t('foda'),      url: '/foda' },
  { title: t('login'),     url: '/login' },
  { title: t('register'),  url: '/registro' },
])

const searchResults = ref([])
const onSearch = async () => {
  const q = searchQ.value.trim().toLowerCase()
  if (q.length < 2) { 
    searchOpen.value = false
    searchResults.value = []
    return 
  }

  // 1. Filtrar páginas estáticas
  const pageMatches = INDEX.value
    .filter(i => i.title.toLowerCase().includes(q))
    .map(i => ({ ...i, type: 'page' }))

  // 2. Buscar autos en el backend
  let carMatches = []
  try {
    const { data } = await api.get('/cars', { params: { q } })
    const cars = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
    carMatches = cars.map(c => ({
      title: `${c.marca} ${c.nombre}`,
      url: `/servicios?q=${encodeURIComponent(c.nombre)}`,
      type: 'car'
    }))
  } catch (err) {
    console.error('Search error:', err)
  }

  searchResults.value = [...pageMatches, ...carMatches]
  searchOpen.value = true
}

const closeSearch = (e) => { if (searchWrap.value && !searchWrap.value.contains(e.target)) searchOpen.value = false }
onMounted(() => {
  // Aplicar dark mode al body desde el inicio
  applyDark(localStorage.getItem('rc_dark') === 'true')
  document.addEventListener('click', closeSearch)
  if (auth.isLoggedIn) cart.fetch()
  if (highContrast.value) document.body.classList.add('high-contrast')
  // Navegación por teclado
  document.addEventListener('keydown', e => { if (e.key === 'Tab') document.body.classList.add('keyboard-nav') })
  document.addEventListener('mousedown', () => document.body.classList.remove('keyboard-nav'))
})
onUnmounted(() => document.removeEventListener('click', closeSearch))

// ── Logout ──
async function handleLogout() {
  await auth.logout()
  success('👋 Sesión cerrada')
  router.push('/')
}

// ── Cart actions ──
async function onClear() { await cart.clear(); success('🗑️ Carrito vaciado') }
async function onCheckout() {
  await cart.checkout()
  cart.open = false
  success('🚗 ¡Reserva confirmada! Te contactaremos pronto.', 'success', 5000)
}
</script>

<style>
body.high-contrast {
  --bg: #000 !important;
  --surface: #111 !important;
  --border: #ffff00 !important;
  --text: #fff !important;
  --muted: #ffff00 !important;
  --accent: #ffff00 !important;
  --navy: #ffff00 !important;
}
body.high-contrast * { border-color: #ffff00 !important; }
body.keyboard-nav :focus { outline: 3px solid var(--accent) !important; outline-offset: 3px !important; }

/* Hamburger oculto en desktop */
#hambBtn { display: none; }

@media(max-width:768px) {
  .nav-links { display: none !important; }
  .nav-links.mobile-open {
    display: flex !important; flex-direction: column;
    position: fixed; top: 100px; left: 0; right: 0;
    background: var(--surface); padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    z-index: 99; gap: 1.1rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
  }
  #hambBtn { display: flex !important; }
  .search-wrap { display: none; }
}
</style>
