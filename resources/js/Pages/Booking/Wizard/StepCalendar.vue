<template>
  <div>
    <div class="text-center mb-16">
      Seleziona il giorno e l’ora del tuo appuntamento
    </div>
    <div class="space-y-4 max-w-xl mx-auto">
      <div v-if="wizardSelection.available_days">
        <!-- days -->
        <div class="bg-[#A9D6DB] rounded-xl p-8 mb-4">
          <div>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg">
            <span v-if="wizardSelection.selected_day">
              {{ dayjs(wizardSelection.selected_day?.date).format("dddd DD MMMM") }}
            </span>
                <span v-else>Seleziona un giorno</span>
              </h3>
            </div>
            <div class="relative overflow-x-auto">
              <div class="flex space-x-8 pb-4">
                <div
                    v-for="day in wizardSelection.available_days"
                    :key="day.date"
                    class="group cursor-pointer flex flex-col items-center"
                    :class="{
                      'opacity-40': !day.available,
                    }"
                    @click="() => {
                      if (day.available) selectDay(day);
                      otherDate = false;
                    }"
                >
                  <div
                      class="mb-2"
                      :class="{
                  'font-bold': wizardSelection.selected_day?.date === day.date,
                }"
                  >
                    {{ H.capitalizeFirstLetter(dayjs(day.date).format("ddd")) }}
                  </div>
                  <div
                      class="rounded-full bg-white w-12 h-12 grid place-items-center"
                      :class="{
                        'group-hover:bg-bb-primary group-hover:text-white': day.available,
                        'bg-bb-primary-800 group-hover:bg-bb-primary-800 text-white': wizardSelection.selected_day?.date == day.date,
                      }"
                  >
                    <div>
                      {{ dayjs(day.date).format("DD") }}
                    </div>
                  </div>
                </div>
                <div
                    class="group cursor-pointer flex flex-col items-center"
                    @click="() => {
                      wizardSelection.selected_day = null;
                      wizardSelection.selected_slot = null;
                      datepickerValue = dayjs(wizardSelection.available_days.slice(-1)[0].date, 'YYYY-MM-DD').add(1,'day').format('YYYY-MM-DD');
                      otherDate = true;
                    }"
                >
                  <div
                      class="mb-2 font-bold"
                  >
                    Altro
                  </div>
                  <div
                      class="rounded-full bg-white w-12 h-12 grid place-items-center"
                      :class="{
                        'group-hover:bg-bb-primary group-hover:text-white': true,
                        'bg-bb-primary-800 group-hover:bg-bb-primary-800 text-white': otherDate,
                      }"
                  >
                    <div>
                      +
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div v-if="noSlotsAvailable">
          <p class="text-center my-5 px-8">
            Sembra che non ci siano slot liberi. <br>
            Prova a modificare la prenotazione oppure contattaci.
          </p>
        </div>

        <div v-if="otherDate">
          <div class="flex justify-between items-center">
            <datepicker
              class="bb-datepicker-button"
              locale="it-IT"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
              :minDate="dayjs(wizardSelection.available_days.slice(-1)[0].date, 'YYYY-MM-DD').add(1,'day').format('YYYY-MM-DD')"
              v-model="datepickerValue"
              @update:modelValue="() => {
                selectDay({
                  date: dayjs(datepickerValue).format('YYYY-MM-DD')
                });
              }"
            />
            <p class="font-bold">{{ (wizardSelection.selected_day) ? dayjs(wizardSelection.selected_day?.date).format('dddd DD MMMM') : '' }}</p>
          </div>
          <div v-if="wizardSelection.selected_day" class="mt-4">
            <div>
              <Collapsible ref="slotsCollapsible" :start-open="true">
                <template #toggle="{ isOpen }">
                  <div
                      class="flex items-center justify-between py-2 px-8 bg-[#A9D6DB] rounded-full cursor-pointer mb-4"
                  >
                    <div class="font-bold">
                    <span v-if="wizardSelection.selected_slot">
                      Orario selezionato: {{ wizardSelection.selected_slot?.time }}
                    </span>
                      <span v-else>Seleziona un'orario</span>
                    </div>
                    <ChevronRightIcon
                        class="w-4 h-4 text-white transition transform-gpu"
                        :class="{
                      'rotate-90': isOpen,
                    }"
                    />
                  </div>
                </template>
                <template #content="{ close }">
                  <div class="space-y-4">
                    <template
                        v-for="slot in otherDateSlots"
                        :key="slot.time"
                    >
                      <button
                          v-if="slot.available"
                          :disabled="!slot.available"
                          class="rounded-full bg-white w-full shadow px-2 py-2 text-lg text-center cursor-pointer transition-colors disabled:opacity-30"
                          :class="{
                        'hover:bg-bb-primary hover:text-white': slot.available,
                        'bg-bb-primary-800 hover:bg-bb-primary-800 text-white':
                          wizardSelection.selected_slot?.time == slot.time,
                      }"
                          @click="
                        () => {
                          if (slot.available) {
                            selectSlot(slot);
                            close();
                          }
                        }
                      "
                      >
                        {{ slot.time }}
                      </button>
                    </template>
                  </div>
                </template>
              </Collapsible>
            </div>
          </div>
        </div>

        <!-- slots -->
        <div v-if="wizardSelection.selected_day && ! otherDate">
          <Collapsible ref="slotsCollapsible" :start-open="true">
            <template #toggle="{ isOpen }">
              <div
                  class="flex items-center justify-between py-2 px-8 bg-[#A9D6DB] rounded-full cursor-pointer mb-4"
              >
                <div class="font-bold">
                <span v-if="wizardSelection.selected_slot">
                  Orario selezionato: {{ wizardSelection.selected_slot?.time }}
                </span>
                  <span v-else>Seleziona un'orario</span>
                </div>
                <ChevronRightIcon
                    class="w-4 h-4 text-white transition transform-gpu"
                    :class="{
                  'rotate-90': isOpen,
                }"
                />
              </div>
            </template>
            <template #content="{ close }">
              <div class="space-y-4">
                <template
                    v-for="slot in wizardSelection.selected_day.slots"
                    :key="slot.time"
                >
                  <button
                      v-if="slot.available"
                      :disabled="!slot.available"
                      class="rounded-full bg-white w-full shadow px-2 py-2 text-lg text-center cursor-pointer transition-colors disabled:opacity-30"
                      :class="{
                    'hover:bg-bb-primary hover:text-white': slot.available,
                    'bg-bb-primary-800 hover:bg-bb-primary-800 text-white':
                      wizardSelection.selected_slot?.time == slot.time,
                  }"
                      @click="
                    () => {
                      if (slot.available) {
                        selectSlot(slot);
                        close();
                      }
                    }
                  "
                  >
                    {{ slot.time }}
                  </button>
                </template>
              </div>
            </template>
          </Collapsible>
        </div>

        <bb-button
            v-if="isValid[activeStep.next]"
            class="w-full mt-10"
            outline
            @click.stop="next"
            :disabled="! ready"
        >
          Avanti
        </bb-button>
      </div>
      <div v-else>
        <div class="bg-[#A9D6DB] rounded-xl p-8 mb-4">
          <div class="flex justify-start items-center gap-2">
            <p class="text-center">
              Stiamo verificando la disponibilità
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import dayjs from "dayjs";
import {computed, inject, onMounted, ref, watch} from "vue";
import H from "@/helpers.js";
import Collapsible from "@/Components/General/Collapsible.vue";
import { ChevronRightIcon } from "@heroicons/vue/outline";
import {useWizardStore} from "../../../DataStore/Wizard/wizardStore";
import {storeToRefs} from "pinia";
import TimePicker from "vue3-timepicker";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";

const store = useWizardStore()
const { wizardSelection, isValid, activeStep, ready, people, wizardGeneral } = storeToRefs(store)

const updateData = inject("updateData");
const prev = inject("prev");
const next = inject("next");

const otherDate = ref(false);

// slots collapsible
const slotsCollapsible = ref(null);

// no slots available
const noSlotsAvailable = computed(() => {
  return ! wizardSelection.value.available_days.find(a => { return a.available });
})

const datepickerValue = ref(null);
const otherDateSlots = ref([{
  time: '08:00',
  available: true
},{
  time: '08:15',
  available: true
},{
  time: '08:30',
  available: true
}]);

watch(datepickerValue, (n) => {
  console.log(dayjs(n).format('YYYY-MM-DD'));
  axios.post(route('checkAvailability.single'), {
    ...wizardGeneral.value,
    ...wizardSelection.value,
    people: people.value,
    day: dayjs(n).format('YYYY-MM-DD')
  }).then((res) => {
    console.log(res.data);
    otherDateSlots.value = res.data;
  })
})

onMounted(() => {
});

// select day
function selectDay(day) {
  // console.log(day);
  wizardSelection.value.selected_day = day;
  wizardSelection.value.selected_slot = null;
  if (slotsCollapsible.value) slotsCollapsible.value.open();
  updateData();
}

// select slot
function selectSlot(slot) {
  // console.log(slot);
  wizardSelection.value.selected_slot = slot;
  updateData();
}

</script>
