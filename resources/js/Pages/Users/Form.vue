<script setup>
import { ref, computed, onMounted, onUpdated } from 'vue';
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from '@/Layouts/AppLayout.vue';
import helpers from "../../helpers";

const props = defineProps({
  user: Object,
  stores: Object,
  addOns: Object,
  hairService: Object,
});

const password = {
  password: null,
  password_confirmation: null
}
const form = useForm({...props.user, ...password, ...{'hair_service': props.hairService}});

function isCreation()
{
  return ( ! form.id);
}

function isTamigoRequired()
{
  return (form.role === helpers.role_stylist);
}

function isStoresRequired()
{
  return (form.role !== helpers.role_customer);
}

function storeModel()
{

  form.hair_service = props.hairService
  if (form.id)
  {
    form.put(route("user.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash)
      }
    });
  }
  else
  {
    form.post(route("user.store"), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash)
      }
    });
  }
}

</script>

<template>
  <AppLayout title="Utente" :show-title="false">

    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link :url="route('user.index')"></bb-back-link>
    </div>

    <div class="bb-card py-5 px-5">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica utente</span>
        <span v-else>Aggiungi nuovo utente</span>
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Nome</bb-label>
          <bb-input type="text" placeholder="Nome" v-model="form.name"></bb-input>
          <bb-input-validation :form="form" name="name"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Cognome</bb-label>
          <bb-input type="text" placeholder="Cognome" v-model="form.surname"></bb-input>
          <bb-input-validation :form="form" name="surname"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Email</bb-label>
          <bb-input type="email" placeholder="Email" v-model="form.email"></bb-input>
          <bb-input-validation :form="form" name="email"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Codice fiscale</bb-label>
          <bb-input type="text" placeholder="Codice fiscale" v-model="form.fiscal_code" :autocomplete="false"></bb-input>
          <bb-input-validation :form="form" name="fiscal_code"></bb-input-validation>
        </div>
        <div v-if=" ! isCreation()">
          <bb-label class="mb-1">Password</bb-label>
          <bb-input type="password" placeholder="Password" v-model="form.password" :autocomplete="false"></bb-input>
          <bb-input-validation :form="form" name="password"></bb-input-validation>
        </div>
        <div v-if=" ! isCreation()">
          <bb-label class="mb-1">Conferma password</bb-label>
          <bb-input type="password" placeholder="Conferma Password" v-model="form.password_confirmation" :autocomplete="false"></bb-input>
          <bb-input-validation :form="form" name="password_confirmation"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Ruolo</bb-label>
          <bb-select
              mode="single"
              placeholder="Seleziona il ruolo"
              :close-on-select="true"
              :options="helpers.roles"
              v-model="form.role"
          ></bb-select>
          <bb-input-validation :form="form" name="role"></bb-input-validation>
        </div>
        <div v-if="isStoresRequired()">
          <bb-label class="mb-1">Stores</bb-label>
          <bb-select
              mode="tags"
              placeholder="Seleziona gli stores"
              :close-on-select="true"
              :options="stores"
              v-model="form.stores"
          ></bb-select>
          <bb-input-validation :form="form" name="stores"></bb-input-validation>
        </div>
        <div v-if="isTamigoRequired()" class="sm:col-span-2">
          <bb-label class="mb-1">Codice Tanda</bb-label>
          <bb-input type="text" placeholder="Tanda" v-model="form.tanda_code" readonly></bb-input>
          <bb-input-validation :form="form" name="tanda_code"></bb-input-validation>
        </div>
      </div>

      <div class="grid grid-cols-1 mt-5">
        <h3 class="text-bb-blue-500 mb-4 big-header-title">
          <span>Servizi associati (Massaggio)</span>
        </h3>
        <div class="grid grid-cols-4" v-for="obj in addOns.massage">
          <bb-label class="mb-1">
            <input type="checkbox" name="hair_service" :id="'service' + obj.id" :value="obj.id" :modelValue="hairService" >
            {{ obj.title }}
          </bb-label>
        </div>
      </div>

      <div class="grid grid-cols-1 mt-5">
        <h3 class="text-bb-blue-500 mb-4 big-header-title">
          <span>Servizi associati (Raccolto)</span>
        </h3>
        <div class="grid grid-cols-4" v-for="obj in addOns.updo">
          <bb-label class="mb-1">
            <input type="checkbox" name="hair_service" :id="'service' + obj.id" :value="obj.id" v-model="hairService" >
            {{ obj.title }}
          </bb-label>
        </div>

      </div>

      <div class="grid grid-cols-1 mt-5">
        <h3 class="text-bb-blue-500 mb-4 big-header-title">
          <span>Servizi associati (Trattamento)</span>
        </h3>
        <div class="grid grid-cols-4" v-for="obj in addOns.treatment">
          <bb-label class="mb-1">
            <input type="checkbox" name="hair_service" :id="'service' + obj.id" :value="obj.id" v-model="hairService" >
            {{ obj.title }}
          </bb-label>
        </div>
      </div>

      <div class="sm:col-span-2 mt-5">
        <div class="flex justify-start items-center">
          <bb-switch v-model="form.active"></bb-switch>
          <bb-label class="mb-0 ml-2">Attivo</bb-label>
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
