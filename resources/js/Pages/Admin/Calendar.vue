<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout,
})

const props = defineProps({
  doctor: Object,
  duration: Number,
})

const events = ref([])

async function load () {
  if (!props.doctor) return
  const res = await fetch(`/api/admin/calendar?doctor=${props.doctor.slug}`)
  events.value = await res.json()
}

// Handler robusto para el click en una celda de VueCal
async function selectSlot (payload) {
  console.log('cell-click payload:', payload)

  let jsDate = null

  // 1) Si VueCal manda directamente un Date o string/number
  if (payload instanceof Date || typeof payload === 'string' || typeof payload === 'number') {
    jsDate = new Date(payload)
  }
  // 2) Si manda un objeto con date
  else if (payload && payload.date) {
    jsDate = new Date(payload.date)
  }
  // 3) Si manda un objeto con start
  else if (payload && payload.start) {
    jsDate = new Date(payload.start)
  }

  // Si no pudimos obtener una fecha, salimos sin romper la app
  if (!jsDate || isNaN(jsDate.getTime())) {
    console.error('Formato inesperado de cell-click, no se puede obtener fecha:', payload)
    return
  }

  const start = jsDate.toISOString()

  try {
    const res = await fetch('/api/admin/appointments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute('content'),
      },
      body: JSON.stringify({
        doctor_slug: props.doctor.slug,
        start,
      }),
    })

    if (res.ok) {
      alert('Cita creada exitosamente (pendiente de confirmación)')
      await load()
    } else {
      const data = await res.json().catch(() => ({}))
      alert(data.error || 'Error al crear cita')
    }
  } catch (error) {
    console.error(error)
    alert('Error de conexión')
  }
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Agenda de {{ doctor?.name }}
      </h2>
    </template>

    <div class="max-w-6xl mx-auto p-6">
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
  </AppLayout>
</template>
