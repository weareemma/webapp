<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref, onMounted, onUpdated, reactive, computed, provide} from "vue";
import {Inertia} from "@inertiajs/inertia";
import { ChevronLeftIcon } from "@heroicons/vue/solid";
import { PencilIcon } from "@heroicons/vue/outline";
import helpers from "../../../helpers";
import dayjs from "dayjs";
import Wizard from "@/Components/General/Wizard.vue";
import StepCustomer from "@/Pages/Booking/Wizard/StepCustomer.vue";
import StepPeople from "@/Pages/Booking/Wizard/StepPeople.vue";
import StepPrimaryHairService from "@/Pages/Booking/Wizard/StepPrimaryHairService.vue";
import StepUpdo from "@/Pages/Booking/Wizard/StepUpdo.vue";
import StepAddons from "@/Pages/Booking/Wizard/StepAddons.vue";
import StepCalendar from "@/Pages/Booking/Wizard/StepCalendar.vue";
import StepCheckout from "@/Pages/Booking/Wizard/StepCheckout.vue";
import StepSuccess from "@/Pages/Booking/Wizard/StepSuccess.vue";
import AdminBookingSideBar from "@/Pages/Booking/Wizard/WizardComponents/AdminBookingSidebar.vue";
import { useWizardStore } from "@/DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import axios from "axios";

const props = defineProps({
  stores_list_wizard: Object,
  customers: Object,
  originalBooking: { type: Object, default: null },
  primaryHairServices: { type: Array, default: null },
  addonHairServices: { type: Object, default: null },
});

const store = useWizardStore();
const {
  activeStep,
  isStepCheckout,
  wizardSelection,
  wizardGeneral,
  people,
  wizardLoadBooking,
  ready,
  wizardFetchData
} = storeToRefs(store);

const wizard = ref(null);
const paymentLinkSent = ref(false);

onMounted(() => {
  helpers.lg(props);
  resetStore();

  // Init store
  wizardGeneral.value.stores = props.stores_list_wizard;

  if (props.originalBooking)
  {
    store.wizardLoadBooking(props.originalBooking);
  }
  else
  {
    // Init store id
    wizardSelection.value.store_id = props.store_id;
  }

  // Init customers list
  wizardGeneral.value.customers = props.customers;

  console.log(wizardSelection.value.store_id);
  console.log(wizardGeneral.value.stores);

  fetchPaymentInfos();
})

function resetStore() {
  store.$reset()
}

function linkSent() {
  paymentLinkSent.value = true
}

async function fetchPaymentInfos() {
  const response = await axios.get(route("booking.payment-infos"));
  if (response.data) {
    wizardGeneral.value.payment_infos = response.data;
  }
}

function getOriginalDiscount() {
  return props.originalBooking?.additional_data?.discount;
}

function fetchBookingData({ update }) {
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
            }
        );
      }

      update();
    },
  });
}

const wizardSteps = computed(() => {
  let steps = [
    {
      name: "step_customer",
      title: "Scegli il cliente",
    },
    {
      name: "step_people",
      title: "Per chi vuoi prenotare?",
    },
    {
      name: "step_primary_hair_service",
      title: "Quale servizio vuoi prenotare?",
    },
    {
      name: "step_updo",
      title: "Quali raccolti vuoi aggiungere?",
    },
    {
      name: "step_addons",
      title: "Quali add-on vuoi aggiungere?",
    }
  ];

  steps = steps.concat([
    // {
    //   name: "step_calendar",
    //   title: "Quando vuoi venire?",
    //   onBeforeShow: ({ wizardData }) => {
    //     return !!(wizardData.customer_id && wizardData.primary_hair_service_id);
    //   },
    //   onShow: ({ wizardData }) => {
    //     wizardData.available_days = null;
    //   },
    // },
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
    {
      name: "step_success",
      title: "Acquisto effettuato!",
    },
  ]);

  return steps;
});

const menuItems = computed(() => {
  let items = [
    { title: "Chi", wizardStep: "step_customer" },
    { title: "Numero persone", wizardStep: "step_people" },
    { title: "Servizi", wizardStep: "step_primary_hair_service" },
    { title: "Raccolti", wizardStep: "step_updo" },
    { title: "Add-on", wizardStep: "step_addons" }
  ];

  items = items.concat([
    { title: "Orario", wizardStep: "step_calendar" },
    { title: "Checkout", wizardStep: "step_checkout" },
  ]);

  return items;
});

function getMenuItemData(wizardStep) {
  switch (wizardStep) {
    case "step_customer":
      if (wizardGeneral.value.customer_id) {
        return props.customers[wizardGeneral.value.customer_id];
      }
      return "-";

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

function getSidebarData(wizardStep)
{
  let data = [];
  switch (wizardStep) {

    case "step_customer":
      return [
        {
          title: '',
          data: [{
            title: getMenuItemData(wizardStep)
          }]
        }
      ];

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
          }]
        })
      });
      return data;

    case "step_updo":
      _.forEach(people.value, (p) => {
        data.push({
          title: (p.name === 0) ? 'Tu' : ('Amica ' + p.name),
          data: [{
            title: (p.addons.updo.length > 0) ? (p.addons.updo[0].title + '(' + p.addons.updo[0].execution_time_fe + "')") : null,
            price: (p.addons.updo.length > 0) ? (p.addons.updo[0].net_price) : null,
          }]
        })
      });
      return data;

    case "step_addons":
      _.forEach(people.value, (p) => {
        let titles = [];
        let prices = [];
        if (p.addons.massage.length > 0)
        {
          titles.push(p.addons.massage[0].title + '(' + p.addons.massage[0].execution_time_fe + "')");
          prices.push(p.addons.massage[0].net_price);
        }
        if (p.addons.treatment.length > 0)
        {
          titles.push(p.addons.treatment[0].title + '(' + p.addons.treatment[0].execution_time_fe + "')");
          prices.push(p.addons.treatment[0].net_price);
        }
        data.push({
          title: (p.name === 0) ? 'Tu' : ('Amica ' + p.name),
          data: [{
            title: titles.join(', '),
            price: prices.join('€, '),
          }]
        })
      });
      return data;

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

function goToWithAnchor(e)
{
  wizard.value.goToStepByName(e);
  if (navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i) || navigator.userAgent.match(/Windows Phone/i)) {
    window.scrollTo({
      top: 100000,
      left: 0,
      behavior: 'smooth'
    });
  }
}
</script>

<template>
  <StylistLayout title="Modifica">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10">
      <div class="mx-auto">

        <bb-link :href="route('stylist.booking.details', props.originalBooking.id)">
          <ChevronLeftIcon class="h-4" />
          Torna indietro
        </bb-link>

        <div class="flex flex-wrap gap-6 mt-6">
          <AdminBookingSideBar
              @go-to="goToWithAnchor($event)"
              :menu-items="menuItems" />

          <!-- wizard -->
          <div class="flex-1" id="wizard_div">
            <Wizard
                ref="wizard"
                :steps="wizardSteps"
                :showCallback="fetchBookingData"
                @link-sent="linkSent"
                class="w-full px-6"
            >
              <!-- title -->
              <template #title="{ title }">
                <h2>{{ title }}</h2>
              </template>

              <!-- step 1 -->
              <template #step_customer>
                <StepCustomer />
              </template>

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

              <!-- step 5 -->
              <template #step_calendar>
                <StepCalendar />
              </template>

              <!-- step 6 -->
              <template #step_checkout>
                <StepCheckout :no-stripe="true" />
              </template>

              <!-- step 7 -->
              <template #step_success>
                <StepSuccess :payment-link-sent="paymentLinkSent"/>
              </template>
            </Wizard>
          </div>
        </div>

      </div>
    </div>
  </StylistLayout>
</template>