<template>
  <AppLayout title="Appointments schedule" :show-title="false">
    <div class="pb-16">
      <!-- title -->
      <h2 class="text-2xl font-extrabold text-bb-primary mb-3">
        Calendario {{ store.name }}
      </h2>

      <!-- page actions -->
      <div class="flex items-center justify-between mb-4 flex-wrap gap-x-4 gap-y-2">
        <div class="flex-grow flex justify-start gap-x-2">
          <div v-if="filters.viewName == 'calendar'" class="text-bb-primary">
            Visualizza e gestisci gli appuntamenti
          </div>
          <div v-else>
            <bb-search-input  :isSearching="isSearching" v-model="searchQuery" />
          </div>
        </div>
        <div class="w-fit justify-self-end flex justify-end gap-2">
          <span v-if="noStylistBookingsCount > 0" class="inline-flex items-center rounded-full bg-yellow-100 px-4 py-2.5 text-sm font-medium text-yellow-800">
            <ExclamationIcon class="w-6 h-6 mr-1" />
            {{ noStylistBookingsCount }}
          </span>
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
        <div class="grid grid-cols-2 col-span-2 sm:flex items-center gap-x-4 gap-y-2 flex-wrap w-full sm:w-auto">
          <template v-if="filters.viewName === 'calendar'">
            <bb-button secondary light @click="filters.viewName = 'list'">
              <ViewListIcon class="w-3 h-3" /> &nbsp; Elenco
            </bb-button>
          </template>
          <template v-if="filters.viewName === 'list'">
            <bb-button secondary light @click="filters.viewName = 'calendar'" class="px-1 sm:px-4">
              <CalendarIcon class="w-3 h-3" /> &nbsp; Calendario
            </bb-button>
          </template>
          
          <bb-button class="justify-self-end" @click="add">Aggiungi</bb-button>
<!--          <bb-button @click="updateShifts">Refresh Tanda</bb-button>-->
          <bb-button @click="() => {calendar.slot5 = !calendar.slot5}" v-if="filters.viewName === 'calendar'"> 
            {{(calendar?.slot5) ? 'Slot 30' : 'Slot 5'}}
          </bb-button>
        </div>
      </div>

      <!-- calendar view -->
      <div v-if="filters.viewName == 'calendar'">
        <Calendar ref="calendar" @changed="(dt) => updateNostylistCount(dt)"/>
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
  </AppLayout>
</template>

<script setup>
import { computed, ref, watchEffect } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import H from "@/helpers";
import AppLayout from "@/Layouts/AppLayout.vue";
import Calendar from "@/Pages/Schedules/Partials/ScheduleAppointmentCalendar.vue";
import List from "@/Pages/Schedules/Partials/ScheduleAppointmentList.vue";
import { Inertia } from "@inertiajs/inertia";
import { CalendarIcon, ViewListIcon, ExclamationIcon } from "@heroicons/vue/solid";
import { useSearch } from "@/Composables/search.js";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import helpers from "@/helpers.js";

const props = defineProps({
  viewName: String,
  store: Object,
  bookings: Object,
  noStylistBookingsCount: Number
});

const isAdmin = computed(() => usePage().props.value.is_admin);

const { searchQuery, isSearching, filters } = useSearch(
  "schedule.appointment.index",
  {
    viewName: props.viewName ?? "calendar",
    from: route().params.from,
    to: route().params.to
  }
);

const noStylistBookingsCount = ref(0);

// add new booking
function add() {
  Inertia.visit(route("booking.admin-dashboard"));
}

// calendar
const calendar = ref(null);
const calendarDate = ref(null);
function changeDay(date) {
  calendar.value.goToDay(date);
  updateNostylistCount(date);
}

function updateNostylistCount(date)
{
  axios.post(route('schedule.nostylist.count'), {
    day: dayjs(date).hour(5).toDate()
  }).then(res => {
    noStylistBookingsCount.value = res.data.count;
  })
}

function updateShifts()
{
  Inertia.post(route('tanda.update.shifts'), {}, {
    onSuccess: (res) => {
      helpers.flash(res);
    }
  })
}
</script>
