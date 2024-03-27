<template>
  <div>
    <div class="text-center mb-16">
      Puoi prenotare fino ad un massimo di {{wizardSelection.washing_stations}} persone
    </div>

    <div class="max-w-sm mx-auto">
      <div class="space-y-4">
        <div
            class="bb-wizard__button"
            :class="{
          'bb-wizard__button--active': ! wizardSelection.multiple,
        }"
            light
            @click="selectMultiple(false)"
        >
          <p>Solo per me</p>
        </div>
        <div
            v-if="wizardSelection.washing_stations > 1"
            class="bb-wizard__button"
            :class="{
          'bb-wizard__button--active': wizardSelection.multiple,
        }"
            light
            @click="selectMultiple(true)"
        >
          <p>Per me e altre amiche</p>
        </div>
      </div>

      <div class="my-3" v-if="wizardSelection.multiple">
        <p class="text-center">Seleziona il numero di amiche.</p>
        <p class="text-center">Per più di {{wizardSelection.washing_stations}} persone contattaci al <a href="mailto:info@weareemma.com" class="text-bb-primary">info@weareemma.com</a></p>
        <PeopleCountInput v-model="peopleCount" :max="wizardSelection.washing_stations" class="my-4" />
      </div>

    </div>

    <div class="flex justify-center">
      <bb-button
          class="mt-10"
          outline
          @click.stop="next"
          :disabled="goNextDisabled"
      >
        Avanti
      </bb-button>
    </div>
  </div>
</template>

<script setup>
import { Inertia } from "@inertiajs/inertia";
import {inject, onMounted, ref, watch} from "vue";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import helpers from "../../../helpers";
import PeopleCountInput from './WizardComponents/PeopleCountInput.vue';

const store = useWizardStore()
const {
  wizardSelection,
  isValid,
  activeStep,
  people,
} = storeToRefs(store)

const prev = inject("prev");
const next = inject("next");

const goNextDisabled = ref(!(wizardSelection.value.store_id));
const peopleCount = ref( (people.value.length === 1) ? 1 : (people.value.length - 1));

onMounted(() => {
  helpers.lg(wizardSelection.value.washing_stations);
})

watch(peopleCount, (n,o) => {
  updatePeopleObject();
})

function updatePeopleObject()
{
  store.wizardResetPeople();
  store.wizardAddPeople(peopleCount.value);
}

function selectMultiple(multi) {
  wizardSelection.value.multiple = multi;
  if (multi)
  {
    updatePeopleObject();
  }
  else
  {
    wizardSelection.value.different_services = false;
    store.wizardResetPeople();
  }
}
</script>
