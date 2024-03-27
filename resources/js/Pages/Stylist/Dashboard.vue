<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref, onMounted, onUpdated, reactive} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/solid';
import { ArrowCircleRightIcon, BookmarkIcon } from '@heroicons/vue/outline';
import { useSearch } from "@/Composables/search.js";
import BookingStatusLight from "@/Pages/Booking/Partials/BookingStatusLight.vue";
import dayjs from "dayjs";

const props = defineProps({
  stores: Object,
  bookings_next: Object,
  bookings_past: Object
});

onMounted(() => {
  helpers.lg(filters.day);
  console.log(route().params.day)
})

onUpdated(() => {
  helpers.lg(filters.day);
  helpers.lg(props.bookings_next);
  helpers.lg(props.bookings_past);
})

// search
const { searchQuery, isSearching, filters } = useSearch("stylist.dashboard", {
  store: null,
  day: route().params.day ?? dayjs().format('YYYY-MM-DD')
});

// User
const user = ref(usePage().props.value.user);

// Store
const stores = ref(props.stores);
function filterStore(store)
{
  filters.store = (store) ? store.name : null;
}

// Tabs
const tabs = [
  { name: 'Prossimi app.' },
  { name: 'App. conclusi' }
];
const currentTab = ref(tabs[0].name);
function changeTab(name)
{
  currentTab.value = name;
}

// Calendar
const calendarDate = ref(filters.day);
function changeDay(date) {
  filters.day = dayjs(date).format('YYYY-MM-DD');
  if (dayjs(filters.day).isSameOrAfter(dayjs(), 'day'))
  {
    changeTab(tabs[0].name);
  }
  else
  {
    changeTab(tabs[1].name);
  }
}
function today()
{
  filters.day = dayjs().format('YYYY-MM-DD');
  calendarDate.value = filters.day;
  changeTab(tabs[0].name);
}

</script>

<template>
  <StylistLayout title="Dashboard">
    <h1 class="text-xl font-extrabold text-bb-blue-500 hidden sm:block">Ciao {{user.full_name}}!</h1>
    <h4 class="text-bb-gray-800 hidden sm:block">Visualizza e gestisci gli appuntamenti che ti hanno assegnato</h4>

    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10">
      <div class="mx-auto max-w-3xl">
        <div class="bb-card bg-transparent px-0">
          <div class="sm:flex justify-between items-center">
            <div class="flex justify-between items-center sm:gap-3 gap-1 mb-3 sm:mb-0">
              <div>
                <p class="text-xl text-bb-gray-800 font-extrabold">{{dayjs(filters.day).format('DD MMMM YYYY')}}</p>
              </div>
              <div class="flex justify-start items-center gap-1">
                <p @click="today" class="cursor-pointer"><span class="inline-flex items-center rounded-full bg-white px-2.5 py-0.5 text-xs font-medium text-bb-gray-500">Oggi</span></p>
                <datepicker
                    class="bb-datepicker-button-sm"
                    v-model="calendarDate"
                    locale="it-IT"
                    :enableTimePicker="false"
                    monthNameFormat="long"
                    autoApply
                    @update:modelValue="changeDay"
                />
              </div>
            </div>
            <div class="flex justify-start items-center gap-2">
              <p class="text-sm text-bb-gray-600">Filtra per store:</p>
              <Menu as="div" class="relative inline-block text-left">
                <div>
                  <MenuButton class="inline-flex w-full justify-center px-4 py-2 text-sm font-medium text-gray-700">
                    {{ (filters.store) ? filters.store : 'Tutti'}}
                    <ChevronDownIcon class="-mr-1 ml-2 h-5 w-5" aria-hidden="true" />
                  </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                  <MenuItems class="absolute -right-12 sm:right-0 z-10 mt-2 w-56 sm:origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div class="py-1">
                      <MenuItem v-slot="{ active }">
                        <p @click="filterStore(null)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">Tutti</p>
                      </MenuItem>
                      <MenuItem v-slot="{ active }" v-for="store in stores">
                        <p @click="filterStore(store)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">{{ store.name }}</p>
                      </MenuItem>
                    </div>
                  </MenuItems>
                </transition>
              </Menu>
            </div>
          </div>
          <div>
            <div class="">
              <label for="tabs" class="sr-only"></label>
            </div>
            <div class="block">
              <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 cursor-pointer" aria-label="Tabs">
                  <p v-if="dayjs(filters.day).isSameOrAfter(dayjs(), 'day')" @click="changeTab(tabs[0].name)"  :key="tabs[0].name" :class="[currentTab === tabs[0].name ? 'border-bb-blue-500 text-bb-blue-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm']" :aria-current="currentTab === tabs[0].name ? 'page' : undefined">{{ tabs[0].name }}</p>
                  <p v-if="dayjs(filters.day).isSameOrBefore(dayjs(), 'day')" @click="changeTab(tabs[1].name)"  :key="tabs[1].name" :class="[currentTab === tabs[1].name ? 'border-bb-blue-500 text-bb-blue-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm']" :aria-current="currentTab === tabs[1].name ? 'page' : undefined">{{ tabs[1].name }}</p>
                </nav>
              </div>
            </div>
          </div>
          <div v-show="currentTab === tabs[0].name" class="py-6">
            <p v-if="bookings_next.length === 0" class="text-sm text-bb-blue-500 text-center">Nessun appuntamento</p>
            <div v-for="booking in bookings_next" class="bb-card bg-bb-lilla mb-3">
              <div class="flex justify-start items-center gap-4">
                <p class="text-white font-bold">{{booking.hour_formatted}}</p>
                <booking-status-light :status="booking.status"></booking-status-light>
              </div>
              <p class="text-white font-bold text-sm">{{booking.customer.full_name}}</p>
              <p class="text-white text-sm">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
              <div class="flex justify-between items-center mt-6">
                <p class="text-sm text-[#2B3B77]">{{booking.store.name}}</p>
                <a :href="route('stylist.booking.details', booking.id)"><ArrowCircleRightIcon class="h-6 w-6 text-white"></ArrowCircleRightIcon></a>
              </div>
            </div>
          </div>
          <div v-show="currentTab === tabs[1].name" class="py-6">
            <p v-if="bookings_past.length === 0" class="text-sm text-bb-blue-500 text-center">Nessun appuntamento</p>
            <div v-for="booking in bookings_past" class="bb-card bg-bb-lilla mb-3">
              <div class="flex justify-start items-center gap-4">
                <p class="text-white font-bold">{{booking.hour_formatted}}</p>
                <booking-status-light :status="booking.status"></booking-status-light>
              </div>
              <p class="text-white font-bold text-sm">{{booking.customer.full_name}}</p>
              <p class="text-white text-sm">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
              <div class="flex justify-between items-center mt-6">
                <p class="text-sm text-[#2B3B77]">{{booking.store.name}}</p>
                <a :href="route('stylist.booking.details', booking.id)"><ArrowCircleRightIcon class="h-6 w-6 text-white"></ArrowCircleRightIcon></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </StylistLayout>
</template>