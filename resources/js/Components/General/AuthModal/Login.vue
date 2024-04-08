<script setup>
import {inject} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";
import JetCheckbox from '@/Jetstream/Checkbox.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = async () => {
  await storeState()
  form.transform(data => ({
    ...data,
    no_redirect: true,
    route: route().current(),
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onSuccess: () => {
      close();
      emit('loggedIn')
    },
    onFinish: () => form.reset('password'),
  });
};

const socialLogin = async (provider) => {
  await storeState()
  Inertia.get(route('social.login', {
    provider: provider,
    no_redirect: true,
    route: route().current()
  }))
}

const emit = defineEmits(['loggedIn'])
const goTo = inject('goTo')
const close = inject('close')
const storeState = inject('storeState')
const registerButton = inject('registerButton')
const socialButtons = inject('socialButtons')
const canResetPassword = inject('canResetPassword')

</script>
<template>
  <div class="px-4 py-5">
    <h3 class="text-center mt-0 mb-2">Login</h3>
    <h6 class="text-center mt-0 mb-2">Inserisci le credenziali per continuare</h6>
    <form @submit.prevent="submit">
      <div class="sm:col-span-4 my-4">
        <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
        <div class="mt-1">
          <bb-input v-model="form.email" id="email" name="email" type="email" autocomplete="email" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required />
          <p v-if="form.errors.email" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors.email }}</p>
        </div>
      </div>

      <div class="sm:col-span-4 my-4">
        <label for="password" class="block text-sm font-medium text-gray-700"> Password </label>
        <div class="mt-1">
          <bb-input v-model="form.password" id="password" name="password" type="password" autocomplete="password" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.password ? 'border-red-600' : 'border-gray-300')" required />
          <p v-if="form.errors.password" class="mt-2 text-sm text-red-600" id="password-error">{{ form.errors.password }}</p>
        </div>
      </div>

      <div class="flex justify-between items-center my-4">
        <label class="flex items-center">
          <JetCheckbox v-model:checked="form.remember" name="remember" />
          <span class="ml-2 text-sm text-gray-600">Ricordami</span>
        </label>
        <a v-if="canResetPassword" @click.prevent="goTo('forgot')" class="underline text-sm text-gray-600 hover:text-gray-900 cursor-pointer">
          Hai dimenticato la password?
        </a>
      </div>

      <div class="w-full my-4">
        <!--        <Button>Accedi</Button>-->
        <bb-button class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Accedi
        </bb-button>
      </div>

      <div v-if="socialButtons">
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
            @click.prevent="socialLogin('google')"
            class="bb-social-login-btn" style="height: 46px;">
          <img class="bb-social-login-logo" src="/img/google.svg">
          <div class="w-full h-full flex items-center justify-center font-medium">Accedi con Google</div>
        </div>

        <div
            @click.prevent="socialLogin('facebook')"
            class="bb-social-login-btn" style="height: 46px;">
          <img class="bb-social-login-logo" src="/img/facebook.svg">
          <div class="w-full h-full flex items-center justify-center font-medium">Accedi con Facebook</div>
        </div>
      </div>

      <div v-if="registerButton">
        <p class="text-center text-sm">Non hai un account? <a @click="goTo('register')" class="text-bb-blue-500 font-extrabold cursor-pointer">Registrati</a></p>
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
