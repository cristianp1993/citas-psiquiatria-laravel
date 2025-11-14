
<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({ doctor:Object })
const events = ref([])
async function load(){
  if(!props.doctor) return
  const res = await fetch(`/api/admin/calendar?doctor=${props.doctor.slug}`)
  events.value = await res.json()
}
onMounted(load)
</script>
<template>
  <div class="max-w-6xl mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Agenda de {{ doctor?.name }}</h1>
    <VueCal
      :time-from="8*60"
      :time-to="18*60"
      default-view="week"
      :events="events"
      :hide-view-selector="true"
      :disable-views="['years','year','month','day']"
    />
  </div>
</template>
