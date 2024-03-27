<template>
  <CustomerLayout title="Dettaglio abbonamento">
    <div>
      <!-- <p class="text-xl font-bold">Abbonamenti e pacchetti</p> -->
      <!-- <p class="text-sm mt-1 mb-6">Sed ut perspiciatis unde omnis iste natus error sit voluptatem </p> -->
    </div>
    <div>
      <p class="text-bb-blue-500 font-bold text-lg mb-5">Abbonamenti</p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
        <div v-for="plan in subscriptions" :key="plan.id" >
          <div v-if="plan.active || isCurrentSubscription(plan)" :class="[(isCurrentSubscription(plan)) ? 'bg-[#C6E4E7]' : 'bg-white' ,'bb-card p-6 hover:bg-[#C6E4E7] shadow-lg']">
            <p class="text-sm italic text-bb-lilla mb-1">Abbonamento</p>
            <p class="font-bold text-lg">{{ plan.name }}</p>
            <!--          <img class="rounded-2xl w-full h-24 object-center object-cover my-5" src="https://via.placeholder.com/150"/>-->
            <p class="text-sm my-3">{{plan.description}}</p>
            <div class="flex justify-between items-center">
              <template v-if="plan.name !== promoName">
                <component :is="(isCurrentSubscription(plan)) ? Link : 'div'" class="text-sm font-semibold flex items-center gap-2 cursor-pointer" :href="route('customer.plan.edit')" @click.prevent="(isCurrentSubscription(plan)) ? () => {} : swapConfirm(plan.id)">
                  <svg width="22" height="8" viewBox="0 0 22 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 3.5C0.723858 3.5 0.5 3.72386 0.5 4C0.5 4.27614 0.723858 4.5 1 4.5V3.5ZM21.3536 4.35355C21.5488 4.15829 21.5488 3.84171 21.3536 3.64645L18.1716 0.464466C17.9763 0.269204 17.6597 0.269204 17.4645 0.464466C17.2692 0.659728 17.2692 0.976311 17.4645 1.17157L20.2929 4L17.4645 6.82843C17.2692 7.02369 17.2692 7.34027 17.4645 7.53553C17.6597 7.7308 17.9763 7.7308 18.1716 7.53553L21.3536 4.35355ZM1 4.5H21V3.5H1V4.5Z" fill="black"/>
                  </svg>
                  {{(isCurrentSubscription(plan)) ? 'Dettagli' : ((subscription) ? 'Passa a questo' : 'Acquista')}}
                </component>
              </template>
              <template v-else>
                <template v-if=" isCurrentSubscription(plan) || ! promoAlready">
                  <Link class="text-sm font-semibold flex items-center gap-2" :href="(isCurrentSubscription(plan)) ? route('customer.plan.edit') : route('buy.subscription.checkout', plan.id)">
                    <svg width="22" height="8" viewBox="0 0 22 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 3.5C0.723858 3.5 0.5 3.72386 0.5 4C0.5 4.27614 0.723858 4.5 1 4.5V3.5ZM21.3536 4.35355C21.5488 4.15829 21.5488 3.84171 21.3536 3.64645L18.1716 0.464466C17.9763 0.269204 17.6597 0.269204 17.4645 0.464466C17.2692 0.659728 17.2692 0.976311 17.4645 1.17157L20.2929 4L17.4645 6.82843C17.2692 7.02369 17.2692 7.34027 17.4645 7.53553C17.6597 7.7308 17.9763 7.7308 18.1716 7.53553L21.3536 4.35355ZM1 4.5H21V3.5H1V4.5Z" fill="black"/>
                    </svg>
                    {{(isCurrentSubscription(plan)) ? 'Dettagli' : ((subscription) ? 'Passa a questo' : 'Acquista')}}
                  </Link>
                </template>
                <template v-else>
                  <p class="text-sm">Hai già usufruito di questo abbonamento</p>
                </template>
              </template>
              <span v-if="isCurrentSubscription(plan) && subEndsAt === null" class="inline-flex items-center rounded-full bg-[#C2EFB3] px-6 py-2.5 text-xs font-bold text-bb-blue-500">Attivo</span>
              <span v-else-if="isCurrentSubscription(plan) && subEndsAt" class="inline-flex items-center rounded-full bg-bb-danger px-6 py-2.5 text-xs font-bold text-white">Scade il {{ dayjs(subEndsAt).format("D/M/YYYY") }}</span>
              <p v-else class="text-bb-lilla font-semibold self-end">Da {{Math.round(plan.first_price)}} €</p>
            </div>
          </div>
        </div>
      </div>
      <div v-if="packs.length > 0">
        <p class="text-bb-blue-500 font-bold text-lg mb-5">Pacchetti</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
          <div v-for="pack in packs" :class="[(user_packs.includes(pack.id)) ? 'bg-[#C6E4E7]' : 'bg-white','bb-card p-6 hover:bg-[#C6E4E7] shadow-lg']">
            <div class="grid grid-cols-2 gap-x-5">
<!--              <img class="rounded-2xl w-full h-24 object-center object-cover col-span-1" src="https://via.placeholder.com/150"/>-->
              <div class="col-span-2">
                <p class="text-sm italic text-bb-lilla">Pacchetto</p>
                <p class="font-bold text-lg">{{ pack.name}}</p>
                <p class="text-md font-semibold text-bb-aqua-500 my-2">{{pack.expired_at}}</p>
                <p class="text-sm mb-4">{{pack.description}}</p>
                <div class="flex flex-row-reverse justify-between items-center content-end">
                  <CheckCircleIcon v-if="user_packs.includes(pack.id)" class="h-5 w-5 text-bb-aqua-500"/>
                  <p v-else class="text-bb-lilla font-semibold">{{Math.round(pack.price)}} €</p>
                  <a v-if=" ! user_packs.includes(pack.id)" class="text-sm font-semibold flex items-center gap-2" :href="route('buy.package.checkout', pack.id)">
                    <svg width="22" height="8" viewBox="0 0 22 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 3.5C0.723858 3.5 0.5 3.72386 0.5 4C0.5 4.27614 0.723858 4.5 1 4.5V3.5ZM21.3536 4.35355C21.5488 4.15829 21.5488 3.84171 21.3536 3.64645L18.1716 0.464466C17.9763 0.269204 17.6597 0.269204 17.4645 0.464466C17.2692 0.659728 17.2692 0.976311 17.4645 1.17157L20.2929 4L17.4645 6.82843C17.2692 7.02369 17.2692 7.34027 17.4645 7.53553C17.6597 7.7308 17.9763 7.7308 18.1716 7.53553L21.3536 4.35355ZM1 4.5H21V3.5H1V4.5Z" fill="black"/>
                    </svg>
                    Acquista
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BbDialog ref="confirmSwap" type="plain" size="sm">
      <template #title>
        {{(subscription) ? 'Cambio abbonamento' : 'Acquista abbonamento'}}
      </template>

      <span>
        {{(subscription) ? 'Sei sicuro di voler cambiare abbonamento?' : 'Sei sicuro di voler acquistare questo abbonamento?'}}
      </span>

      <template #buttons>
        <BbButton primary @click="gotoSwap">
          Conferma
        </BbButton>
      </template>
    </BbDialog>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import dayjs from "dayjs";
import CustomerLayout from "@/Layouts/CustomerLayout.vue";
import { PencilIcon } from "@heroicons/vue/outline";
import UpdateMethod from "@/Components/General/UpdatePaymentMethodModal.vue";
import { CheckCircleIcon } from '@heroicons/vue/solid';

const props = defineProps({
  plan: Object,
  packs: Object,
  user_packs: Object,
  price: Object,
  subscription: Object,
  subscriptions: Object,
});

onMounted(() => {})

const promoName = ref(usePage().props.value.promo_name);
const promoExpires = ref(usePage().props.value.promo_expires);
const promoAlready = ref(usePage().props.value.promo_already);

const confirmSwap = ref(null);

const selected = ref(null);

const deleteDialog = ref(null);

const subEndsAt = ref(usePage().props.value.customer.subscription_ends_at)
const defaultPaymentMethod = ref(usePage().props.value.customer.default_payment_method)

function cancelConfirm()
{
  deleteDialog.value.open();
}

const planSelected = ref(null);
function swapConfirm(planId)
{
  planSelected.value = planId;
  confirmSwap.value.open();
}

function gotoSwap()
{
  Inertia.visit(route('buy.subscription.checkout', planSelected.value));
}

function getPlanIntervalPrice() {
  return Math.round(props.price.price);
}

function getPlanIntervalDuration() {

  let duration = props.price.duration_qty + ':' + props.price.duration_type;
  switch (duration) {
    case "1:month":
      return "mese";
    case "3:month":
      return "3 mesi";
    case "6:month":
      return "6 mesi";
    case "1:year":
      return "anno";
    default:
      return '-';
  }
}

function isCurrentSubscription(plan)
{
  return (plan) ?  (plan.stripe_product_id === props.subscription?.name) : false;
}

</script>
