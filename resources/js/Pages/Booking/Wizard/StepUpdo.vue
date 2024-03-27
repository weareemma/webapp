<template>
  <div>
    <div class="text-center mb-16">
      Seleziona il raccolto che desideri prenotare
    </div>

    <div class="max-w-xl mx-auto">
      <div class="text-right mb-10">
        <a class="cursor-pointer underline"
           v-if="isValid[activeStep.next] && ((wizardSelection.different_services && currentPeople === people[people.length - 1].name) || ! wizardSelection.different_services)"
           @click.stop="next"
           :disabled="! ready"
        >
          Salta Raccolti >
        </a>
        <a class="cursor-pointer underline"
           v-if="wizardSelection.different_services && ! (currentPeople === people[people.length - 1].name)"
           @click.stop="nextPeople"
           :disabled="! ready"
        >
          Salta Raccolti >
        </a>
      </div>
    </div>

    <div v-if="!wizardSelection.different_services" class="grid lg:grid-cols-2 gap-6 mb-8">
      <div
        v-for="updo in people[0].services_selection['updo']"
        :key="updo.id"
        class="bb-wizard__selectable-card"
        :class="{
          'bb-wizard__selectable-card--active':
            compareUpdo(0, updo),
        }"
        @click="compareUpdo(0, updo) ? unselectPeopleService(0) : selectPeopleService(updo, 0)"
      >
        <h3>{{ updo.title }}</h3>
        <div class="text-bb-gray-900">
          {{ updo.description }}
        </div>
        <div class="flex items-center justify-between text-white">
          <div class="flex items-center space-x-2">
            <ClockIcon class="w-4 h-4" />
            <div>{{ updo.execution_time_fe }}'</div>
          </div>
          <div class="flex items-center space-x-2">
            <span :class="(wizardGeneral.subscribed && !wizardSelection.multiple) ? 'line-through' : ''">{{ helpers.printPrice(updo.net_price) }}€</span>
            <span v-if="wizardGeneral.subscribed && !wizardSelection.multiple">{{ helpers.printPrice(updo.net_price_discounted) }}€</span>
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
      <div v-for="p in people" :key="p.name" class="grid lg:grid-cols-2 gap-6">
        <template v-if="currentPeople === p.name">
          <div
              v-for="updo in p.services_selection['updo'] "
              :key="updo.id"
              class="bb-wizard__selectable-card"
              :class="{
          'bb-wizard__selectable-card--active':
            compareUpdo(p.name, updo)
        }"
              @click="compareUpdo(p.name, updo) ? unselectPeopleService(p.name) : selectPeopleService(updo, p.name)"
          >
            <h3>{{ updo.title }}</h3>
            <div class="text-bb-gray-900">
              {{ updo.description }}
            </div>
            <div class="flex items-center justify-between text-white">
              <div class="flex items-center space-x-2">
                <ClockIcon class="w-4 h-4" />
                <div>{{ updo.execution_time_fe }}'</div>
              </div>
              <div class="flex items-center space-x-2">
                <div v-if="p.name === 0" class="flex gap-x-1">
                  <span class="line-through">{{ helpers.printPrice(updo.net_price) }}€</span>
                  <span>{{ helpers.printPrice(updo.net_price_discounted) }}€</span>
                </div>
                <div v-else>
                  {{ updo.net_price }}€
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="flex justify-center">
      <bb-button
          class="mt-10"
          v-if="isValid[activeStep.next] && ((wizardSelection.different_services && currentPeople === people[people.length - 1].name) || ! wizardSelection.different_services)"
          outline
          @click.stop="next"
          :disabled="! ready"
      >
        Avanti
      </bb-button>
      <bb-button
          class="mt-10"
          v-if="wizardSelection.different_services && ! (currentPeople === people[people.length - 1].name)"
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

const store = useWizardStore()
const {
  isValid,
  activeStep,
  people,
  wizardSelection,
  wizardGeneral,
  ready
} = storeToRefs(store)

const prev = inject("prev");
const next = inject("next");

const currentPeople = ref(people.value[0].name);

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

onMounted(() => {
  helpers.lg(currentPeople.value);
})

function compareUpdo(name, service)
{
  let p = people.value.find(p => p.name === name);

  if (p.addons.updo.length > 0)
  {
    return p.addons.updo[0].id === service.id;
  }

  return false;
}

function selectPeopleService(service, name)
{
  people.value.forEach((p) => {
    if (p.name === name || ! wizardSelection.value.different_services)
    {
      p.addons.updo = [];
      p.addons.updo.push(service);
    }
  })
}

function unselectPeopleService(name)
{
  people.value.forEach((p) => {
    if (p.name === name || ! wizardSelection.value.different_services)
    {
      p.addons.updo = [];
    }
  })
}

function jumpUpdo()
{
  people.value.forEach((p) => {
    p.addons.updo = [];
  })
  next();
}
</script>
