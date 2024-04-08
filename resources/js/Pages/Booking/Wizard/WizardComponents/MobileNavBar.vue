<script setup>
import {storeToRefs} from "pinia";
import { Inertia } from "@inertiajs/inertia";
import {useWizardStore} from "../../../../DataStore/Wizard/wizardStore"
import { DotsBurgerIcon } from "@/Components/Icons";
import { ArrowLeftIcon } from "@heroicons/vue/outline";
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
  <div class="block md:hidden">
    <div class="flex items-center justify-between">
      <ArrowLeftIcon @click="Inertia.get(route('home'))" class="w-5 h-5" />
      <div class="font-bold text-2xl">Il tuo appuntamento</div>
    </div>
    <div class="relative mt-8 mb-4">
      <img
          src="/img/girl.png"
          class="absolute z-20 bottom-0 h-44 left-[calc(50%-88px)]"
      />
      <div
          class="relative z-10 inset-0 bg-[#F7BC74] rounded-2xl h-40"
      ></div>
    </div>
    <div class="relative overflow-x-auto no-scrollbar">
      <div class="flex space-x-8 min-w-max">
        <div
            v-for="(menuItem, index) in menuItems"
            :key="menuItem.wizardStep"
            class="cursor-pointer font-bold"
            :class="{
                'text-bb-gray-300' : index > menuItems.findIndex(p => p.wizardStep == activeStep.name),
                'text-bb-blue': index <  menuItems.findIndex(p => p.wizardStep == activeStep.name),
                'text-bb-blue underline': activeStep.name == menuItem.wizardStep
              }"
            @click="!disableEdit || isValid[menuItem.wizardStep] ? emit('goTo', menuItem.wizardStep) : void(0)"
        >
          {{ menuItem.title }}
        </div>
      </div>
    </div>
  </div>
</template>
