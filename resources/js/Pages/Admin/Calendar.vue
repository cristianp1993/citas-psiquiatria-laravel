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

defineOptions({
  layout: AppLayout,
})

const selectedDoctorSlug = ref(
  props.filters?.doctor || props.doctor?.slug || null
)

const events = ref([])

async function loadEvents() {
  if (!selectedDoctorSlug.value) {
    events.value = []
    return
  }

  try {
    const res = await fetch(`/admin/calendar-data?doctor=${selectedDoctorSlug.value}`)

    if (!res.ok) {
      console.error('Error API /admin/calendar-data', res.status)
      events.value = []
      return
    }

    const data = await res.json()
    const list = Array.isArray(data) ? data : []

    const parsed = list.map(ev => ({
      ...ev,
      start: new Date(ev.start),
      end: new Date(ev.end),
      class: ev.class,
    }))

    const appointmentEvents = parsed.filter(e =>
      e.class === 'pending' ||
      e.class === 'confirmed' ||
      e.class === 'rejected'
    )

    const availableEvents = parsed.filter(e => e.class === 'available')

    const overlaps = (a, b) => a.start < b.end && b.start < a.end

    const filteredAvailable = availableEvents.filter(av =>
      !appointmentEvents.some(ap => overlaps(av, ap))
    )

    events.value = [
      ...filteredAvailable.map(e => ({
        ...e,
        title: 'Disponible',
      })),
      ...appointmentEvents,
    ]
  } catch (e) {
    console.error('Error cargando calendario admin', e)
    events.value = []
  }
}

function isWithinAvailableSlot(date) {
  return events.value.some(ev =>
    ev.class === 'available' && // ✅
    date >= ev.start &&
    date < ev.end
  )
}

function changeDoctor() {
  router.get(
    route('admin.calendar'),
    { doctor: selectedDoctorSlug.value },
    {
      preserveState: true,
      replace: true,
      onSuccess: () => {
        loadEvents()
      },
    }
  )
}


async function selectSlot(payload) {
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

  if (!isWithinAvailableSlot(jsDate)) {
    console.log('Click fuera de un slot disponible (admin)')
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

function getEventClass(event) {
  return event.class || 'available'
}


onMounted(loadEvents)

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
        <select v-model="selectedDoctorSlug" @change="changeDoctor"
          class="border rounded p-2 min-w-[220px] dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
          <option v-for="d in doctors" :key="d.slug" :value="d.slug">
            {{ d.name }}
          </option>
        </select>
      </div>
    </template>

    <VueCal :time-from="7 * 60" :time-to="18 * 60" :time-step="duration" default-view="week" :events="events"
      :hide-view-selector="true" :disable-views="['years', 'year', 'month', 'day']" locale="es"
      :event-class="getEventClass" @cell-click="selectSlot" />
  </div>
</template>

<style scoped>
/* Citas: pending / confirmed / rejected */
:deep(.vuecal__event.pending),
:deep(.vuecal__event.confirmed),
:deep(.vuecal__event.rejected) {
  background-color: #fef3c7;
  color: #92400e;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  margin: 0 !important;
  border-radius: 0;
  box-shadow: none;
  padding: 2px 4px;
  z-index: 2;
}

/* Confirmadas → verde */
:deep(.vuecal__event.confirmed) {
  background-color: #bbf7d0;
  color: #065f46;
}

/* Rechazadas → rojo */
:deep(.vuecal__event.rejected) {
  background-color: #fee2e2;
  color: #991b1b;
}

:deep(.vuecal__cell-events) {
  padding: 0;
}

/* Disponibles → naranja (debajo de las citas) */
:deep(.vuecal__event.available) {
  background-color: #fed7aa;
  color: #9a3412;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  margin: 0 !important;
  border-radius: 0;
  box-shadow: none;
  border: 1px solid #fed7aa;
  padding: 2px 4px;
  z-index: 1;
}

/* Ocultar título y hora en disponibles */
:deep(.vuecal__event.available .vuecal__event-time),
:deep(.vuecal__event.available .vuecal__event-title) {
  display: none;
}

/* Ocultar hora en citas */
:deep(.vuecal__event.pending .vuecal__event-time),
:deep(.vuecal__event.confirmed .vuecal__event-time),
:deep(.vuecal__event.rejected .vuecal__event-time) {
  display: none;
}

/* Estilo del título en citas */
:deep(.vuecal__event.pending .vuecal__event-title),
:deep(.vuecal__event.confirmed .vuecal__event-title),
:deep(.vuecal__event.rejected .vuecal__event-title) {
  font-size: 0.7rem;
  white-space: normal;
  line-height: 1.1;
  overflow: visible;
}
</style>