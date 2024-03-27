<script setup>
import {inject, ref} from "vue";
import {useWizardStore} from "../../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import Logo from "@/Components/General/Logo.vue";
import {
  PencilIcon,
  ChevronDownIcon,
  ChevronUpIcon,
} from "@heroicons/vue/outline";

const store = useWizardStore()
const { isStepCheckout, isValid, wizardGeneral, activeStep, wizardSelection } = storeToRefs(store)
const props = defineProps({
  menuItems: {
    type: Object
  },
  disableEdit: {
    type: Boolean,
    default: false
  }
})
const getMenuItemData = inject("getMenuItemData");
const emit = defineEmits(['goTo']);
const showSlideover = ref(false);
function hasPrice(step) {
  return step === 'step_updo' || step === 'step_primary_hair_service' || step === 'step_addons'
}
</script>
<template>
  <div
      class="fixed z-40 bg-white bottom-0 w-full px-4 py-3 shadow-[0_30px_60px_0px_rgba(0,0,0,0.3)]"
      :class="{
              'block md:hidden' : !isStepCheckout,
              'block xl:hidden' : isStepCheckout
            }"
      @click="showSlideover = true"
  >
    <div
        class="flex items-center justify-between text-bb-primary text-lg font-bold"
    >
      <div>Totale</div>
      <div class="flex items-center space-x-4">
        <div
            v-if="wizardGeneral.booking_infos?.actual_net_price"
            class="flex space-x-2"
        >
          <div
              v-if="
                wizardGeneral.booking_infos?.original_net_price !=
                wizardGeneral.booking_infos?.actual_net_price
              "
              class="line-through text-bb-gray-600"
          >
            {{ wizardGeneral.booking_infos?.original_net_price }}€
          </div>
          <div class="font-bold">
            {{ wizardGeneral.booking_infos?.actual_net_price }}€
          </div>
        </div>
        <div v-else>-</div>
        <ChevronUpIcon class="w-5 h-5" />
      </div>
    </div>
  </div>
  <div
      class="block xl:hidden fixed z-50 transform-gpu transition-transform bottom-0 left-0 w-full"
      :class="{
        'translate-y-0': showSlideover,
        'translate-y-full': !showSlideover,
      }"
  >
    <div class="bg-white rounded-t-3xl p-6" :class="{'shadow-md-up' : showSlideover}">
      <div class="flex justify-between mb-4">

      </div>

      <!-- title -->
      <h3 class="font-bold text-xl text-bb-primary mb-6 px-3">
        Il tuo appuntamento
      </h3>

      <!-- menu data -->
      <div class="mb-6">
        <div
            v-for="menuItem in menuItems"
            :key="menuItem.wizardStep"
            class="border-b border-white mb-0 px-3 py-2 text-sm rounded-xl"
            :class="{
              'bg-bb-gray-100' : wizardGeneral.activeStep?.name === menuItem.wizardStep
            }"
        >
          <div class="flex items-center justify-between">
            <div
                class="font-bold"
                :class="{
                  'text-bb-primary':
                    activeStep.name === menuItem.wizardStep,
                }"
            >
              {{ menuItem.title }}
            </div>
            <button
                :disabled="disableEdit || !isValid[menuItem.wizardStep]"
                @click="
                  () => {
                    emit('goTo', menuItem.wizardStep);
                    showSlideover = false;
                  }
                "
            >
              <PencilIcon class="w-3 h-3" />
            </button>
          </div>
          <div v-if="hasPrice(menuItem.wizardStep)">
<!--            <div v-if="selectedServices[menuItem.wizardStep].length > 0">-->
<!--              <div-->
<!--                  class="w-full flex flex-row items-center justify-between"-->
<!--                  v-for="item in selectedServices[menuItem.wizardStep]"-->
<!--                  :key="`service_${item.id}`">-->
<!--                <span>{{ item.title }} ({{ item.execution_time }}")</span>-->
<!--                <span class="font-bold">{{ item.net_price }}€</span>-->
<!--              </div>-->
<!--            </div>-->
<!--            <div v-else>-->
<!--              - -->
<!--            </div>-->
          </div>
          <div v-else>
            {{ getMenuItemData(menuItem.wizardStep) }}
          </div>
        </div>
      </div>

      <!-- total -->
      <div
          class="flex items-center justify-between text-bb-primary text-lg font-bold"
      >
        <div>Totale</div>
        <div class="flex items-center space-x-4">
          <div
              v-if="wizardGeneral.booking_infos?.actual_net_price"
              class="flex space-x-2"
          >
            <div
                v-if="
                  wizardGeneral.booking_infos?.original_net_price !=
                  wizardGeneral.booking_infos?.actual_net_price
                "
                class="line-through text-bb-gray-600"
            >
              {{ wizardGeneral.booking_infos?.original_net_price }}€
            </div>
            <div class="font-bold">
              {{ wizardGeneral.booking_infos?.actual_net_price }}€
            </div>
          </div>
          <div v-else>-</div>
          <ChevronDownIcon
              class="w-6 h-6 text-bb-primary"
              @click="showSlideover = false"
          />
        </div>
      </div>
    </div>
  </div>
</template>
