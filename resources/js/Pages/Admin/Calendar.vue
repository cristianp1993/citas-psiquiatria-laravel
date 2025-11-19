<script setup>
import { ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Toastify from 'toastify-js'

const props = defineProps({
  doctors: Array,
  doctor: Object,
  duration: Number,
  filters: Object,
})

// usamos layout global, NO volvemos a envolver en <AppLayout> dentro del template
defineOptions({
  layout: AppLayout,
})

const selectedDoctorSlug = ref(
  props.filters?.doctor || props.doctor?.slug || null
)

const events = ref([])

// cargar eventos del doctor seleccionado
async function loadEvents () {
  if (!selectedDoctorSlug.value) {
    events.value = []
    return
  }

  try {
    const res = await fetch(`/admin/calendar-data?doctor=${selectedDoctorSlug.value}`)

    if (!res.ok) {
      console.error('Error API /api/admin/calendar', res.status)
      events.value = []
      return
    }

    const data = await res.json()
    const list = Array.isArray(data) ? data : []

    events.value = list.map(ev => ({
      ...ev,
      start: new Date(ev.start),
      end: new Date(ev.end),
    }))
  } catch (e) {
    console.error('Error cargando calendario admin', e)
    events.value = []
  }
}

// cambio de doctor desde el select ⇒ recargamos la página con Inertia
function changeDoctor () {
  router.get(
    route('admin.calendar'),
    { doctor: selectedDoctorSlug.value },
    {
      preserveState: true,
      replace: true,
      onSuccess: () => {
        // cuando el servidor devuelva de nuevo la página con otro doctor,
        // volvemos a cargar eventos
        loadEvents()
      },
    }
  )
}

// handler de clic en celda para crear cita
async function selectSlot (payload) {
  console.log('cell-click payload:', payload)

  let jsDate = null

  if (payload instanceof Date || typeof payload === 'string' || typeof payload === 'number') {
    jsDate = new Date(payload)
  } else if (payload && payload.date) {
    jsDate = new Date(payload.date)
  } else if (payload && payload.start) {
    jsDate = new Date(payload.start)
  }

  if (!jsDate || isNaN(jsDate.getTime())) {
    console.error('Formato inesperado de cell-click:', payload)
    return
  }

  const start = jsDate.toISOString()

  try {
   const res = await fetch('/admin/appointments', { 

      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute('content'),
      },
      body: JSON.stringify({
        doctor_slug: selectedDoctorSlug.value,
        start,
      }),
    })

    if (res.ok) {
      Toastify({
        text: 'Cita creada (pendiente de confirmación)',
        duration: 3000,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#16a34a',
      }).showToast()

      await loadEvents()
    } else {
      const data = await res.json().catch(() => ({}))
      Toastify({
        text: data.error || 'Error al crear cita',
        duration: 3000,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#dc2626',
      }).showToast()
    }
  } catch (error) {
    console.error(error)
    Toastify({
      text: 'Error de conexión',
      duration: 3000,
      gravity: 'top',
      position: 'right',
      backgroundColor: '#dc2626',
    }).showToast()
  }
}

onMounted(loadEvents)

// si cambia el doctor desde props (por navegación Inertia), sincronizamos el ref
watch(
  () => props.doctor,
  (val) => {
    if (val?.slug) {
      selectedDoctorSlug.value = val.slug
      loadEvents()
    }
  }
)
</script>

<template>
  <div class="max-w-6xl mx-auto p-6">
    <template v-if="doctors && doctors.length">
      <div class="mb-4 flex items-center gap-4">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
          Seleccionar médico
        </label>
        <select
          v-model="selectedDoctorSlug"
          @change="changeDoctor"
          class="border rounded p-2 min-w-[220px] dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
        >
          <option
            v-for="d in doctors"
            :key="d.slug"
            :value="d.slug"
          >
            {{ d.name }}
          </option>
        </select>
      </div>
    </template>

    <VueCal
      :time-from="8 * 60"
      :time-to="18 * 60"
      :time-step="duration"
      default-view="week"
      :events="events"
      :hide-view-selector="true"
      :disable-views="['years', 'year', 'month', 'day']"
      locale="es"
      @cell-click="selectSlot"
    />
  </div>
</template>

<style scoped>
:deep(.vuecal__event.pending),
:deep(.vuecal__event.confirmed),
:deep(.vuecal__event.rejected) {
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  margin: 0 !important;
  border-radius: 0;
  box-shadow: none;
  padding: 2px 4px;
  font-size: 0.7rem;
  line-height: 1.1;
}

/* colores por estado */
:deep(.vuecal__event.pending) {
  background-color: #fef3c7;
  color: #92400e;
}

:deep(.vuecal__event.confirmed) {
  background-color: #bbf7d0;
  color: #065f46;
}

:deep(.vuecal__event.rejected) {
  background-color: #fee2e2;
  color: #991b1b;
}

/* huecos disponibles sin texto ni color */
:deep(.vuecal__event.available) {
  background-color: transparent;
  color: transparent;
  border: none;
  box-shadow: none;
}

:deep(.vuecal__event.available .vuecal__event-title),
:deep(.vuecal__event.available .vuecal__event-time) {
  display: none;
}

/* opcional: quitar horas dentro de los eventos de citas */
:deep(.vuecal__event.pending .vuecal__event-time),
:deep(.vuecal__event.confirmed .vuecal__event-time),
:deep(.vuecal__event.rejected .vuecal__event-time) {
  display: none;
}
</style>
