<script setup>
import {provide, ref} from "vue";
import Logo from '@/Components/General/Logo.vue';
import Forgot from "@/Components/General/AuthModal/Forgot.vue";
import Register from "@/Components/General/AuthModal/Register.vue";
import Login from "@/Components/General/AuthModal/Login.vue";

const emit = defineEmits(['loggedIn', 'registered'])
const currentStep = ref('login');
const authModal = ref(null);

const props = defineProps({
  localState: {
    type: Object,
    default: {}
  },
  localStateKey: {
    type: String,
    default: 'wizardData'
  },
  canResetPassword: {
    type: Boolean,
    default: true
  },
  socialButtons: {
    type: Boolean,
    default: true
  },
  registerButton: {
    type: Boolean,
    default: true
  }
})
provide('registerButton', props.registerButton)
provide('socialButtons', props.socialButtons)
provide('canResetPassword', props.canResetPassword)

function open () {
  authModal.value.open()
}

function close () {
  authModal.value.close()
}
provide ('close', close)

const storeState = async () => {
  await localStorage.setItem(props.localStateKey, JSON.stringify(props.localState))
}
provide ('storeState', storeState)

const goToTab = (step) => {
  currentStep.value = step
}
provide('goTo', goToTab)

defineExpose({open})
</script>

<template>
  <bb-dialog ref="authModal" size="md" :no-cancel="true">
    <template #title>
      <div class="flex flex-row w-full justify-center">
        <Logo class="mb-2" :light="false" :linked="false"></Logo>
      </div>
    </template>
    <Login v-if="currentStep === 'login'" @logged-in="emit('loggedIn')"/>
    <Register v-if="currentStep === 'register'" @registered="emit('registered')"/>
    <Forgot v-if="currentStep === 'forgot'"/>
  </bb-dialog>
</template>

