<script setup>
import { ref, computed, onMounted, onUpdated, watch } from 'vue';
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from '@/Layouts/AppLayout.vue';
import helpers from "@/helpers";
import { TrashIcon } from "@heroicons/vue/solid";
import _ from "lodash";

const props = defineProps({
  model: Object,
  stores: Object,
  users: Object,
  service_typologies: Object,
  services: Object,
  typologies: Object,
  valid_for: Object,
  service_levels: Object,
  service_types: Object,
  types: Object
});

const form = useForm(props.model);

watch(() => form.type, (n,o) => {
  if (n === 'general' && form.typology === 'free') form.typology = 'percentage';
});

const usersSorted = computed(() => {
  return _.orderBy(props.users, ['label']);
});

onMounted(() => {
  
}) 

function storeModel()
{
  if (form.id)
  {
    form.put(route("discount.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash({
          type: 'success',
          message: "Sconto modificato"
        })
      }
    });
  }
  else
  {
    form.post(route("discount.store"), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash({
          type: 'success',
          message: "Sconto creato"
        })
      }
    });
  }
}

</script>

<template>
  <AppLayout title="Sconto" :show-title="false">

    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica sconto</span>
        <span v-else>Aggiungi nuovo sconto</span>
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Codice</bb-label>
          <bb-input type="text" placeholder="Codice" v-model="form.code"></bb-input>
          <bb-input-validation :form="form" name="code"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Tipologia checkout</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.checkout_type"
              :options="[
                  {value: 'appointment', label: 'Appuntamento'},
              ]"
          ></bb-radio-group>
          <bb-input-validation :form="form" name="checkout_type"></bb-input-validation>
        </div>
        <div class="col-span-2">
          <bb-label class="mb-1">Tipo</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.type"
              :options="types"
          ></bb-radio-group>
          <bb-input-validation :form="form" name="type"></bb-input-validation>
        </div>
        <div class="col-span-2">
          <bb-label class="mb-1">Tipologia offerta</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.typology"
              :options="typologies.filter((t) => (form.type === 'service' || (t.value !== 'free')))"
          ></bb-radio-group>
          <bb-input-validation :form="form" name="typology"></bb-input-validation>
        </div>

        <div v-if="form.type === 'service'">
          <bb-label class="mb-1">Cosa</bb-label>
          <bb-select
              mode="single"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="service_typologies"
              v-model="form.service_typology"
          ></bb-select>
          <bb-input-validation :form="form" name="service_typology"></bb-input-validation>
        </div>

        <div v-if="form.type === 'service'">
          <div v-if="form.service_typology === 'service'">
            <bb-label class="mb-1">Servizi</bb-label>
            <bb-select
                mode="tags"
                placeholder="Seleziona uno o più servizi"
                :close-on-select="true"
                :options="services"
                v-model="form.services"
            ></bb-select>
            <bb-input-validation :form="form" name="services"></bb-input-validation>
          </div>
          <div v-if="form.service_typology === 'service_level'">
            <bb-label class="mb-1">Livello servizi</bb-label>
            <bb-select
                mode="single"
                placeholder="Seleziona un livello"
                :close-on-select="true"
                :options="service_levels"
                v-model="form.service_level"
            ></bb-select>
            <bb-input-validation :form="form" name="service_level"></bb-input-validation>
          </div>
          <div v-if="form.service_typology === 'add_on'">
            <bb-label class="mb-1">Tipologia di add-on</bb-label>
            <bb-select
                mode="single"
                placeholder="Seleziona un add-on"
                :close-on-select="true"
                :options="service_types"
                v-model="form.addon_typology"
            ></bb-select>
            <bb-input-validation :form="form" name="addon_typology"></bb-input-validation>
          </div>
        </div>

        <div v-if="form.typology === 'percentage'">
          <bb-label class="mb-1">Percentuale</bb-label>
          <bb-input type="number" placeholder="%" v-model="form.percentage"></bb-input>
          <bb-input-validation :form="form" name="percentage"></bb-input-validation>
        </div>

        <div v-if="form.typology === 'fixed'">
          <bb-label class="mb-1">Importo</bb-label>
          <bb-input type="number" placeholder="€" v-model="form.value"></bb-input>
          <bb-input-validation :form="form" name="value"></bb-input-validation>
        </div>

        <div :class="(form.typology === 'free') ? 'col-span-2' : 'col-span-1'">
          <bb-label class="mb-1">Spesa minima</bb-label>
          <bb-input type="number" placeholder="Spesa minima" v-model="form.minimum_charge"></bb-input>
          <bb-input-validation :form="form" name="minimum_charge"></bb-input-validation>
        </div>    

        <div>
          <bb-label class="mb-1">Valido dal</bb-label>
          <datepicker
              v-model="form.valid_from"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
          <bb-input-validation :form="form" name="valid_from"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Valido al</bb-label>
          <datepicker
              v-model="form.valid_to"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
          <bb-input-validation :form="form" name="valid_to"></bb-input-validation>
        </div>

        <div>
          <bb-label class="mb-1">N. massimo di utilizzi</bb-label>
          <bb-input type="number" placeholder="N. massimo di utilizzi" v-model="form.maximum_count_per_user"></bb-input>
          <bb-input-validation :form="form" name="maximum_count_per_user"></bb-input-validation>
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
          <bb-label class="mb-1">Valido per</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.target"
              :options="valid_for"
          ></bb-radio-group>
          <bb-input-validation :form="form" name="target"></bb-input-validation>
        </div>

        <div v-if="form.target === 'users'" class="col-span-2">
          <bb-label class="mb-1">Utenti</bb-label>
          <bb-select
              mode="tags"
              placeholder="Seleziona gli utenti"
              :close-on-select="true"
              :options="usersSorted"
              :searchable="true"
              v-model="form.users"
          ></bb-select>
          <bb-input-validation :form="form" name="users"></bb-input-validation>
        </div>

        <div class="col-span-2">
          <bb-label class="mb-1">Descrizione</bb-label>
          <bb-textarea class="min-h-[180px]" type="text" v-model="form.description"></bb-textarea>
          <bb-input-validation :form="form" name="description"></bb-input-validation>
        </div>
        <div class="sm:col-span-2 flex justify-start items-center">
          <bb-switch v-model="form.active"></bb-switch>
          <bb-label class="mb-0 ml-2">Sconto attivo</bb-label>
        </div>
        <bb-input-validation :form="form" name="active"></bb-input-validation>
      </div>

      <div class="flex justify-end items-center mt-4">
        <bb-button type="button" @click="storeModel" :disabled="form.processing">
          <span v-if="form.id">Salva</span>
          <span v-else>Aggiungi</span>
        </bb-button>
      </div>
    </div>

  </AppLayout>
</template>