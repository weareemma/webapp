<template>
  <StylistLayout title="Appointments schedule" :show-title="false">
    <div class="pb-16">
      <!-- title -->
      <h2 class="text-2xl font-extrabold text-bb-primary mb-3">
        Calendario {{ store.name }}
      </h2>

      <!-- page actions -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <div v-if="filters.viewName == 'calendar'" class="text-bb-primary">
            Visualizza gli appuntamenti
          </div>
          <div v-else>
            <bb-search-input :isSearching="isSearching" v-model="searchQuery" />
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <template v-if="filters.viewName == 'calendar'">
            <datepicker
                class="bb-datepicker-button"
                v-model="calendarDate"
                locale="it-IT"
                :enableTimePicker="false"
                monthNameFormat="long"
                autoApply
                @update:modelValue="changeDay"
            />
            <bb-button secondary light @click="filters.viewName = 'list'">
              <ViewListIcon class="w-3 h-3" /> &nbsp; Elenco
            </bb-button>
          </template>
          <bb-button
              v-if="filters.viewName == 'list'"
              secondary
              light
              @click="filters.viewName = 'calendar'"
          >
            <CalendarIcon class="w-3 h-3" /> &nbsp; Calendario
          </bb-button>
        </div>
      </div>

      <!-- calendar view -->
      <div v-if="filters.viewName == 'calendar'">
        <Calendar ref="calendar" />
      </div>

      <!-- list view -->
      <div v-if="filters.viewName == 'list'">
        <List
            v-if="bookings?.data"
            :collection="bookings?.data"
            :links="bookings?.links"
        />
      </div>
    </div>
  </StylistLayout>
</template>

<script setup>
import { computed, ref, watchEffect } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import H from "@/helpers";
import StylistLayout from "@/Layouts/StylistLayout.vue";
import Calendar from "@/Pages/Schedules/Partials/ScheduleAppointmentCalendar.vue";
import List from "@/Pages/Schedules/Partials/ScheduleAppointmentList.vue";
import { Inertia } from "@inertiajs/inertia";
import { CalendarIcon, ViewListIcon } from "@heroicons/vue/solid";
import { useSearch } from "@/Composables/search.js";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";

const props = defineProps({
  viewName: String,
  store: Object,
  bookings: Object,
});

const { searchQuery, isSearching, filters } = useSearch(
    "stylist.appointment.index",
    {
      viewName: props.viewName ?? "calendar",
    }
);

// calendar
const calendar = ref(null);
const calendarDate = ref(null);
function changeDay(date) {
  calendar.value.goToDay(date);
}
</script>
