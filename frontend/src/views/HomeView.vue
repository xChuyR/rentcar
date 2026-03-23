<!-- HomeView.vue -->
<template>
  <main>
    <!-- HERO -->
    <section class="hero">
      <div>
        <p class="hero-eyebrow">{{ $t('heroEyebrow') }}</p>
        <h1 :innerHTML="slides[slideIdx].title"></h1>
        <p class="hero-desc">{{ slides[slideIdx].desc }}</p>
        <div class="flex gap-2 flex-wrap">
          <RouterLink to="/servicios" class="btn btn-primary">{{ $t('viewVehicles') }}</RouterLink>
          <button class="btn btn-outline" @click="nextSlide">{{ $t('exploreMore') }}</button>
        </div>
        <div class="hero-stats">
          <div class="stat-item"><strong>120+</strong><span>{{ $t('statsVehicles') }}</span></div>
          <div class="stat-item"><strong>8,500+</strong><span>{{ $t('statsClients') }}</span></div>
          <div class="stat-item"><strong>15</strong><span>{{ $t('statsExperience') }}</span></div>
        </div>
      </div>
      <div class="hero-visual" aria-hidden="true">
        <img src="../assets/img/Tesla Model S.png" alt="">
        <span class="hero-badge">✦ Flota 2024–2025</span>
      </div>
    </section>

    <!-- Features -->
    <section class="section" style="background:var(--surface);padding-top:3rem">
      <div class="section-inner">
        <span class="section-label">{{ $t('whyChooseUs') }}</span>
        <h2 class="section-title">{{ $t('bestExperience') }}</h2>
        <div class="divider"></div>
        <div class="grid-4 mt-3">
          <div class="card" style="padding:1.6rem" v-for="f in feats" :key="f.title">
            <div style="font-size:2rem;margin-bottom:.8rem">{{ f.icon }}</div>
            <h3 style="font-size:1rem;margin-bottom:.4rem">{{ f.title }}</h3>
            <p style="font-size:.83rem;color:var(--muted);line-height:1.65">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Promo -->
    <section class="promo-banner">
      <h2 :innerHTML="$t('promoTitle')"></h2>
      <p :innerHTML="$t('promoDesc')"></p>
      <RouterLink to="/servicios" class="btn btn-accent">{{ $t('checkAvailability') }}</RouterLink>
    </section>

    <!-- Testimonios -->
    <section class="section">
      <div class="section-inner">
        <span class="section-label">{{ $t('opinions') }}</span>
        <h2 class="section-title">{{ $t('whatClientsSay') }}</h2>
        <div class="divider"></div>
        <div class="grid-3 mt-3">
          <div class="card" style="padding:1.5rem" v-for="testimonial in testimonials" :key="testimonial.name">
            <p style="font-size:.88rem;color:var(--muted);line-height:1.7;font-style:italic;margin-bottom:1rem">"{{ testimonial.text }}"</p>
            <div class="flex items-center gap-1">
              <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-d);font-weight:900;font-size:.9rem;flex-shrink:0">{{ testimonial.name[0] }}</div>
              <div><strong style="font-size:.88rem;display:block">{{ testimonial.name }}</strong><span style="font-size:.75rem;color:var(--muted)">{{ testimonial.city }}</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'

const { t } = useI18nStore()

const slideIdx = ref(0)
const slides = computed(() => [
  { title: t('heroSlide1Title'), desc: t('heroSlide1Desc') },
  { title: t('heroSlide2Title'), desc: t('heroSlide2Desc') },
  { title: t('heroSlide3Title'), desc: t('heroSlide3Desc') },
])
function nextSlide() { slideIdx.value = (slideIdx.value + 1) % slides.value.length }

const feats = computed(() => [
  { icon: '🚗', title: t('featFlotaTitle'), desc: t('featFlotaDesc') },
  { icon: '🛡️', title: t('featSeguroTitle'), desc: t('featSeguroDesc') },
  { icon: '⚡', title: t('featReservaTitle'), desc: t('featReservaDesc') },
  { icon: '📍', title: t('featEntregaTitle'), desc: t('featEntregaDesc') },
])

const testimonials = [
  { name: 'María González', city: 'Piedras Negras', text: 'Excelente servicio. El auto llegó limpio y en perfectas condiciones. Sin duda volvería.' },
  { name: 'Carlos Ramírez', city: 'Monterrey', text: 'El proceso fue súper fácil. El SUV Hybrid es espacioso y muy cómodo para viajes largos.' },
  { name: 'Laura Vásquez', city: 'Saltillo', text: 'Precios justos, atención impecable. Renté por una semana y todo salió perfecto. ¡10 de 10!' },
]
</script>
