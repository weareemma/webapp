<script setup>
import { ref, computed, onMounted, onUpdated, watch, nextTick } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import { ComboboxButton } from "@headlessui/vue";
import {useWizardStore} from "../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import LoadingOverlay from "@/Components/General/LoadingOverlay.vue";
import helpers from "@/helpers.js";

const props = defineProps({
  user: Object,
  type: { type: String, default: "charge" },
  products: Array,
  totalAmount: Number,
  stripeKey: String,
  intent: Object,
  additionalPayload: Object,
  disableEdit: { type: Boolean, default: false },
  enableLoadingOverlay: { type: Boolean, default: false}
});


const showFiscal = ref(false);
const fiscalForm = useForm({
  ...usePage().props.value.fiscal,
  surname: props.user.surname,
  username: props.user.name
});

const total = ref(props.totalAmount);
watch(
  () => props.totalAmount,
  () => (total.value = props.totalAmount)
);

const formVisible = computed(() => props.totalAmount > 0)

// Stripe
let style = {
  style: {
    base: {
      fontSize: '14px',
      backgroundColor: 'white',
      lineHeight: '24px',
      fontWeight: '400',
      '::placeholder': {
        color: '#A3A9C0',
      },
    },
    invalid: {
      iconColor: '#ef0e22',
      color: '#ef0e22',
    },
  }
};

const store = useWizardStore()
const { loading } = storeToRefs(store)

onMounted(() => {
  fiscalForm.email = props.user.email;
});

onUpdated( () => {

})

// Buy
function buy() {
  if(showFiscal.value)
  {
     fiscalForm.post(route('user.fiscalFile.update'), {
      preserveScroll: true,
      onSuccess: () => {
        checkout();
      }
    })
  }
  else
  {
    checkout();
  }
}

const checkout = () => {
  // store.loading = true;
  Inertia.post(route("buy"), {
    total: total.value,
    type: props.type,
    additional_payload: props.additionalPayload,
  }, {
    onFinish: () => {
      store.loading = false;
    }
  });
}

</script>

<template>
  <div class="bb-card md:px-6 py-5">
    <LoadingOverlay :isOpen="loading" />
    <div class="grid grid-cols-1 gap-y-10 gap-x-0 lg:gap-x-10">
      <div class="flex flex-col gap-y-6 mb-5 col-span-2 order-2">

        <div class="md:col-span-2 flex justify-start items-center">
          <bb-checkbox v-model="showFiscal"></bb-checkbox>
          <bb-label class="mx-3"
          >Mi serve la fattura</bb-label
          >
        </div>

        <div v-if="showFiscal" class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4 mb-5 col-span-2">
          <div>
            <bb-label class="mb-1">Nome</bb-label>
            <bb-input
                type="text"
                placeholder="Mario"
                v-model="fiscalForm.username"
                :disabled="disableEdit"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="username"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Cognome</bb-label>
            <bb-input
                type="text"
                placeholder="Bianchi"
                v-model="fiscalForm.surname"
                :disabled="disableEdit"
                required
            ></bb-input>
            <bb-input-validation
                :form="fiscalForm"
                name="surname"
            ></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Codice fiscale</bb-label>
            <bb-input
                type="text"
                placeholder="MROBNC27829290"
                v-model="fiscalForm.fiscal_code"
                :disabled="disableEdit"
                required
            ></bb-input>
            <bb-input-validation
                :form="fiscalForm"
                name="fiscal_code"
            ></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Data di nascita</bb-label>
            <bb-input type="date" :disabled="disableEdit" placeholder="12/03/83"></bb-input>
<!--            <bb-input-validation-->
<!--                :form="user"-->
<!--                name="birthday"-->
<!--            ></bb-input-validation>-->
          </div>
          <div>
            <bb-label class="mb-1">Telefono</bb-label>
            <bb-input type="text" v-model="fiscalForm.phone" :disabled="disableEdit" placeholder="+39..."></bb-input>
            <bb-input-validation :form="fiscalForm" name="phone"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Email</bb-label>
            <bb-input
                type="email"
                placeholder="Email"
                v-model="fiscalForm.email"
                :disabled="disableEdit"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="email"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Pec</bb-label>
            <bb-input
                type="email"
                placeholder="Pec"
                v-model="fiscalForm.pec"
                :disabled="disableEdit"
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="pec"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Via</bb-label>
            <bb-input
                v-model="fiscalForm.address"
                :disabled="disableEdit"
                type="text"
                placeholder="Via Empoli 14"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="address"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Città</bb-label>
            <bb-input
                v-model="fiscalForm.city"
                :disabled="disableEdit"
                type="text"
                placeholder="Milano"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="city"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Provincia</bb-label>
            <bb-input
                v-model="fiscalForm.province"
                :disabled="disableEdit"
                type="text"
                placeholder="MI"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="province"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">CAP</bb-label>
            <bb-input
                v-model="fiscalForm.postal_code"
                :disabled="disableEdit"
                type="text"
                placeholder="19203"
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="postal_code"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Tipologia di soggetto</bb-label>
            <bb-select
                v-model="fiscalForm.business_type"
                :disabled="disableEdit"
                mode="single"
                placeholder="Seleziona la tipologia"
                :close-on-select="true"
                :options="fiscalForm.available_business_types"
                
            ></bb-select>
            <bb-input-validation :form="fiscalForm" name="business_type"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Codice SDI</bb-label>
            <bb-input
                v-model="fiscalForm.invoice_code"
                :disabled="disableEdit"
                type="text"
                placeholder=""></bb-input>
            <bb-input-validation :form="fiscalForm" name="invoice_code"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">Ragione sociale</bb-label>
            <bb-input
                v-model="fiscalForm.name"
                :disabled="disableEdit"
                type="text"
                placeholder=""
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="name"></bb-input-validation>
          </div>
          <div>
            <bb-label class="mb-1">P. IVA</bb-label>
            <bb-input
                v-model="fiscalForm.vat_number"
                :disabled="disableEdit"
                type="text"
                placeholder=""
                required
            ></bb-input>
            <bb-input-validation :form="fiscalForm" name="vat_number"></bb-input-validation>
          </div>
        </div>


      </div>
      <div>
        <div class="bb-card py-10 px-10 bg-bb-primary relative order-1">
          <slot name="summary" :buy="buy" />
          <!-- <p class="font-bold text-white mb-3">Hai un codice sconto?</p>
          <div class="mb-3">
            <bb-input
              class="bg-bb-primary border-white text-white mb-2"
              type="text"
              placeholder="Inserisci il codice sconto"
            ></bb-input>
            <bb-input-validation :form="user" name="name"></bb-input-validation>
            <div class="flex justify-end">
              <bb-link class="text-white underline hover:text-bb-blue-100"
                >Applica sconto</bb-link
              >
            </div>
          </div>
          <div>
            <div
              v-for="(p, index) in products"
              :key="index"
              class="flex justify-between items-center my-1"
            >
              <p class="text-white text-sm">{{ p.name }}</p>
              <p class="text-white text-sm font-semibold">{{ p.price }} €</p>
            </div>
          </div>
          <div class="h-[1px] bg-white my-4"></div>
          <div class="flex justify-between items-center">
            <p class="text-white text-lg font-semibold">Totale</p>
            <p class="text-white text-xl font-bold">{{ total }} €</p>
          </div>
          <div class="flex justify-center mt-5">
            <bb-button primary light :dissabled="buyButtonDisabled" @click="buy"
              >Acquista</bb-button
            >
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>
