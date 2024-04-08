<script setup>
import {ref, watch} from "vue";
import {
  PlusIcon,
  MinusIcon
} from "@heroicons/vue/outline";

const props = defineProps({
  min: {type: Number, default: 1},
  max: {type: Number, default: 3},
  modelValue: null
})

const emit = defineEmits([
    'update:modelValue'
])

const currentCount = ref(props.modelValue);

watch(currentCount, (n,o) => {
  emit('update:modelValue', n);
})

const add = () => {
  if (currentCount.value < props.max - 1)
  {
    currentCount.value = currentCount.value + 1;
  }
  else
  {
    boxDialog.value.open();
  }

}

const sub = () => {
  if (currentCount.value > props.min)
  currentCount.value = currentCount.value - 1;
}

const boxDialog = ref(null);

</script>

<template>
  <div class="flex justify-center">
    <div class="grid grid-cols-3 gap-x-1 gap-y-4 items-center">
      <button
          @disabled="currentCount.value === min"
          @click="sub"
          :class="{
            'w-10 h-10 rounded-full bg-[#99A7DA] text-white flex justify-center items-center' : true,
            'opacity-25' : currentCount.value === min
          }"
      >
        <minus-icon class="w-5 h-5" />
      </button>
      <div class="text-center">{{currentCount}}</div>
      <button
          @disabled="currentCount.value === max"
          @click="add"
          class="w-10 h-10 rounded-full bg-[#99A7DA] text-white flex justify-center items-center disabled:opacity-75">
        <plus-icon class="w-5 h-5" />
      </button>
    </div>

    <BbDialog ref="boxDialog" type="plain" size="sm" :noCancel="true">
      <template #title>
        <p class="text-bb-primary">Troppe persone</p>
      </template>

      <p class="text-center">
        Non è possibile prenotare per più di {{max}} persone in questo store.
      </p>
      <br>
      <p class="text-center">
        Se vuoi prenotare per più persone contattaci a questo indirizzo email:
        <a href="mailto:info@weareemma.com" class="text-bb-primary">info@weareemma.com</a>
      </p>

      <template #buttons>
        <BbButton primary @click="boxDialog.close()">
          Ho capito
        </BbButton>
      </template>
    </BbDialog>
  </div>
</template>