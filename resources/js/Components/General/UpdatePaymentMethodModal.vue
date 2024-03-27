<script setup>
import {ref, computed, onMounted, onUpdated, onUnmounted, watch} from 'vue';
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import { PencilIcon } from "@heroicons/vue/outline";

const props = defineProps({
  intent: Object,
  opened: Boolean
});

// Dialog
const dialog = ref(null);

// Stripe
const stripe = window.Stripe(Inertia.page.props.stripeKey);
const elements = stripe.elements();
let cardNumber = ref(null);
let cardExpiry = ref(null);
let cardCvc = ref(null);
const cardOwner = usePage().props.value.user.full_name;


function openModal()
{
  dialog.value.open();

  setTimeout(function () {
    helpers.lg('test');
    cardNumber = elements.create('cardNumber');
    cardExpiry = elements.create('cardExpiry');
    cardCvc = elements.create('cardCvc');
    cardNumber.mount('#stripe-card-number');
    cardExpiry.mount('#stripe-card-expiry');
    cardCvc.mount('#stripe-card-cvc');
  }, 200);
}

function resetStripe()
{
  cardNumber.destroy();
  cardExpiry.destroy();
  cardCvc.destroy();
}


// Buy
async function updateMethod()
{

  const { paymentMethod, error } = await stripe.createPaymentMethod(
      'card', cardNumber, {
        billing_details: { name: cardOwner.value }
      }
  );
  if (error) {
    helpers.flash({
      type: 'error',
      message : 'Completa correttamente i campi'
    })
  } else {
    Inertia.post(route('customer.updatePaymentMethod'),
    {
      pmethod: paymentMethod.id
    },
        {
          onSuccess: (page) => {
            helpers.flash({
              type: 'success',
              message : 'Metodo di pagamento aggiornato correttamente'
            })
            dialog.value.close();
            Inertia.visit(route('plan.detail'));
          }
        }
    )
    // Inertia.post(route('customer.updatePaymentMethod'), {
    //   pmethod: paymentMethod.id
    // });
  }
}
</script>

<template>
  <div>
    <button class="bb-button-rounded-primary-light p-2" @click="openModal">
      <PencilIcon class="w-5 h-5" />
    </button>
    <BbDialog ref="dialog" type="plain" size="lg" @close="resetStripe">
      <template #title>
        <p class="text-bb-blue-500">Modifica i dati della carta per il pagamento</p>
      </template>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <bb-label class="mb-1">Nome intestatario</bb-label>
          <bb-input v-model="cardOwner" type="text" placeholder="Mario Bianchi"></bb-input>
        </div>
        <div>
          <bb-label class="mb-1">Numero Carta</bb-label>
          <div id="stripe-card-number"></div>
        </div>
        <div>
          <bb-label class="mb-1">Data di scadenza</bb-label>
          <div id="stripe-card-expiry"></div>
        </div>
        <div>
          <bb-label class="mb-1">CVV</bb-label>
          <div id="stripe-card-cvc"></div>
        </div>
      </div>

      <template #buttons>
        <BbButton primary @click="updateMethod">
          Salva
        </BbButton>
      </template>
    </BbDialog>
  </div>
</template>
