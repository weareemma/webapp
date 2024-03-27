<script setup>
import {storeToRefs} from "pinia";
import {useWizardStore} from "../../../../DataStore/Wizard/wizardStore"
import { PencilIcon } from "@heroicons/vue/outline";
import {inject} from "vue";
import helpers from "../../../../helpers";

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
  <div
      class="w-full py-10 px-5 rounded-3xl shadow shadow-md bg-white sticky top-0"
      :class="{
              'hidden md:block' : !isStepCheckout,
              'hidden xl:block' : isStepCheckout
            }"
  >

    <!-- title -->
    <h2 class="text-xl font-bold text-bb-blue mb-6 px-4">Il tuo appuntamento:</h2>

    <!-- menu data -->
    <div
        v-for="menuItem in menuItems"
        :key="menuItem.wizardStep"
        class="mb-4 px-4 py-3 rounded-xl"
        :class="{
          'bg-bb-gray-100' : activeStep.name === menuItem.wizardStep
        }"
    >
      <div class="flex items-center justify-between">
        <div class="font-bold text-bb-blue">
          {{ menuItem.title }}
        </div>
        <button
            v-if="!disableEdit && isValid[menuItem.wizardStep]"
            :disabled="disableEdit || !isValid[menuItem.wizardStep]"
            @click="emit('goTo', menuItem.wizardStep)">
          <PencilIcon class="w-3 h-3 text-bb-blue" />
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

<!--      <div v-if="!multiple" class="grid grid-cols-3">-->
<!--        <p class="col-span-2">{{ getMenuItemData(menuItem.wizardStep) }}</p>-->
<!--        <p class="text-right">{{ getPriceItemData(menuItem.wizardStep) }}</p>-->
<!--      </div>-->
<!--      <div v-if="multiple" class="grid grid-cols-3">-->
<!--        <p class="col-span-2">{{ getMenuItemData(menuItem.wizardStep) }}</p>-->
<!--        <p class="text-right">{{ getPriceItemData(menuItem.wizardStep) }}</p>-->
<!--      </div>-->
    </div>

    <!-- total -->
    <div
        class="flex items-center justify-between font-bold text-bb-blue mt-4 pt-6 border-bb-blue border-t"
    >
      <div>Totale</div>

      <div class="flex items-center space-x-3">
        <div
            v-if="
              wizardGeneral.booking_infos?.actual_net_price !=
              wizardGeneral.booking_infos?.original_net_price
            "
            class="text-bb-gray-600 line-through"
        >
          {{ helpers.printPrice(wizardGeneral.booking_infos?.original_net_price)  }}€
        </div>
        <div>{{ helpers.printPrice(wizardGeneral.booking_infos?.actual_net_price ?? 0) }}€</div>
      </div>
    </div>
  </div>
</template>
