<script setup>
import { Inertia } from "@inertiajs/inertia";
import Logo from "@/Components/General/Logo.vue";
import dayjs from "dayjs";
import {computed, onMounted, ref} from "vue";
import helpers from "../../helpers";
import {usePage} from "@inertiajs/inertia-vue3";
import _, {isEmpty} from "lodash";
import {useWizardStore} from "../../DataStore/Wizard/wizardStore";

const promoName = ref(usePage().props.value.promo_name);
const promoExpires = ref(usePage().props.value.promo_expires);

const wizardStore = useWizardStore()
function backHome()
{
  wizardStore.$reset()
  Inertia.visit(route('home'));
}

function formatDate(date) {
  return !isEmpty(date) ? dayjs(date).format("D MMM YYYY") : ''
}

function getServices(slots) {
  let list = []
  if (!isEmpty(slots) && slots.length > 0) {
    slots.forEach(s => {
      list.push(s.service?.title)
    })
  }
  return list
}

const date = ref('')
const hour = ref('')
const store = ref('')
const storeAddress = ref('')
const services = ref([])
const servicesList = computed(() => {
  let list = ''
  services.value.forEach((service, index) => {
    list += service
    if (index !== (services.value.length - 1)) list += ' + '
  })
  return list
})

const people = ref([]);
const peopleServices = (slots) => {
  let list = '';
  slots.forEach((service, index) => {
    console.log(service, index);
    list += service.service.title;
    if (index !== (slots.length - 1)) list += ' + '
  })
  return list
}

const subscription = ref(null);
const pack = ref(null);
const user = computed(() => usePage().props.value.user);

onMounted(() => {
  date.value = formatDate(usePage().props.value.flash?.data?.booking?.start_date)
  hour.value = usePage().props.value.flash?.data?.booking?.start
  store.value = usePage().props.value.flash?.data?.booking?.store?.name
  storeAddress.value = usePage().props.value.flash?.data?.booking?.store?.address
  services.value = getServices(usePage().props.value.flash?.data?.booking?.slots)
  people.value = usePage().props.value.flash?.data?.booking?.get_children

  subscription.value = usePage().props.value.flash?.data?.subscription
  pack.value = usePage().props.value.flash?.data?.pack

  helpers.lg(usePage().props.value.flash?.data?.booking)
})

const goToProfileEdit = () => {
  Inertia.visit(route('customer.profile'));
}
</script>

<template>
  <div class="min-h-screen bg-white flex justify-center items-center relative">
    <div class="w-full h-24 flex flex-row justify-center md:justify-start items-center absolute top-0 left-0">
      <div class="md:mx-16 px-4 sm:px-6 lg:px-8 py-2">
        <Logo :light="false"></Logo>
      </div>
    </div>
    <div class="w-full pt-24 pb-6 md:py-0">
      <h1 class="title-xl capitalize text-center text-bb-primary-500">Grazie!</h1>
      <div class="w-full flex justify-center mx-auto md:max-w-2xl">
        <p class="text-center p-6" v-if="date">
          Il pagamento è andato a buon fine. Ricordati che puoi modificare o disdire il tuo appuntamento fino a 24h prima.
        </p>
      </div>
      <div class="w-full flex flex-col md:flex-row justify-center">
        <div class="bg-[#F7BC74] rounded-2xl flex justify-between mx-6 mt-6 md:max-w-3xl">
          <div class="p-8 max-w-md">
            <div v-if="date">
              <p class="font-bold text-white text-lg mb-6">
                Il tuo appuntamento
              </p>
              <p class="text-bb-primary my-1 font-semibold">
                {{ date }} ore {{ hour }}
              </p>
              <p class="text-bb-primary my-1">
                {{ store }} - {{ storeAddress }}
              </p>
              <p class="text-white my-1 text-sm">Tu</p>
              <p class="text-bb-primary my-1">
                {{ servicesList }}
              </p>
              <div v-for="p in people">
                <p class="text-white my-1 text-sm">Amica {{p.guest}}</p>
                <p class="text-bb-primary my-1">
                  {{ peopleServices(p.slots) }}
                </p>
              </div>
            </div>
            <div v-else-if="subscription">
              <p class="font-bold text-white text-lg mb-6">
                Il tuo abbonamento
              </p>
              <p class="text-bb-primary my-1 font-semibold">
                {{ subscription.plan.name}}
              </p>
              <p v-if="subscription.plan.name !== promoName" class="text-bb-primary my-1">
                {{ Math.round(subscription.price) }}€  {{ helpers.durationFormatted(subscription.duration) }}
              </p>
              <p v-else class="text-bb-primary my-1">
                Scade il
                <span class="font-bold"> {{dayjs(promoExpires).format('DD/MM/YYYY')}}</span>
              </p>
            </div>
            <div v-else-if="pack">
              <p class="font-bold text-white text-lg mb-6">
                Il tuo pacchetto
              </p>
              <p class="text-bb-primary my-1 font-semibold">
                {{ pack.name }}
              </p>
              <p class="text-bb-primary my-1">
                Scade il {{ pack.expired_at }}
              </p>
              <p class="text-bb-primary my-1">
                {{ pack.price }} €
              </p>
            </div>
            <bb-button @click="backHome()" class="mt-6" light>Vai alla mia dashboard</bb-button>
          </div>
          <div class="hidden lg:flex lg:flex-col justify-end items-baseline pr-8 pt-8">
            <img
                src="/img/girl.png"
            />
          </div>
        </div>
        <div v-if="user.profile_photo_path === null" class="bg-white rounded-2xl md:max-w-md shadow-lg p-8 mx-6 mt-6">
          <p class="font-bold text-bb-primary text-lg mb-6">
            Carica la tua foto profilo!
          </p>
          <p class="text-bb-black my-1">
            In questo modo ci aiuterai a riconoscerti quando ti presenterai nello store!
          </p>
          <bb-button @click="goToProfileEdit" class="mt-6">Carica foto</bb-button>
        </div>
      </div>
    </div>
  </div>
</template>
