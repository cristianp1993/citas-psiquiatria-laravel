
<script setup>
import { useForm } from '@inertiajs/vue3'
const props = defineProps({ doctor:Object, start:String })
const form = useForm({ doctor_id: props.doctor.id, patient_name:'', patient_email:'', patient_phone:'', start_at: props.start })
function submit(){ form.post('/appointments') }
</script>
<template>
  <div class="max-w-xl mx-auto p-6 space-y-3">
    <h1 class="text-xl font-semibold">Reservar con {{ doctor.name }}</h1>
    <input v-model="form.patient_name" class="border p-2 w-full" placeholder="Nombre completo" />
    <input v-model="form.patient_email" class="border p-2 w-full" placeholder="Correo" />
    <input v-model="form.patient_phone" class="border p-2 w-full" placeholder="Teléfono" />
    <div>Inicio: {{ new Date(start).toLocaleString() }}</div>
    <button @click="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Reservar</button>
  </div>
</template>
