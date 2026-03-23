<!-- NosotrosView.vue -->
<template>
  <main>
    <div class="bc"><div class="bc-inner"><RouterLink to="/">{{ $t('home') }}</RouterLink><span class="bc-sep">›</span><span class="bc-current">{{ $t('about') }}</span></div></div>
    <section class="section" style="padding-top:3rem"><div class="section-inner">
      <span class="section-label">{{ $t('theCompany') }}</span>
      <h1 class="section-title">{{ $t('whoWeAre') }}</h1>
      <div class="divider"></div>
      <p class="section-sub">{{ $t('aboutSub') }}</p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;margin-top:3rem">
        <div style="border-radius:1rem;overflow:hidden;aspect-ratio:4/3;background:linear-gradient(135deg,var(--navy),#0d2240);display:flex;align-items:center;justify-content:center">
          
          <img src="../assets/img/Ford_F150_Raptor.png" alt="">
        </div>
        <div>
          <p style="color:var(--muted);line-height:1.8;margin-bottom:1rem">{{ $t('history1') }}</p>
          <p style="color:var(--muted);line-height:1.8;margin-bottom:1.5rem">{{ $t('history2') }}</p>
          <h3 style="font-size:1rem;font-family:var(--font-d);font-weight:700;margin-bottom:.7rem">{{ $t('coreValues') }}</h3>
          <ul style="list-style:none;display:flex;flex-direction:column;gap:.6rem">
            <li v-for="v in valores" :key="v" style="display:flex;gap:.7rem;font-size:.9rem;color:var(--muted)">
              <span style="color:var(--accent);flex-shrink:0">✦</span>{{ v }}
            </li>
          </ul>
        </div>
      </div>

      <div style="margin-top:3rem">
        <h2 class="section-title" style="font-size:1.5rem">{{ $t('ourTeam') }}</h2>
        <div class="divider"></div>
        <div class="grid-4">
          <div class="card" style="padding:1.4rem;text-align:center" v-for="p in equipo" :key="p.name">
            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--accent));margin:0 auto .9rem;display:flex;align-items:center;justify-content:center;font-family:var(--font-d);font-size:1.3rem;color:#fff;font-weight:900">{{ p.name[0] }}</div>
            <strong style="font-size:.95rem">{{ p.name }}</strong>
            <p style="font-size:.78rem;color:var(--muted);margin-top:.2rem">{{ p.role }}</p>
          </div>
        </div>
      </div>
    </div></section>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'

const { t } = useI18nStore()
const valores = computed(() => [t('value1'), t('value2'), t('value3'), t('value4')])
const equipo  = [{ name:'Roberto Medina', role:'CEO & Fundador' },{ name:'Andrea López', role:'Dir. Operaciones' },{ name:'Jorge Fuentes', role:'Jefe de Flota' },{ name:'Sofía Torres', role:'Atención al Cliente' }]
</script>
