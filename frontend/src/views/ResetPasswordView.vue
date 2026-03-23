<template>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">Rent<span>Car</span></div>

      <div v-if="!done">
        <h1 style="font-family:var(--font-d);font-size:1.5rem;font-weight:900;margin-bottom:.3rem">{{ $t('resetTitle') }}</h1>
        <p class="text-muted" style="font-size:.88rem;margin-bottom:1.75rem">
          {{ $t('resetSub') }}
        </p>

        <div class="alert-box alert-error" :class="{ show: apiError }">⚠️ {{ apiError }}</div>

        <div v-if="!token" class="alert-box alert-error show">
          ⚠️ {{ $t('invalidLink') }} <RouterLink to="/login" style="color:var(--accent)">{{ $t('requestNew') }}</RouterLink>.
        </div>

        <template v-if="token">
          <div class="field">
            <label>{{ $t('newPassword') }}</label>
            <input v-model="form.password" :type="showPw ? 'text' : 'password'"
              :placeholder="$t('passwordPlaceholder')"
              :class="{ error: errors.password }" />
            <span class="field-error" :class="{ show: errors.password }">{{ errors.password }}</span>
            <button class="link-btn" style="margin-top:.35rem" @click="showPw = !showPw" type="button">
              {{ showPw ? '🙈 ' + $t('hide') : '👁 ' + $t('show') }}
            </button>
          </div>
          <div class="field">
            <label>{{ $t('confirmPassword') }}</label>
            <input v-model="form.confirm" type="password" :placeholder="$t('confirmPlaceholder')"
              :class="{ error: errors.confirm }" />
            <span class="field-error" :class="{ show: errors.confirm }">{{ errors.confirm }}</span>
          </div>

          <button class="btn btn-primary" style="width:100%;justify-content:center"
            :disabled="loading" @click="doReset">
            {{ loading ? $t('saving') : $t('saveNewPassword') }}
          </button>
        </template>
      </div>

      <div v-else style="text-align:center;padding:1.5rem 0">
        <div style="font-size:3rem;margin-bottom:1rem">🔑</div>
        <h2 style="font-family:var(--font-d);font-size:1.4rem;font-weight:900;margin-bottom:.5rem">{{ $t('passwordUpdated') }}</h2>
        <p class="text-muted" style="margin-bottom:1.5rem">{{ $t('canLoginNow') }}</p>
        <RouterLink to="/login" class="btn btn-primary" style="justify-content:center">{{ $t('backToLogin') }}</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth  = useAuthStore()
const route = useRoute()

const token   = ref('')
const form    = ref({ password: '', confirm: '' })
const errors  = ref({ password: '', confirm: '' })
const showPw  = ref(false)
const loading = ref(false)
const apiError= ref('')
const done    = ref(false)

onMounted(() => { token.value = route.query.token || '' })

async function doReset() {
  errors.value = { password: '', confirm: '' }
  apiError.value = ''
  if (form.value.password.length < 8) { errors.value.password = 'Mínimo 8 caracteres'; return }
  if (!/[A-Z]/.test(form.value.password)) { errors.value.password = 'Debe tener al menos una mayúscula'; return }
  if (!/[0-9]/.test(form.value.password)) { errors.value.password = 'Debe tener al menos un número'; return }
  if (form.value.password !== form.value.confirm) { errors.value.confirm = 'Las contraseñas no coinciden'; return }

  loading.value = true
  try {
    await auth.resetPassword({ token: token.value, password: form.value.password, password_confirm: form.value.confirm })
    done.value = true
  } catch (e) {
    apiError.value = e.response?.data?.error || 'Error al restablecer'
  } finally {
    loading.value = false
  }
}
</script>
