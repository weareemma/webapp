<template>
  <div class="bb-wizard">
    <template v-if="steps.length">
      <div class="bb-wizard__title">
        <slot
          v-if="'title' in $slots"
          name="title"
          :title="steps[activeStep.value].title"
        ></slot>
        <h2 v-else>{{ steps[activeStep.value].title }}</h2>
      </div>
      <div class="bb-wizard__content">
        <template v-for="(step, stepIndex) in steps" :key="step.name">
          <slot
            v-if="stepIndex == activeStep.value"
            :name="step.name"
            :wizardData="wizardData"
            :prev="prevStep"
            :next="nextStep"
          >
          </slot>
        </template>
      </div>
    </template>
    <auth-modal
        ref="authDialog"
        :local-state="wizardData"
        @logged-in="loggedIn"
        @registered="registered"
    />
  </div>
</template>

<script setup>
import { Inertia } from "@inertiajs/inertia";
import _, {isEmpty} from "lodash";
import { reactive, ref, provide, onMounted } from "vue";
import AuthModal from "@/Components/General/AuthModal/AuthModal.vue"
import { storeToRefs } from 'pinia'
import {useWizardStore} from "../../DataStore/Wizard/wizardStore";

const store = useWizardStore()

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
  steps: {
    type: Array,
    default: [],
  },
  initialData: {
    type: Object,
    default: {},
  },
  showCallback: {
    type: Function,
    default: null,
  },
  hideCallback: {
    type: Function,
    default: null,
  },
});
const emit = defineEmits(["update:modelValue", "loggedIn", "registered", "linkSent"]);

const { activeStep, wizardSelection } = storeToRefs(store)

const authDialog = ref(null);

const loggedIn = () => {
  emit('loggedIn')
}

const registered = () => {
  emit('registered')
}

const openAuth = () => {
  authDialog.value.open()
}
// the state of wizard
const wizardData = reactive(
  _.cloneDeep({ ...props.modelValue }) ?? _.cloneDeep(props.initialData)
);
provide("wizardData", wizardData);

// steps management
function prevStep() {
  if (activeStep.value.value > 0) {
    goToStep(activeStep.value.value - 1);
  }
}
provide("prev", prevStep);

function nextStep(afterWizardRoute = null, sendLink = false) {
  if (sendLink) {
    emit('linkSent')
  }
  if (afterWizardRoute && activeStep.value.value >= props.steps.length - 1) {
    Inertia.visit(afterWizardRoute);
  }
  if (activeStep.value.value < props.steps.length - 1) {
    if (activeStep.value.value === props.steps.length - 2 && isEmpty(Inertia.page.props.user)) {
      openAuth()
    } else {
      goToStep(activeStep.value.value + 1);
    }
  }
}
provide("next", nextStep);

function goToStep(stepIndex) {
  if (stepIndex > -1) {
    if (!triggerBeforeCallbacks(stepIndex)) return;
    const lastStep = activeStep.value.value;

    updateSteps(stepIndex, lastStep);
  }
}
function goToStepByName(stepName) {
  // verify logged in
  if (stepName === 'step_checkout' && isEmpty(Inertia.page.props.user)) return openAuth()
  const stepIndex = props.steps.findIndex((s) => s.name == stepName);
  goToStep(stepIndex);
}
function reloadStep() {
  goToStep(activeStep.value.value);
}
provide("goTo", goToStepByName);
provide("goToIndex", goToStep);
provide("reload", reloadStep);

// updating data to outside the component
function updateData(data = null) {
  if (data && typeof data == "object") {
    for (const key in data) {
      wizardData[key] = data[key];
      if (wizardSelection.value.hasOwnProperty(key)) wizardSelection.value[key] = data[key]
    }
  }
  emit("update:modelValue", _.cloneDeep(wizardData));
}
provide("updateData", updateData);
updateData();

// trigger the lifecycle callbacks before step changed
function triggerBeforeCallbacks(newStepIndex) {
  const thisStep = props.steps[newStepIndex];
  let result = null;
  if ("onBeforeShow" in thisStep) {
    result = thisStep.onBeforeShow({ wizardData });
  }
  return result !== false;
}

// update the steps calling the lifecycle callbacks after step changed and updating data
function updateSteps(newStepIndex, lastStepIndex) {
  const thisStep = props.steps[newStepIndex];
  const lastStep = props.steps[lastStepIndex];
  const nextStep = newStepIndex < (props.steps?.length - 1) ? props.steps[newStepIndex + 1].name : '';
  activeStep.value.value = newStepIndex;
  activeStep.value.name = props.steps[newStepIndex].name;
  activeStep.value.next = nextStep;
  // set active step in payload
  wizardData.activeStep = {
    index: activeStep.value.value,
    name: props.steps[activeStep.value.value].name
  };

  // step lifecycle callbacks
  if ("onShow" in thisStep)
    thisStep.onShow({ wizardData, prev: prevStep, next: nextStep });
  if ("onHide" in lastStep) lastStep.onHide({ wizardData });

  // global lifecycle callback
  if (props.showCallback)
    props.showCallback({ wizardData, update: updateData });
  if (props.hideCallback)
    props.hideCallback({ wizardData, update: updateData });

  updateData();
}

// expose step management methods
defineExpose({ prevStep, nextStep, goToStep, goToStepByName, updateData });
</script>
