<script setup>
import {inject, ref} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";

const mailSent = ref(false);

const form = useForm({
  email: '',
});

const submitForgot = () => {
  form.transform(data => ({
    ...data,
    no_redirect: true,
    route: route().current(),
  })).post(route('password.email'), {
    onSuccess: () => mailSent.value = true
  });
};

const goTo = inject('goTo')

</script>
<template>
  <div class="px-4 py-5">
    <div v-if="mailSent">
      <h3 class="text-center mt-4 mb-5">Controlla la tua email!</h3>

      <h6 class="text-center mb-8">Abbiamo inviato un’email all’indirizzo che ci hai indicato. Segui le istruzioni per reimpostare la password.</h6>

      <h6 class="text-center mb-2">Non hai ricevuto l’email? <a class="cursor-pointer" @click.prevent="mailSent = false">Rinvia</a></h6>
      <a class="cursor-pointer" @click.prevent="goTo('login')">Torna al login</a>
    </div>
    <div v-else>
      <h3 class="text-center mt-4 mb-5">Password dimenticata?</h3>

      <h6 class="text-center mb-8">Inserisci la tua email. Ti invieremo le istruzioni per reimpostare la tua password.</h6>

      <form @submit.prevent="submitForgot">

        <div class="sm:col-span-4 my-4">
          <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
          <div class="mt-1">
            <bb-input v-model="form.email" id="email" name="email" type="email" autocomplete="email" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors.email }}</p>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <a class="cursor-pointer" @click.prevent="goTo('login')">Torna al login</a>
          <bb-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            Invia
          </bb-button>
        </div>
      </form>
    </div>
  </div>
</template>
<style scoped>
h3 {
  font-size: 32px;
  font-weight: bold;
}
h6 {
  font-size: 16px;
}
</style>
