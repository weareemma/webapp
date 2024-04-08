<script setup>
import { ref, computed, onMounted, onUpdated } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import helpers from "../../helpers";
import { TrashIcon } from "@heroicons/vue/solid";

const props = defineProps({
  model: Object,
  stores: Object,
  services: Object,
});

const form = useForm(props.model);

function storeModel() {
  if (form.id) {
    form.put(route("package.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.helpers.flash(res.props.flash);
      },
    });
  } else {
    form.post(route("package.store"), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.helpers.flash(res.props.flash);
      },
    });
  }
}

function addInterval(e) {
  let empty = {
    ids: null,
    units: null,
  };

  form.services ? form.services.push(empty) : (form.services = [empty]);
}

function removeInterval(idx, service) {
  form.services.splice(idx, 1);
}
</script>

<template>
  <AppLayout title="Pacchetto" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica pacchetto</span>
        <span v-else>Aggiungi nuovo pacchetto</span>
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
        <div class="col-span-2">
          <bb-label class="mb-1 text-bb-gray-700">SERVIZI</bb-label>
          <div
            class="my-5 grid grid-cols-1 sm:grid-cols-2 gap-x-5"
            v-for="(service, index) in form.services"
            :key="index"
          >
            <div>
              <div class="flex justify-start items-baseline">
                <bb-label class="mb-1">Servizio {{ index + 1 }}</bb-label>
                <Bblink
                  link
                  @click="removeInterval(index, service)"
                  class="cursor-pointer text-bb-danger text-sm mb-1 ml-2"
                >
                  Cancella
                </Bblink>
              </div>
              <bb-select
                mode="tags"
                placeholder="Seleziona i servizi"
                :close-on-select="true"
                :options="services"
                v-model="service.ids"
              ></bb-select>
              <bb-input-validation
                :form="form"
                :name="'services.' + index + '.ids'"
              ></bb-input-validation>
            </div>
            <div>
              <bb-label class="mb-1"
                >Numero servizi compresi {{ index + 1 }}</bb-label
              >
              <bb-input
                type="number"
                placeholder="Inserisci il numero di servizi"
                v-model="service.units"
              ></bb-input>
              <bb-input-validation
                :form="form"
                :name="'services.' + index + '.units'"
              ></bb-input-validation>
            </div>
          </div>
          <Bblink
            link
            @click="addInterval"
            class="cursor-pointer text-bb-blue-500"
            >+ Aggiungi servizio</Bblink
          >
          <bb-input-validation
            :form="form"
            name="services"
          ></bb-input-validation>
        </div>

        <div>
          <bb-label class="mb-1">Scadenza</bb-label>
          <datepicker
            v-model="form.expired_at"
            format="dd/MM/yyyy"
            previewFormat="dd/MM/yyyy"
            locale="it-IT"
            modelType="dd/MM/yyyy"
            :enableTimePicker="false"
            monthNameFormat="long"
            autoApply
          />
          <bb-input-validation
            :form="form"
            name="expired_at"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Prezzo IVA inclusa</bb-label>
          <bb-input
            type="number"
            placeholder="Prezzo IVA inclusa"
            v-model="form.price"
          ></bb-input>
          <bb-input-validation :form="form" name="price"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1"
            >Durata di validità dalla data di acquisto (giorni)*</bb-label
          >
          <bb-input
            type="number"
            placeholder="Inserisci il numero di giorni"
            v-model="form.valid_from"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="valid_from"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Store</bb-label>
          <bb-select
            mode="tags"
            placeholder="Seleziona gli store"
            :close-on-select="true"
            :options="stores"
            v-model="form.stores"
          ></bb-select>
          <bb-input-validation :form="form" name="stores"></bb-input-validation>
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
          <bb-label class="mb-0 ml-2">Pacchetto attivo</bb-label>
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
