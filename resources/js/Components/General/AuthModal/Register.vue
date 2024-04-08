<script setup>
import {useForm} from "@inertiajs/inertia-vue3";
import {inject} from "vue";

const form = useForm({
  name: '',
  surname: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = async () => {
  await storeState()
  form.transform(data => ({
    ...data,
    no_redirect: true,
    route: route().current(),
  })).post(route('register'), {
    onSuccess: () => {
      close();
      emit('registered')
    },
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};

const storeState = inject('storeState')
const close = inject('close')
const goTo = inject('goTo')
const emit = defineEmits(['registered'])

</script>
<template>
  <div class="px-4 py-5">
    <h3 class="text-center mt-0 mb-2">Registrati</h3>
    <h6 class="text-center mt-0 mb-2">Inserisci i tuoi dati per registrarti</h6>
    <form @submit.prevent="submit">
      <div class="sm:col-span-4 my-4">
        <label for="name" class="block text-sm font-medium text-gray-700"> Nome </label>
        <div class="mt-1">
          <bb-input v-model="form.name" id="name" name="name" type="text" autocomplete="name" placeholder="Mario" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
          <bb-input-validation :form="form" name="name"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="surname" class="block text-sm font-medium text-gray-700"> Cognome </label>
        <div class="mt-1">
          <bb-input v-model="form.surname" id="surname" name="surname" type="text" autocomplete="surname" placeholder="Bianchi" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
          <bb-input-validation :form="form" name="surname"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
        <div class="mt-1">
          <bb-input v-model="form.email" id="email" name="email" type="email" autocomplete="email" placeholder="bianchi@gmail.com" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
          <bb-input-validation :form="form" name="email"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="phone" class="block text-sm font-medium text-gray-700"> Phone </label>
        <div class="mt-1">
          <bb-input v-model="form.phone" id="phone" name="phone" type="text" autocomplete="phone" placeholder="+39..." :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
          <bb-input-validation :form="form" name="phone"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="password" class="block text-sm font-medium text-gray-700"> Password </label>
        <div class="mt-1">
          <bb-input v-model="form.password" id="password" name="password" type="password" autocomplete="password" placeholder="Crea la tua password" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.password ? 'border-red-600' : 'border-gray-300')" required />
          <bb-input-validation :form="form" name="password"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="password" class="block text-sm font-medium text-gray-700"> Conferma Password </label>
        <div class="mt-1">
          <bb-input v-model="form.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" autocomplete="password_confirmation" placeholder="Ripeti la password" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.password ? 'border-red-600' : 'border-gray-300')" required />
          <bb-input-validation :form="form" name="password_confirmation"></bb-input-validation>
        </div>
      </div>

      <div class="sm:col-span-4 my-6">
        <div class="flex justify-start items-center gap-2">
          <bb-checkbox v-model="form.terms"></bb-checkbox>
          <bb-label class=" text-sm">Ho letto e accetto <a class="text-bb-blue-500">i termini e le condizioni</a></bb-label>
        </div>
        <bb-input-validation :form="form" name="terms"></bb-input-validation>
      </div>

      <div class="w-full">
        <!--        <Button>Accedi</Button>-->
        <bb-button class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Registrati
        </bb-button>
      </div>

      <div class="relative my-5">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
          <div class="w-full border-t border-gray-300" />
        </div>
        <div class="relative flex justify-center">
          <span class="bg-white px-2 text-sm text-gray-500">Oppure</span>
        </div>
      </div>

      <div>
        <p class="text-center text-sm">Hai un account? <a @click.prevent="goTo('login')" class="text-bb-blue-500 font-extrabold cursor-pointer">Accedi</a></p>
      </div>
    </form>
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
