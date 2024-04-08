<script setup>
import {storeToRefs} from "pinia";
import {useWizardStore} from "../../../../DataStore/Wizard/wizardStore"
import {inject} from "vue";

const store = useWizardStore()
const { activeStep, isValid } = storeToRefs(store)

const props = defineProps({
  menuItems: {
    type: Object
  },
  disableEdit: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['goTo'])

</script>
<template>
  <div class="hidden md:block w-full bg-white shadow-sm">
    <div class="max-w-5xl w-full flex mx-auto px-8 py-4 items-center justify-between">
      <button
          v-for="(menuItem, index) in props.menuItems"
          :key="`desktop_btns_${menuItem.wizardStep}`"
          :disabled="disableEdit || !isValid[menuItem.wizardStep]"
          @click="emit('goTo', menuItem.wizardStep)"
          class="cursor-pointer font-bold"
          :class="{
                'text-bb-gray-300' : index > props.menuItems.findIndex(p => p.wizardStep == activeStep.name),
                'text-bb-blue': index < props.menuItems.findIndex(p => p.wizardStep == activeStep.name),
                'text-bb-blue underline': activeStep.name == menuItem.wizardStep
              }"
      >
        {{ menuItem.title }}
      </button>
    </div>
  </div>
</template>
