<script setup>
import {PencilIcon} from "@heroicons/vue/outline";
import {useWizardStore} from "../../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import {inject} from "vue";

const store = useWizardStore()
const { isStepCheckout, activeStep, isValid, wizardGeneral  } = storeToRefs(store)
const props = defineProps({
  menuItems: {
    type: Object
  },
  disableEdit: {
    type: Boolean,
    default: false
  }
})
const getSidebarData = inject("getSidebarData");
const emit = defineEmits(['goTo']);

</script>
<template>
  <!-- infos -->
  <div
      v-if="activeStep?.name !== 'step_success'"
      class="w-full md:w-80 bg-[#B8C0E0] rounded-3xl py-10 px-8"
  >
    <!-- title -->
    <h2 class="text-xl font-bold text-bb-blue mb-6">Info appuntamento:</h2>

    <!-- menu data -->
    <div
        v-for="menuItem in menuItems"
        :key="menuItem.wizardStep"
        class="border-b border-white mb-4 pb-4"
    >
      <div class="flex items-center justify-between">
        <div
            class="font-bold"
            :class="{
                'text-white': activeStep.name === menuItem.wizardStep,
              }"
        >
          {{ menuItem.title }}
        </div>
        <button
            :disabled="!isValid[menuItem.wizardStep]"
            @click="emit('goTo', menuItem.wizardStep)">
          <PencilIcon class="text-white w-3 h-3" />
        </button>
      </div>
      <div v-if="getSidebarData(menuItem.wizardStep).length > 0">
        <div v-for="g in getSidebarData(menuItem.wizardStep)">
          <div v-for="d in g.data" class="mb-2">
            <p class="text-sm text-gray-600" v-if="getSidebarData(menuItem.wizardStep).length > 1">{{g.title}}</p>
            <div class="grid grid-cols-3 gap-1">
              <p class="col-span-2">{{ d.title ?? '-' }}</p>
              <div class="flex gap-x-1 place-self-end">
                <span class="text-right" :class="{
                  'line-through' : d.discounted
                }">
                  {{ (d.price) ? (d.price + ' €') : '' }}
                </span>
                <span v-if="d.discounted && d.discountedPrice" class="break-normal">
                  {{ d.discountedPrice + ' €' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-3">
        <p class="col-span-2">-</p>
        <p class="text-right"></p>
      </div>
    </div>

    <!-- total -->
    <div class="flex items-center justify-between font-bold">
      <div>Totale</div>

      <div class="flex items-center space-x-3">
        <div
            v-if="
                wizardGeneral.booking_infos?.actual_net_price !=
                wizardGeneral.booking_infos?.original_net_price
              "
            class="text-bb-gray-600 line-through"
        >
          {{ wizardGeneral.booking_infos?.original_net_price }}€
        </div>
        <div>{{ wizardGeneral.booking_infos?.actual_net_price ?? 0 }}€</div>
      </div>
    </div>
  </div>
</template>
