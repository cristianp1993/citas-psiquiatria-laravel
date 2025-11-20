<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PublicNavbar from '@/Components/PublicNavbar.vue'
import Toastify from "toastify-js";

const props = defineProps({
  doctors: Array,
  duration: Number,
})

const doctor = ref(props.doctors[0] ?? null)
const events = ref([])

const showModal = ref(false)
const selectedDate = ref(null)

const form = reactive({
  doctor_id: null,
  patient_name: '',
  patient_email: '',
  patient_phone: '',
  start_at: '',
  end_at: '',
})

async function loadAvailability () {
  if (!doctor.value) {
    events.value = []
    return
  }

  try {
    const res = await fetch(`/api/public/availability?doctor=${doctor.value.slug}`)

    if (!res.ok) {
      console.error('Error API /public/availability', res.status)
      events.value = []
      return
    }

    const data = await res.json()
    console.log('EVENTS FROM API RAW:', data)

    const list = Array.isArray(data) ? data : []

    const parsed = list.map(ev => ({
      ...ev,
      start: new Date(ev.start),
      end: new Date(ev.end),
    }))

    const appointmentEvents = parsed.filter(e =>
      e.class === 'pending' || e.class === 'confirmed'
    )

    const availableEvents = parsed.filter(e => e.class === 'available')

    const overlaps = (a, b) => a.start < b.end && b.start < a.end

    const filteredAvailable = availableEvents.filter(av =>
      !appointmentEvents.some(ap => overlaps(av, ap))
    )

    events.value = [
      ...appointmentEvents.map(e => ({
        ...e,
        title: e.title,
      })),
      ...filteredAvailable.map(e => ({
        ...e,
        title: 'Disponible',
      })),
    ]

    console.table(
      events.value.map(e => ({
        start: e.start,
        end: e.end,
        class: e.class,
        title: e.title,
      }))
    )
  } catch (e) {
    console.error('Error cargando disponibilidad', e)
    events.value = []
  }
}

function toInputDateTime (date) {
  const offset = date.getTimezoneOffset()
  const local = new Date(date.getTime() - offset * 60000)
  return local.toISOString().slice(0, 16)
}

function openReservationModal (jsDate) {
  if (isNaN(jsDate.getTime())) {
    console.error('Fecha inválida al abrir modal:', jsDate)
    return
  }

  selectedDate.value = jsDate
  form.start_at = toInputDateTime(jsDate)

  const end = new Date(jsDate.getTime() + (props.duration ?? 20) * 60 * 1000)
  form.end_at = toInputDateTime(end)

  form.doctor_id = doctor.value?.id ?? null
  showModal.value = true
}

function isWithinAvailableSlot (date) {
  return events.value.some(ev =>
    ev.class === 'available' &&
    date >= ev.start &&
    date < ev.end
  )
}

function normalizeEventPayload (payload) {
  return payload && payload.event ? payload.event : payload
}

function handleEventClick (payload) {
  const ev = normalizeEventPayload(payload)
  console.log('event-click:', ev, 'raw:', payload)

  if (!ev || ev.class !== 'available') {
    return
  }

  const jsDate = new Date(ev.start)
  openReservationModal(jsDate)
}

function handleTimeClick ({ start }) {
  const jsDate = new Date(start)
  console.log('time-click:', jsDate)

  if (!isWithinAvailableSlot(jsDate)) {
    console.log('Click fuera de un slot disponible')
    return
  }

  openReservationModal(jsDate)
}

function closeModal () {
  showModal.value = false
  form.patient_name = ''
  form.patient_email = ''
  form.patient_phone = ''
  form.start_at = ''
  form.end_at = ''
}

function submitReservation () {
  router.post('/appointments', form, {
    onSuccess: () => {
      Toastify({
        text: "Cita reservada en estado pendiente",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#16a34a",
      }).showToast();

      closeModal()
      loadAvailability()
    },
    onError: (errors) => {
      console.error(errors)

      Toastify({
        text: "Ya hay una cita a la misma hora por favor cambie la hora",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#dc2626",
      }).showToast();
    },
    preserveScroll: true,
  })
}

onMounted(loadAvailability)
</script>

<template>
  <PublicNavbar />
  <div class="max-w-6xl mx-auto p-6 relative">
    <div class="mb-4">
      <label class="block mb-1 font-semibold">Seleccionar doctor</label>
      <select
        v-model="doctor"
        @change="loadAvailability"
        class="border rounded p-2 w-full max-w-sm"
      >
        <option v-for="d in doctors" :key="d.slug" :value="d">
          {{ d.name }}
        </option>
      </select>
    </div>

    <VueCal
      :key="doctor?.slug"
      :time-from="7 * 60"
      :time-to="18 * 60"
      :time-step="duration"
      default-view="week"
      :events="events"
      :hide-view-selector="true"
      :disable-views="['years', 'year', 'month', 'day']"
      locale="es"
      @event-click="handleEventClick"
      @time-click="handleTimeClick"
    />

    <transition name="fade">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
          <h2 class="text-xl font-semibold mb-4">
            Reservar cita con {{ doctor?.name }}
          </h2>

          <p class="mb-3 text-sm text-gray-600">
            <strong>Duración por defecto:</strong> {{ props.duration }} minutos
          </p>

          <form @submit.prevent="submitReservation" class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">Inicio</label>
              <input
                v-model="form.start_at"
                type="datetime-local"
                class="w-full border rounded px-3 py-2"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Fin</label>
              <input
                v-model="form.end_at"
                type="datetime-local"
                class="w-full border rounded px-3 py-2"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Nombre del paciente</label>
              <input
                v-model="form.patient_name"
                type="text"
                class="w-full border rounded px-3 py-2"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Correo electrónico</label>
              <input
                v-model="form.patient_email"
                type="email"
                class="w-full border rounded px-3 py-2"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Teléfono (opcional)</label>
              <input
                v-model="form.patient_phone"
                type="text"
                class="w-full border rounded px-3 py-2"
              />
            </div>

            <div class="mt-4 flex justify-end space-x-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 rounded border border-gray-300 text-gray-700"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
              >
                Confirmar cita
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
:deep(.vuecal__event.pending),
:deep(.vuecal__event.confirmed) {
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

:deep(.vuecal__event.confirmed) {
  background-color: #bbf7d0;
  color: #065f46;
}

:deep(.vuecal__cell-events) {
  padding: 0;
}

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

:deep(.vuecal__event.available .vuecal__event-time),
:deep(.vuecal__event.available .vuecal__event-title) {
  display: none;
}

:deep(.vuecal__event.pending .vuecal__event-time),
:deep(.vuecal__event.confirmed .vuecal__event-time) {
  display: none;
}

:deep(.vuecal__event.pending .vuecal__event-title),
:deep(.vuecal__event.confirmed .vuecal__event-title) {
  font-size: 0.7rem;
  white-space: normal;
  line-height: 1.1;
  overflow: visible;
}
</style>
