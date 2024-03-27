<template>
  <div class="bb-collapsible">
    <div class="bb-collpsible__toggle" @click="isOpen = !isOpen">
      <slot name="toggle" :isOpen="isOpen" :close="close"></slot>
    </div>
    <div v-if="isOpen" class="bb-collpsible__content">
      <slot name="content" :isOpen="isOpen" :close="close"></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  startOpen: {
    type: Boolean,
    default: false,
  },
});

const isOpen = ref(props.startOpen);

function open() {
  isOpen.value = true;
}

function close() {
  isOpen.value = false;
}

defineExpose({ open });
</script>
