<script setup>
import { Head, useForm, usePage } from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import LoginLayout from '@/Layouts/LoginLayout.vue';
import Logo from '@/Components/General/Logo.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: ''
});

const submit = () => {
    form.post(route('password.update'), {
        onError: (res) => helpers.lg(res),
        onSuccess: (res) => helpers.lg(res),
        onFinish: (res) => {
          helpers.lg(res);
          form.reset('password', 'password_confirmation');
        }
    });
};
</script>

<template>
  <Head title="Reset Password" />

  <LoginLayout>
    <JetAuthenticationCard class="">
      <template #logo>
        <Logo class="mb-2"></Logo>
      </template>

      <h3 class="text-center mt-4 mb-5">Imposta la tua nuova password</h3>

      <h6 class="text-center mb-8">Crea una nuova password.</h6>

      <form @submit.prevent="submit">

        <div class="sm:col-span-4 my-4">
          <label for="password" class="block text-sm font-medium text-gray-700"> Password </label>
          <div class="mt-1">
            <bb-input v-model="form.password" id="password" name="password" type="password" autocomplete="password" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md ' + (form.errors.password ? 'border-red-600' : 'border-gray-300')" required />
            <p v-if="form.errors" class="mt-2 text-sm text-red-600" id="password-error">{{ form.errors.password }}</p>
          </div>
        </div>

        <div class="sm:col-span-4 my-4">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700"> Conferma password </label>
          <div class="mt-1">
            <bb-input v-model="form.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" autocomplete="password_confirmation" :class="'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md border-gray-300'" required />
          </div>
        </div>

        <div class="sm:col-span-4 my-4">
          <div class="mt-1">
            <p v-if="form.email" class="mt-2 text-sm text-red-600" id="password-error">{{ form.errors.email }}</p>
          </div>
        </div>

        <div class="flex justify-end items-center">
          <bb-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            Crea password
          </bb-button>
        </div>
      </form>

    </JetAuthenticationCard>
  </LoginLayout>

<!--    <JetAuthenticationCard>-->
<!--        <template #logo>-->
<!--            <JetAuthenticationCardLogo />-->
<!--        </template>-->

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

<!--            <div class="mt-4">-->
<!--                <JetLabel for="password" value="Password" />-->
<!--                <JetInput-->
<!--                    id="password"-->
<!--                    v-model="form.password"-->
<!--                    type="password"-->
<!--                    class="mt-1 block w-full"-->
<!--                    required-->
<!--                    autocomplete="new-password"-->
<!--                />-->
<!--            </div>-->

<!--            <div class="mt-4">-->
<!--                <JetLabel for="password_confirmation" value="Confirm Password" />-->
<!--                <JetInput-->
<!--                    id="password_confirmation"-->
<!--                    v-model="form.password_confirmation"-->
<!--                    type="password"-->
<!--                    class="mt-1 block w-full"-->
<!--                    required-->
<!--                    autocomplete="new-password"-->
<!--                />-->
<!--            </div>-->

<!--            <div class="flex items-center justify-end mt-4">-->
<!--                <JetButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">-->
<!--                    Reset Password-->
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
