<script setup>
import {computed, ref} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";

const props = defineProps({
  isOpen: {type: Boolean, default: false}
})

const isAdmin = computed(() => usePage().props.value.is_admin);
const isStylist = computed(() => usePage().props.value.is_stylist);

</script>

<template>
  <div v-if="isOpen" class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
    <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
    <h2 class="text-center text-white text-xl font-semibold">
      {{(isAdmin || isStylist) ? 'Elaboro la prenotazione...' : 'Stiamo elaborando la tua prenotazione...'}}
    </h2>
    <p class="w-1/3 text-center text-white">Aspetta qualche instante.</p>
  </div>
</template>