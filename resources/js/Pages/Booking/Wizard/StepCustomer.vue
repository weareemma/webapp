<template>
  <div>
    <div class="text-center mb-16">
      Seleziona il cliente per il quale desideri effettuare la prenotazione
    </div>

    <div class="space-y-4 max-w-sm mx-auto">
      <bb-select
        mode="single"
        track-by="name"
        :searchable="true"
        placeholder="Seleziona un cliente"
        :close-on-select="true"
        :filter-results="false"
        :min-chars="1"
        :resolve-on-load="true"
        :delay="0"
        :options="async function(query) {
          return await fetchCustomers(query);
        }"
        :disabled="!!wizardGeneral.original_booking"
        v-model="wizardSelection.customer_id"
      />

      <bb-button
        v-if="wizardSelection.customer_id"
        class="w-full"
        outline
        @click.stop="next"
      >
        Avanti
      </bb-button>
    </div>
  </div>
</template>

<script setup>
import { Inertia } from "@inertiajs/inertia";
import {inject, onUpdated, onMounted , watch} from "vue";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";

const store = useWizardStore()
const {
  wizardSelection,
  wizardGeneral,
  isValid,
  activeStep,
  people,
  wizardFetchData
} = storeToRefs(store)

const prev = inject("prev");
const next = inject("next");

onMounted(() => {
  store.wizardFetchData('step_customer');
})

onUpdated(() => {
  store.wizardFetchData('step_customer');
})

const fetchCustomers = async (query = 'a') => {
  let customers = [];
  await axios.post(route('booking.customer.select'), {q: query}, {
    preserveScroll: true
  }).then((res) => {
    customers = res.data;
  })

  return customers;
}
</script>
