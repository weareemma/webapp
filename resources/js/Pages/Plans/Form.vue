<template>
  <AppLayout title="Abbonamento" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica abbonamento</span>
        <span v-else>Aggiungi nuovo abbonamento</span>
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Nome</bb-label>
          <bb-input
            type="text"
            placeholder="Nome"
            v-model="form.name"
          ></bb-input>
          <bb-input-validation :form="form" name="name"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Servizio principale</bb-label>
          <bb-select
              mode="single"
              placeholder="Seleziona il servizio principale"
              :close-on-select="true"
              :options="hairServices"
              v-model="form.hair_service_id"
          ></bb-select>
          <bb-input-validation :form="form" name="hair_service_id"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Numero servizi compresi</bb-label>
          <bb-input
              type="number"
              placeholder="Numero servizi compresi"
              v-model="form.hair_service_count"
          ></bb-input>
          <bb-input-validation :form="form" name="hair_service_count"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Percentuale di sconto</bb-label>
          <bb-input
              type="number"
              placeholder="Percentuale di sconto"
              v-model="form.discount_percentage"
          ></bb-input>
          <bb-input-validation :form="form" name="discount_percentage"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Numero Drink&Style compresi</bb-label>
          <bb-input
              type="number"
              placeholder="Numero Drink&Style compresi"
              v-model="form.ds_count"
          ></bb-input>
          <bb-input-validation :form="form" name="ds_count"></bb-input-validation>
        </div>
        <div class="col-span-2">
          <bb-label class="mb-1 text-bb-gray-700">DURATE</bb-label>
          <div
            class="my-5 grid grid-cols-1 sm:grid-cols-2 gap-x-5"
            v-for="(pricing, index) in listedPricings"
            :key="pricing.id"
          >
            <div>
              <div class="flex justify-start items-baseline">
                <bb-label class="mb-1">Durata {{ index + 1 }}</bb-label>
                <button
                  v-if="pricing.stripe_price_id === undefined"
                  link
                  @click="removePricing(pricing.id)"
                  class="bb-link-primary cursor-pointer text-bb-danger text-sm mb-1 ml-2"
                >
                  Cancella
                </button>
              </div>
              <bb-select
                mode="single"
                placeholder="Seleziona la durata"
                :close-on-select="true"
                :options="durationOptions"
                v-model="pricing.duration"
                :disabled="pricing.stripe_price_id !== undefined"
              ></bb-select>
              <bb-input-validation
                :form="form"
                :name="'pricings.' + index + '.duration'"
              ></bb-input-validation>
            </div>
            <div>
              <bb-label class="mb-1">Prezzo {{ index + 1 }} (IVA inclusa)</bb-label>
              <bb-input
                type="number"
                placeholder="Prezzo IVA inclusa"
                v-model="pricing.price"
                :disabled="pricing.stripe_price_id !== undefined"
              ></bb-input>
              <bb-input-validation
                :form="form"
                :name="'pricings.' + index + '.price'"
              ></bb-input-validation>
            </div>
            <div class="sm:col-span-2 flex justify-start items-center mt-2">
              <bb-switch v-model="pricing.active"></bb-switch>
              <bb-label class="mb-0 ml-2">Durata attiva</bb-label>
            </div>
          </div>
          <button
            v-if="pricings?.length < 4"
            link
            @click="addPricing"
            class="bb-link-primary cursor-pointer text-bb-blue-500"
          >
            + Aggiungi durata
          </button>
          <bb-input-validation
            :form="form"
            name="pricings"
          ></bb-input-validation>
        </div>
        <div class="col-span-2">
          <bb-label class="mb-1">Descrizione</bb-label>
          <bb-textarea
            class="min-h-[180px]"
            type="text"
            v-model="form.description"
          ></bb-textarea>
          <bb-input-validation
            :form="form"
            name="description"
          ></bb-input-validation>
        </div>
        <div class="sm:col-span-2 flex justify-start items-center">
          <bb-switch v-model="form.active"></bb-switch>
          <bb-label class="mb-0 ml-2">Abbonamento attivo</bb-label>
        </div>
        <bb-input-validation :form="form" name="active"></bb-input-validation>
      </div>

      <div class="flex justify-end items-center mt-4">
        <bb-button
          type="button"
          @click="storeModel"
          :disabled="form.processing"
        >
          <span v-if="form.id">Salva</span>
          <span v-else>Aggiungi</span>
        </bb-button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUpdated } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import helpers from "../../helpers";
import { TrashIcon } from "@heroicons/vue/solid";
import uniqid from "uniqid";

const props = defineProps({
  model: Object,
  availableDurations: Array,
  hairServices: Object,
});

console.log(props.hairServices)

const form = useForm({...props.model, hair_services: props.hairServices});

function storeModel() {
  const tForm = form.transform((f) => ({
    ...f,
    pricings: pricings.value.filter((p) => !p.deleted || !p.new),
  }));
  if (form.id) {
    tForm.put(route("plan.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
      },
    });
  } else {
    tForm.post(route("plan.store"), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
      },
    });
  }
}

// manage pricings
const pricings = ref(form.pricings ?? []);
const listedPricings = computed(() => pricings.value.filter((p) => !p.deleted));
function addPricing() {
  let empty = {
    new: true,
    id: uniqid(),
    duration: "",
    price: null,
    active: true,
  };

  if (pricings.value?.length >= 4) return;
  pricings.value.push(empty);
}

function removePricing(pricingId) {
  const pricingIndex = pricings.value.findIndex((p) => p.id == pricingId);
  if (pricingIndex > -1) pricings.value[pricingIndex].deleted = true;
}

// duration options
const durationOptions = computed(() => {
  const opts = {};

  props.availableDurations.forEach((ad) => {
    switch (ad) {
      case "1:month":
        opts[ad] = "1 mese";
        break;
      case "3:month":
        opts[ad] = "3 mesi";
        break;
      case "6:month":
        opts[ad] = "6 mesi";
        break;
      case "1:year":
        opts[ad] = "1 anno";
        break;
    }
  });

  return opts;
});


// hairServicesOptions options
const hairServicesOptions = computed(() => {
  const opts = {};

  props.hairServices.forEach((hs) => {
    opts[hs.value] = hs.label
  });

  return opts;
});
</script>
