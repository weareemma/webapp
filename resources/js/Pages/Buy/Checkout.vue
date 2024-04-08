<script setup>
import CheckoutCharge from "@/Components/General/CheckoutCharge.vue";
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import {onMounted, ref, watch} from 'vue';
import helpers from "@/helpers.js";
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import {Inertia} from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import dayjs from "dayjs";

const props = defineProps({
  user: Object,
  intent: Object,
  stripeKey: String,
  checkoutType: String,
  totalAmount: Number,
  model: Object,
  prices: Object
});

const additionalData = ref(null);
const total = ref(null);
const price = ref(null);
const currentPrice = ref(usePage().props.value.customer.current_price);
const promoName = ref(usePage().props.value.promo_name);
const promoExpires = ref(usePage().props.value.promo_expires);
const form = useForm({
  price_id: null
})
const accept = ref(false);

onMounted(() => {

  helpers.lg(props.model);

  if (props.totalAmount)
  {
    total.value = props.totalAmount;
  }

  if (props.checkoutType === 'package')
  {
    additionalData.value = props.model;
  }

  if (props.checkoutType === 'subscription')
  {
    let pr = props.prices[0];
    total.value = Math.round(pr.price);
    additionalData.value = pr;
    price.value = pr;
  }
})

// watch(price, (n,o) => {
//   if (props.checkoutType === 'subscription')
//   {
//     total.value = Math.round(n.price);
//     additionalData.value = n;
//   }
// })

const showWarning = () => {
  helpers.flash({
    type:"error",
    message:"Devi accettare i termini e le condizioni"
  })
}

const disableBuyButton = ref(false);
function changeSubscription(callFunc)
{
  disableBuyButton.value = true;
  form.transform(data => ({
    ...data,
    price_id: price.value.id
  })).post(route('subscription.swap'), {
    onSuccess: () => callFunc(),
    onError: () => disableBuyButton.value = false,
    onFinish: (res) => {}
  });
}
</script>

<template>
  <CustomerLayout title="Checkout">
    <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5 text-center">Checkout</h3>
    <div class="p-0 w-full mx-auto">
      <checkout-charge
          :user="user"
          :intent="intent"
          :stripe-key="stripeKey"
          :type="checkoutType"
          :total-amount="total"
          :additional-payload="additionalData"
          :formVisible="true"
          :enable-loading-overlay="true"
      >
        <template #summary="{ buy }">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-y-10 gap-x-10">

            <div class="col-span-1 flex flex-col justify-between">
              <div v-if="checkoutType === 'package'" >
                <p class="text-white font-bold text-xl mb-1">Acquisto pacchetto</p>
                <p class="text-white mb-2">{{ model?.name }}</p>
                <div class="w-full h-[1px] bg-white my-5"></div>
                <div class="flex justify-between flex-wrap items-center gap-y-2 mb-5">
                  <p class="text-white font-bold">Totale</p>
                  <p class="text-white font-extrabold text-2xl">{{model?.price}} €</p>
                </div>
              </div>
              <div v-if="checkoutType === 'subscription'">
                <p class="text-white font-bold text-xl mb-1">Acquisto abbonamento</p>
                <p class="text-white mb-2">{{ model?.name }}</p>
                <p class="text-white mb-2">{{ model?.description }}</p>
                <div class="w-full h-[1px] bg-white my-5"></div>
                <div class="flex justify-between flex-wrap items-center gap-y-2 mb-5">
                  <p class="text-white font-extrabold text-2xl">{{(total) ? total + ' €' : '-'}}</p>
                  <div v-if="model?.name !== promoName">
                    <p class="text-white font-extrabold text-lg">{{helpers.durationFormatted(price?.duration)}}</p>
                  </div>
                  <div v-else>
                    <p class="text-white">
                      Scade il
                      <span class="text-white font-extrabold text-lg">{{dayjs(promoExpires).format('DD/MM/YYYY')}}</span>
                    </p>
                  </div>
                </div>
              </div>
              <bb-button :disabled="total === null || disableBuyButton" light @click="(accept) ? ((currentPrice && checkoutType === 'subscription') ? changeSubscription(buy) : buy()) : showWarning()">Acquista</bb-button>
              <div class="flex justify-start items-center mt-5">
                <bb-checkbox v-model="accept"></bb-checkbox>
                <bb-label class="mx-3 text-white">
                  <a target="_blank" href="https://weareemma.ac-page.com/terms-and-conditions">Accetta i termini e le condizioni</a>
                </bb-label>
              </div>
            </div>


            <div class="col-span-2 flex justify-end items-center">
              <img class="max-w-auto lg:max-w-[400px] rounded-lg" src="/img/wow-experience.jpg">
            </div>
          </div>
        </template>
      </checkout-charge>
    </div>
  </CustomerLayout>

</template>
