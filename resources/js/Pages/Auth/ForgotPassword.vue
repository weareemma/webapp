<script setup>
import { Head, useForm, Link } from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import LoginLayout from '@/Layouts/LoginLayout.vue';
import Logo from '@/Components/General/Logo.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
  <Head title="Forgot Password" />

  <LoginLayout>
    <JetAuthenticationCard class="">
      <template #logo>
        <Logo class="mb-2"></Logo>
      </template>

      <div v-if="status">
        <h3 class="text-center mt-4 mb-5">Controlla la tua email!</h3>

        <h6 class="text-center mb-8">Abbiamo inviato un’email all’indirizzo che ci hai indicato. Segui le istruzioni per reimpostare la password.</h6>

        <h6 class="text-center mb-2">Non hai ricevuto l’email? <Link :href="route('password.request')">Rinvia</Link></h6>
      </div>

      <div v-else>
        <h3 class="text-center mt-4 mb-5">Password dimenticata?</h3>

        <h6 class="text-center mb-8">Inserisci la tua email. Ti invieremo le istruzioni per reimpostare la tua password.</h6>

        <form @submit.prevent="submit">

          <div class="sm:col-span-4 my-4">
            <label for="email" class="block text-sm font-medium text-gray-700"> Email </label>
            <div class="mt-1">
              <bb-input v-model="form.email" id="email" name="email" type="email" autocomplete="email" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.email ? 'border-red-600' : 'border-gray-300') " required autofocus />
              <p v-if="form.errors.email" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors.email }}</p>
            </div>
          </div>

          <div class="flex justify-between items-center">
            <Link :href="route('login')">Torna al login</Link>
            <bb-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Invia
            </bb-button>
          </div>
        </form>
      </div>


    </JetAuthenticationCard>
  </LoginLayout>

<!--    <JetAuthenticationCard>-->
<!--        <template #logo>-->
<!--            <JetAuthenticationCardLogo />-->
<!--        </template>-->

<!--        <div class="mb-4 text-sm text-gray-600">-->
<!--            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.-->
<!--        </div>-->

<!--        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">-->
<!--            {{ status }}-->
<!--        </div>-->

<!--        <JetValidationErrors class="mb-4" />-->

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

<!--            <div class="flex items-center justify-end mt-4">-->
<!--                <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">-->
<!--                    Email Password Reset Link-->
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
