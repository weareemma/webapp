<template>
  <div>
    <LoadingOverlay :isOpen="loading" />
    <!-- checkout form -->
    <checkout-charge
      v-if="!noStripe && wizardGeneral.payment_infos"
      :disable-edit="wizardGeneral.disable_edit"
      :user="wizardGeneral.payment_infos.user"
      :additional-payload="{
        ...wizardGeneral,
        ...wizardSelection,
        people: people
      }"
      :intent="wizardGeneral.payment_infos.intent"
      :stripe-key="wizardGeneral.payment_infos.stripeKey"
      :total-amount="totalAmount"
    >
      <template #summary="{ buy }">
        <template v-if="totalAmount > 0 && wizardGeneral.booking_infos.actual_net_price > 0 && wizardGeneral.original_booking === null">
          <p class="font-bold text-white mb-3">Hai un codice sconto?</p>
          <div class="mb-3">
            <div v-if="!wizardGeneral.discount">
              <bb-input
                  v-model="wizardGeneral.discount_code"
                  class="w-full bg-bb-blue-500 text-white"
                  :class="{
                'border-bb-danger': wizardGeneral.discount_errors?.length,
              }"
                  placeholder="Inserisci il codice sconto"
              />

              <div
                  v-if="wizardGeneral.discount_errors?.length"
                  class="text-xs text-bb-danger-200 my-1"
              >
                Codice sconto non valido
              </div>

              <div class="flex justify-end">
                <bb-link
                    class="text-white underline hover:text-bb-blue-100"
                    @click="checkDiscount"
                >
                  Applica sconto
                </bb-link>
              </div>
            </div>

            <div v-else class="text-white flex items-center space-x-1">
              <div>
                Codice applicato:
                <strong>{{ wizardGeneral.discount.code }}</strong>
              </div>
              <button @click="removeDiscount">
                <XCircleIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </template>

        <div class="mt-2 mb-4">
          <p class="text-xs text-[#BADFF7]">Stai prenotando per il</p>
          <p class="text-lg font-bold text-white">{{dayjs(wizardSelection.selected_day.date).format('D MMM YYYY')}} ore {{ wizardSelection.selected_slot.time }}</p>
          <p class="text-bb-blue-100">{{storeName}} - {{storeAddress}}</p>
        </div>
        <div class="text-white space-y-2">
          <p class="text-xs text-[#BADFF7]">Servizi</p>
<!--           primary service -->
          <template v-for="p in people">
            <p class="text-sm m-0 text-[#BADFF7] my-0">{{ (p.name === 0) ? 'Tu' : ('Amica ' + p.name) }}</p>
            <div class="flex justify-between my-0">
              <div>{{ p.primary_service.title }}</div>
              <div class="font-bold" :class="((p.name === 0) && wizardGeneral.subscribed && ( ! primariesNotIncluded.includes(p.primary_service?.title))) ? 'line-through' : ''">
                {{ helpers.printPrice(p.primary_service.net_price) }}€
              </div>
            </div>
            <div class="flex justify-between my-0" v-for="m in p.addons.updo">
              <div>{{ m.title }}</div>
              <div class="flex justify-end gap-x-2">
                <div :class="['font-bold', ((p.name === 0) && wizardGeneral.subscribed) ? 'line-through' : '']">{{ helpers.printPrice(m.net_price) }}€</div>
                <div v-if="(p.name === 0) && wizardGeneral.subscribed" class="font-bold">{{ helpers.printPrice(m.net_price_discounted) }}€</div>
              </div>
            </div>
            <div class="flex justify-between my-0" v-for="m in p.addons.massage">
              <div>{{ m.title }}</div>
              <div class="flex justify-end gap-x-2">
                <div :class="['font-bold', ((p.name === 0) && wizardGeneral.subscribed) ? 'line-through' : '']">{{ helpers.printPrice(m.net_price) }}€</div>
                <div v-if="(p.name === 0) && wizardGeneral.subscribed" class="font-bold">{{ helpers.printPrice(m.net_price_discounted) }}€</div>
              </div>
            </div>
            <div class="flex justify-between my-0" v-for="m in p.addons.treatment">
              <div>{{ m.title }}</div>
              <div class="flex justify-end gap-x-2">
                <div :class="['font-bold', ((p.name === 0) && wizardGeneral.subscribed) ? 'line-through' : '']">{{ helpers.printPrice(m.net_price) }}€</div>
                <div v-if="(p.name === 0) && wizardGeneral.subscribed" class="font-bold">{{ helpers.printPrice(m.net_price_discounted) }}€</div>
              </div>
            </div>
          </template>

        </div>
        <div class="h-[1px] bg-white my-4"></div>
        <div>
          <div v-if="wizardGeneral.order && wizardGeneral.booking_infos.actual_net_price >= wizardGeneral.alreadyPaid" class="flex justify-between items-baseline">
            <p class="text-white text-md font-semibold">
              <span>Già pagato</span>
            </p>
            <div
                class="flex items-baseline space-x-4"
            >
              <div class="text-white text-md font-bold">
                {{ wizardGeneral.order.paid }}€
              </div>
            </div>
          </div>
          <div class="flex justify-between items-baseline">
            <p class="text-white text-lg font-semibold">
              <span>Totale</span>
            </p>
            <div
                class="flex items-baseline space-x-4"
            >
              <div class="text-white text-xl font-bold">
                {{ helpers.printPrice(totalAmount) }}€
              </div>
            </div>
          </div>
          <div v-if="wizardGeneral.original_booking && wizardGeneral.booking_infos.actual_net_price < wizardGeneral.alreadyPaid" class="flex justify-between items-baseline">
            <p class="text-white text-md font-semibold">
              <span>Da rimborsare</span>
            </p>
            <div
                class="flex items-baseline space-x-4"
            >
              <div class="text-white text-md font-bold">
                {{ wizardGeneral.alreadyPaid }}€
              </div>
            </div>
          </div>
        </div>
        <div class="md:col-span-2 flex justify-start items-start my-4">
          <bb-checkbox v-model="privacyChecked" class="bg-bb-blue-500 checked:border-white"></bb-checkbox>
          <bb-label class="mx-3 text-sm text-white"
          >Ho letto e accetto i <a target="_blank" href="https://weareemma.ac-page.com/terms-and-conditions">termini e le condizioni del servizio</a></bb-label
          >
        </div>
        <div class="flex justify-center mt-5">
          <!-- new booking -->
          <bb-button :disabled="confirmDisabled" v-if="!wizardGeneral.original_booking && totalAmount === 0" light @click="() => {
            if (checkPrivacy()) lastCheck( () => {checkoutNoStripe()});
          }">
            Conferma
          </bb-button>
          <bb-button :disabled="confirmDisabled" v-if="!wizardGeneral.original_booking && totalAmount > 0" light @click="() => {
            if (checkPrivacy()) lastCheck(() => {buy()})
          }">
            Acquista
          </bb-button>
          <template v-else-if="wizardGeneral.original_booking">
            <bb-button :disabled="confirmDisabled" v-if="totalAmount === wizardGeneral.alreadyPaid" light @click="() => {
              if (checkPrivacy()) lastCheck(() => {editBooking()})
            }">
              Salva modifiche
            </bb-button>
            <bb-button v-else :disabled="confirmDisabled" light @click="() => {
              if (checkPrivacy()) lastCheck(() => {buy()})
          }">
              Acquista modifiche
            </bb-button>
          </template>
        </div>
      </template>
    </checkout-charge>

    <!-- loader -->
    <div
      v-if="!noStripe && !wizardGeneral.payment_infos"
      class="p-8 grid place-items-center"
    >
      <LoaderIcon class="w-12 h-12 animate-spin text-bb-primary" />
    </div>

    <!-- checkout without stripe -->
    <div v-if="noStripe" class="flex flex-col items-center">
      <div class="max-w-xl mx-auto">
        <template v-if="(totalAmount > 0 || wizardGeneral.discount) && wizardGeneral.original_booking === null">
          <p class="font-bold mb-3">Hai un codice sconto?</p>
          <!-- discount -->
          <div class="mb-6">
            <div v-if="!wizardGeneral.discount">
              <div class="flex space-x-4">
                <bb-input
                    v-model="wizardGeneral.discount_code"
                    class="w-auto"
                    placeholder="Inserisci codice sconto"
                />
                <bb-button success @click="checkDiscount"> Verifica </bb-button>
              </div>

              <div
                  v-if="wizardGeneral.discount_errors?.length"
                  class="text-xs text-bb-danger mt-2"
              >
                Codice sconto non valido
              </div>
            </div>

            <div v-else>
              <div class="mb-2">
                Codice sconto applicato:
                <strong>{{ wizardGeneral.discount.code }}</strong>
              </div>
              <div
                  class="text-center text-bb-danger cursor-pointer"
                  @click="removeDiscount"
              >
                rimuovi codice sconto
              </div>
            </div>
          </div>
        </template>
        
        <!-- checkout -->
        <bb-button
          v-if="!wizardGeneral.original_booking"
          class="w-full"
          outline
          @click="checkoutNoStripe()"
          :disabled="confirmDisabled"
        >
          Effettua prenotazione
        </bb-button>

        <!-- edit -->
        <div v-else>
          <bb-button :disabled="confirmDisabled" class="w-full" outline @click="editBooking">
            Modifica prenotazione
          </bb-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import CheckoutCharge from "@/Components/General/CheckoutCharge.vue";
import { LoaderIcon } from "@/Components/Icons";
import { XCircleIcon } from "@heroicons/vue/solid";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import dayjs from "dayjs";
import LoadingOverlay from "@/Components/General/LoadingOverlay.vue";
import helpers from '@/helpers.js';

const store = useWizardStore()
const {
  isStylist,
  loading,
  wizardSelection,
  wizardGeneral,
  people,
  ready,
  totalReady
} = storeToRefs(store)
const props = defineProps({
  noStripe: { type: Boolean, default: false },
});

const primariesNotIncluded = usePage().props.value.primaries_not_included;

const updateData = inject("updateData");
const reload = inject("reload");
const prev = inject("prev");
const next = inject("next");

const lastChecking = ref(false);
const confirmDisabled = computed(() => {
  return !totalReady.value || lastChecking.value;
})

const privacyChecked = ref(false);
function checkPrivacy()
{
  if (!privacyChecked.value)
  {
    helpers.flash({
      type: "error",
      message: 'Devi accettare i termini e le condizioni del servizio',
    });
    return false;
  }
  return true;
}

const storeName = ref(wizardGeneral.value.stores.find((s) => s.id === wizardSelection.value.store_id).name);
const storeAddress = ref(wizardGeneral.value.stores.find((s) => s.id === wizardSelection.value.store_id).address);

// total amount
const totalAmount = computed(() => {

  let t = wizardGeneral.value.booking_infos.actual_net_price;

  if (wizardGeneral.value.original_booking && wizardGeneral.value.order && t > wizardGeneral.value.order.paid)
  {
    t = t - wizardGeneral.value.order.paid;
  }

  return t;
});

// check discount
function checkDiscount() {
  reload();
}

function resetStore() {
  store.$reset()
}

// remove discount code
function removeDiscount() {
  const discount = wizardGeneral.value.discount;
  wizardGeneral.value.discount = null;
  wizardGeneral.value.discount_errors = null;
  wizardGeneral.value.booking_infos.discount = null;
  wizardGeneral.value.booking_infos.actual_net_price += discount.discount_amount;
  updateData();
}

// checkout whitout stripe (admin)
function checkoutNoStripe(alertUser = false) {
  console.log('CHECKOUT NOSTRIPE ADMIN ' + totalAmount.value);
  store.loading = true;
  const form = useForm({ ...wizardGeneral.value, ...wizardSelection.value, people: people.value });
  form.transform(data => ({
    ...data,
    notify: alertUser,
  })).post(route("booking.store"), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (res) => {
      let booking = res.props?.flash?.data?.booking
      const bookingInfo = useForm({ ...booking })
      bookingInfo.post(route('booking.success.nostripe'))
    },
    onError: () => {
      store.loading = false;
    }
  });
}

// edit bokking (admin)
function editBooking(withRefund = false, sendLink = false) {
  console.log('EDIT BOOKING ADMIN' + totalAmount.value);
  store.loading = true;
  wizardGeneral.value.with_refund = withRefund;
  const form = useForm({ ...wizardGeneral.value, ...wizardSelection.value, people: people.value });
  form.transform(data => ({
    ...data,
    notify: sendLink
  })).put(route("booking.update", wizardGeneral.value.original_booking.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      resetStore()
      helpers.flash({
        type: 'success',
        message: 'Appuntamento modificato'
      })
      Inertia.visit(route('home'));
    },
    onError: () => {
      store.loading = false;
    }
  });
}


function lastCheck(call)
{
  if (wizardSelection.value.selected_day && wizardSelection.value.selected_slot)
  {
    lastChecking.value = true;
    axios.post(route("booking.hair-services.check-availability"), {
      ...wizardGeneral.value,
      ...wizardSelection.value,
      people: people.value,
      axios: true
    }).then((response) => {
      helpers.lg(response);
      const { days } = response.data;

      // Check day selected availability
      const day = days.find(
          (d) => d.date === wizardSelection.value.selected_day.date && d.available
      );
      if (day)
      {
        // Check slot selected availability
        const slot = day.slots.find(
            (s) => s.time === wizardSelection.value.selected_slot.time && s.available
        );
        if (slot)
        {
          call();
        }
        else
        {
          returnToCalendar();
        }
      }
      else
      {
        returnToCalendar();
      }
    });
  }
}

function returnToCalendar()
{
  helpers.flash({
    type: "error",
    message: 'Lo slot selezionato non è più disponibile!',
  });

  prev();
}
</script>
