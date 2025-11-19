<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
  doctors: Array,
  pending: Array,
  upcoming: Array,
  rejected: Array,
  filters: Object,
})

const selectedDoctor = ref(props.filters?.doctor || '')

watch(selectedDoctor, (value) => {
  router.get(
    route('dashboard'),
    { doctor: value || undefined },
    {
      preserveState: true,
      replace: true,
    }
  )
})

const pendingCount = computed(() => props.pending.length)
const upcomingCount = computed(() => props.upcoming.length)
const rejectedCount = computed(() => props.rejected.length)
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout title="Dashboard">
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Resumen de citas
        </h2>

        <div class="mt-3 md:mt-0">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Filtrar por médico
          </label>
          <select
            v-model="selectedDoctor"
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                   rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
          >
            <option value="">Todos</option>
            <option v-for="d in doctors" :key="d.slug" :value="d.slug">
              {{ d.name }}
            </option>
          </select>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Cards resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Pendientes -->
          <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Citas pendientes
            </h3>
            <p class="mt-3 text-3xl font-bold text-yellow-600">
              {{ pendingCount }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Últimas 10 citas en estado pendiente
            </p>
          </div>

          <!-- Próximas confirmadas -->
          <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Próximas confirmadas
            </h3>
            <p class="mt-3 text-3xl font-bold text-green-600">
              {{ upcomingCount }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Citas confirmadas a futuro (máx. 10)
            </p>
          </div>

          <!-- Rechazadas (o “otras”) -->
          <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Citas rechazadas
            </h3>
            <p class="mt-3 text-3xl font-bold text-red-600">
              {{ rejectedCount }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Últimas 10 citas en estado rechazado
            </p>
          </div>
        </div>

        <!-- Listado de pendientes -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
          <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">
            Pendientes
          </h3>
          <div v-if="pending.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
            No hay citas pendientes para el filtro actual.
          </div>
          <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <li
              v-for="a in pending"
              :key="a.id"
              class="py-2 flex items-center justify-between text-sm"
            >
              <div>
                <p class="font-medium text-gray-800 dark:text-gray-100">
                  {{ a.patient_name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ a.doctor?.name }} •
                  {{ new Date(a.start_at).toLocaleString() }}
                </p>
              </div>
              <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                Pendiente
              </span>
            </li>
          </ul>
        </div>

        <!-- Listado de próximas confirmadas -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
          <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">
            Próximas confirmadas
          </h3>
          <div v-if="upcoming.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
            No hay citas confirmadas próximas para el filtro actual.
          </div>
          <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <li
              v-for="a in upcoming"
              :key="a.id"
              class="py-2 flex items-center justify-between text-sm"
            >
              <div>
                <p class="font-medium text-gray-800 dark:text-gray-100">
                  {{ a.patient_name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ a.doctor?.name }} •
                  {{ new Date(a.start_at).toLocaleString() }}
                </p>
              </div>
              <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                Confirmada
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
