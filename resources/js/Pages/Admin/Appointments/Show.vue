<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout,
})

defineProps({ appointment: Object })
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cita de {{ appointment.patient_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4">
                            <strong>Paciente:</strong> {{ appointment.patient_name }}
                        </div>
                        <div class="mb-4">
                            <strong>Email:</strong> {{ appointment.patient_email }}
                        </div>
                        <div class="mb-4">
                            <strong>Teléfono:</strong> {{ appointment.patient_phone || 'No proporcionado' }}
                        </div>
                        <div class="mb-4">
                            <strong>Doctor:</strong> {{ appointment.doctor.name }}
                        </div>
                        <div class="mb-4">
                            <strong>Inicio:</strong> {{ new Date(appointment.start_at).toLocaleString() }}
                        </div>
                        <div class="mb-4">
                            <strong>Fin:</strong> {{ new Date(appointment.end_at).toLocaleString() }}
                        </div>
                        <div class="mb-4">
                            <strong>Estado:</strong>
                            <span :class="{
                                'text-yellow-600': appointment.status === 'pending',
                                'text-green-600': appointment.status === 'confirmed',
                                'text-red-600': appointment.status === 'rejected'
                            }">
                                {{ appointment.status === 'pending' ? 'Pendiente' : appointment.status === 'confirmed' ? 'Confirmada' : 'Rechazada' }}
                            </span>
                        </div>
                        <div class="mb-4">
                            <strong>Creada:</strong> {{ new Date(appointment.created_at).toLocaleString() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>