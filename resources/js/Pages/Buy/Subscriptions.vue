<script setup>
import {ref, computed, onBeforeMount, onMounted} from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import helpers from "../../helpers";
import { RadioGroup, RadioGroupDescription, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import { CheckCircleIcon } from '@heroicons/vue/solid';
import SiteLayout from "@/Layouts/SiteLayout.vue";


const swapSubscription = ref(false)
const loading = ref(false)
const props = defineProps({
  plans: Object,
});

const form = useForm({
  price_id: null
})

const newsletterForm = useForm({
  name: null,
  email: null,
  policy: false
});

const newsletterSuccess = ref(false);

onMounted(() => {
  helpers.lg(props.plans);
  swapSubscription.value = usePage().props.value.swap
})

const selectedPricingId = ref(null);

function buy() {
  if (swapSubscription.value) {
    changeSubscription()
  } else {
    Inertia.get(route("buy.subscription.checkout", selectedPricingId.value));
  }
}

function clickAndBuy(priceId)
{
  Inertia.get(route("buy.subscription.checkout", priceId));
}

function changeSubscription()
{
  loading.value = true;
  form.transform(data => ({
    ...data,
    price_id: selectedPricingId.value
  })).post(route('subscription.swap'), {
    onError: (res) => helpers.lg(res),
    onSuccess: (res) => helpers.lg(res),
    onFinish: (res) => loading.value = false
  });
}

function registerToNewsletter()
{
  newsletterForm.post(route('buy.newsletter'), {
    preserveScroll: true,
    onError: (res) => helpers.lg(res),
    onSuccess: (res) => newsletterSuccess.value = true,
  })
}

</script>

<template>
  <SiteLayout>
<!--    <div class="p-5">-->
<!--      <div v-for="plan in plans" :key="plan.id" class="my-5 bb-card bg-bb-gray-100 p-6">-->
<!--        <RadioGroup v-model="selectedPricingId">-->
<!--          <RadioGroupLabel class="text-xl font-extrabold text-gray-900">{{plan.name}}</RadioGroupLabel>-->
<!--          <RadioGroupDescription as="span" class="mt-1 flex items-center text-sm text-gray-500">{{plan.description}}</RadioGroupDescription>-->

<!--          <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-x-4">-->
<!--            <RadioGroupOption as="template" v-for="price in plan.pricings" :key="price.id" :value="price.id" v-slot="{ checked, active }">-->
<!--              <div :class="[checked ? 'border-transparent' : 'border-transparent', active ? 'border-bb-gray-800 ring-2 ring-bb-gray-800' : '', 'relative flex cursor-pointer rounded-lg border bg-[#A9D6DB] p-4 shadow-sm focus:outline-none']">-->
<!--          <span class="flex flex-1">-->
<!--            <span class="flex flex-col">-->
<!--              <RadioGroupLabel as="span" class="block text-xl font-bold text-bb-gray-800">{{ helpers.durationFormatted(price.duration) }}</RadioGroupLabel>-->

<!--              <RadioGroupDescription as="span" class="mt-8 text-lg text-bb-gray-800">{{ Math.round(price.price) }} €</RadioGroupDescription>-->
<!--            </span>-->
<!--          </span>-->
<!--                <CheckCircleIcon :class="[!checked ? 'invisible' : '', 'h-5 w-5 text-bb-gray-800']" aria-hidden="true" />-->
<!--                <span :class="[active ? 'border' : 'border-2', checked ? 'border-bb-gray-800' : 'border-transparent', 'pointer-events-none absolute -inset-px rounded-lg']" aria-hidden="true" />-->
<!--              </div>-->
<!--            </RadioGroupOption>-->
<!--          </div>-->
<!--        </RadioGroup>-->
<!--      </div>-->

<!--      <bb-button-->
<!--          primary-->
<!--          class="mx-auto my-10"-->
<!--          @click="buy"-->
<!--          :disabled="!selectedPricingId || loading"-->
<!--      >-->
<!--        {{ swapSubscription ? 'Cambia' : 'Acquista' }}-->
<!--      </bb-button>-->
<!--    </div>-->
<!--    <p class="text-5xl font-bold my-10 text-center">-->
<!--      <span class="line-through text-[#99a7da]">Spacchiamo</span>  Spieghiamo il capello in quattro (anzi, in tre).-->
<!--    </p>-->
<!--    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 items-stretch p-5 gap-10">-->
<!--      <div-->
<!--          class="bg-red-100 rounded-3xl overflow-hidden flex flex-col justify-start"-->
<!--          :class="{-->
<!--            'bg-[#c6e4e7]': [0,3,6,9].includes(idx),-->
<!--            'bg-[#f7bc74]': [1,4,7,10].includes(idx),-->
<!--            'bg-[#b8c0e0]': [2,5,8,11].includes(idx),-->
<!--          }"-->
<!--          v-for="(p, idx) in plans"-->
<!--          :key="p.id"-->
<!--      >-->
<!--&lt;!&ndash;        <img&ndash;&gt;-->
<!--&lt;!&ndash;            class="w-full h-64 object-cover"&ndash;&gt;-->
<!--&lt;!&ndash;            src="https://picsum.photos/800"&ndash;&gt;-->
<!--&lt;!&ndash;        />&ndash;&gt;-->

<!--        <div class="p-10 flex flex-col justify-between grow">-->
<!--          <p class="text-2xl font-extrabold mb-3">{{p.name}}</p>-->
<!--          <p class="grow">{{p.description}}</p>-->
<!--          <button-->
<!--              class="px-5 py-1.5 bg-[#99a7da] text-white rounded-full font-bold mt-6 w-fit self-center"-->
<!--              @click="clickAndBuy(p.pricings[0])"-->
<!--          >-->
<!--            Acquista-->
<!--          </button>-->
<!--        </div>-->
<!--      </div>-->
<!--    </div>-->

    <div class="py-16 bg-[#99A7DA] px-10 flex justify-center items-center">
      <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-x-10 gap-y-10 lg:gap-y-0 max-w-[1200px]">
        <div class="text-white self-end">
          <h1 class="text-5xl font-semibold">La rivoluzione delle <span class="text-[#FBD64A]">pieghe illimitate</span></h1>
          <p class="font-semibold my-5 text-lg">Organizza la tua agenda e cambia look tutte le volte che vuoi</p>
        </div>
        <div
            class="w-full h-[500px] rounded-3xl row-span-2"
            style="background: url('https://ac-landing-pages-user-uploads-production.s3.amazonaws.com/0000141845/9264eaa4-4b22-4ab5-b2ce-930ced5347a4.gif') center center / cover repeat; top: 0px; bottom: 0px; opacity: 1;"
        >
        </div>
        <div class="text-white self-start">
          <p class="text-lg">
            Call il martedì? Party il venerdì sera? Sentiti libera di giocare con il tuo styling ogni giorno, grazie al nostro abbonamento pieghe illimitate. Pronta a prendere parte alla rivoluzione della #piegazerosbatti?
          </p>
        </div>

      </div>

    </div>

    <div class="py-16 px-10 flex justify-center items-center">
      <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-10 max-w-[1200px] text-bb-primary">
        <div
            class="w-full h-[500px] rounded-3xl order-2 lg:order-1"
            style="background: url('https://ac-landing-pages-user-uploads-production.s3.amazonaws.com/0000141845/7955fc46-5f2e-427f-afd2-5bf3af5ceac3.jpg') center center / cover no-repeat; top: 0px; bottom: 0px; opacity: 1;"
        >
        </div>
        <div class="order-1 lg:order-2">
          <h1 class="text-5xl  font-semibold mb-7">
            La piega zero sbatti, anche in abbonamento
          </h1>
          <ul class="text-xl list-disc font-medium list-inside">
            <li class="my-2">Paghi una volta al mese, e non ci pensi più</li>
            <li class="my-2">Disdici quando vuoi (senza se e senza ma)</li>
            <li class="my-2">Fai tutto online, crediamo nell'arte di semplificarti la vita</li>
            <li class="my-2">Come andare in palestra, ma più rilassante</li>
            <li class="my-2">Come guardare Netflix, ma con "ta daan" al posto del "tu duuun"</li>
            <li class="my-2">Come prendere il treno ogni giorno, ma senza ritardi</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="py-16 px-10 lg:px-56 xl:px-64 flex flex-col justify-center items-center text-center bg-[#99A7DA] text-white">
      <div class="max-w-[680px]">
        <h1 class="text-5xl font-semibold">
          We Are Emma... e tu?
        </h1>
        <h2 class="text-3xl font-semibold my-6">
          Rimani aggiornata con la nostra newsletter!
        </h2>
        <p class="font-semibold mb-10">
          Non ti preoccupare: no spam, no sbatti, no cose strane. Solo i nostri migliori tips per capelli troppo belli per restare a casa + tanti sconti e promozioni su misura per te
        </p>
        <input
            class="bg-[#99A7DA] border border-white rounded-lg w-full placeholder-white focus:border-white focus:ring-white"
            type="text"
            placeholder="Nome*"
            v-model="newsletterForm.name"
            required
        >
        <bb-input-validation :form="newsletterForm" name="name"></bb-input-validation>
        <input
            class="bg-[#99A7DA] border border-white rounded-lg w-full placeholder-white focus:border-white focus:ring-white mt-3"
            type="email"
            placeholder="Email*"
            v-model="newsletterForm.email"
            required
        >
        <bb-input-validation :form="newsletterForm" name="email"></bb-input-validation>
        <div class="flex justify-start items-center gap-2 mt-3">
          <bb-checkbox v-model="newsletterForm.policy"></bb-checkbox>
          <bb-label class="text-sm text-white">Accetto <a target="_blank" href="https://weareemma.com/privacy-policy/">i termini della privacy policy*</a></bb-label>
        </div>
        <bb-input-validation :form="newsletterForm" name="policy"></bb-input-validation>
        <button type="button" class="bg-white text-[#99A7DA] py-2 px-4 rounded-lg w-full mt-3" @click="registerToNewsletter">
          Iscriviti
        </button>
        <p v-if="newsletterSuccess" class="mt-4 text-green-300">Sei iscritto alla newsletter</p>
      </div>
    </div>
  </SiteLayout>
</template>
