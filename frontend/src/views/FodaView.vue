<template>
  <main>
    <div class="bc"><div class="bc-inner"><RouterLink to="/">{{ $t('home') }}</RouterLink><span class="bc-sep">›</span><span class="bc-current">{{ $t('foda') }}</span></div></div>

    <section class="section" style="padding-top:3rem"><div class="section-inner">
      <span class="section-label">{{ $t('identityLabel') }}</span>
      <h1 class="section-title">{{ $t('swotTitle') }}</h1>
      <div class="divider"></div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem">
        <div class="card" style="padding:2rem">
          <div class="flex items-center gap-2" style="margin-bottom:1.2rem">
            <span style="font-size:2.2rem">🎯</span>
            <div><span style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent)">Declaración</span><h2 style="font-family:var(--font-d);font-size:1.35rem;font-weight:900">{{ $t('missionLabel') }}</h2></div>
          </div>
          <p style="color:var(--muted);line-height:1.8;font-size:.95rem">{{ $t('missionText') }}</p>
        </div>
        <div class="card" style="padding:2rem">
          <div class="flex items-center gap-2" style="margin-bottom:1.2rem">
            <span style="font-size:2.2rem">🔭</span>
            <div><span style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent)">Horizonte 2030</span><h2 style="font-family:var(--font-d);font-size:1.35rem;font-weight:900">{{ $t('visionLabel') }}</h2></div>
          </div>
          <p style="color:var(--muted);line-height:1.8;font-size:.95rem">{{ $t('visionText') }}</p>
        </div>
      </div>

      <!-- Valores -->
      <div style="background:linear-gradient(135deg,var(--navy),#0d2240);border-radius:1rem;padding:2rem;color:#fff;margin-bottom:2.5rem">
        <h2 style="color:#fff;font-family:var(--font-d);font-size:1.3rem;font-weight:900;margin-bottom:1.2rem">{{ $t('corporateValues') }}</h2>
        <div class="grid-4">
          <div v-for="v in valores" :key="v.title" style="text-align:center;padding:.9rem;background:rgba(255,255,255,.08);border-radius:.65rem">
            <div style="font-size:1.65rem;margin-bottom:.4rem">{{ v.icon }}</div>
            <strong style="font-size:.85rem;display:block;margin-bottom:.2rem">{{ v.title }}</strong>
            <p style="font-size:.73rem;opacity:.72;line-height:1.5">{{ v.desc }}</p>
          </div>
        </div>
      </div>

      <!-- Matriz FODA -->
      <span class="section-label">{{ $t('strategicAnalysis') }}</span>
      <h2 class="section-title">{{ $t('swotMatrix') }}</h2>
      <div class="divider"></div>
      <div class="foda-grid">
        <div v-for="q in foda" :key="q.id" class="foda-card" :class="q.id">
          <div class="flex items-center gap-2" style="margin-bottom:1rem">
            <span style="font-size:1.5rem">{{ q.icon }}</span>
            <div>
              <span style="font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" :style="{ color: q.color }">{{ q.tag }}</span>
              <h3 style="font-family:var(--font-d);font-weight:700;font-size:1rem" :style="{ color: q.color }">{{ q.title }}</h3>
            </div>
          </div>
          <ul class="foda-list">
            <li v-for="item in q.items" :key="item">{{ item }}</li>
          </ul>
        </div>
      </div>

      <!-- Estrategias -->
      <h3 style="font-family:var(--font-d);font-size:1.3rem;font-weight:700;margin:2rem 0 1rem">{{ $t('strategiesDerived') }}</h3>
      <div class="grid-2">
        <div class="card" style="padding:1.4rem" v-for="s in estrategias" :key="s.label">
          <h4 style="font-size:.9rem;font-weight:700;margin-bottom:.5rem" :style="{ color: s.color }">{{ s.label }}</h4>
          <p style="font-size:.83rem;color:var(--muted);line-height:1.65">{{ s.text }}</p>
        </div>
      </div>
    </div></section>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18nStore } from '@/stores/i18n'

const { t } = useI18nStore()
const i18n = useI18nStore()

const valores = computed(() => [
  { icon:'🤝', title:t('en' === i18n.lang ? 'Trust' : 'Confianza'), desc:t('en' === i18n.lang ? 'Transparency in every process.' : 'Transparencia en cada proceso.') },
  { icon:'⭐', title:t('en' === i18n.lang ? 'Excellence' : 'Excelencia'), desc:t('en' === i18n.lang ? 'Highest standards in service.' : 'Estándares más altos en servicio.') },
  { icon:'🌱', title:t('en' === i18n.lang ? 'Sustainability' : 'Sostenibilidad'), desc:t('en' === i18n.lang ? 'Committed to the planet.' : 'Comprometidos con el planeta.') },
  { icon:'💡', title:t('en' === i18n.lang ? 'Innovation' : 'Innovación'), desc:t('en' === i18n.lang ? 'Technology to simplify your experience.' : 'Tecnología para simplificar tu experiencia.') },
])
const foda_es = [
  { id:'F', icon:'💪', tag:'Interno · Positivo', title:'Fortalezas', color:'var(--success)', items:['Flota de 120+ vehículos modernos (2022–2025)','15 años de experiencia y reputación sólida','97% índice de satisfacción del cliente','Proceso de reserva digital en menos de 5 minutos','Seguros integrales incluidos','Atención al cliente 24/7','Vehículos eléctricos e híbridos pioneros en la región','Ubicación estratégica en cruce fronterizo'] },
  { id:'O', icon:'🚀', tag:'Externo · Positivo', title:'Oportunidades', color:'var(--navy)', items:['Crecimiento del turismo fronterizo MX-USA','Demanda de movilidad eléctrica y sustentable','Digitalización: apps, reservas en línea','Alianzas con hoteles y agencias de viaje','Expansión a Acuña, Eagle Pass y Laredo','Incremento del comercio binacional','Renta corporativa y long-term en crecimiento','Incentivos gubernamentales para flotas eléctricas'] },
  { id:'D', icon:'⚠️', tag:'Interno · Negativo', title:'Debilidades', color:'var(--accent)', items:['Cobertura limitada a Piedras Negras y alrededores','Presupuesto de marketing digital reducido','Dependencia de temporadas turísticas','Costo de mantenimiento elevado para eléctricos','Flota insuficiente en temporada alta','Sin programa de lealtad o membresías','Falta de integración con GDS de viajes'] },
  { id:'A', icon:'⚡', tag:'Externo · Negativo', title:'Amenazas', color:'var(--danger)', items:['Entrada de plataformas internacionales (Hertz, Avis)','Auge de Uber, DiDi y car-sharing P2P','Inflación y alza en combustibles','Tipo de cambio peso-dólar volátil','Regulaciones aduaneras en cruce fronterizo','Escasez de semiconductores para nuevos vehículos','Fraudes en renta vehicular en línea'] },
]
const foda_en = [
  { id:'F', icon:'💪', tag:'Internal · Positive', title:'Strengths', color:'var(--success)', items:['Fleet of 120+ modern vehicles (2022–2025)','15 years of experience and solid reputation','97% customer satisfaction rate','Digital booking process in less than 5 minutes','Comprehensive insurance included','24/7 customer service','Pioneer electric and hybrid vehicles in the region','Strategic location at border crossing'] },
  { id:'O', icon:'🚀', tag:'External · Positive', title:'Opportunities', color:'var(--navy)', items:['Growth in MX-USA border tourism','Demand for electric and sustainable mobility','Digitalization: apps, online bookings','Alliances with hotels and travel agencies','Expansion to Acuña, Eagle Pass, and Laredo','Increase in binational trade','Corporate and long-term rental growth','Government incentives for electric fleets'] },
  { id:'D', icon:'⚠️', tag:'Internal · Negative', title:'Weaknesses', color:'var(--accent)', items:['Limited coverage to Piedras Negras and surroundings','Reduced digital marketing budget','Dependence on tourist seasons','High maintenance cost for electrics','Insufficient fleet in high season','No loyalty program or memberships','Lack of integration with travel GDS'] },
  { id:'A', icon:'⚡', tag:'External · Negative', title:'Threats', color:'var(--danger)', items:['Entry of international platforms (Hertz, Avis)','Rise of Uber, DiDi, and P2P car-sharing','Inflation and rise in fuel costs','Volatile peso-dollar exchange rate','Customs regulations at border crossing','Shortage of semiconductors for new vehicles','Online vehicle rental fraud'] },
]
const foda = computed(() => i18n.lang === 'en' ? foda_en : foda_es)
const estrategias_es = [
  { label:'FO · Maxi-Maxi', color:'var(--success)', text:'Aprovechar la experiencia y la flota eléctrica para capturar el segmento de turismo sustentable fronterizo. Alianzas con hoteles de Eagle Pass y Laredo para paquetes combinados.' },
  { label:'DO · Mini-Maxi', color:'var(--navy)', text:'Implementar programa de lealtad digital y expandir presencia en OTAs globales para reducir dependencia estacional y competir con operadores internacionales.' },
  { label:'FA · Maxi-Mini', color:'var(--accent)', text:'Usar el 97% de satisfacción como diferenciador ante plataformas internacionales. El servicio personalizado y el conocimiento del cruce fronterizo son ventajas únicas.' },
  { label:'DA · Mini-Mini', color:'var(--danger)', text:'Diversificar hacia renta corporativa y logística para reducir vulnerabilidad a crisis turísticas. Tarifas dinámicas en dólar para mitigar riesgo cambiario.' },
]
const estrategias_en = [
  { label:'SO · Maxi-Maxi', color:'var(--success)', text:'Leverage experience and electric fleet to capture the sustainable border tourism segment. Alliances with Eagle Pass and Laredo hotels for combined packages.' },
  { label:'WO · Mini-Maxi', color:'var(--navy)', text:'Implement digital loyalty program and expand presence in global OTAs to reduce seasonal dependence and compete with international operators.' },
  { label:'ST · Maxi-Mini', color:'var(--accent)', text:'Use 97% satisfaction as a differentiator from international platforms. Personalized service and knowledge of the border crossing are unique advantages.' },
  { label:'WT · Mini-Mini', color:'var(--danger)', text:'Diversify into corporate rental and logistics to reduce vulnerability to tourism crises. Dynamic dollar rates to mitigate exchange risk.' },
]
const estrategias = computed(() => i18n.lang === 'en' ? estrategias_en : estrategias_es)
</script>
