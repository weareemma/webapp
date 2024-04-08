<template>
  <StylistLayout title="Booking show" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8 mb-8">
      <!-- title -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex items-center space-x-4">
          <h1 class="text-bb-blue-500 big-header-title">
            Appuntamento di {{ booking.customer?.name }} del
            {{ dayjs(booking.date).format("DD/MM/YYYY") }}
          </h1>
          <BookingStatusLight :status="booking.status" />
        </div>
        <div class="flex items-center space-x-4">
          <bb-button v-if="can()" light @click="openDeleteDialog">Cancella</bb-button>
          <bb-button v-if="can()" @click="goTo('booking.edit')">Modifica</bb-button>
        </div>
      </div>

      <!-- tab bar -->
      <div class="bb-tab-bar">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            class="bb-tab-bar__button"
            :class="{
            'bb-tab-bar__button--active': tab.key == activeTab,
          }"
            @click="activeTab = tab.key"
        >
          {{ tab.title }}
        </button>
      </div>

      <!-- info tab -->
      <div v-if="activeTab == 'info'">
        <!-- info -->
        <div class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Informazioni</h2>

          <!-- customer -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs pt-[5px]">
              Cliente
            </div>
            <div class="flex-1 flex items-center space-x-2">
              <img
                  :src="booking.customer?.profile_photo_url"
                  class="block rounded-full w-8 h-8 object-cover object-center"
              />
              <div>
                <a class="underline" v-if="can()" :href="route('customer.show', booking.customer)">{{
                    booking.customer?.full_name
                  }}</a>
                <p v-else>{{
                    booking.customer?.full_name
                  }}</p>
              </div>
            </div>
          </div>

          <!-- time -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs pt-[5px]">
              Orario
            </div>
            <div class="flex-1">
              {{ dayjs(`2000-01-01 ${booking.start}`).format("HH:mm") }} -
              {{
                dayjs(`2000-01-01 ${booking.start}`)
                    .add(booking.total_execution_time, "minutes")
                    .format("HH:mm")
              }}
            </div>
          </div>

          <!-- stylist -->
          <div class="flex items-center" v-if="can()">
            <div class="uppercase text-bb-gray-700 w-36 text-xs pt-[5px]">
              Stylist
            </div>
            <div class="flex-1">
              <BbSelect
                  mode="single"
                  placeholder="Seleziona lo stylist"
                  :close-on-select="true"
                  :options="stylists"
                  v-model="stylist"
                  @change="updateStylist"
              >
              </BbSelect>
            </div>
          </div>

          <!-- status -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs">Status</div>
            <div class="flex-1">
              <StatusBadge :status="booking.status" />
            </div>
          </div>

          <!-- applied -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs">Applicato</div>
            <div class="flex-1">
              <span v-if="!subscription && !packages?.length && !discount">
                Nessuno sconto applicato
              </span>
              <span v-if="subscription"
              >Abbonamento {{ subscription.name }}</span
              >
              <span v-if="packages?.length"
              >, {{ packages.map((p) => `Pacchetto ${p.name}`).join(", ") }}
              </span>
              <span v-if="discount">, Codice sconto {{ discount.code }}</span>
            </div>
          </div>
        </div>

        <!-- services -->
        <div class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Servizi prenotati</h2>

          <bb-table
              :collection="booking.slots"
              :columns="[
              {
                key: 'service.title',
                label: 'Servizio',
                classes: 'font-bold',
              },
              {
                key: 'service.net_price',
                label: 'Prezzo IVA inclusa',
                format: 'currency',
              },

              // empty column to shrink left other cols
              {
                computed: () => null,
                label: '',
                classes: 'w-full',
              },
            ]"
          />
        </div>

        <!-- total -->
        <div class="flex justify-end space-x-12">
          <!-- amount -->
          <div class="flex items-center space-x-3">
            <div class="uppercase">totale</div>
            <div
                v-if="
                booking.additional_data?.original_net_price >
                booking.additional_data?.actual_net_price
              "
                class="line-through text-bb-gray-500"
            >
              {{ booking.additional_data?.original_net_price }}€
            </div>
            <div class="font-bold text-bb-blue-600">
              {{ booking.additional_data?.actual_net_price }}€
            </div>
          </div>

          <!-- paid -->
          <div class="flex items-center space-x-3">
            <div class="uppercase">pagato</div>
            <div
                class="font-bold"
                :class="{
                'text-bb-blue-600': booking.amount_to_pay <= 0,
                'text-bb-danger': booking.amount_to_pay > 0,
              }"
            >
              {{ booking.paid_amount }} €
            </div>
          </div>
        </div>

        <!-- payments -->
        <div class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Transazioni</h2>

          <bb-table
              :collection="payments"
              :columns="[
              {
                key: 'date',
                label: 'Data',
                format: 'date:Y-m-d',
              },
              {
                computed: (item) => {
                  switch (item.subject) {
                    case 'booking-create':
                      return 'Nuovo appuntamento';
                    case 'booking-edit':
                      return 'Modifica appuntamento';
                    case 'package':
                      return 'Acquisto pacchetto';
                    case 'subscription':
                      return 'Acquisto abbonamento';
                  }
                },
                label: 'Oggetto',
              },
              {
                computed: (item) => {
                  switch (item.method) {
                    case 'stripe':
                      return 'Stripe';
                    case 'cash':
                      return 'Pagamento in contanti';
                    case 'satispay':
                      return 'Pagamento con Satispay';
                    case 'card':
                      return 'Pagamento in carta';
                  }
                },
                label: 'Metodo di pagamento',
              },
              {
                key: 'total',
                label: 'Totale',
                format: 'currency',
              },
            ]"
              :actions="['download']"
              @action="handleTableActions"
          />
        </div>

        <!-- refunds -->
        <div class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Rimborsi</h2>

          <bb-table
              :collection="refunds"
              :columns="[
              {
                key: 'created_at',
                label: 'Data',
                format: 'date',
              },
              {
                key: 'total',
                label: 'Totale',
                format: 'currency',
              },
            ]"
          />
        </div>
      </div>

      <!-- store payments tab -->
      <div v-if="activeTab == 'store-payments'">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-medium text-bb-primary">
            Pagamenti in store
          </h2>
          <bb-button v-if="can()" @click="addStorePayment">Aggiungi</bb-button>
        </div>

        <bb-table
            :collection="storePayments"
            :columns="[
            {
              key: 'date',
              label: 'Data',
              format: 'date:Y-m-d',
            },
            {
              computed: (item) => {
                switch (item.method) {
                  case 'stripe':
                    return 'Stripe';
                  case 'cash':
                    return 'Pagamento in contanti';
                  case 'satispay':
                    return 'Pagamento con Satispay';
                  case 'card':
                    return 'Pagamento in carta';
                }
              },
              label: 'Metodo di pagamento',
            },
            {
              key: 'total',
              label: 'Totale',
              format: 'currency',
            },
          ]"
            :actions="['delete']"
            @action="handleStorePaymentsTableActions"
        />
      </div>

      <!-- form tab -->
      <div v-if="activeTab == 'form'">TODO</div>
    </div>
  </StylistLayout>

  <BbDialog ref="deleteDialog" type="plain" size="sm">
    <template #title> Elimina appuntamento </template>

    <span>Una volta eliminato, non potrai più recuperare le informazioni.</span>

    <template #buttons>
      <BbButton danger :disabled="itemForm.processing" @click="deleteItem">
        Elimina
      </BbButton>
    </template>
  </BbDialog>

  <BbDialog ref="storePaymentDeleteDialog" type="plain" size="sm">
    <template #title> Elimina pagamento </template>

    <span>Una volta eliminato, non potrai più recuperare le informazioni.</span>

    <template #buttons>
      <BbButton
          danger
          :disabled="paymentForm.processing"
          @click="deletePayment"
      >
        Elimina
      </BbButton>
    </template>
  </BbDialog>

  <BookingPaymentDialog
      :booking-id="booking.id"
      v-model="openBookingPaymentDialog"
  />
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import dayjs from "dayjs";
import StylistLayout from "@/Layouts/StylistLayout.vue";
import BookingStatusLight from "@/Pages/Booking/Partials/BookingStatusLight.vue";
import StatusBadge from "@/Pages/Schedules/Partials/ScheduleAppointmentStatusBadge.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import { Inertia } from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import BookingPaymentDialog from "@/Pages/Payments/Partials/BookingPaymentDialog.vue";

const props = defineProps({
  booking: Object,
  payments: Array,
  storePayments: Array,
  refunds: Array,
});

// Role
const role = ref(usePage().props.value.role);
function can()
{
  helpers.lg(role.value === helpers.role_admin);
  return role.value === helpers.role_admin;
}

// Stylist
const stylist = ref(null);
const stylists = ref([]);
onMounted(() => {
  stylist.value = props.booking.stylist_id;
  if (can()) loadStylists();
});

function loadStylists()
{
  axios.get(route("booking.stylists", props.booking.id)).then((res) => {
    helpers.lg(res.data);
    stylists.value = res.data;
  });
}
function updateStylist(stylist_id)
{
  Inertia.post(route("booking.update.stylist", props.booking.id), {
    stylist_id: stylist_id
  }, {
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
    }
  })
}

// tabs
const activeTab = ref("info");
const tabs = [
  { key: "info", title: "Info" },
  { key: "store-payments", title: "Pagamenti in store" },
  { key: "form", title: "Scheda" },
];

// subscription
const subscription = computed(
    () => props.booking.additional_data?.subscription
);

// packages
const packages = computed(() => props.booking.additional_data?.packages ?? []);

// discount
const discount = computed(() => props.booking.additional_data?.discount);

// store payments
const storePaymentDeleteDialog = ref(null);
const openBookingPaymentDialog = ref(false);
const paymentForm = ref(null);

function addStorePayment() {
  openBookingPaymentDialog.value = true;
}

function handleStorePaymentsTableActions({ action, item }) {
  switch (action) {
    case "delete":
      paymentForm.value = useForm(item);
      storePaymentDeleteDialog.value.open();
      break;
  }
}

function deletePayment() {
  paymentForm.value.delete(route("payment.destroy", paymentForm.value.id), {
    onSuccess: () => {
      storePaymentDeleteDialog.value.close();
    },
  });
}

// delete
const itemForm = ref(null);
const deleteDialog = ref(null);

function openDeleteDialog() {
  itemForm.value = useForm(props.booking);
  deleteDialog.value.open();
}

function deleteItem() {
  itemForm.value
      .transform((f) => ({ ...f, redirect_to: "schedule.appointment.index" }))
      .delete(route("booking.destroy", itemForm.value.id));
}

// handle table actions
function handleTableActions({ action, item }) {
  switch (action) {
    case "download":
      console.log(item);
      break;
  }
}

// go to
function goTo(routeName) {
  Inertia.visit(route(routeName, props.booking.id));
}
</script>
