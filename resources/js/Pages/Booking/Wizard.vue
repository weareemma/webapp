<template>
  <div class="bg-white h-screen overflow-y-auto">
    <!-- title desktop -->
    <div class="relative hidden md:block py-12 bg-[#F7BC74]">
      <Logo class="w-full flex items-center justify-center z-20" :light="false"></Logo>
      <div class="absolute w-full h-full top-0 ">
        <Link href="/"><div class="cursor-pointer max-w-5xl flex relative mx-auto w-full h-full px-8 z-20 overflow-hidden">
          <img class="absolute -bottom-16" src="/img/girl.png">
        </div></Link>
      </div>
    </div>

    <DesktopNavBar
        @go-to="wizard.goToStepByName($event)"
        :disable-edit="wizardGeneral.disableEdit"
        :menu-items="menuItems" />

    <div id="wizard-container" class="max-w-7xl mx-auto py-5 px-8">
      <MobileNavBar
          @go-to="wizard.goToStepByName($event)"
          :disable-edit="wizardGeneral.disableEdit"
          :menu-items="menuItems" />
      <div
          class="grid md:gap-6 col-end md:col-start"
          :class="{
            'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : ! isStepCheckout,
            'grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3' : isStepCheckout
          }"
      >
        <div
            class="col-span-3 sm:px-8"
            :class="(activeStep.name !== 'step_checkout') ? 'md:col-span-1 lg:col-span-2' : ''"
          >
          <Wizard
              ref="wizard"
              :steps="wizardSteps"
              :showCallback="fetchBookingData"
              @logged-in="restoreState"
              @registered="restoreState"
          >
            <!-- title -->
            <template #title="{ title }">
              <p class="text-2xl font-bold text-center text-bb-gray-900">{{ title }}</p>
            </template>

            <!-- step 1 -->
            <template #step_store>
              <StepStore />
            </template>

            <!-- step 1b -->
            <template #step_people>
              <StepPeople />
            </template>

            <!-- step 2 -->
            <template #step_primary_hair_service>
              <StepPrimaryHairService />
            </template>

            <!-- step 3 -->
            <template #step_updo>
              <StepUpdo />
            </template>

            <!-- step 4 -->
            <template #step_addons>
              <StepAddons />
            </template>

            <template #step_stylist>
              <StepStylist />
            </template>

            <!-- step 5 -->
            <template #step_calendar>
              <StepCalendar />
            </template>

            <!-- step 6 -->
            <template #step_checkout>
              <StepCheckout />
            </template>
          </Wizard>
        </div>

        <div class="col-auto">
          <DesktopSideBar
              v-if="activeStep.name !== 'step_checkout'"
              @go-to="wizard.goToStepByName($event)"
              :disable-edit="wizardGeneral.disableEdit"
              :menu-items="menuItems" />
        </div>
      </div>
    </div>
    <MobileSlideOver
        v-if="(activeStep.name !== 'step_checkout')"
        @go-to="wizard.goToStepByName($event)"
        :disable-edit="wizardGeneral.disableEdit"
        :menu-items="menuItems" />

    <Footer class="mt-10" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUpdated, reactive, provide, onBeforeUnmount } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import Logo from "@/Components/General/Logo.vue";
import dayjs from "dayjs";
import Wizard from "@/Components/General/Wizard.vue";
import StepStore from "./Wizard/StepStore.vue";
import StepPeople from "./Wizard/StepPeople.vue";
import StepPrimaryHairService from "./Wizard/StepPrimaryHairService.vue";
import StepUpdo from "./Wizard/StepUpdo.vue";
import StepAddons from "./Wizard/StepAddons.vue";
import StepStylist from "./Wizard/StepStylist.vue";
import StepCalendar from "./Wizard/StepCalendar.vue";
import StepCheckout from "./Wizard/StepCheckout.vue";
import axios from "axios";
import { useWizardStore } from "@/DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import DesktopNavBar from "./Wizard/WizardComponents/DesktopNavBar.vue";
import MobileNavBar from "./Wizard/WizardComponents/MobileNavBar.vue";
import DesktopSideBar from "./Wizard/WizardComponents/DesktopSideBar.vue";
import MobileSlideOver from "./Wizard/WizardComponents/MobileSlideOver.vue";
import Footer from '@/Layouts/Footer.vue';
import helpers from "../../helpers";

const props = defineProps({
  stores_list_wizard: Object,
  originalBooking: { type: Object, default: null },
  primaryHairServices: { type: Array, default: null },
  addonHairServices: { type: Object, default: null },
  jumpToStep: { type: String, default: null }
});

onBeforeUnmount(() => {
  resetStore();
})

const store = useWizardStore();

const {
  activeStep,
  isStepCheckout,
  wizardSelection,
  wizardGeneral,
  people,
  wizardLoadBooking,
  ready,
  totalReady,
  wizardFetchData
} = storeToRefs(store);

const primariesNotIncluded = usePage().props.value.primaries_not_included;

const restore_state = computed(() => usePage().props.value.flash.restore_state)
const subscribed = computed(() => usePage().props.value.subscribed);

const wizard = ref(null);

onUpdated(() => {
  wizardGeneral.value.subscribed = subscribed.value;
});

onMounted(() => {

  resetStore();

  // Init sto
  wizardGeneral.value.stores = props.stores_list_wizard;
  wizardGeneral.value.subscribed = subscribed.value;
  console.log(wizardGeneral.subscribed + ' test');
  if (props.originalBooking)
  {
    store.wizardLoadBooking(props.originalBooking);
  }

  fetchPaymentInfos();
  if (props.jumpToStep) {
    wizard.value.goToStepByName(props.jumpToStep);
  }

  if (restore_state.value) {
    restoreState()
  }

});

function resetStore() {
  store.$reset()
}

function fetchBookingData({ update }) {
  totalReady.value = false;
  Inertia.post(route("booking.infos"), {
    ...wizardSelection.value,
    ...wizardGeneral.value,
    people: people.value
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (response) => {
      wizardGeneral.value.booking_infos = {
        ...wizardGeneral.value.booking_infos,
        ...response.props.flash.data,
      };

      // if step calendar do the availability check
      // do it HERE instead of the step because this Inertia visit will cancel other visits
      // like booking.infos if done after it
      if (activeStep.value.name === "step_calendar") {
        Inertia.post(
          route("booking.hair-services.check-availability"),
            {
              ...wizardGeneral.value,
              ...wizardSelection.value,
              people: people.value
            },
          {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (response) => {
              const { days, infos } = response.props.flash.data;
              wizardGeneral.value.booking_infos = {
                ...wizardGeneral.value.booking_infos,
                ...infos,
              };
              wizardSelection.value.available_days = days;

              console.log(response);

              if (wizardSelection.value.selected_day) {
                const day = wizardSelection.value.available_days.find(
                  (d) => d.date === wizardSelection.value.selected_day.date && d.available
                );
                wizardSelection.value.selected_day = day ?? null;

                if (wizardSelection.value.selected_slot && day) {
                  const slot = day.slots.find(
                      (s) => s.time === wizardSelection.value.selected_slot.time && s.available
                  );
                  helpers.lg(slot);
                  wizardSelection.value.selected_slot = slot ?? null;
                }
                else
                {
                  wizardSelection.value.selected_slot = null;
                }
              }
              else
              {
                wizardSelection.value.selected_day = null;
                wizardSelection.value.selected_slot = null;
              }

              if (wizardSelection.value.selected_day === null)
              {
                let firstAvailableDay = wizardSelection.value.available_days.find(s => s.available );
                if (firstAvailableDay)
                {
                  helpers.lg('SET DAY 2');
                  wizardSelection.value.selected_day = firstAvailableDay;

                  let slot = wizardSelection.value.selected_day.slots.find(s => s.available);

                  if (slot)
                  {
                    wizardSelection.value.selected_slot = slot;
                  }
                }
              }

              update();
            },
            onError: (error) => {
              console.log(error);
            },
            onFinish: () => {
              totalReady.value = true;
            }
          }
        );
      }

      // if step checkout do the discount
      // do it HERE instead of the step because this Inertia visit will cancel other visits
      // like booking.infos if done after it
      if (activeStep.value.name === "step_checkout") {
        if (wizardGeneral.value.discount_code) {
          Inertia.post(
            route("booking.discount.check"),
            {
              ...wizardGeneral.value,
              ...wizardSelection.value,
              people: people.value
            },
            {
              preserveScroll: true,
              preserveState: true,
              onSuccess: (response) => {
                const data = response.props.flash?.data;
                const discount = data?.discount;
                const errors = data?.errors ?? [];
                wizardGeneral.value.discount = discount;
                wizardGeneral.value.discount_errors = errors;
                wizardGeneral.value.booking_infos.discount = discount;

                if (discount) {
                  wizardGeneral.value.booking_infos.actual_net_price =
                    discount.discounted_price;
                  update();
                }
              },
              onFinish: () => {
                totalReady.value = true;
              }
            }
          );
        }
        else
        {
          totalReady.value = true;
        }
      }

      update();
    },
    onFinish: () => {
      if (activeStep.value.name !== 'step_calendar' && activeStep.value.name !== 'step_checkout') totalReady.value = true;
    }
  });
}

async function fetchPaymentInfos() {
  const response = await axios.get(route("booking.payment-infos"));
  if (response.data) {
    wizardGeneral.value.payment_infos = response.data;
  }
}

async function restoreState() {
  let old_state = JSON.parse(localStorage.getItem('wizardData'))
  wizard.value.updateData(old_state)
  await fetchPaymentInfos()
  wizard.value.goToStepByName('step_checkout')
  localStorage.removeItem('wizardData')
}

const wizardSteps = computed(() => {
  let steps = [
    {
      name: "step_store",
      title: "Dove vuoi prenotare?",
    },
    {
      name: "step_people",
      title: "Per chi vuoi prenotare?",
    },
    {
      name: "step_primary_hair_service",
      title: "Quale servizio?",
    },
    {
      name: "step_updo",
      title: "Raccolti e Trattamenti",
    },
    {
      name: "step_addons",
      title: "Taglio/Colore",
    },
    {
      name: "step_stylist",
      title: "Quale stylist?",
    }
  ];

  // if (!selectedPrimaryService.value?.dry_style) {
  //   steps.push();
  // }

  steps = steps.concat([
    {
      name: "step_calendar",
      title: "Quando vuoi venire?",
      onBeforeShow: () => {
        return (wizardSelection.value.store_id);
      },
      onShow: () => {
        wizardSelection.value.available_days = null;
      },
    },
    {
      name: "step_checkout",
      title: props.originalBooking
        ? "Conferma le modifiche"
        : "Procedi all’acquisto per concludere la prenotazione",
      onBeforeShow: () => {
        return !!(
            wizardSelection.value.store_id &&
            wizardSelection.value.selected_day &&
            wizardSelection.value.selected_slot
        );
      },
    },
  ]);

  return steps;
});

const menuItems = computed(() => {
  let items = [
    { title: "Dove", wizardStep: "step_store" },
    { title: "Numero persone", wizardStep: "step_people" },
    { title: "Servizi", wizardStep: "step_primary_hair_service" },
    { title: "Raccolti/Trattamenti", wizardStep: "step_updo" },
    { title: "Taglio/Colore", wizardStep: "step_addons" },
    { title: "Stylist", wizardStep: "step_stylist" }
  ];

  // if (selectedPrimaryService.value && !selectedPrimaryService.value?.dry_style)
  //   items.push({ title: "Add-on", wizardStep: "step_addons" });

  items = items.concat([
    { title: "Quando", wizardStep: "step_calendar" },
    { title: "Checkout", wizardStep: "step_checkout" },
  ]);

  return items;
});

function getMenuItemData(wizardStep) {
  switch (wizardStep) {
    case "step_store":
      if (wizardSelection.value.store_id) {
        let store = wizardGeneral.value.stores.find(el => el.id === wizardSelection.value.store_id);
        return (store) ? store.name : '-';
      }
      return null;
    case "step_primary_hair_service":
      return "-";
    case "step_people":
      if (wizardSelection.value.multiple && people.value.length > 0) {
        return (people.value.length) + ' persone';
      }
      return 'Solo tu';
    case "step_updo":
      return '-';
    case "step_addons":
      return "-";
    case "step_stylist":
      if (wizardGeneral.value.stylist) {
        let stylist = wizardGeneral.value.stylists.find(el => el.id === wizardGeneral.value.stylist);
        return (stylist) ? stylist.full_name : '-';
      }
      return null
    case "step_calendar":
      if ((wizardSelection.value.selected_day, wizardSelection.value.selected_slot)) {
        return dayjs(
          `${wizardSelection.value.selected_day?.date} ${wizardSelection.value.selected_slot?.time}`
        ).format("ddd DD MMMM, HH:mm");
      }
      return "-";
  }
}
provide('getMenuItemData', getMenuItemData)

function getPriceItemData(wizardStep) {
  // switch (wizardStep) {
  //
  //   case "step_primary_hair_service":
  //     if (wData.value.primary_hair_service_id) {
  //       return `${selectedPrimaryService.value?.net_price}`;
  //     }
  //     return null;
  //
  //   case "step_updo":
  //     return selectedUpdoPrice.value;
  //
  //   case "step_addons":
  //     if (wData.value.addons && Object.values(wData.value.addons).length > 0) {
  //       return selectedAddonsPrice.value ?? '-';
  //     }
  //     return null;
  //   default: return "";
  // }
  return 'temp';
}
provide('getPriceItemData', getPriceItemData)

function getSidebarData(wizardStep)
{
  let data = [];
  switch (wizardStep) {

    case "step_store":
      return [
        {
          title: '',
          data: [{
            title: getMenuItemData(wizardStep)
          }]
        }
      ];

    case "step_people":
      return [
        {
          title: '',
          data: [{
            title: getMenuItemData(wizardStep)
          }]
        }
      ];

    case "step_primary_hair_service":
      _.forEach(people.value, (p) => {
        data.push({
          title: (p.name === 0) ? 'Tu' : ('Amica ' + p.name),
          data: [{
            title: ( p.primary_service ) ? (p.primary_service.title + '(' + p.primary_service.execution_time_fe + "')") : null,
            price: ( p.primary_service ) ? (p.primary_service?.net_price) : null,
            discounted: (p.name === 0 && wizardGeneral.value.subscribed && ( ! primariesNotIncluded.includes(p.primary_service?.title)))
          }]
        })
      });
      return data;

    case "step_updo":
      _.forEach(people.value, (p) => {
        data.push({
          title: (p.name === 0) ? 'Tu' : ('Amica ' + p.name),
          data: [{
            title: (p.addons.updo.length > 0) ? (p.addons.updo[0].title + '(' + ((p.addons.updo[0].extra === true) ? 0 : p.addons.updo[0].execution_time_fe) + "')") : null,
            price: (p.addons.updo.length > 0) ? (p.addons.updo[0].net_price) : null,
            discounted: (p.name === 0) && wizardGeneral.value.subscribed,
            discountedPrice: ((p.name === 0) && wizardGeneral.value.subscribed && p.addons.updo.length > 0) ? (p.addons.updo[0].net_price_discounted) : null
          }]
        })
      });
      return data;

    case "step_addons":
      _.forEach(people.value, (p) => {
        let titles = [];
        let prices = [];
        let discountedPrices = [];
        if (p.addons.massage.length > 0)
        {
          titles.push(p.addons.massage[0].title + '(' + ((p.addons.massage[0].extra === true) ? 0 : p.addons.massage[0].execution_time_fe) + "')");
          prices.push(p.addons.massage[0].net_price);
          discountedPrices.push(helpers.printPrice(p.addons.massage[0].net_price_discounted));
        }
        if (p.addons.treatment.length > 0)
        {
          titles.push(p.addons.treatment[0].title + '(' + ((p.addons.treatment[0].extra === true) ? 0 : p.addons.treatment[0].execution_time_fe) + "')");
          prices.push(p.addons.treatment[0].net_price);
          discountedPrices.push(helpers.printPrice(p.addons.treatment[0].net_price_discounted));
        }
        data.push({
          title: (p.name === 0) ? 'Tu' : ('Amica ' + p.name),
          data: [{
            title: titles.join(', '),
            price: prices.join('€ '),
            discounted: (p.name === 0) && wizardGeneral.value.subscribed,
            discountedPrice: ((p.name === 0) && wizardGeneral.value.subscribed) ? discountedPrices.join('€ ') : null
          }]
        })
      });
      return data;

    case "step_stylist":
      return [
        {
          title: '',
          data: [{
            title: getMenuItemData(wizardStep)
          }]
        }
      ];

    case "step_calendar":
      return [
        {
          title: '',
          data: [{
            title: getMenuItemData(wizardStep)
          }]
        }
      ];

    default: return [];
  }

  return [];
}
provide('getSidebarData', getSidebarData)
</script>
