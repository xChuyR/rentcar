<template>
  <div class="login-wrap">
    <div class="login-card" style="max-width:480px">

      <div class="login-logo">Rent<span>Car</span></div>
      <h1 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.3rem;text-align:center">
        {{ $t('register') }}
      </h1>
      <p class="text-muted" style="font-size:.88rem;margin-bottom:1.75rem;text-align:center">
        {{ $t('registerSub') }}
      </p>

      <div class="alert-box alert-error" :class="{ show: apiError }">⚠️ {{ apiError }}</div>
      <div class="alert-box alert-success" :class="{ show: successMsg }">✅ {{ successMsg }}</div>

      <div class="grid-2" style="gap:1rem">
        <div class="field">
          <label>{{ $t('firstName') }} *</label>
          <input v-model="form.nombre" type="text" placeholder="Ana"
            :class="{ error: errors.nombre }" />
          <span class="field-error" :class="{ show: errors.nombre }">{{ errors.nombre }}</span>
        </div>
        <div class="field">
          <label>{{ $t('lastName') }} *</label>
          <input v-model="form.apellido" type="text" placeholder="García"
            :class="{ error: errors.apellido }" />
          <span class="field-error" :class="{ show: errors.apellido }">{{ errors.apellido }}</span>
        </div>
      </div>

      <div class="field">
        <label>{{ $t('emailAddress') }} *</label>
        <input v-model="form.email" type="email" placeholder="tu@correo.com"
          :class="{ error: errors.email }" />
        <span class="field-error" :class="{ show: errors.email }">{{ errors.email }}</span>
      </div>

      <div class="field">
        <label>{{ $t('phone') }} (opcional)</label>
        <input v-model="form.telefono" type="tel" placeholder="+52 861 000 0000" />
      </div>

      <div class="field">
        <label>{{ $t('password') }} *</label>
        <input v-model="form.password" :type="showPw ? 'text' : 'password'" placeholder="Mín. 8 chars, 1 mayúscula, 1 número"
          :class="{ error: errors.password }" />
        <span class="field-error" :class="{ show: errors.password }">{{ errors.password }}</span>
        <button type="button" class="link-btn" style="margin-top:.35rem" @click="showPw = !showPw">
          {{ showPw ? '🙈 ' + $t('hide') : '👁 ' + $t('moreInfo').split(' ')[0] }}
        </button>
      </div>

      <div class="field">
        <label>{{ $t('confirmPassword') }} *</label>
        <input v-model="form.confirm" type="password" placeholder="Repite la contraseña"
          :class="{ error: errors.confirm }" />
        <span class="field-error" :class="{ show: errors.confirm }">{{ errors.confirm }}</span>
      </div>

      <!-- Indicador de fortaleza -->
      <div v-if="form.password" style="margin-bottom:1rem">
        <div style="height:4px;border-radius:99px;background:var(--border);overflow:hidden">
          <div :style="{ width: strength.pct + '%', background: strength.color, height: '100%', transition: 'all .3s' }"></div>
        </div>
        <p style="font-size:.73rem;color:var(--muted);margin-top:.3rem">{{ strength.label }}</p>
      </div>

      <button class="btn btn-primary" style="width:100%;justify-content:center"
        :disabled="loading" @click="doRegister">
        {{ loading ? $t('registering') : $t('register') }}
      </button>

      <div style="text-align:center;margin-top:1rem;font-size:.82rem;color:var(--muted)">
        {{ $t('alreadyHaveAccount') }}
        <RouterLink to="/login" style="color:var(--accent);font-weight:600">{{ $t('login') }}</RouterLink>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const auth    = useAuthStore()
const router  = useRouter()
const { success } = useToast()

const showPw    = ref(false)
const loading   = ref(false)
const apiError  = ref('')
const successMsg= ref('')

const form   = ref({ nombre: '', apellido: '', email: '', password: '', confirm: '', telefono: '' })
const errors = ref({ nombre: '', apellido: '', email: '', password: '', confirm: '' })

const strength = computed(() => {
  const pw = form.value.password
  let score = 0
  if (pw.length >= 8) score++
  if (/[A-Z]/.test(pw)) score++
  if (/[0-9]/.test(pw)) score++
  if (/[^A-Za-z0-9]/.test(pw)) score++
  const map = [
    { pct: 0,   color: 'var(--border)', label: '' },
    { pct: 25,  color: 'var(--danger)', label: '🔴 Muy débil' },
    { pct: 50,  color: '#e67e22',       label: '🟠 Débil' },
    { pct: 75,  color: '#f1c40f',       label: '🟡 Moderada' },
    { pct: 100, color: 'var(--success)',label: '🟢 Fuerte' },
  ]
  return map[score] || map[0]
})

function validate() {
  errors.value = { nombre: '', apellido: '', email: '', password: '', confirm: '' }
  let ok = true
  if (!form.value.nombre) { errors.value.nombre = 'Requerido'; ok = false }
  if (!form.value.apellido) { errors.value.apellido = 'Requerido'; ok = false }
  if (!form.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Correo inválido'; ok = false
  }
  if (form.value.password.length < 8) { errors.value.password = 'Mínimo 8 caracteres'; ok = false }
  else if (!/[A-Z]/.test(form.value.password)) { errors.value.password = 'Debe tener al menos una mayúscula'; ok = false }
  else if (!/[0-9]/.test(form.value.password)) { errors.value.password = 'Debe tener al menos un número'; ok = false }
  if (form.value.password !== form.value.confirm) { errors.value.confirm = 'Las contraseñas no coinciden'; ok = false }
  return ok
}

async function doRegister() {
  apiError.value  = ''
  successMsg.value = ''
  if (!validate()) return

  loading.value = true
  try {
    await auth.register({
      nombre:   form.value.nombre,
      apellido: form.value.apellido,
      email:    form.value.email,
      password: form.value.password,
      telefono: form.value.telefono,
    })
    successMsg.value = '¡Cuenta creada! Recibirás un correo de bienvenida. Redirigiendo al login...'
    success('✅ ¡Bienvenido/a a RentCar!')
    setTimeout(() => router.push('/login'), 2500)
  } catch (e) {
    apiError.value = e.response?.data?.error || 'Error al crear la cuenta'
  } finally {
    loading.value = false
  }
}
</script>
