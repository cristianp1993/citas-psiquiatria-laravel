<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ specialties: Array })

const weekdayOptions = [
  { value: 1, label: 'Lunes' },
  { value: 2, label: 'Martes' },
  { value: 3, label: 'Miércoles' },
  { value: 4, label: 'Jueves' },
  { value: 5, label: 'Viernes' },
  { value: 6, label: 'Sábado' },
  { value: 7, label: 'Domingo' },
]

const form = useForm({
  name: '',
  email: '',
  specialty_id: '',
  is_active: true,
  schedules: [
    {
      weekday: 1,
      start_time: '',
      end_time: '',
    },
  ],
})

function addScheduleRow() {
  form.schedules.push({
    weekday: 1,
    start_time: '',
    end_time: '',
  })
}

function removeScheduleRow(index) {
  form.schedules.splice(index, 1)
}

function submit() {
  const validSchedules = form.schedules.filter(
    s => s.weekday && s.start_time && s.end_time
  )

  if (validSchedules.length === 0) {
    form.setError('schedules', 'Debes agregar al menos un horario válido (día y horas completas).')
    return
  }

  form.clearErrors('schedules')
  form.schedules = validSchedules
  form.post(route('doctors.store'))
}
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Crear Doctor
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submit">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  required
                />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  required
                />
                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                  {{ form.errors.email }}
                </div>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad</label>
                <select
                  v-model="form.specialty_id"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  required
                >
                  <option value="">Seleccionar especialidad</option>
                  <option v-for="specialty in specialties" :key="specialty.id" :value="specialty.id">
                    {{ specialty.name }}
                  </option>
                </select>
                <div v-if="form.errors.specialty_id" class="text-red-600 text-sm mt-1">
                  {{ form.errors.specialty_id }}
                </div>
              </div>

              <div class="mb-4">
                <label class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600"
                  />
                  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Activo</span>
                </label>
              </div>

              <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Horarios de atención</h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                  Define los días y horas en que este doctor atiende. Estos horarios se usarán para
                  calcular la disponibilidad en el calendario público.
                </p>

                <div v-if="form.errors.schedules" class="text-red-600 text-sm mb-2">
                  {{ form.errors.schedules }}
                </div>

                <div class="space-y-2">
                  <div
                    v-for="(schedule, index) in form.schedules"
                    :key="index"
                    class="flex flex-wrap items-center gap-2"
                  >
                    <select
                      v-model="schedule.weekday"
                      class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm"
                    >
                      <option
                        v-for="day in weekdayOptions"
                        :key="day.value"
                        :value="day.value"
                      >
                        {{ day.label }}
                      </option>
                    </select>

                    <input
                      v-model="schedule.start_time"
                      type="time"
                      class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm"
                    />

                    <span>a</span>

                    <input
                      v-model="schedule.end_time"
                      type="time"
                      class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm"
                    />

                    <button
                      type="button"
                      @click="removeScheduleRow(index)"
                      class="text-red-600 text-xs ml-2"
                    >
                      Quitar
                    </button>
                  </div>
                </div>

                <button
                  type="button"
                  @click="addScheduleRow"
                  class="mt-3 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-1 rounded"
                >
                  + Añadir horario
                </button>
              </div>

              <div class="flex items-center justify-end">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  Crear Doctor
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
