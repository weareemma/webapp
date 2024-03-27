<script setup>
import { ref, computed, onMounted, onUpdated, watch } from 'vue';
import { Inertia } from "@inertiajs/inertia";
import {usePage} from "@inertiajs/inertia-vue3";
import helpers from "../../helpers";
import {useWizardStore} from "../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue';
import { CheckIcon, ChevronDownIcon } from "@heroicons/vue/solid";

const store = useWizardStore()
const { wizardSelection } = storeToRefs(store)

const stores = computed(() => usePage().props.value.stores_list);
const currentStore = ref(
    (usePage().props.value.current_store)
      ? usePage().props.value.current_store.id
      : ''
);

onMounted(() => {
  wizardSelection.value.store_id = currentStore.value
})

watch(currentStore, (v) => {
//   helpers.lg(v)
  Inertia.post(route('store.change'), {
    store: currentStore.value
  }, {
    preserveScroll: true,
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
      window.location.reload();
    }
  });
});



</script>

<template>
  <div class="">
    <!-- <bb-select
        mode="single"
        class="bg-bb-yellow-300 text-bb-blue-500 font-bold rounded-full border-0 w-[250px] sm:w-[250px] absolute top-3 md:left-0 left-30"
        placeholder="Seleziona lo store"
        :close-on-select="true"
        :options="stores"
        v-model="currentStore"
        :canClear="false"
        :canDeselect="false"
    ></bb-select> -->
   <!-- <select
       id="store_switch"
       v-if="stores.length > 0"
       class="bg-bb-yellow-300 text-bb-blue-500 font-bold rounded-full border-0"
       v-model="currentStore"
   >
     <option v-for="(store, idx) in stores" :value="idx" :selected="idx === currentStore">{{ store }}</option>
   </select> -->
   <Listbox as="div" v-model="currentStore">
    <div class="w-full">
      <ListboxButton class="relative w-full bg-bb-yellow-300 text-bb-blue-500 font-bold rounded-full border-0 pl-3 pr-8 py-2">
        <span class="block truncate">{{ stores[currentStore] ?? '' }}</span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </span>
      </ListboxButton>

      <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <ListboxOptions class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm p-1">
          <ListboxOption as="template" v-for="(store, idx) in stores" :key="idx" :value="idx" v-slot="{ active, selected }">
            <li :class="[active ? 'bg-bb-primary-100' : 'text-gray-900', 'relative cursor-default select-none py-2 pl-3 pr-9 rounded-md']">
              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ store }}</span>

              <span v-if="selected" :class="[active ? 'text-white' : 'text-indigo-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
  </div>
</template>
