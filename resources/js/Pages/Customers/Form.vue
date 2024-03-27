<script setup>
import { ref, computed, onMounted, onUpdated } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import helpers from "../../helpers";

const props = defineProps({
  customer: Object,
});

const password = {
  password: null,
  password_confirmation: null,
};
const form = useForm({ ...props.customer, ...password });

function storeModel() {
  if (form.id) {
    form.put(route("customer.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
      },
    });
  } else {
    form
      .transform((f) => ({
        ...f,
        role: "customer",
      }))
      .post(route("customer.store"), {
        preserveScroll: true,
        onSuccess: (res) => {
          helpers.flash(res.props.flash);
        },
      });
  }
}
</script>

<template>
  <AppLayout title="Cliente" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link :url="route('customer.index')"></bb-back-link>
    </div>

    <div class="bb-card py-5 px-5">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica cliente</span>
        <span v-else>Aggiungi nuovo cliente</span>
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
          <bb-label class="mb-1">Cognome</bb-label>
          <bb-input
            type="text"
            placeholder="Cognome"
            v-model="form.surname"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="surname"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Email</bb-label>
          <bb-input
            type="email"
            placeholder="Email"
            v-model="form.email"
          ></bb-input>
          <bb-input-validation :form="form" name="email"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Telefono</bb-label>
          <bb-input
            type="number"
            placeholder="Telefono"
            v-model="form.phone"
          ></bb-input>
          <bb-input-validation :form="form" name="phone"></bb-input-validation>
        </div>
        <div v-if="form.id">
          <bb-label class="mb-1">Password</bb-label>
          <bb-input
            type="password"
            placeholder="Password"
            v-model="form.password"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="password"
          ></bb-input-validation>
        </div>
        <div v-if="form.id">
          <bb-label class="mb-1">Conferma password</bb-label>
          <bb-input
            type="password"
            placeholder="Conferma Password"
            v-model="form.password_confirmation"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="password_confirmation"
          ></bb-input-validation>
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
