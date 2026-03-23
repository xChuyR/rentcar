<template>
  <div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div style="padding:0 1.5rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1)">
        <RouterLink to="/" class="nav-logo" style="font-size:1.3rem">Rent<span>Car</span></RouterLink>
        <p style="font-size:.72rem;color:rgba(255,255,255,.5);margin-top:.3rem">{{ $t('adminPanel') }}</p>
      </div>
      <ul class="admin-menu" style="margin-top:1rem">
        <li v-for="item in menuItems" :key="item.id">
          <a href="#" :class="{ active: activeTab === item.id }" @click.prevent="activeTab = item.id">
            <span class="icon">{{ item.icon }}</span> {{ item.label }}
          </a>
        </li>
      </ul>
    </aside>

    <!-- Contenido -->
    <main class="admin-content">

      <!-- ══ DASHBOARD ══ -->
      <div v-if="activeTab === 'dashboard'">
        <h1 style="font-family:var(--font-d);font-size:1.8rem;font-weight:900;margin-bottom:1.5rem">{{ $t('dashboard') }}</h1>

        <div class="grid-4" style="margin-bottom:2rem">
          <div class="stat-card">
            <p class="stat-num">{{ stats.usuarios || 0 }}</p>
            <p class="stat-label">{{ $t('statsUsers') }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-num">{{ stats.autos || 0 }}</p>
            <p class="stat-label">{{ $t('statsVehicles') }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-num">{{ stats.autos_disponibles || 0 }}</p>
            <p class="stat-label">{{ $t('statsAvailable') }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-num" style="color:var(--danger)">{{ stats.mensajes_nuevos || 0 }}</p>
            <p class="stat-label">{{ $t('statsNewMessages') }}</p>
          </div>
        </div>

        <h2 style="font-family:var(--font-d);font-size:1.2rem;font-weight:700;margin-bottom:1rem">{{ $t('recentActivity') }}</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>{{ $t('action') }}</th><th>{{ $t('user') }}</th><th>IP</th><th>{{ $t('date') }}</th></tr></thead>
            <tbody>
              <tr v-for="log in logs" :key="log.id">
                <td><span class="badge" :class="logBadge(log.accion)">{{ log.accion }}</span></td>
                <td>{{ log.usuario }}</td>
                <td style="font-size:.78rem;color:var(--muted)">{{ log.ip }}</td>
                <td style="font-size:.78rem;color:var(--muted)">{{ fmtDate(log.creado_en) }}</td>
              </tr>
              <tr v-if="!logs.length"><td colspan="4" style="text-align:center;color:var(--muted)">{{ $t('noActivity') }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ AUTOS ══ -->
      <div v-else-if="activeTab === 'cars'">
        <div class="flex justify-between items-center" style="margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
          <h1 style="font-family:var(--font-d);font-size:1.8rem;font-weight:900">{{ $t('vehicleManagement') }}</h1>
          <button class="btn btn-primary" @click="openCarModal()">{{ $t('addVehicle') }}</button>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>{{ $t('image') }}</th><th>{{ $t('name') }}</th><th>{{ $t('type') }}</th><th>{{ $t('priceDay') }}</th>
                <th>{{ $t('available') }}</th><th>{{ $t('actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="car in cars" :key="car.id">
                <td>
                  <div style="width:56px;height:40px;border-radius:.3rem;overflow:hidden;background:var(--border)">
                    <img v-if="car.imagen_url" :src="car.imagen_url" :alt="car.nombre"
                      style="width:100%;height:100%;object-fit:cover" />
                    <div v-else style="display:flex;align-items:center;justify-content:center;height:100%;font-size:1.2rem">🚗</div>
                  </div>
                </td>
                <td>
                  <strong style="font-size:.9rem">{{ car.nombre }}</strong>
                  <p style="font-size:.74rem;color:var(--muted)">{{ car.marca }} {{ car.modelo }} {{ car.año }}</p>
                </td>
                <td><span class="badge badge-navy">{{ car.tipo }}</span></td>
                <td style="font-family:var(--font-d);font-weight:700">${{ Number(car.precio_dia).toLocaleString() }}</td>
                <td>
                  <span class="badge" :class="car.disponible ? 'badge-success' : 'badge-danger'">
                    {{ car.disponible ? $t('yes') : $t('no') }}
                  </span>
                </td>
                <td>
                  <div class="flex gap-1">
                    <button class="btn btn-outline btn-xs" @click="openCarModal(car)">{{ $t('edit') }}</button>
                    <button class="btn btn-danger btn-xs" @click="confirmDelete(car)">{{ $t('delete') }}</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!cars.length"><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem">
                No hay vehículos registrados.
              </td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ USUARIOS ══ -->
      <div v-else-if="activeTab === 'users'">
        <h1 style="font-family:var(--font-d);font-size:1.8rem;font-weight:900;margin-bottom:1.5rem">Usuarios</h1>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead>
            <tbody>
              <tr v-for="u in users" :key="u.id">
                <td><strong>{{ u.nombre }} {{ u.apellido }}</strong></td>
                <td style="font-size:.85rem">{{ u.email }}</td>
                <td><span class="badge" :class="u.rol === 'admin' ? 'badge-accent' : 'badge-navy'">{{ u.rol }}</span></td>
                <td><span class="badge" :class="u.activo ? 'badge-success' : 'badge-danger'">{{ u.activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td style="font-size:.78rem;color:var(--muted)">{{ fmtDate(u.creado_en) }}</td>
                <td>
                  <button class="btn btn-outline btn-xs" @click="toggleUserActive(u)">
                    {{ u.activo ? '🚫 Desactivar' : '✅ Activar' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ MENSAJES ══ -->
      <div v-else-if="activeTab === 'messages'">
        <h1 style="font-family:var(--font-d);font-size:1.8rem;font-weight:900;margin-bottom:1.5rem">Mensajes de Contacto</h1>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nombre</th><th>Correo</th><th>Asunto</th><th>Mensaje</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
              <tr v-for="m in messages" :key="m.id">
                <td><strong>{{ m.nombre }}</strong></td>
                <td style="font-size:.83rem">{{ m.email }}</td>
                <td style="font-size:.83rem">{{ m.asunto || '—' }}</td>
                <td style="font-size:.78rem;color:var(--muted);max-width:200px">{{ m.mensaje?.slice(0,80) }}...</td>
                <td><span class="badge" :class="m.leido ? 'badge-muted' : 'badge-accent'">{{ m.leido ? 'Leído' : 'Nuevo' }}</span></td>
                <td style="font-size:.78rem;color:var(--muted)">{{ fmtDate(m.creado_en) }}</td>
              </tr>
              <tr v-if="!messages.length"><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem">
                No hay mensajes.
              </td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!-- ══ MODAL VEHÍCULO ══ -->
    <div class="modal-overlay" :class="{ open: carModal }">
      <div class="modal-box" style="max-width:580px">
        <div class="modal-header">
          <h2 class="modal-title">{{ editingCar ? $t('edit') : $t('addVehicle') }}</h2>
          <button class="modal-close" @click="carModal = false">✕</button>
        </div>

        <div class="alert-box alert-error" :class="{ show: carError }">⚠️ {{ carError }}</div>

        <div class="grid-2" style="gap:1rem">
          <div class="field">
            <label>{{ $t('name') }} *</label>
            <input v-model="carForm.nombre" type="text" placeholder="Toyota RAV4 Hybrid" />
          </div>
          <div class="field">
            <label>Marca *</label>
            <input v-model="carForm.marca" type="text" placeholder="Toyota" />
          </div>
          <div class="field">
            <label>Modelo *</label>
            <input v-model="carForm.modelo" type="text" placeholder="RAV4 Hybrid" />
          </div>
          <div class="field">
            <label>Año *</label>
            <input v-model="carForm.anio" type="number" placeholder="2024" min="2000" :max="2026" />
          </div>
          <div class="field">
            <label>{{ $t('type') }} *</label>
            <select v-model="carForm.tipo">
              <option v-for="t in tiposAuto" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="field">
            <label>{{ $t('priceDay') }} (MXN) *</label>
            <input v-model="carForm.precio_dia" type="number" placeholder="950" min="0" />
          </div>
          <div class="field">
            <label>Transmisión</label>
            <select v-model="carForm.transmision">
              <option>Automático</option><option>Manual</option><option>CVT</option>
            </select>
          </div>
          <div class="field">
            <label>Pasajeros</label>
            <input v-model="carForm.pasajeros" type="number" min="1" max="9" />
          </div>
        </div>

        <div class="field">
          <label>Motor</label>
          <input v-model="carForm.motor" type="text" placeholder="2.5L Turbo 256hp" />
        </div>
        <div class="field">
          <label>Descripción</label>
          <textarea v-model="carForm.descripcion" rows="3" placeholder="Descripción del vehículo..."></textarea>
        </div>
        <div class="field">
          <label>Características (separadas por coma)</label>
          <input v-model="carForm.caracteristicas" type="text" placeholder="GPS, Bluetooth, A/C, Apple CarPlay" />
        </div>
        <div class="field">
          <label>Imagen del vehículo</label>
          <input type="file" accept="image/jpeg,image/png,image/webp" @change="onFileChange" style="font-size:.84rem" />
          <p style="font-size:.73rem;color:var(--muted);margin-top:.3rem">JPG, PNG o WebP. Máx. 5MB.</p>
          <div v-if="previewUrl || carForm.imagen_url" style="margin-top:.6rem">
            <img :src="previewUrl || carForm.imagen_url" alt="Preview"
              style="width:100%;height:140px;object-fit:cover;border-radius:.5rem;border:1px solid var(--border)" />
          </div>
        </div>
        <div class="field" style="display:flex;align-items:center;gap:.5rem">
          <input v-model="carForm.disponible" type="checkbox" id="dispCheck" style="width:auto" />
          <label for="dispCheck" style="text-transform:none;letter-spacing:0;font-size:.9rem;margin:0">
            {{ $t('available') }}
          </label>
        </div>

        <div class="flex gap-1" style="justify-content:flex-end;margin-top:1rem">
          <button class="btn btn-outline" @click="carModal = false">{{ $t('cancel') }}</button>
          <button class="btn btn-primary" :disabled="carSaving" @click="saveCar">
            {{ carSaving ? $t('saving') : (editingCar ? $t('save') : $t('addVehicle')) }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm delete -->
    <div class="modal-overlay" :class="{ open: deleteConfirm }">
      <div class="modal-box" style="max-width:380px;text-align:center">
        <div style="font-size:2.5rem;margin-bottom:1rem">🗑️</div>
        <h2 class="modal-title" style="text-align:center;margin-bottom:.5rem">¿Eliminar vehículo?</h2>
        <p class="text-muted" style="font-size:.88rem;margin-bottom:1.5rem">
          Se eliminará permanentemente <strong>{{ deletingCar?.nombre }}</strong>.
        </p>
        <div class="flex gap-1" style="justify-content:center">
          <button class="btn btn-outline" @click="deleteConfirm = false">Cancelar</button>
          <button class="btn btn-danger" :disabled="carSaving" @click="doDelete">
            {{ carSaving ? 'Eliminando...' : 'Sí, eliminar' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/client'
import { useToast } from '@/composables/useToast'
import { useI18nStore } from '@/stores/i18n'

const i18n = useI18nStore()
const { t } = i18n


const { success, error } = useToast()

const menuItems = computed(() => [
  { id: 'dashboard', icon: '📊', label: t('dashboard') },
  { id: 'cars',      icon: '🚗', label: t('vehicles') },
  { id: 'users',     icon: '👥', label: t('users') },
  { id: 'messages',  icon: '✉️', label: t('messages') },
])

const activeTab = ref('dashboard')
const stats    = ref({})
const logs     = ref([])
const cars     = ref([])
const users    = ref([])
const messages = ref([])

const tiposAuto = ['SUV','Sedan','Deportivo','Pickup','Eléctrico','Minivan']

// ── Car modal ──
const carModal   = ref(false)
const carError   = ref('')
const carSaving  = ref(false)
const editingCar = ref(null)
const previewUrl = ref(null)
const imageFile  = ref(null)

const emptyForm = () => ({
  nombre:'', marca:'', modelo:'', anio: new Date().getFullYear(),
  tipo:'SUV', precio_dia:'', descripcion:'', caracteristicas:'',
  pasajeros:5, transmision:'Automático', motor:'', disponible:true, imagen_url:''
})
const carForm = ref(emptyForm())

const deleteConfirm = ref(false)
const deletingCar   = ref(null)

// ── Load data ──
async function loadDashboard() {
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value    = data.data.stats
    logs.value     = data.data.logs
    messages.value = data.data.mensajes
  } catch {}
}

async function loadCars() {
  try {
    const { data } = await api.get('/cars?disponible=')
    cars.value = data.data
  } catch {}
}

async function loadUsers() {
  try {
    const { data } = await api.get('/admin/users')
    users.value = data.data
  } catch {}
}

onMounted(async () => {
  await loadDashboard()
  await loadCars()
  await loadUsers()
})

// ── Car CRUD ──
function openCarModal(car = null) {
  carError.value = ''
  previewUrl.value = null
  imageFile.value  = null
  if (car) {
    editingCar.value = car
    carForm.value = {
      nombre: car.nombre, marca: car.marca, modelo: car.modelo,
      anio: car.año, tipo: car.tipo, precio_dia: car.precio_dia,
      descripcion: car.descripcion || '',
      caracteristicas: Array.isArray(car.caracteristicas) ? car.caracteristicas.join(', ') : '',
      pasajeros: car.pasajeros, transmision: car.transmision, motor: car.motor || '',
      disponible: !!car.disponible, imagen_url: car.imagen_url || ''
    }
  } else {
    editingCar.value = null
    carForm.value = emptyForm()
  }
  carModal.value = true
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) { carError.value = 'La imagen no puede superar 5MB'; return }
  imageFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

async function saveCar() {
  carError.value = ''
  if (!carForm.value.nombre || !carForm.value.marca || !carForm.value.tipo || !carForm.value.precio_dia) {
    carError.value = 'Nombre, marca, tipo y precio son requeridos'
    return
  }

  carSaving.value = true
  try {
    const fd = new FormData()
    Object.entries(carForm.value).forEach(([k, v]) => {
      if (k === 'anio') { fd.append('anio', v); return }
      if (k === 'caracteristicas') {
        fd.append('caracteristicas', JSON.stringify(v.split(',').map(s => s.trim()).filter(Boolean)))
        return
      }
      if (k === 'imagen_url') return
      fd.append(k, v)
    })
    if (imageFile.value) fd.append('imagen', imageFile.value)

    if (editingCar.value) {
      fd.append('_method', 'PUT')
      await api.post('/cars/' + editingCar.value.id, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      success('✅ Vehículo actualizado')
    } else {
      await api.post('/cars', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      success('✅ Vehículo agregado')
    }
    carModal.value = false
    await loadCars()
  } catch (e) {
    carError.value = e.response?.data?.error || 'Error al guardar'
  } finally {
    carSaving.value = false
  }
}

function confirmDelete(car) { deletingCar.value = car; deleteConfirm.value = true }

async function doDelete() {
  carSaving.value = true
  try {
    await api.delete('/cars/' + deletingCar.value.id)
    success('🗑️ Vehículo eliminado')
    deleteConfirm.value = false
    await loadCars()
  } catch (e) {
    error(e.response?.data?.error || 'Error al eliminar')
  } finally {
    carSaving.value = false
  }
}

async function toggleUserActive(u) {
  try {
    await api.put('/admin/users/' + u.id, { activo: u.activo ? 0 : 1 })
    u.activo = !u.activo
    success('Usuario ' + (u.activo ? 'activado' : 'desactivado'))
  } catch (e) {
    error('Error al actualizar usuario')
  }
}

// ── Helpers ──
function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' })
}

function logBadge(accion) {
  if (accion.includes('FAIL') || accion.includes('DELETE')) return 'badge-danger'
  if (accion.includes('OK') || accion.includes('CREATED') || accion.includes('REGISTER')) return 'badge-success'
  return 'badge-muted'
}
</script>
