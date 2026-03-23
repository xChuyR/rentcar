<template>
  <main>
    <div class="bc"><div class="bc-inner">
      <RouterLink to="/">{{ $t('home') }}</RouterLink>
      <span class="bc-sep">›</span><span class="bc-current">{{ $t('contact') }}</span>
    </div></div>

    <section class="section" style="padding-top:3rem">
      <div class="section-inner">
        <span class="section-label">{{ $t('opinions') }}</span>
        <h1 class="section-title">{{ $t('contactTitle') }}</h1>
        <div class="divider"></div>
        <p class="section-sub">{{ $t('contactSub') }}</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:flex-start;margin-top:3rem">

          <!-- Formulario -->
          <div>
            <div class="alert-box alert-success" :class="{ show: sent }">
              ✅ ¡Gracias! Tu mensaje fue enviado correctamente. Te responderemos pronto.
            </div>
            <div class="alert-box alert-error" :class="{ show: apiErr }">⚠️ {{ apiErr }}</div>

            <form @submit.prevent="doSend" novalidate>
              <div class="field">
                <label>{{ $t('fullName') }} *</label>
                <input v-model="form.nombre" type="text" placeholder="Ana Martínez"
                  :class="{ error: errors.nombre }" />
                <span class="field-error" :class="{ show: errors.nombre }">{{ errors.nombre }}</span>
              </div>
              <div class="field">
                <label>{{ $t('emailAddress') }} *</label>
                <input v-model="form.email" type="email" placeholder="ana@correo.com"
                  :class="{ error: errors.email }" />
                <span class="field-error" :class="{ show: errors.email }">{{ errors.email }}</span>
              </div>
              <div class="field">
                <label>{{ $t('subject') }}</label>
                <input v-model="form.asunto" type="text" :placeholder="$t('subject')" />
              </div>
              <div class="field">
                <label>{{ $t('message') }} *</label>
                <textarea v-model="form.mensaje" rows="5" :placeholder="$t('message')"
                  :class="{ error: errors.mensaje }"></textarea>
                <span class="field-error" :class="{ show: errors.mensaje }">{{ errors.mensaje }}</span>
              </div>
              <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center"
                :disabled="loading">
                {{ loading ? $t('entering') : $t('sendMessage') }}
              </button>
            </form>
          </div>

          <!-- Info + mapa -->
          <div>
            <div style="display:flex;flex-direction:column;gap:.8rem;margin-bottom:1.75rem">
              <div class="card" style="padding:1.1rem;display:flex;gap:1rem;align-items:center">
                <span style="font-size:1.6rem">📍</span>
                <div>
                  <strong style="font-size:.9rem;display:block;margin-bottom:.1rem">{{ $t('visitUs') }}</strong>
                  <p style="font-size:.83rem;color:var(--muted)">314 Bertha B. de La Peña, Piedras Negras, Coahuila</p>
                </div>
              </div>
              <div class="card" style="padding:1.1rem;display:flex;gap:1rem;align-items:center">
                <span style="font-size:1.6rem">📞</span>
                <div>
                  <strong style="font-size:.9rem;display:block;margin-bottom:.1rem">{{ $t('callUs') }}</strong>
                  <a href="tel:+528611000000" style="font-size:.83rem;color:var(--muted)">+52 (861) 100-0000</a>
                </div>
              </div>
              <div class="card" style="padding:1.1rem;display:flex;gap:1rem;align-items:center">
                <span style="font-size:1.6rem">✉️</span>
                <div>
                  <strong style="font-size:.9rem;display:block;margin-bottom:.1rem">{{ $t('emailUs') }}</strong>
                  <a href="mailto:info@rentcar.mx" style="font-size:.83rem;color:var(--muted)">info@rentcar.mx</a>
                </div>
              </div>
              <div class="card" style="padding:1.1rem;display:flex;gap:1rem;align-items:center">
                <span style="font-size:1.6rem">🕐</span>
                <div>
                  <strong style="font-size:.9rem;display:block;margin-bottom:.1rem">Horario</strong>
                  <p style="font-size:.83rem;color:var(--muted)">Lunes – Domingo · 7:00 am – 10:00 pm</p>
                </div>
              </div>
            </div>

            <!-- Mapa OpenStreetMap — 314 Bertha B. de La Peña, Piedras Negras, Coahuila -->
            <!-- Coordenadas: 28.7006° N, 100.5234° W -->
            <div>
              <p style="font-size:.82rem;font-weight:700;margin-bottom:.6rem;font-family:var(--font-d)">📌 Nuestra ubicación</p>
              <div class="map-container">
                <iframe
                  src="https://www.openstreetmap.org/export/embed.html?bbox=-100.5384%2C28.6906%2C-100.5084%2C28.7106&layer=mapnik&marker=28.7006%2C-100.5234"
                  title="Mapa RentCar — 314 Bertha B. de La Peña, Piedras Negras, Coahuila"
                  loading="lazy"
                  aria-label="Mapa de ubicación de RentCar en Piedras Negras, Coahuila"
                ></iframe>
              </div>
              <div class="flex gap-1 mt-2" style="flex-wrap:wrap">
                <a
                  href="https://www.openstreetmap.org/?mlat=28.7006&mlon=-100.5234#map=16/28.7006/-100.5234"
                  target="_blank" rel="noopener"
                  class="btn btn-outline btn-sm"
                >🗺️ Ver en OpenStreetMap</a>
                <a
                  href="https://maps.google.com/?q=314+Bertha+B+de+La+Pena,+Piedras+Negras,+Coahuila,+Mexico"
                  target="_blank" rel="noopener"
                  class="btn btn-outline btn-sm"
                >📍 Abrir en Google Maps</a>
              </div>
              <p style="font-size:.7rem;color:var(--muted);margin-top:.4rem">
                Mapa © <a href="https://www.openstreetmap.org" target="_blank" style="color:var(--accent)">OpenStreetMap</a> contributors
              </p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section" style="background:var(--surface);padding-top:2rem">
      <div class="section-inner">
        <span class="section-label">FAQ</span>
        <h2 class="section-title">Preguntas frecuentes</h2>
        <div class="divider"></div>
        <div class="grid-2" style="margin-top:1.5rem">
          <div class="card" style="padding:1.4rem" v-for="faq in faqs" :key="faq.q">
            <h3 style="font-size:.97rem;font-family:var(--font-d);margin-bottom:.4rem">{{ faq.q }}</h3>
            <p style="font-size:.84rem;color:var(--muted);line-height:1.7">{{ faq.a }}</p>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/client'
import { useToast } from '@/composables/useToast'

const { success, error: toastError } = useToast()

const loading = ref(false)
const sent    = ref(false)
const apiErr  = ref('')
const form    = ref({ nombre: '', email: '', asunto: '', mensaje: '' })
const errors  = ref({ nombre: '', email: '', mensaje: '' })

const faqs = [
  { q: '¿Qué documentos necesito?', a: 'INE o pasaporte vigente, licencia de manejo y tarjeta de crédito/débito a tu nombre.' },
  { q: '¿El seguro está incluido?', a: 'Sí. Todos los vehículos incluyen seguro de responsabilidad civil. Cobertura total disponible como opción.' },
  { q: '¿Puedo cancelar mi reserva?', a: 'Cancelaciones con +48h de anticipación son gratuitas. Cancelaciones tardías aplican 20% del total.' },
  { q: '¿Ofrecen entrega a domicilio?', a: 'Sí, sin costo en Piedras Negras. Entrega foránea con cargo adicional según distancia.' },
]

function validate() {
  errors.value = { nombre: '', email: '', mensaje: '' }
  let ok = true
  if (!form.value.nombre.trim()) { errors.value.nombre = 'Requerido'; ok = false }
  if (!form.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) { errors.value.email = 'Correo inválido'; ok = false }
  if (!form.value.mensaje.trim() || form.value.mensaje.length < 10) { errors.value.mensaje = 'Mínimo 10 caracteres'; ok = false }
  return ok
}

async function doSend() {
  apiErr.value = ''
  if (!validate()) return
  loading.value = true
  try {
    await api.post('/contact', form.value)
    sent.value = true
    form.value = { nombre: '', email: '', asunto: '', mensaje: '' }
    success('✅ Mensaje enviado')
    setTimeout(() => sent.value = false, 8000)
  } catch (e) {
    apiErr.value = e.response?.data?.error || 'Error al enviar'
  } finally { loading.value = false }
}
</script>

<style scoped>
@media(max-width:900px){
  section .section-inner > div:nth-child(3){ grid-template-columns:1fr !important }
}
</style>
