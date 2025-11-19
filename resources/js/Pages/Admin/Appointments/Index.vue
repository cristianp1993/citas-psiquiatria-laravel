<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ appointments: Object, filters: Object })

const doctorFilter = ref(props.filters.doctor || '')
const statusFilter = ref(props.filters.status || '')

function applyFilters() {
    router.get(route('appointments.index'), {
        doctor: doctorFilter.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
}

function accept(appointment) {
    if (confirm('¿Aceptar esta cita?')) {
        router.post(route('appointments.accept', appointment.slug))
    }
}

function reject(appointment) {
    if (confirm('¿Rechazar esta cita?')) {
        router.post(route('appointments.reject', appointment.slug))
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Citas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4 flex space-x-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por doctor</label>
                                <input v-model="doctorFilter" @input="applyFilters" type="text" placeholder="Slug del doctor" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por estado</label>
                                <select v-model="statusFilter" @change="applyFilters" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">Todos</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="confirmed">Confirmada</option>
                                    <option value="rejected">Rechazada</option>
                                </select>
                            </div>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="appointment in appointments.data" :key="appointment.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.patient_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ appointment.doctor.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ new Date(appointment.start_at).toLocaleString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        <span :class="{
                                            'text-yellow-600': appointment.status === 'pending',
                                            'text-green-600': appointment.status === 'confirmed',
                                            'text-red-600': appointment.status === 'rejected'
                                        }">
                                            {{ appointment.status === 'pending' ? 'Pendiente' : appointment.status === 'confirmed' ? 'Confirmada' : 'Rechazada' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('appointments.show', appointment.slug)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">Ver</Link>
                                        <button v-if="appointment.status === 'pending'" @click="accept(appointment)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2">Aceptar</button>
                                        <button v-if="appointment.status !== 'rejected'" @click="reject(appointment)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-2">Rechazar</button>
                                        <Link :href="route('appointments.destroy', appointment.slug)" method="delete" as="button" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Eliminar</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4" v-if="appointments.last_page > 1">
                            <Link v-for="page in appointments.last_page" :key="page" :href="route('appointments.index', { ...filters, page })" :class="['px-3 py-2 mx-1 border rounded', page == appointments.current_page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500']">
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>