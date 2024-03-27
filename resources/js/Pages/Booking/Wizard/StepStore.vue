<template>
  <div>
    <div class="text-center mb-16">
      Seleziona lo store dove desideri effettuare la prenotazione
    </div>

    <div class="max-w-sm mx-auto">
      <div class="space-y-4">
        <div
            v-for="(store, index) in wizardGeneral.stores"
            :key="store.id"
            class="bb-wizard__button"
            :class="{
          'bb-wizard__button--active': wizardSelection.store_id === store.id,
        }"
            light
            @click="selectStore(store)"
        >
          <p>{{ store.name }}</p>
          <p class="text-sm">{{store.address}}</p>
        </div>
      </div>
    </div>

    <div class="flex justify-center">
      <bb-button
          v-if="isValid[activeStep.next]"
          class="mt-10"
          outline
          @click.stop="next"
          :disabled="! ready"
      >
        Avanti
      </bb-button>
    </div>
  </div>
</template>

<script setup>
import { Inertia } from "@inertiajs/inertia";
import {inject, onMounted, ref} from "vue";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import helpers from "@/helpers.js";

const store = useWizardStore()
const {
  wizardSelection,
  isValid,
  activeStep,
  wizardGeneral,
  ready,
  wizardFetchData
} = storeToRefs(store)

const prev = inject("prev");
const next = inject("next");

const goNextDisabled = ref(!(wizardSelection.value.store_id));

function selectStore(storeObj) {
  helpers.lg(wizardGeneral.value.stores);
  wizardSelection.value.store_id = storeObj.id;
  wizardSelection.value.washing_stations = storeObj.washing_stations;
  store.wizardFetchData('step_store');
}

onMounted(() => {

})
</script>
