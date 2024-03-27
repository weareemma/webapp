<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref, onMounted, onUpdated, reactive, computed} from "vue";
import {Inertia} from "@inertiajs/inertia";
import { ChevronLeftIcon, PencilIcon } from "@heroicons/vue/solid";
import BookingStatusBadge from '@/Pages/Booking/Partials/BookingStatusBadge.vue';
import dayjs from "dayjs";
import {useForm} from "@inertiajs/inertia-vue3";
import helpers from "../../../helpers";
import PhotoUploader from "@/Components/General/PhotoUploader.vue";
import _ from "lodash";

const props = defineProps({
  booking: Object,
  services: Object
});

onMounted(() => {
  helpers.lg(props.services);
  currentPhotos.value = props.booking.photos;
})

const takeChargeForm = useForm({
  booking_id: props.booking.id,
  stylist_notes: props.booking.stylist_notes,
  customer_notes: props.booking.customer.last_notes,
  uid: null
});
const confirmDialog = ref(null);
const endDialog = ref(null);

function takeCharge()
{
  takeChargeForm.post(route('stylist.booking.take'), {
    preserveScroll: true,
    onSuccess: (res) => {
      confirmDialog.value.close();
      helpers.flash(res.props.flash);
    }
  })
}

function endService()
{
  takeChargeForm
      .transform((data) => ({
        ...data,
        photos: photos.value
      }))
      .post(route('stylist.booking.end'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (res) => {
          endDialog.value.close();
          currentPhotos.value = props.booking.photos;
          helpers.flash(res.props.flash);
        }
      });
}

function updateUidTempMedia(data) {
  takeChargeForm.uid = data.uid;
}

const photosUploader = ref(null);
const currentPhotos = ref([]);
const photos = ref(null);

// Photo modal
const photoModal = ref(null);
const photoUrl = ref(null);
function showModal (url)
{
  photoUrl.value = url;
  photoModal.value.open();
}

// Edit booking
function goToEditBooking()
{
  Inertia.visit(route('stylist.booking.edit', props.booking.parent_id ?? props.booking.id));
}

function getExtraFromBooking()
{
  return props.booking.slots.filter((slot) => slot.service.extra);
}

function getNotExtraFromBooking()
{
  return props.booking.slots.filter((slot) => ! slot.service.extra);
}

const extraDialog = ref(null);
const extraServices = ref(null);
const openExtraModal = () => {
  extraServices.value = getExtraFromBooking().map((slot) => {return slot.service.id});
  extraDialog.value.open();
}

const addExtra = () => {
  Inertia.post(route('stylist.booking.addExtra', props.booking.id), {
    services: extraServices.value
  }, {
    onSuccess: (res) => {
      extraDialog.value.close();
      helpers.flash(res.props.flash);
    }
  })
}

function setNotShown()
{
  Inertia.post(route('booking.notShown', props.booking.id), {
    onFinish: () => {
      helpers.flash({
        type: 'success',
        message: 'Stato aggiornato'
      });
    }
  })
}

</script>

<template>
  <StylistLayout title="Appuntamento">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10">
      <div class="mx-auto max-w-3xl">

        <bb-link :href="route('stylist.dashboard')">
        <ChevronLeftIcon class="h-4" />
          Torna indietro
        </bb-link>

        <div class="bb-card-stylist px-8 py-7 mt-5 bg-[#C6E4E7] text-bb-gray-800">
          <p>{{ dayjs(booking.date).format('dddd D MMMM YYYY') }}</p>
          <p class="text-lg font-semibold">{{ booking.hour_formatted }}</p>
          <div class="flex justify-start gap-3 items-center mt-3 mb-5">
            <img
                class="h-8 w-8 rounded-full object-cover"
                :src="booking.customer.profile_photo_url.replace('3000', '')"
                alt=""
            />
            <a :href="route('stylist.customer.show', booking.customer.id)">
              <p class="font-semibold underline">
                {{ booking.customer.full_name }}
              </p>
            </a>
            <p>
              {{ (booking.parent_id !== null) ? ('Amica ' + booking.guest) : ''}}
            </p>
          </div>
          <div class="grid grid-cols-8 gap-x-2 gap-y-3 items-baseline">
            <p class="text-sm text-bb-gray-700 col-span-2 sm:col-span-1">SERVIZI</p>
            <p class="text-bb-gray-800 inline-block col-span-6 sm:col-span-7">{{getNotExtraFromBooking().map((slot) => {return slot.service.title}).join(' + ')}}</p>
            <p class="text-sm text-bb-gray-700 col-span-2 sm:col-span-1">EXTRA</p>
            <p class="text-bb-gray-800 col-span-6 sm:col-span-7 flex items-center gap-x-2">
              {{getExtraFromBooking().map((slot) => {return slot.service.title}).join(' + ')}}
              <bb-button primary class="p-1" @click="openExtraModal()">
                <PencilIcon class="w-3 h-3" />
              </bb-button>
            </p>
            <p class="text-sm text-bb-gray-700 col-span-2 sm:col-span-1">STATO</p>
            <div class="col-span-6 sm:col-span-7">
              <booking-status-badge :status="booking.status" :label="booking.status_formatted"></booking-status-badge>
            </div>
            <p class="text-sm text-bb-gray-700 col-span-2 sm:col-span-1">STORE</p>
            <p class="text-bb-gray-800 col-span-6 sm:col-span-7">{{ booking.store.name }}</p>
          </div>

          <div v-if="booking.status === helpers.booking_status_ended">
            <div class="relative">
              <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300" />
              </div>
              <div class="relative flex justify-center">
                <span class="bg-white px-2 text-sm text-gray-500 my-4"></span>
              </div>
            </div>

            <div class="flex justify-start items-center gap-4 flex-wrap">
              <div v-for="photo in booking.photos">
                <img v-if="photo.id > 0" @click="showModal(photo.original.replace('3000', ''))" :key="photo.id" class="inline-block h-20 w-20 rounded-md object-cover cursor-pointer" :src="photo.url.replace('3000', '')" alt="" />
              </div>
            </div>

            <bb-modal ref="photoModal" size="md" :withClose="true">
              <div class="bb-card p-0 overflow-hidden bg-white">
                <img class="object-cover" :src="photoUrl" />
              </div>
            </bb-modal>

            <div v-if="booking.stylist_notes" class="mt-4">
              <p class="text-sm text-bb-gray-700 col-span-1">NOTE APPUNTAMENTO</p>
              <p class="text-bb-gray-800 inline-block col-span-7">{{booking.stylist_notes}}</p>
            </div>

            <div v-if="booking.customer?.last_notes" class="mt-4">
              <p class="text-sm text-bb-gray-700 col-span-1">NOTE CLIENTE</p>
              <p class="text-bb-gray-800 inline-block col-span-7 m-0">{{booking.customer?.last_notes}}</p>
              <p class="text-bb-gray-600 m-0 text-xs col-span-7">
                Scritte da {{booking.customer?.last_notes_by?.name}} {{booking.customer?.last_notes_by?.surname}} il {{dayjs(booking.customer?.last_notes_updated_at).format('DD/MM/YYYY')}}
              </p>
            </div>
          </div>


          <div class="flex justify-end mt-6 gap-2">
            <bb-button v-if="booking.status === helpers.booking_status_not_executed" primary light @click="setNotShown">Non presentato</bb-button>
            <bb-button v-if="!booking.multiple && booking.status !== helpers.booking_status_ended && booking.status !== helpers.booking_status_not_shown" primary light @click="goToEditBooking">Modifica</bb-button>
            <bb-button v-if="booking.status === helpers.booking_status_todo || booking.status === helpers.booking_status_not_executed" primary @click="confirmDialog.open()">Inizia servizio</bb-button>
            <bb-button v-if="booking.status === helpers.booking_status_progress" primary @click="endDialog.open()">Termina servizio</bb-button>
            <bb-button v-if="booking.status === helpers.booking_status_ended || booking.status === helpers.booking_status_not_shown" primary light @click="endDialog.open()">Modifica resoconto</bb-button>
          </div>
        </div>

        <BbDialog ref="confirmDialog" type="plain" size="sm" :no-cancel="true">
          <template #title> Inizia servizio </template>

          <span>Confermi che il servizio sta iniziando?</span>

          <template #buttons>
            <BbButton secondary light @click="confirmDialog.close()">
              Annulla
            </BbButton>
            <BbButton primary @click="takeCharge">
              Conferma
            </BbButton>
          </template>
        </BbDialog>



        <BbDialog ref="endDialog" type="plain" size="md" :no-cancel="true">
          <template #title >
            <h3 class="text-bb-blue-500">Il servizio è terminato?</h3>
          </template>

          <p class="text-bb-gray-800">
            Confermi che il servizio è terminato?
          </p>
          <p class="text-bb-gray-800">
            Carica una foto del risultato finale e inserisci eventuali note utili sul cliente e sul lavoro svolto.
          </p>

          <div class="mt-5">
            <div class="mb-4">
              <bb-label class="mb-0 text-bb-gray-800">
                Foto
              </bb-label>
              <bb-label class="mb-1 text-sm text-bb-gray-600">
                Carica una o più foto del risultato finale.
              </bb-label>
              <photo-uploader
                  ref="photosUploader"
                  :photos="currentPhotos"
                  @changed="(t) => photos = t"
              >
              </photo-uploader>
            </div>
            <div class="mb-4">
              <bb-label class="mb-0 text-bb-gray-800">
                Note sul cliente
              </bb-label>
              <bb-label class="mb-1 text-sm text-bb-gray-600">
                Queste note compariranno nelle info generali del cliente.
              </bb-label>
              <bb-textarea
                  class="min-h-[180px]"
                  type="text"
                  placeholder="Come sono i capelli del cliente? Qualche peculiarità? Vuoi dare qualche consiglio su come trattarli al meglio?"
                  v-model="takeChargeForm.customer_notes"
              ></bb-textarea>
            </div>
            <div>
              <bb-label class="mb-1">Note sull'appuntamento</bb-label>
              <bb-textarea
                  class="min-h-[180px]"
                  type="text"
                  placeholder="Come è andato l’appuntamento? Tutti i trattamenti richiesti sono stati completati come da procedura? Il cliente è sembrato soddisfatto?"
                  v-model="takeChargeForm.stylist_notes"
              ></bb-textarea>
            </div>
          </div>

          <template #buttons>
            <BbButton secondary light @click="endDialog.close()">
              Annulla
            </BbButton>
            <BbButton primary @click="endService">
              Conferma
            </BbButton>
          </template>
        </BbDialog>

        <BbDialog ref="extraDialog" type="plain" size="md" :no-cancel="true">
          <template #title >
            <h3 class="text-bb-blue-500">Aggiungi dei servizi extra</h3>
          </template>

          <bb-select
              mode="tags"
              placeholder="Seleziona uno o più servizi"
              :close-on-select="false"
              :options="services"
              v-model="extraServices"
          ></bb-select>

          <template #buttons>
            <BbButton secondary light @click="extraDialog.close()">
              Annulla
            </BbButton>
            <BbButton primary @click="addExtra()">
              Salva
            </BbButton>
          </template>
        </BbDialog>

      </div>
    </div>
  </StylistLayout>
</template>