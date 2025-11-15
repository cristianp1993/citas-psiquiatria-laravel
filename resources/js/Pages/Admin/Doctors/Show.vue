<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout,
})

defineProps({ doctor: Object })
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Doctor: {{ doctor.name }}
                </h2>
                <Link :href="route('doctors.edit', doctor.slug)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Editar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4">
                            <strong>Nombre:</strong> {{ doctor.name }}
                        </div>
                        <div class="mb-4">
                            <strong>Email:</strong> {{ doctor.email }}
                        </div>
                        <div class="mb-4">
                            <strong>Especialidad:</strong> {{ doctor.specialty.name }}
                        </div>
                        <div class="mb-4">
                            <strong>Activo:</strong> {{ doctor.is_active ? 'Sí' : 'No' }}
                        </div>
                        <div class="mb-4">
                            <strong>Horarios:</strong>
                            <ul v-if="doctor.schedules.length" class="list-disc list-inside">
                                <li v-for="schedule in doctor.schedules" :key="schedule.id">
                                    Día {{ schedule.weekday }}: {{ schedule.start_time }} - {{ schedule.end_time }}
                                </li>
                            </ul>
                            <span v-else>No hay horarios definidos.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>