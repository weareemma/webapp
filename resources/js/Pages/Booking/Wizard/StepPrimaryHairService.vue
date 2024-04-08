<template>
  <div>
    <div class="text-center ">
      Seleziona il servizio che desideri prenotare
    </div>

    <div v-if="wizardSelection.multiple" class="flex justify-center gap-2 mb-12 mt-8">
      <bb-checkbox v-model="wizardSelection.different_services"></bb-checkbox>
      <bb-label class="ml-2">
        Io e le mie amiche prenotiamo servizi diversi
      </bb-label>
    </div>

    <div v-if="!wizardSelection.different_services" class="grid lg:grid-cols-2 gap-6 mb-8 mt-8">
      <div
        v-for="hs in wizardGeneral.primaries"
        :key="hs.id"
        class="bb-wizard__selectable-card"
        :class="{
          'bb-wizard__selectable-card--active':
            people[0].primary_service?.id === hs.id,
        }"
        @click="selectPeopleService(hs, 0)"
      >
        <h3>{{ hs.title }}</h3>
        <div class="text-bb-gray-900">
          {{ hs.description }}
        </div>
        <div class="flex items-center justify-between text-white">
          <div class="flex items-center space-x-2">
            <ClockIcon class="w-4 h-4" />
            <div class="bb-wizard__selectable-card__info">{{ hs.execution_time_fe }}'</div>
          </div>
          <div class="flex items-center space-x-2">
            <div class="bb-wizard__selectable-card__info" :class="(!wizardSelection.multiple && wizardGeneral.subscribed && ( ! primariesNotIncluded.includes(hs.title))) ? 'line-through' : ''">{{ hs.net_price }}€</div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="wizardSelection.multiple && wizardSelection.different_services" class="flex flex-col justify-center items-center mt-8">
      <div class="mb-8">
        <div class="sm:hidden">
          <label for="tabs" class="sr-only"></label>
          <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
          <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option v-for="p in people" :key="p.name" :selected="(p.name) === currentPeople">
              {{ (p.name === 0) ? 'Tu' : ('Amica ' + p.name) }}
            </option>
          </select>
        </div>
        <div class="hidden sm:block">
          <nav class="flex space-x-4" aria-label="Tabs">
            <button
                v-for="p in people"
                :key="p.name"
                :class="[(p.name) === currentPeople ? 'bg-bb-blue-500 text-white' : 'text-bb-blue-400 hover:text-bb-blue-700', 'px-6 py-1.5 font-bold text-sm rounded-full']"
                :aria-current="(p.name) === currentPeople ? 'page' : undefined"
                @click="selectPeople(p.name)"
            >
              {{ (p.name === 0) ? 'Tu' : ('Amica ' + p.name) }}
            </button>
          </nav>
        </div>
      </div>
      <div v-for="p in people" :key="p.name" class="grid lg:grid-cols-2 gap-6 p-0">
        <div
            v-if="currentPeople === p.name"
            v-for="hs in wizardGeneral.primaries"
            :key="hs.id"
            class="bb-wizard__selectable-card"
            :class="{
          'bb-wizard__selectable-card--active':
            p.primary_service?.id === hs.id,
        }"
            @click="selectPeopleService(hs, p.name)"
        >
          <h3>{{ hs.title }}</h3>
          <div class="text-bb-gray-900">
            {{ hs.description }}
          </div>
          <div class="flex items-center justify-between text-white">
            <div class="flex items-center space-x-2">
              <ClockIcon class="w-4 h-4" />
              <div class="bb-wizard__selectable-card__info">{{ hs.execution_time_fe }}'</div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="bb-wizard__selectable-card__info" :class="(p.name === 0 && wizardGeneral.subscribed && ( ! primariesNotIncluded.includes(hs.title))) ? 'line-through' : ''">{{ hs.net_price }}€</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-center">
      <bb-button
        class="mt-10"
        v-if="isValid[activeStep.next]"
        outline
        @click.stop="next"
        :disabled="! ready"
      >
        Avanti
      </bb-button>
      <bb-button
          class="mt-10"
          v-if="(! isValid[activeStep.next]) && wizardSelection.different_services"
          outline
          @click.stop="nextPeople"
      >
        Prossima amica
      </bb-button>
    </div>
  </div>
</template>

<script setup>
import { Inertia } from "@inertiajs/inertia";
import {inject, onMounted, ref, watch} from "vue";
import { ClockIcon } from "@heroicons/vue/outline";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import helpers from "../../../helpers";
import {usePage} from "@inertiajs/inertia-vue3";

const prev = inject("prev");
const next = inject("next");
const store = useWizardStore()
const {
  wizardSelection,
  wizardGeneral,
  isValid,
  activeStep,
  people,
  wizardFetchData,
  wizardResetAddons,
  wizardResetPrimary,
  ready
} = storeToRefs(store)

const primariesNotIncluded = usePage().props.value.primaries_not_included;

watch(() => wizardSelection.value.different_services, (n,o) => {

  people.value.forEach((p) => {
    console.log(p.name);
    if (p.primary_service)
    {
      store.wizardResetPrimary(p.name);
      store.wizardResetAddons(p.name);
    }
  })
})

const goNextDisabled = ref(false);

const currentPeople = ref(0);

const selectPeople = (name) => {
  currentPeople.value = name;
}

const nextPeople = () => {
  let idx = people.value.findIndex(p => p.name === currentPeople.value) + 1;
  if (people.value[idx])
  {
    currentPeople.value = people.value[idx].name
  }
}


function selectPeopleService(service, name)
{
  people.value.forEach((p) => {
    if (p.name === name || ! wizardSelection.value.different_services)
    {
      p.primary_service = service;
      store.wizardResetAddons(p.name);
      store.wizardFetchData('step_primary_hair_service', {
        primaryId: service.id,
        name: name
      });
    }
  })
}
</script>
