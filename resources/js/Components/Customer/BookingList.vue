<script setup>
import { ref, computed, onMounted, onUpdated, watch } from 'vue';
import { Inertia } from "@inertiajs/inertia";
import { usePage, Link } from "@inertiajs/inertia-vue3";

const bookings = ref(usePage().props.value.customer.bookings);

function goToStep(id, step) {
  const url = route('booking.edit', id) + "?step=" + step
  Inertia.visit(url);
}
</script>

<template>
  <div class="bb-card py-6 px-5 bg-bb-lilla">
    <h3 class="text-xl text-bb-gray-800 font-bold">I tuoi prossimi appuntamenti</h3>
    <div v-for="(booking, index) in bookings" :key="booking.id">
      <div class="flex flex-row justify-between items-center py-4">
        <span class="text-lg text-bb-gray-800 font-bold">{{booking.date}} {{booking.start}}</span>
        <bb-button
            v-if="booking.customer_must_pay"
            @click="goToStep(booking.id, 'step_checkout')" type="button">
            Paga ora
        </bb-button>
      </div>
      <div v-if="index != (bookings.length - 1)" class="flex-1 h-px bg-bb-gray-400 my-3" />
    </div>
  </div>
</template>
