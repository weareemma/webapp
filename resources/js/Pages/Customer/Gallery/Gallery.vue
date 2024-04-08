<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import dayjs from "dayjs";
import BbTable from "@/Components/Bitboss/Table.vue";
import {onUpdated, ref, watch} from "vue";
import { LocationMarkerIcon, ArrowCircleRightIcon, ArrowLeftIcon, ArrowRightIcon, PencilIcon, TrashIcon } from "@heroicons/vue/outline";
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  bookings: null
})

// Photo modal
const photoModal = ref(null);
const photoUrl = ref(null);
function showModal (url)
{
  photoUrl.value = url;
  photoModal.value.open();
}
</script>

<template>
  <CustomerLayout title="Dashboard">
    <div class="mx-auto w-full">
      <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5">Gallery</h3>
      <p>
        Se lo vorrai dopo la tua visita in salone potrai caricare qui le foto delle tue pieghe preferite per aiutare i tuoi Stylist a identificare i tuoi gusti e e tue preferenze.
      </p>

      <div v-for="booking in bookings" class="mb-5">
        <h3 class="text-lg text-bb-gray-800">{{ dayjs(booking.start_date).format('DD MMM YYYY - H:mm')}}</h3>
        <div v-if="booking.photos.length > 0" class="flex justify-start items-center gap-4 flex-wrap">
          <div v-for="photo in booking.photos">
            <img v-if="photo.id > 0" @click="showModal(photo.original)" :key="photo.id" class="inline-block h-20 w-20 rounded-md object-fill" :src="photo.url" alt="" />
          </div>
        </div>
        <div v-else>
          <p class="text-sm text-bb-gray-400">Non ci sono foto per questo appuntamento</p>
        </div>
      </div>

      <bb-modal ref="photoModal" size="md" :withClose="true">
        <div class="bb-card p-0 overflow-hidden bg-white">
          <img class="object-cover" :src="photoUrl" />
        </div>
      </bb-modal>
    </div>
  </CustomerLayout>
</template>