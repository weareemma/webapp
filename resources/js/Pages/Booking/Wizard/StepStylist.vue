<template>
  <div>
    <div class="text-center mb-16">
      Seleziona lo stylist
    </div>

    <div class="max-w-sm mx-auto">
      <div class="space-y-4">
        <div
            v-for="(store, index) in wizardGeneral.stylists"
            :key="store.id"
            class="bb-wizard__button"
            :class="{
          'bb-wizard__button--active': wizardGeneral.stylist === store.id,
        }"
          light
          @click="selectStylist(store)"
        >
          <p>{{ store.full_name }}</p>
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
import {inject} from "vue"
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore"
import {storeToRefs} from "pinia"

const store = useWizardStore()
const {
  wizardSelection,
  isValid,
  activeStep,
  wizardGeneral,
  ready
} = storeToRefs(store)

const next = inject("next");

function selectStylist(storeObj) {
  console.log(storeObj)
  wizardGeneral.value.stylist = storeObj.id;
}

</script>