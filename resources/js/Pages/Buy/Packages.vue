<script setup>
import { ref, computed, onBeforeMount } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import { RadioGroup, RadioGroupDescription, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import { CheckCircleIcon } from '@heroicons/vue/solid';
import { CheckIcon } from '@heroicons/vue/solid';

const props = defineProps({
  packages: Object,
});

const selectedPackageId = ref(null);

function buy() {
  Inertia.get(route("buy.package.checkout", selectedPackageId.value));
}
</script>

<template>
  <div class="p-5">
    <RadioGroup v-model="selectedPackageId">
      <div class="mt-4 grid grid-cols-1 gap-y-6 md:grid-cols-3 md:gap-x-4">
        <RadioGroupOption as="template" v-for="pack in packages" :key="pack.id" :value="pack.id" v-slot="{ checked, active }">
          <div :class="[checked ? 'border-transparent' : 'border-transparent', active ? 'border-bb-gray-800 ring-2 ring-bb-gray-800' : '', 'relative flex cursor-pointer rounded-lg border bg-gray-100 p-4 shadow-sm focus:outline-none']">
          <span class="flex flex-1">
            <span class="flex flex-col">
              <RadioGroupLabel as="span" class="block text-xl font-bold text-bb-gray-800">{{ pack.name }}</RadioGroupLabel>

              <div class="pt-6 pb-8">
                <h3 class="text-sm font-medium text-gray-900">Include</h3>
                <ul role="list" class="mt-6 space-y-4">
                  <li v-for="(service, idx) in pack.services_formatted" :key="idx" class="flex space-x-3">
                    <CheckIcon class="h-5 w-5 flex-shrink-0 text-green-500" aria-hidden="true" />
                    <span class="text-sm text-gray-500">{{ service }}</span>
                  </li>
                </ul>
              </div>

              <RadioGroupDescription as="span" class="mt-8 text-lg text-bb-gray-800">{{ Math.round(pack.price) }} €</RadioGroupDescription>
            </span>
          </span>
            <CheckCircleIcon :class="[!checked ? 'invisible' : '', 'h-5 w-5 text-bb-gray-800']" aria-hidden="true" />
            <span :class="[active ? 'border' : 'border-2', checked ? 'border-bb-gray-800' : 'border-transparent', 'pointer-events-none absolute -inset-px rounded-lg']" aria-hidden="true" />
          </div>
        </RadioGroupOption>
      </div>
    </RadioGroup>

    <bb-button
        primary
        class="mx-auto my-10"
        @click="buy"
        :disabled="!selectedPackageId"
    >
      Acquista
    </bb-button>
  </div>
</template>
