<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import dayjs from "dayjs";
import BbTable from "@/Components/Bitboss/Table.vue";
import {onUpdated, ref, watch} from "vue";
import { LocationMarkerIcon, ArrowCircleRightIcon, ArrowLeftIcon, ArrowRightIcon, PencilIcon, TrashIcon, UsersIcon } from "@heroicons/vue/outline";
import {Inertia} from "@inertiajs/inertia";
import { Link } from '@inertiajs/inertia-vue3';

const props = defineProps({
  bookings: Object,
});

onUpdated(() => {
  bookingsCurrentPage.value = 0;
  bookingsPage.value = props.bookings.slice(0, 10);
})

function goToEditBooking(id)
{
  Inertia.visit(route('booking.edit', id));
}

// Bookings pagination
const bookingsCurrentPage = ref(0);
const bookingsPage = ref(props.bookings.slice(bookingsCurrentPage * 10, 10))
watch(bookingsCurrentPage, (n, o) => {
  bookingsPage.value = props.bookings.slice(n * 10, (n * 10) + 10)
})


</script>

<template>
  <CustomerLayout title="Dashboard">
    <div class="mx-auto w-full">
      <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5">I tuoi appuntamenti</h3>
      <div>
        <div class="block">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <Link :href="route('customer.bookings.future')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm">App. futuri</Link>
              <Link :href="route('customer.bookings.past')" class="border-bb-blue-500 text-bb-blue-500 whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm" aria-current="page">App. passati</Link>
            </nav>
          </div>
        </div>
      </div>
      <div class="mt-8" v-if="bookings.length > 0">
        <div v-for="booking in bookingsPage" class="bb-card-stylist p-4 flex justify-between items-center mb-4">
          <div class="">
            <p class="text-sm text-bb-gray-600">{{dayjs(booking.date).format('dddd D MMMM YYYY')}} / {{booking.start_hour_formatted}}</p>
            <p class="text-md text-bb-gray-800 mb-1">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
            <p class="text-xs text-bb-gray-600 flex items-center gap-1">
              <location-marker-icon class="h-3 w-3"></location-marker-icon>
              {{booking.store.name}} - {{booking.store.address}}
            </p>
            <p class="text-xs text-bb-gray-600 flex items-center gap-1">
              <users-icon class="h-3 w-3"></users-icon>
              {{ booking.guest_count + 1 }}
            </p>
          </div>
          <div>
            <div class="flex justify-start items-center gap-x-2">
              <button v-if="booking.canBeEdited" @click="goToEditBooking(booking.id)" type="button" class="icon-button !bg-bb-lightblue !rounded-full !text-bb-blue-500">
                <PencilIcon />
              </button>
              <button v-if="false" type="button" class="icon-button !bg-bb-lightblue !rounded-full !text-bb-blue-500" @click.prevent="">
                <TrashIcon />
              </button>
            </div>
          </div>
        </div>
        <div v-if="bookings.length > 10" class="flex justify-end gap-2 items-center">
          <bb-button :disabled="bookingsCurrentPage === 0" @click="bookingsCurrentPage--" class="px-2.5" primary light><arrow-left-icon class="w-4 h-4"></arrow-left-icon></bb-button>
          <bb-button :disabled="bookingsCurrentPage === Math.floor(bookings.length / 10)" @click="bookingsCurrentPage++" class="px-2.5" primary light><arrow-right-icon class="w-4 h-4"></arrow-right-icon></bb-button>
        </div>
      </div>
      <div v-else>
        <p class="text-bb-gray-800 text-md text-center mt-10">Non sono presenti appuntamenti</p>
      </div>
    </div>
  </CustomerLayout>
</template>