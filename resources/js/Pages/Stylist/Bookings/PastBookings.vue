<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref, onMounted, onUpdated, reactive, watch} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import { LocationMarkerIcon, ArrowCircleRightIcon, ArrowLeftIcon, ArrowRightIcon } from "@heroicons/vue/outline";
import dayjs from "dayjs";

const props = defineProps({
  bookings: Object
});

onUpdated(() => {
  bookingsCurrentPage.value = 0;
  bookingsPage.value = props.bookings.slice(0, 10);
})

const { searchQuery, isSearching } = useSearch("stylist.booking.past");

// Bookings pagination
const bookingsCurrentPage = ref(0);
const bookingsPage = ref(props.bookings.slice(bookingsCurrentPage * 10, 10))
watch(bookingsCurrentPage, (n, o) => {
  bookingsPage.value = props.bookings.slice(n * 10, (n * 10) + 10)
})
</script>

<template>
  <StylistLayout title="App. passati">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10">
      <div class="mx-auto max-w-3xl">
        <div>
          <p class="text-xl text-bb-gray-800 font-extrabold">Appuntamenti Passati</p>
        </div>
        <div class="my-3">
          <bb-search-input bordered placeholder="Cerca" :searching="isSearching" v-model="searchQuery" />
        </div>

        <div class="mt-8" v-if="bookings.length > 0">
          <div v-for="booking in bookingsPage" class="bb-card-stylist p-4 flex justify-between items-center mb-4">
            <div class="">
              <p class="text-sm text-bb-gray-600">{{dayjs(booking.date).format('dddd D MMMM YYYY')}} / {{booking.hour_formatted}}</p>
              <p class="text-md text-bb-gray-800 font-bold">{{booking.customer.full_name}}</p>
              <p class="text-md text-bb-gray-800 mb-1">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
              <p class="text-xs text-bb-gray-600 flex items-center gap-1">
                <location-marker-icon class="h-3 w-3"></location-marker-icon>
                {{booking.store.name}} - {{booking.store.address}}
              </p>
            </div>
            <div>
              <a :href="route('stylist.booking.details', booking.id)"><arrow-circle-right-icon class="w-6 h-6"></arrow-circle-right-icon></a>
            </div>
          </div>
          <div v-if="bookings.length > 10" class="flex justify-end gap-2 items-center">
            <bb-button :disabled="bookingsCurrentPage === 0" @click="bookingsCurrentPage--" class="px-2.5" primary light><arrow-left-icon class="w-4 h-4"></arrow-left-icon></bb-button>
            <bb-button :disabled="bookingsCurrentPage === Math.floor(bookings.length / 10)" @click="bookingsCurrentPage++" class="px-2.5" primary light><arrow-right-icon class="w-4 h-4"></arrow-right-icon></bb-button>
          </div>
        </div>
        <div v-else>
          <p class="text-bb-gray-800 text-md text-center mt-10">Non sono presenti appuntamenti passati</p>
        </div>
      </div>
    </div>
  </StylistLayout>
</template>