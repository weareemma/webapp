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
import { Inertia } from "@inertiajs/inertia";


defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
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
  <Head title="Log in" />

  <LoginLayout>
    <JetAuthenticationCard class="">
      <template #logo>
        <Logo class="mb-2"></Logo>
      </template>

      <div class="px-4 py-5">
        <h3 class="text-center mt-4 mb-5">Bentornato!</h3>

        <h6 class="text-center mb-8">Inserisci le credenziali per accedere</h6>

        <form @submit.prevent="submit">
          <div class="sm:col-span-4 my-4">
            <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
            <div class="mt-1">
              <bb-input v-model="form.email" id="email" name="email" type="email" autocomplete="email" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
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
            <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
              Hai dimenticato la password?
            </Link>
          </div>

          <div class="w-full my-4">
            <!--        <Button>Accedi</Button>-->
            <bb-button class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Accedi
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
            <p class="text-center text-sm">Non hai un account? <a :href="route('register')" class="text-bb-blue-500 font-extrabold">Registrati</a></p>
          </div>

        </form>
      </div>

    </JetAuthenticationCard>
  </LoginLayout>

<!--    <JetAuthenticationCard>-->
<!--        <template #logo>-->
<!--            <JetAuthenticationCardLogo />-->
<!--        </template>-->

<!--        <JetValidationErrors class="mb-4" />-->

<!--        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">-->
<!--            {{ status }}-->
<!--        </div>-->

<!--        <form @submit.prevent="submit">-->

<!--            <div>-->
<!--                <JetLabel for="email" value="Email" />-->
<!--                <JetInput-->
<!--                    id="email"-->
<!--                    v-model="form.email"-->
<!--                    type="email"-->
<!--                    class="mt-1 block w-full"-->
<!--                    required-->
<!--                    autofocus-->
<!--                />-->
<!--            </div>-->

<!--            <div class="mt-4">-->
<!--                <JetLabel for="password" value="Password" />-->
<!--                <JetInput-->
<!--                    id="password"-->
<!--                    v-model="form.password"-->
<!--                    type="password"-->
<!--                    class="mt-1 block w-full"-->
<!--                    required-->
<!--                    autocomplete="current-password"-->
<!--                />-->
<!--            </div>-->

<!--            <div class="block mt-4">-->
<!--                <label class="flex items-center">-->
<!--                    <JetCheckbox v-model:checked="form.remember" name="remember" />-->
<!--                    <span class="ml-2 text-sm text-gray-600">Remember me</span>-->
<!--                </label>-->
<!--            </div>-->

<!--            <div class="flex items-center justify-end mt-4">-->
<!--                <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">-->
<!--                    Forgot your password?-->
<!--                </Link>-->

<!--                <JetButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">-->
<!--                    Log in-->
<!--                </JetButton>-->
<!--            </div>-->
<!--        </form>-->
<!--    </JetAuthenticationCard>-->
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
