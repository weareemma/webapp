<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref, onMounted, computed, watch} from "vue";
import {Inertia} from "@inertiajs/inertia";
import { ChevronLeftIcon } from "@heroicons/vue/solid";
import { LocationMarkerIcon, ArrowCircleRightIcon, ArrowLeftIcon, ArrowRightIcon } from "@heroicons/vue/outline";
import BackLink from "@/Components/Bitboss/BackLink.vue";
import dayjs from "dayjs";

const props = defineProps({
  customer: Object,
  packages: Object,
  bookings: Object,
  nextBookings: Object,
  subscription: Object,
  bookingCount: Number
});

onMounted(() => {
  helpers.lg(props.customer);
  helpers.lg(props.packages);
  helpers.lg(props.bookings);
  helpers.lg(props.subscription);
})

// Tabs
const tabs = [
  { name: 'Info' },
  { name: 'App. Passati' },
  { name: 'App. Futuri' },
  { name: 'Gallery' },
];
const currentTab = ref(tabs[0].name);
function changeTab(name)
{
  currentTab.value = name;
}

// Packages
const packagesName = computed(()  => {
  let list = _.map(props.packages, (pack) => {
    return pack.name;
  });

  return list.join('; ');
});

// Bookings pagination
const bookingsCurrentPage = ref(0);
const bookingsPage = ref(props.bookings.slice(bookingsCurrentPage * 10, 10))
watch(bookingsCurrentPage, (n, o) => {
  bookingsPage.value = props.bookings.slice(n * 10, (n * 10) + 10)
})

const nextBookingsCurrentPage = ref(0);
const nextBookingsPage = ref(props.nextBookings.slice(nextBookingsCurrentPage * 10, 10))
watch(nextBookingsCurrentPage, (n, o) => {
  nextBookingsPage.value = props.nextBookings.slice(n * 10, (n * 10) + 10)
})

// Photos
const photoModal = ref(null);
const photoUrl = ref(null);
function showModal (url)
{
  photoUrl.value = url;
  photoModal.value.open();
}


</script>

<template>
  <StylistLayout title="Cliente">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10">
      <div class="mx-auto max-w-3xl">

        <back-link></back-link>

        <h3 class="text-lg text-bb-gray-800 font-bold my-3">Scheda di {{ customer.full_name }}</h3>

        <div>
          <div class="">
            <label for="tabs" class="sr-only"></label>
          </div>
          <div class="block">
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-8 cursor-pointer" aria-label="Tabs">
                <p v-for="tab in tabs" @click="changeTab(tab.name)" :key="tab.name" :class="[currentTab === tab.name ? 'border-bb-blue-500 text-bb-blue-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm']" :aria-current="currentTab === tab.name ? 'page' : undefined">{{ tab.name }}</p>
              </nav>
            </div>
          </div>
        </div>

        <div v-show="currentTab === tabs[0].name" class="py-6">
          <div>
            <img class="inline-block h-24 w-24 rounded-full object-cover mb-8" :src="customer.profile_photo_url" alt="" />
            <div class="grid grid-cols-5 gap-x-2 gap-y-3 items-baseline">
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">ATTIVO DAL</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">{{dayjs(customer.created_at).format('DD/MM/YYYY')}}</p>
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">APP. COMPLESSIVI</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">{{bookingCount}}</p>
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">ULTIMO APP.</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">{{(customer.last) ? dayjs(customer.last?.date).format('DD/MM/YYYY') : 'Mai'}}</p>
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">ABBONAMENTO</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">{{ (subscription) ? subscription.name : 'Nessun abbonamento attivo' }}</p>
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">PACCHETTI</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">{{ (packagesName.length > 0) ? packagesName : 'Nessun pacchetto attivo' }}</p>
              <p class="text-sm text-bb-gray-700 sm:col-span-1 col-span-2">CODICE SCONTO</p>
              <p class="text-bb-gray-800 inline-block sm:col-span-4 col-span-3">Nessun codice sconto</p>
            </div>
          </div>

          <div v-if="customer.last_notes" class="bb-card-stylist p-5 bg-white mt-8">
            <p class="text-sm text-bb-gray-700">NOTE SUL CLIENTE</p>
            <p class="text-bb-gray-800 inline-block">{{customer.last_notes}}</p>

          </div>

          <div class="bb-card-stylist p-5 bg-white mt-8">
            <p class="text-sm text-bb-gray-700">NOTE SUGLI APPUNTAMENTI</p>
            <div v-for="b in bookingsPage">
              <p class="text-xs text-bb-gray-600 mt-3">Appuntamento del {{dayjs(b.start_date).format('DD/MM/YYYY HH:mm')}}</p>
              <p v-if="b.stylist_notes" class="text-bb-gray-800 inline-block">{{b.stylist_notes}}</p>
              <p v-else class="text-bb-gray-800 inline-block italic text-xs">Nessuna nota</p>
            </div>
          </div>
        </div>

        <div v-show="currentTab === tabs[1].name" class="py-6">
          <div v-for="booking in bookingsPage" class="bb-card-stylist p-4 flex justify-between items-center mb-4">
            <div class="">
              <p class="text-sm text-bb-gray-600">{{dayjs(booking.date).format('dddd D MMMM YYYY')}} {{booking.hour_formatted}}</p>
              <p class="text-md text-bb-gray-800 mb-1">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
              <p class="text-xs text-bb-gray-600 flex items-center gap-1">
                <location-marker-icon class="h-3 w-3"></location-marker-icon>
                {{booking.store.name}}
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

        <div v-show="currentTab === tabs[2].name" class="py-6">
          <div v-for="booking in nextBookingsPage" class="bb-card-stylist p-4 flex justify-between items-center mb-4">
            <div class="">
              <p class="text-sm text-bb-gray-600">{{dayjs(booking.date).format('dddd D MMMM YYYY')}} {{booking.hour_formatted}}</p>
              <p class="text-md text-bb-gray-800 mb-1">{{booking.slots.map((slot) => {return slot.service.title}).join(' + ')}}</p>
              <p class="text-xs text-bb-gray-600 flex items-center gap-1">
                <location-marker-icon class="h-3 w-3"></location-marker-icon>
                {{booking.store.name}}
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

        <div v-show="currentTab === tabs[3].name" class="py-6">
          <div v-for="booking in bookings">
            <p class="text-sm text-bb-gray-700">{{dayjs(booking.date).format('DD/MM/YYYY')}}</p>
            <div class="flex justify-start flex-wrap items-center gap-4 my-3">
              <div v-if="booking.photos.length > 0" class="flex justify-start flex-wrap items-center gap-4">
                <img @click="showModal(photo.original)" v-for="(photo) in booking.photos" :key="photo.id" class="inline-block h-24 w-24 rounded-md cursor-pointer" :src="photo.url.replace(':3000', '')" alt="" />
              </div>
              <div v-else>
                <p class="text-bb-gray-800 text-sm">Nessuna foto per questo appuntamento</p>
              </div>
            </div>
          </div>

          <bb-modal ref="photoModal" size="md" :withClose="true">
            <div class="bb-card p-0 overflow-hidden bg-white">
              <img class="object-cover" :src="photoUrl" />
            </div>
          </bb-modal>
        </div>

      </div>
    </div>

  </StylistLayout>
</template>