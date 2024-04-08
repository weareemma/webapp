<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import LoginLayout from '@/Layouts/LoginLayout.vue';
import Logo from '@/Components/General/Logo.vue';
import Input from "@/Components/Bitboss/Input.vue";
import {Inertia} from "@inertiajs/inertia";

const form = useForm({
    name: '',
    surname: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const socialLogin = (provider) => {
  Inertia.visit(route('social.login', provider), {
    data: {
      no_redirect: true
    }
  })
}
</script>

<template>
    <Head title="Register" />

  <LoginLayout>
    <JetAuthenticationCard>
      <template #logo>
        <Logo class="mb-2"></Logo>
      </template>

      <div class="px-4 py-5">
        <h3 class="text-center mt-4 mb-5 text-xl font-extrabold">Benvenuto!</h3>

        <h6 class="text-center mb-8">Inserisci i tuoi dati per registrarti</h6>

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
              <bb-label class=" text-sm">Ho letto e accetto <a target="_blank" href="https://weareemma.com/privacy-policy/" class="text-bb-blue-500">la Privacy Policy</a></bb-label>
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

          <!-- Socials -->
          <div
              @click="socialLogin('google')"
              class="bb-social-login-btn" style="height: 46px;">
            <img class="bb-social-login-logo" src="/img/google.svg">
            <div class="w-full h-full flex items-center justify-center font-medium">Accedi con Google</div>
          </div>

          <div
              @click="socialLogin('facebook')"
              class="bb-social-login-btn" style="height: 46px;">
            <img class="bb-social-login-logo" src="/img/facebook.svg">
            <div class="w-full h-full flex items-center justify-center font-medium">Accedi con Facebook</div>
          </div>

          <div>
            <p class="text-center text-sm">Hai già un account? <a :href="route('login')" class="text-bb-blue-500 font-extrabold">Accedi</a></p>
          </div>

        </form>
      </div>


    </JetAuthenticationCard>
  </LoginLayout>
</template>
