<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({ doctor: Object, specialties: Array })

const form = useForm({
    name: props.doctor.name,
    email: props.doctor.email,
    specialty_id: props.doctor.specialty_id,
    is_active: props.doctor.is_active
})

function submit() {
    form.put(route('doctors.update', props.doctor.slug))
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editar Doctor: {{ doctor.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required />
                                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Especialidad</label>
                                <select v-model="form.specialty_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Seleccionar especialidad</option>
                                    <option v-for="specialty in specialties" :key="specialty.id" :value="specialty.id">{{ specialty.name }}</option>
                                </select>
                                <div v-if="form.errors.specialty_id" class="text-red-600 text-sm mt-1">{{ form.errors.specialty_id }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Activo</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                    Actualizar Doctor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>