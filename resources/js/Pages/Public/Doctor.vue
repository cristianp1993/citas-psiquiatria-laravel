<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PublicNavbar from '@/Components/PublicNavbar.vue'
const props = defineProps({ doctor: Object })
const events = ref([])
async function loadAvailability(){
  const res = await fetch(`/api/public/availability?doctor=${props.doctor.slug}`)
  events.value = (await res.json()).map(s=>({start:s.start,end:s.end,title:'Disponible'}))
}
function selectSlot(e){
  const start = new Date(e.start).toISOString()
  router.get('/appointments/new', { doctor: props.doctor.slug, start })
}
onMounted(loadAvailability)
</script>

<template>
  <PublicNavbar />
  <div class="max-w-6xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">{{ doctor.name }}</h1>
    <p>Especialidad: {{ doctor.specialty.name }}</p>
    <VueCal
      :time-from="8*60"
      :time-to="18*60"
      default-view="week"
      :events="events"
      :hide-view-selector="true"
      :disable-views="['years','year','month','day']"
      @cell-click="selectSlot"
    />
  </div>
</template>