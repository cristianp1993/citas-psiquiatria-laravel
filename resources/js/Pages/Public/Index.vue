
<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ doctors: Array })
const doctor = ref(props.doctors[0] ?? null)
const events = ref([])
async function loadAvailability(){
  if(!doctor.value) return
  const res = await fetch(`/api/public/availability?doctor=${doctor.value.slug}`)
  events.value = (await res.json()).map(s=>({start:s.start,end:s.end,title:'Disponible'}))
}
function selectSlot(e){
  const start = new Date(e.start).toISOString()
  router.get('/appointments/new', { doctor: doctor.value.slug, start })
}
onMounted(loadAvailability)
</script>

<template>
  <div class="max-w-6xl mx-auto p-6">
    <div class="mb-4">
      <select v-model="doctor" @change="loadAvailability" class="border rounded p-2">
        <option v-for="d in doctors" :key="d.slug" :value="d">{{ d.name }}</option>
      </select>
    </div>
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
