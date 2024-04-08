<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, onMounted, ref} from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import Calendar from "@/Pages/Schedules/Partials/ScheduleActualCalendar.vue";
import H from "@/helpers";

// data
const store = computed(() => usePage().props.value.current_store);
const lastUpdate = computed(() => usePage().props.value.last_update.shift);

onMounted(() => {
})

// get date from date and time
function getDate(date, time) {
  const day = H.dayjs(date);
  return `${day.format("YYYY-MM-DD")}T${time}`;
}

// calendar
const calendar = ref(null);
const calendarDate = ref(null);
function changeDay(date) {
  calendar.value.goToDay(date);
}
</script>

<template>
  <AppLayout title="Default schedule" :show-title="false">
    <div class="pb-16">
      <Calendar ref="calendar">
        <template #title>
          <div class="flex-grow col-span-2 w-full">
              <h2 class="text-2xl font-extrabold text-bb-primary">
                Programmazione effettiva {{ store.name }}
              </h2>
              <div class="text-bb-primary">
                Dati importati da Tanda
              </div>
            </div>
          <div class="flex justify-between items-center gap-2 col-span-2 sm:col-span-1 flex-grow">
            <div>
                <p class="text-sm text-white">Ultimo aggiornamento: <strong>{{ (lastUpdate) ? H.dayjs(lastUpdate).format('H:m:s DD/MM/YYYY') : 'Mai'}}</strong></p>
              </div>
              <div>
                <datepicker
                    class="bb-datepicker-button"
                    v-model="calendarDate"
                    locale="it-IT"
                    :enableTimePicker="false"
                    monthNameFormat="long"
                    autoApply
                    @update:modelValue="changeDay"
                />
              </div>
          </div>
        </template>
      </Calendar>
    </div>
  </AppLayout>
</template>