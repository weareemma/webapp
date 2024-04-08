<template>
  <AppLayout title="Specific schedule" :show-title="false">
    <div class="pb-16">
      <Calendar :events="events" :businessHours="businessHours">
        <template #title>
          <h2 class="text-2xl font-extrabold text-bb-primary">
            Programmazione specifica {{ store.name }}
          </h2>
          <div class="text-bb-primary">
            Imposta il numero di persone per ogni slot
          </div>
        </template>
      </Calendar>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import H from "@/helpers";
import AppLayout from "@/Layouts/AppLayout.vue";
import Calendar from "@/Pages/Schedules/Partials/ScheduleSpecificCalendar.vue";

// data
const schedules = computed(() => usePage().props.value.schedules);
const store = computed(() => usePage().props.value.store);

const businessHours = computed(() => {
  const bh = [];
  store.value.opening_times?.forEach((ot) => {
    const weekday = H.datetime.weekdayIndex(ot.day);
    ot.slots.forEach((slt) => {
      bh.push({
        daysOfWeek: [weekday],
        startTime: slt.start_time,
        endTime: slt.end_time,
      });
    });
  });
  return bh;
});

// events
const events = computed(() =>
  schedules.value.map((s) => ({
    id: s.id,
    title: s.workers,
    start: getDate(s.date, s.start),
    end: getDate(s.date, s.end),
    editable: true,
  }))
);

// get date from date and time
function getDate(date, time) {
  const day = H.dayjs(date);
  return `${day.format("YYYY-MM-DD")}T${time}`;
}
</script>
