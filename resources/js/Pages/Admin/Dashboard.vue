<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({ doctors: Array, pending: Array, upcoming: Array })

const selectedDoctor = ref('')

const filteredPending = computed(() => {
    if (!selectedDoctor.value) return props.pending
    return props.pending.filter(a => a.doctor.slug === selectedDoctor.value)
})

const filteredUpcoming = computed(() => {
    if (!selectedDoctor.value) return props.upcoming
    return props.upcoming.filter(a => a.doctor.slug === selectedDoctor.value)
})
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por médico:</label>
                            <select v-model="selectedDoctor" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.slug">{{ doctor.name }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Citas Pendientes</h3>
                                <div v-if="filteredPending.length === 0" class="text-gray-500">No hay citas pendientes.</div>
                                <div v-else class="space-y-2">
                                    <div v-for="appointment in filteredPending" :key="appointment.id" class="border rounded p-4">
                                        <div class="font-medium">{{ appointment.patient_name }}</div>
                                        <div class="text-sm text-gray-600">{{ appointment.doctor.name }}</div>
                                        <div class="text-sm">{{ new Date(appointment.start_at).toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-4">Próximas Citas Confirmadas</h3>
                                <div v-if="filteredUpcoming.length === 0" class="text-gray-500">No hay citas próximas confirmadas.</div>
                                <div v-else class="space-y-2">
                                    <div v-for="appointment in filteredUpcoming" :key="appointment.id" class="border rounded p-4">
                                        <div class="font-medium">{{ appointment.patient_name }}</div>
                                        <div class="text-sm text-gray-600">{{ appointment.doctor.name }}</div>
                                        <div class="text-sm">{{ new Date(appointment.start_at).toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>