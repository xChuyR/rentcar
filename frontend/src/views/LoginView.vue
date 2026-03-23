<template>
  <div class="login-wrap">
    <div class="login-card">

      <div class="login-logo">Rent<span>Car</span></div>

      <!-- Dots indicadores -->
      <div class="step-dots">
        <div class="step-dot" :class="{ active: step === 1, done: step > 1 }"></div>
        <div class="step-dot" :class="{ active: step === 2, done: step > 2 }"></div>
        <div class="step-dot" :class="{ active: step === 3 }"></div>
      </div>

      <!-- ══ PASO 1: Credenciales ══ -->
      <div v-if="step === 1">
        <h1 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.3rem">{{ $t('login') }}</h1>
        <p class="text-muted" style="font-size:.88rem;margin-bottom:1.75rem">{{ $t('loginSubtitle') }}</p>

        <div class="alert-box alert-error" :class="{ show: loginError }">⚠️ {{ loginError }}</div>

        <div class="field">
          <label>{{ $t('emailAddress') }}</label>
          <input v-model="form.email" type="email" placeholder="tu@correo.com"
            :class="{ error: errors.email }" @keydown.enter="doLogin" />
          <span class="field-error" :class="{ show: errors.email }">{{ errors.email }}</span>
        </div>
        <div class="field">
          <label>{{ $t('password') }}</label>
          <input v-model="form.password" :type="showPw ? 'text' : 'password'" placeholder="••••••••"
            :class="{ error: errors.password }" @keydown.enter="doLogin" />
          <span class="field-error" :class="{ show: errors.password }">{{ errors.password }}</span>
          <button type="button" class="link-btn" style="margin-top:.4rem" @click="showPw = !showPw">
            {{ showPw ? '🙈 ' + $t('hide') : '👁 ' + $t('moreInfo').split(' ')[0] }} {{ $t('password').toLowerCase() }}
          </button>
        </div>

        <button class="btn btn-primary" style="width:100%;justify-content:center"
          :disabled="auth.loading" @click="doLogin">
          {{ auth.loading ? $t('entering') : $t('login') }}
        </button>

        <div style="text-align:center;margin-top:1rem;display:flex;flex-direction:column;gap:.5rem">
          <button class="link-btn" @click="step = 'forgot'">{{ $t('forgotPassword') }}</button>

          <!-- 
          <span style="font-size:.82rem;color:var(--muted)">
            {{ $t('noAccount') }}
            <RouterLink to="/registro" style="color:var(--accent);font-weight:600">{{ $t('register') }}</RouterLink>
          </span>
          -->

          
        </div>

        <!-- 
        <div class="demo-box">
          <strong>🔑 Credenciales de prueba</strong>
          <div style="display:flex;justify-content:space-between;margin-bottom:.2rem">
            <span>Admin:</span><span style="font-weight:600">admin@rentcar.mx / Admin2025!</span>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span>Cliente:</span><span style="font-weight:600">cliente@rentcar.mx / Cliente2025!</span>
          </div>
          <p style="margin-top:.4rem;font-size:.72rem">El código MFA llegará a tu correo real.</p>
        </div>
        -->
      </div>

      <!-- ══ PASO 2: MFA ══ -->
      <div v-else-if="step === 2">
        <h2 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.3rem">Verificación</h2>
        <p class="text-muted" style="font-size:.88rem;margin-bottom:1.5rem">
          Ingresa el código de 6 dígitos enviado a <strong>{{ emailHint }}</strong>
        </p>

        <div class="alert-box alert-error" :class="{ show: mfaError }">⚠️ {{ mfaError }}</div>

        <div class="mfa-inputs">
          <input
            v-for="(_, i) in mfaDigits" :key="i"
            :ref="el => { if (el) mfaRefs[i] = el }"
            v-model="mfaDigits[i]"
            class="mfa-digit"
            type="text" maxlength="1" inputmode="numeric"
            @input="onMfaInput(i)"
            @keydown.backspace="onMfaBack(i)"
            @paste.prevent="onMfaPaste"
          />
        </div>

        <button class="btn btn-primary" style="width:100%;justify-content:center"
          :disabled="auth.loading || mfaCode.length < 6" @click="doVerifyMFA">
          {{ auth.loading ? 'Verificando...' : 'Verificar código' }}
        </button>

        <div style="text-align:center;margin-top:1rem">
          <button class="link-btn" @click="backToStep1">← Regresar</button>
        </div>
      </div>

      <!-- ══ PASO 3: Éxito ══ -->
      <div v-else-if="step === 3" style="text-align:center;padding:1.5rem 0">
        <div style="font-size:3.5rem;margin-bottom:1rem">✅</div>
        <h2 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.5rem">
          ¡Acceso concedido!
        </h2>
        <p class="text-muted" style="margin-bottom:1.5rem">
          Bienvenido/a, <strong>{{ auth.user?.nombre }}</strong>.
          <span v-if="auth.isAdmin"> Tienes acceso de administrador.</span>
        </p>
        <RouterLink to="/" class="btn btn-primary" style="justify-content:center">Ir al inicio →</RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/admin" class="btn btn-accent" style="justify-content:center;margin-top:.5rem">
          ⚙️ Panel de administración
        </RouterLink>
      </div>

      <!-- ══ RECUPERAR CONTRASEÑA ══ -->
      <div v-else-if="step === 'forgot'">
        <h2 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.3rem">Recuperar Contraseña</h2>
        <p class="text-muted" style="font-size:.88rem;margin-bottom:1.75rem">
          Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <div class="alert-box" :class="{ show: resetMsg, 'alert-success': resetOk, 'alert-error': !resetOk && resetMsg }">
          {{ resetMsg }}
        </div>

        <div class="field">
          <label>Correo electrónico</label>
          <input v-model="resetEmail" type="email" placeholder="tu@correo.com"
            :class="{ error: resetEmailErr }" @keydown.enter="doRequestReset" />
          <span class="field-error" :class="{ show: resetEmailErr }">{{ resetEmailErr }}</span>
        </div>

        <button class="btn btn-primary" style="width:100%;justify-content:center"
          :disabled="resetLoading" @click="doRequestReset">
          {{ resetLoading ? 'Enviando...' : 'Enviar instrucciones' }}
        </button>

        <div style="text-align:center;margin-top:1rem">
          <button class="link-btn" @click="step = 1">← Regresar al login</button>
        </div>
      </div>

      <!-- Dark toggle -->
      <div style="text-align:center;margin-top:1.75rem;border-top:1px solid var(--border);padding-top:1.25rem">
        <RouterLink to="/registro" class="btn btn-outline btn-sm" style="font-size:.78rem">
          ¿Primera vez? Crear cuenta
        </RouterLink>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const auth   = useAuthStore()
const router = useRouter()
const { success, error } = useToast()

const step     = ref(1)
const showPw   = ref(false)
const tempToken = ref(null)
const emailHint = ref('')

const form   = ref({ email: '', password: '' })
const errors = ref({ email: '', password: '' })
const loginError = ref('')

// MFA
const mfaDigits = ref(Array(6).fill(''))
const mfaRefs   = ref([])
const mfaError  = ref('')
const mfaCode   = computed(() => mfaDigits.value.join(''))

// Reset
const resetEmail    = ref('')
const resetEmailErr = ref('')
const resetMsg      = ref('')
const resetOk       = ref(false)
const resetLoading  = ref(false)

// ── Paso 1 ──
async function doLogin() {
  errors.value = { email: '', password: '' }
  loginError.value = ''

  if (!form.value.email) { errors.value.email = 'El correo es requerido'; return }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) { errors.value.email = 'Correo inválido'; return }
  if (!form.value.password) { errors.value.password = 'La contraseña es requerida'; return }

  try {
    const res = await auth.login(form.value.email, form.value.password)
    tempToken.value = res.temp_token
    emailHint.value = res.email_hint
    step.value = 2
    await nextTick()
    mfaRefs.value[0]?.focus()
  } catch (e) {
    loginError.value = e.response?.data?.error || 'Error al iniciar sesión'
  }
}

// ── MFA inputs ──
function onMfaInput(i) {
  mfaDigits.value[i] = mfaDigits.value[i].replace(/\D/g, '').slice(-1)
  if (mfaDigits.value[i] && i < 5) nextTick(() => mfaRefs.value[i + 1]?.focus())
  if (mfaCode.value.length === 6) doVerifyMFA()
}
function onMfaBack(i) {
  if (!mfaDigits.value[i] && i > 0) { mfaDigits.value[i - 1] = ''; nextTick(() => mfaRefs.value[i - 1]?.focus()) }
}
function onMfaPaste(e) {
  const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '')
  ;[...text].slice(0, 6).forEach((ch, i) => { mfaDigits.value[i] = ch })
  nextTick(() => mfaRefs.value[Math.min(text.length, 5)]?.focus())
}

// ── Paso 2: verificar MFA ──
async function doVerifyMFA() {
  if (mfaCode.value.length < 6) return
  mfaError.value = ''
  try {
    await auth.verifyMFA(mfaCode.value, tempToken.value)
    step.value = 3
    success('✅ ¡Bienvenido/a, ' + auth.user.nombre + '!')
    await auth.cartStore?.fetch?.()
  } catch (e) {
    mfaError.value = e.response?.data?.error || 'Código incorrecto'
    mfaDigits.value = Array(6).fill('')
    nextTick(() => mfaRefs.value[0]?.focus())
  }
}

function backToStep1() {
  step.value = 1
  tempToken.value = null
  mfaDigits.value = Array(6).fill('')
  mfaError.value = ''
}

// ── Recuperar contraseña ──
async function doRequestReset() {
  resetEmailErr.value = ''
  resetMsg.value = ''
  if (!resetEmail.value) { resetEmailErr.value = 'El correo es requerido'; return }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(resetEmail.value)) { resetEmailErr.value = 'Correo inválido'; return }

  resetLoading.value = true
  try {
    const res = await auth.requestReset(resetEmail.value)
    resetMsg.value = res.message
    resetOk.value  = true
    resetEmail.value = ''
  } catch (e) {
    resetMsg.value = e.response?.data?.error || 'Error al enviar'
    resetOk.value  = false
  } finally {
    resetLoading.value = false
  }
}
</script>
