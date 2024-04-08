<template>
  <div>
    <div class="text-center mb-8">
      Seleziona gli add-ons che desideri prenotare
    </div>
    <div class="max-w-xl mx-auto">
      <div class="text-right my-4">
        <a class="cursor-pointer underline"
           v-if="isValid[activeStep.next] && ((wizardSelection.different_services && currentPeople === people[people.length - 1].name) || ! wizardSelection.different_services)"
           @click.stop="next"
           :disabled="! ready"
        >
          Salta Add-on >
        </a>
        <a class="cursor-pointer underline"
           v-if="wizardSelection.different_services && ! (currentPeople === people[people.length - 1].name)"
           @click.stop="nextPeople"
           :disabled="! ready"
        >
          Salta Add-on >
        </a>
      </div>

      <div v-if="!wizardSelection.different_services" class="">
        <template
          v-if="currentPeople === 0"
          v-for="(category, key) in people[0].services_selection"
          :key="key"
        >
          <div v-if="key !== 'updo'" class="space-y-4 py-4">
            <div>
              <p class="text-3xl font-bold text-bb-blue-500 mb-0">{{getCategoryTitle(key)}}</p>
              <p class="text-md text-bb-gray-700 mt-1">Puoi selezionare solo un add-on per tipologia</p>
            </div>
            <div
              v-for="addon in people[0].services_selection[key]"
              :key="addon.id"
              class="bb-wizard__selectable-card"
              :class="{
                'bb-wizard__selectable-card--active':
                  compareAddon(0, addon, key),
              }"
              @click="compareAddon(0, addon, key) ? unselectPeopleService(key, 0) : selectPeopleService(key, addon, 0)"
            >
              <h3 class="text-bb-blue font-bold text-xl">
                {{ addon.title }}
              </h3>

              <div>{{ addon.description }}</div>
              <div class="flex items-center justify-between text-white">
                <div class="flex items-center space-x-2">
                  <ClockIcon class="w-4 h-4" />
                  <div class="bb-wizard__selectable-card__info">{{ addon.execution_time_fe }}'</div>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="bb-wizard__selectable-card__info flex gap-x-2">
                    <span :class="(wizardGeneral.subscribed && !wizardSelection.multiple) ? 'line-through' : ''">{{ helpers.printPrice(addon.net_price) }}€</span>
                    <span v-if="wizardGeneral.subscribed && !wizardSelection.multiple">{{ helpers.printPrice(addon.net_price_discounted) }}€</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div v-if="wizardSelection.multiple && wizardSelection.different_services" class="flex flex-col justify-center items-center">
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
        <div v-for="p in people" :key="p.name">
          <template v-if="p.services_selection['massage'] || p.services_selection['treatment']">
            <template
                v-if="currentPeople === p.name"
                v-for="(category, key) in p.addons"
                :key="key"
            >
              <div v-if="key !== 'updo'" class="space-y-4 py-4">
                <div>
                  <p class="text-3xl font-bold text-bb-blue-500 mb-0">{{getCategoryTitle(key)}}</p>
                  <p class="text-md text-bb-gray-700 mt-1">Puoi selezionare solo un add-on per tipologia</p>
                </div>
                <div
                    v-for="addon in p.services_selection[key]"
                    :key="addon.id"
                    class="bb-wizard__selectable-card"
                    :class="{
                'bb-wizard__selectable-card--active':
                  compareAddon(p.name, addon, key),
              }"
                    @click="compareAddon(p.name, addon, key) ? unselectPeopleService(key, p.name) : selectPeopleService(key, addon, p.name)"
                >
                  <h3 class="text-bb-blue font-bold text-xl">
                    {{ addon.title }}
                  </h3>

                  <div>{{ addon.description }}</div>
                  <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-2">
                      <ClockIcon class="w-4 h-4" />
                      <div class="bb-wizard__selectable-card__info">{{ addon.execution_time_fe }}'</div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div v-if="p.name === 0" class="bb-wizard__selectable-card__info flex gap-x-1">
                        <span class="line-through">{{ helpers.printPrice(addon.net_price) }}€</span>
                        <span>{{ helpers.printPrice(addon.net_price_discounted) }}€</span>
                      </div>
                      <div v-else>
                        {{ addon.net_price }}€
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </template>
          <p v-else-if="p.name === currentPeople">Nessun add-on disponibile</p>
        </div>
      </div>

      <div class="flex justify-center">
        <bb-button class="mt-10"
                   v-if="isValid[activeStep.next] && ((wizardSelection.different_services && currentPeople === people[people.length - 1].name) || ! wizardSelection.different_services)"
                   outline
                   @click.stop="next"
                   :disabled="! ready"
        > Avanti
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
  </div>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";
import Collapsible from "@/Components/General/Collapsible.vue";
import { ChevronRightIcon, ClockIcon } from "@heroicons/vue/outline";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import helpers from "../../../helpers";

const prev = inject("prev");
const next = inject("next");
const store = useWizardStore()
const {
  isValid,
  activeStep,
  people,
  wizardSelection,
  wizardGeneral,
  ready
} = storeToRefs(store)

const currentPeople = ref(people.value[0].name);

onMounted(() => {
  helpers.lg(people.value[0].services_selection['massage']);
});

const nextPeople = () => {
  let idx = people.value.findIndex(p => p.name === currentPeople.value) + 1;
  if (people.value[idx])
  {
    currentPeople.value = people.value[idx].name
  }
}

const selectPeople = (name) => {
  currentPeople.value = name;
}

function selectPeopleService(categoryKey, service, name)
{
  people.value.forEach((p) => {
    if (p.name === name || ! wizardSelection.value.different_services)
    {
      p.addons[categoryKey] = [];
      p.addons[categoryKey].push(service);
    }
  })
}

function unselectPeopleService(categoryKey, name)
{
  people.value.forEach((p) => {
    if (p.name === name || ! wizardSelection.value.different_services)
    {
      p.addons[categoryKey] = [];
    }
  })
}

function compareAddon(name, service, category)
{
  let p = people.value.find(p => p.name === name);

  if (p.addons[category].length > 0)
  {
    return p.addons[category][0].id === service.id;
  }

  return false;
}

function jumpAddons()
{
  people.value.forEach((p) => {
    p.addons.massage = [];
    p.addons.treatment = [];
  })
  next();
}

// get addon group name
function getAddonName(key) {
  switch (key) {
    case "massage":
      return "Massaggio";
    case "treatment":
      return "Trattamento";
  }
}

function getCategoryTitle(key)
{
  switch (key) {
    case 'massage':
      return "Servizi Extra";
    case 'treatment':
      return "Trattamenti";
  }
}
</script>
