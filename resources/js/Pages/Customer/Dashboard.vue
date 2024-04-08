<script setup>
import CustomerLayout from "@/Layouts/CustomerLayout.vue";
import BookingList from "@/Components/Customer/BookingList.vue";
import { ref, computed, onMounted } from "vue";
import { usePage, Link } from "@inertiajs/inertia-vue3";
import { XIcon, PlusIcon } from "@heroicons/vue/solid";
import { PencilIcon, TrashIcon, UsersIcon } from "@heroicons/vue/outline";
import dayjs from "dayjs";
import PhotoUploader from "@/Components/General/PhotoUploader.vue";
import { Inertia } from "@inertiajs/inertia";
import helpers from "../../helpers";

const customer = ref(usePage().props.value.customer.user);
const customerDiscount = ref(usePage().props.value.customer.customer_discount);
const promoName = ref(usePage().props.value.promo_name);
const promoExpires = ref(usePage().props.value.promo_expires);
const impersonate = computed(() => usePage().props.value.impersonate);
const bookingLocked = computed(() => {
  if (impersonate.value) return false;
  return usePage().props.value.booking_locked;
});

onMounted(() => {
  helpers.lg(currentPackages.value);
  if (lastBooking.value !== null) {
    currentPhotos.value = lastBooking.value.photos;
  }
});

const props = defineProps({
  lastPhotos: { type: Array, default: null },
  stripeSubscription: {type: Object, default: null}
});

// Pacakage
const currentPackages = ref(usePage().props.value.customer.packages);

// Subscription
const subscription = ref(usePage().props.value.customer.subscription);
const subscriptionEndsAt = ref(
  usePage().props.value.customer.subscription_ends_at
);

// Next booking
const nextBooking = ref(usePage().props.value.customer.next_booking);
const canEditNextBooking = ref(
  usePage().props.value.customer.can_edit_next_booking
);

// Last booking
const lastBooking = ref(usePage().props.value.customer.last_booking);
const lastBookingShow = ref(lastBooking.value !== null);
function closeLastBookingCard() {
  lastBookingShow.value = false;
}
const lastBookingModal = ref(null);
const photosUploader = ref(null);
const currentPhotos = ref([]);
const photos = ref(null);
function showLastBookingModal() {
  lastBookingModal.value.open();
}
function savePhotos() {
  helpers.lg(lastBooking.value.id);
  Inertia.post(
    route("customer.booking.uploadPhotos", lastBooking.value.id),
    {
      photos: photos.value,
    },
    {
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
        lastBookingModal.value.close();
        currentPhotos.value =
          usePage().props.value.customer.last_booking.photos;
      },
    }
  );
}

function goToBookings() {
  Inertia.visit(route("customer.bookings.future"));
}

function goToSubscriptions() {
  Inertia.visit(route("plan.detail"));
}

function goToEditBooking() {
  Inertia.visit(route("booking.edit", nextBooking.value));
}

function goToGallery() {
  Inertia.visit(route("customer.gallery"));
}

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);
function deleteBookingModal()
{
  deleteDialog.value.open();
}
function deleteBooking()
{
  Inertia.delete(route('booking.destroy', nextBooking.value.id), {
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
      nextBooking.value = usePage().props.value.customer.next_booking;
      deleteDialog.value.close();
    }
  })
}
</script>

<template>
  <CustomerLayout title="Dashboard">
    <!-- hero -->
    <div class="block sm:hidden relative -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 h-64">
      <Link
        :href="route('booking.wizard')"
        class="absolute top-4 right-4 bb-button-rounded-primary py-2 px-4 shadow-md"
      >
<!--        <PlusIcon class="w-5 h-5" />-->
        Prenota
      </Link>
      <img src="/img/mobile-hero-2.jpeg" class="h-full w-full object-cover object-center" />
    </div>

    <!-- content -->
    <div
      class="flex justify-between items-start py-7 gap-x-4 flex-wrap gap-y-4"
    >
      <div>
        <h2 class="text-xl font-bold text-bb-black">
          Ciao {{ customer.name }}!
        </h2>
        <p class="mt-2 text-sm">
          Benvenuto nella tua dashboard
        </p>
      </div>
      <div>
        <span
          v-if="subscription"
          class="items-center rounded-full bg-bb-green-300 px-8 py-2 text-sm font-bold text-bb-blue-500 text-center hidden sm:block"
        >
          {{ subscription.name }}
        </span>
      </div>
    </div>

    <div class="flex flex-col justify-start iems-start mt-4">
      <!-- Disocunt code-->
      <div v-if="subscription && customerDiscount && customerDiscount.available" class="bb-card bg-bb-lightblue w-full py-7 px-8 mb-10 shadow-lg">
        <div class="flex justify-between gap-1 flex-wrap">
          <p>Usa questo codice sconto per invitare un altro utente</p>
          <p class="text-xl font-bold text-bb-aqua-500">{{ customerDiscount.code }}</p>
        </div>
      </div>

      <!-- Last booking -->
      <div
        v-if="lastBookingShow"
        class="bb-card bg-bb-lightblue w-full py-7 px-8 mb-10"
      >
        <div class="flex justify-between items-start gap-x-2">
          <div>
            <p class="font-bold text-bb-blue-500">
              Come è andato l'appuntamento del
              {{ dayjs(lastBooking.start_date).format("D MMMM YYYY") }} alle ore
              {{ dayjs(lastBooking.start_date).format("H:mm") }}?
            </p>
            <p class="text-sm text-bb-gray-800 my-3">
              Carica una foto del risultato e dicci come è andata!
            </p>
            <bb-button @click="showLastBookingModal" light class="mt-4"
              >Carica le foto</bb-button
            >
          </div>
          <div>
            <XIcon
              @click="closeLastBookingCard"
              class="w-4 h-4 text-white cursor-pointer"
            />
          </div>
        </div>
      </div>

      <!-- Next booking -->
      <div v-if="! bookingLocked" class="bb-card py-7 px-8 shadow-lg rounded-xl">
        <h3 class="text-xl font-bold text-bb-blue-500">
          Prossimo appuntamento
        </h3>
        <div v-if="nextBooking" class="flex justify-between items-start mb-6">
          <div>
            <p class="mt-6 font-semibold text-sm">
              {{ dayjs(nextBooking.start_date).format("D MMMM YYYY") }} ore
              {{ dayjs(nextBooking.start_date).format("H:mm") }}
            </p>
            <div class="flex justify-start gap-2 items-center mt-5">
              <svg
                width="26"
                height="26"
                viewBox="0 0 26 26"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M13.0026 12.9994C11.8109 12.9994 10.8359 12.0244 10.8359 10.8327C10.8359 9.64102 11.8109 8.66602 13.0026 8.66602C14.1943 8.66602 15.1693 9.64102 15.1693 10.8327C15.1693 12.0244 14.1943 12.9994 13.0026 12.9994ZM19.5026 11.0493C19.5026 7.11685 16.6318 4.33268 13.0026 4.33268C9.37344 4.33268 6.5026 7.11685 6.5026 11.0493C6.5026 13.5844 8.6151 16.9427 13.0026 20.951C17.3901 16.9427 19.5026 13.5844 19.5026 11.0493ZM13.0026 2.16602C17.5526 2.16602 21.6693 5.65435 21.6693 11.0493C21.6693 14.646 18.7768 18.9035 13.0026 23.8327C7.22844 18.9035 4.33594 14.646 4.33594 11.0493C4.33594 5.65435 8.4526 2.16602 13.0026 2.16602Z"
                  fill="#99A7DA"
                />
              </svg>
              <p class="text-sm">{{ nextBooking.store.name }} - {{nextBooking.store.address}}</p>
            </div>
            <div class="flex justify-start gap-2 items-center mt-4">
              <svg
                width="26"
                height="26"
                viewBox="0 0 26 26"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M5.9585 5.49048C5.9585 5.44769 5.99045 5.41139 6.0327 5.40598L17.3779 3.94239C20.2271 3.57406 22.7502 5.79327 22.7502 8.66573C22.7502 11.5382 20.2265 13.7569 17.3779 13.3891L6.0327 11.9255C6.01217 11.9228 5.9933 11.9128 5.97964 11.8972C5.96598 11.8817 5.95846 11.8617 5.9585 11.841V5.49048Z"
                  stroke="#99A7DA"
                />
                <path
                  d="M5.95841 5.41667L2.16675 3.25V14.0833L5.95841 11.9167"
                  stroke="#99A7DA"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M20.5829 12.459L16.9569 21.0704C16.7472 21.5684 16.3952 21.9934 15.9451 22.2923C15.4949 22.5912 14.9666 22.7506 14.4263 22.7507C12.4627 22.7507 11.134 20.7492 11.8956 18.9395L14.6245 12.459"
                  stroke="#99A7DA"
                />
                <path
                  d="M18.9585 13.541C21.6509 13.541 23.8335 11.3584 23.8335 8.66602C23.8335 5.97363 21.6509 3.79102 18.9585 3.79102C16.2661 3.79102 14.0835 5.97363 14.0835 8.66602C14.0835 11.3584 16.2661 13.541 18.9585 13.541Z"
                  stroke="#99A7DA"
                />
              </svg>
              <p class="text-sm">{{ helpers.printServices(nextBooking) }}</p>
            </div>
            <div class="flex justify-start gap-2 items-center mt-4">
              <UsersIcon class="w-6 h-6 stroke-[#99A7DA]" />
              <p class="text-sm">{{ nextBooking.guest_count + 1 }}</p>
            </div>
          </div>
          <div class="flex justify-start items-center gap-x-2">
            <button
              v-if="canEditNextBooking"
              @click="goToEditBooking"
              type="button"
              class="icon-button !bg-bb-lightblue !rounded-full !text-bb-blue-500"
            >
              <PencilIcon />
            </button>
            <button
              v-if="canEditNextBooking"
              type="button"
              class="icon-button !bg-bb-lightblue !rounded-full !text-bb-blue-500"
              @click.prevent="deleteBookingModal"
            >
              <TrashIcon />
            </button>
          </div>
        </div>
        <div class="my-2" v-else>
          <p>Nessun appuntamento programmato.</p>
          <p>Prenotalo subito.</p>
          <Link :href="route('booking.wizard')">
            <bb-button type="button" class="w-full sm:w-auto mt-6 mb-4"
              >Prenota</bb-button
            >
          </Link>
        </div>
        <bb-button
          light
          outline
          class="w-full sm:w-auto"
          @click.prevent="goToBookings"
          >Vai agli appuntamenti</bb-button
        >
      </div>
      <div v-else class="bb-card py-7 px-8 shadow-lg rounded-xl">
        <h3 class="text-xl font-bold text-bb-blue-500">
          A breve apriremo il nostro store!
        </h3>
        <div class="my-2">
          <p>Ti avviseremo quando sarà possibile effettuare una prenotazione.</p>
        </div>
      </div>

      <!-- Subscription -->
      <div
        v-if="subscription || currentPackages.length > 0"
        class="bb-card py-7 px-8 shadow-lg rounded-xl mt-10 customer-subscription-card"
      >
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-bb-aqua-500">
              Abbonamento e pacchetti
            </h3>
            <p v-if="subscription" class="font-bold mt-4 text-sm">
              {{ subscription.name }}
            </p>
            <p v-else class="font-bold mt-4 text-sm">
              Nessun abbonamento attivo
            </p>
            <template v-if="subscription">
              <template v-if="subscription.name !== promoName">
                <p v-if="stripeSubscription && ! subscriptionEndsAt" class="text-sm mt-1">
                  Prossimo rinnovo il {{ dayjs.unix(stripeSubscription.current_period_end).format("D/M/YYYY") }}
                </p>
                <p v-if="subscriptionEndsAt" class="text-sm mt-1">
                  Scade il {{ dayjs(subscriptionEndsAt).format("D/M/YYYY") }}
                </p>
              </template>
              <template v-else>
                <p class="text-sm mt-1">
                  Scade il {{ dayjs(promoExpires).format("D/M/YYYY") }}
                </p>
              </template>
            </template>
            <div class="bg-white h-[1px] w-3/4 my-3"></div>
            <div v-for="pack in currentPackages" :key="pack.id" class="mb-2">
              <p class="font-bold mt-4 text-sm">{{ pack.name }}</p>
              <div>
                <p class="text-sm">Hai ancora a disposizione:</p>
                <p v-for="service in pack.services_formatted" class="text-sm">{{service}}</p>
              </div>
            </div>
            <bb-button
              light
              outline
              class="mt-8 !bg-bb-lightblue hover:border-white"
              @click.prevent="goToSubscriptions"
              >Dettagli</bb-button
            >
          </div>
          <div class="customer-girl-img-card"></div>
        </div>
      </div>

      <!-- Gallery -->
      <div
        v-if="lastPhotos.length > 0"
        class="bb-card py-7 px-8 shadow-lg rounded-xl mt-10"
      >
        <h3 class="text-xl font-bold text-bb-blue-500">Le tue immagini</h3>
        <div class="flex justify-start items-center gap-4 flex-wrap">
          <div v-for="photo in lastPhotos" :key="photo.id">
            <img
              v-if="photo.id > 0"
              class="inline-block h-20 w-20 rounded-md object-fill"
              :src="photo.url"
              alt=""
            />
          </div>
        </div>
        <bb-button light outline class="mt-8" @click.prevent="goToGallery"
          >Vai alla gallery</bb-button
        >
      </div>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Cancella prenotazione </template>

      <span>
        Sei sicuro di voler cancellare la prenotazione?
      </span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteBooking">
          Cancella
        </BbButton>
      </template>
    </BbDialog>

    <BbDialog ref="lastBookingModal" type="plain" size="md" :no-cancel="true">
      <template #title>
        <h3 class="text-bb-blue-500">Ultimo appuntamento</h3>
      </template>

      <div class="mt-5">
        <div class="mb-4">
          <bb-label class="mb-4 text-sm text-bb-gray-600">
            Carica una o più foto del risultato finale.
          </bb-label>
          <photo-uploader
            ref="photosUploader"
            :photos="currentPhotos"
            @changed="(t) => (photos = t)"
          >
          </photo-uploader>
        </div>
      </div>

      <template #buttons>
        <BbButton secondary light @click="lastBookingModal.close()">
          Annulla
        </BbButton>
        <BbButton primary @click="savePhotos"> Salva </BbButton>
      </template>
    </BbDialog>
  </CustomerLayout>
</template>
