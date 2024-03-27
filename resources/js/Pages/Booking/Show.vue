<template>
  <AppLayout title="Booking show" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link :url="route('schedule.appointment.past')"></bb-back-link>
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
        <div class="flex items-center space-x-4" v-if="(! booking.is_past) && (booking.status !== 'cancelled')">
          <bb-button v-if="booking.is_father" light @click="openDeleteDialog">Cancella</bb-button>
          <bb-button @click="goTo('booking.edit')">Modifica</bb-button>
        </div>
        <div v-if="booking.is_past && booking.status !== 'not_shown'"> 
          <bb-button light @click="setNotShown">Non presentato</bb-button>
        </div>
      </div>

      <!-- tab bar -->
      <div class="bb-tab-bar">
        <template v-for="tab in tabs">
          <button
              v-if="tab.visible"
              :key="tab.key"
              class="bb-tab-bar__button"
              :class="{
            'bb-tab-bar__button--active': tab.key == activeTab,
          }"
              @click="activeTab = tab.key"
          >
            {{ tab.title }}
          </button>
        </template>
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
              <div class="underline">
                <a :href="route('customer.show', booking.customer)">{{
                  booking.customer?.full_name
                }}</a>
              </div>
              <div class="text-bb-primary font-bold">
                {{ (booking.is_father) ? '' : 'Amica ' + booking.guest}}
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
          <div class="flex items-center">
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
                  :disabled="booking.is_past || (booking.status === 'cancelled')"
              >
              </BbSelect>
            </div>
          </div>

          <!-- status -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs">Status</div>
            <div class="flex-1">
              <StatusBadge :status="booking.status" :label="booking.status_formatted"></StatusBadge>
            </div>
          </div>

          <!-- status -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs">Store</div>
            <div class="flex-1">
              <span>{{ booking.store?.name ?? '-' }}</span>
            </div>
          </div>

          <!-- applied -->
          <template v-if="booking.is_father">
            <div class="flex items-center">
              <div class="uppercase text-bb-gray-700 w-36 text-xs">Abbonamento</div>
              <span v-if="subscription">
                {{ subscription.name }}
              </span>
              <span v-else>Nessuno</span>
            </div>

            <div class="flex items-center">
              <div class="uppercase text-bb-gray-700 w-36 text-xs">Sconto</div>
              <span v-if="discount">{{ discount.code }}</span>
              <span v-else>Nessuno</span>
            </div>

            <div class="flex items-center">
              <div class="uppercase text-bb-gray-700 w-36 text-xs">Pacchetti</div>
              <span v-if="packages?.length">
                {{ packages.map((p) => `Pacchetto ${p.name}`).join(", ") }}
              </span>
              <span v-else>Nessuno</span>
            </div>

            <!-- created -->
            <div class="flex items-center">
              <div class="uppercase text-bb-gray-700 w-36 text-xs">Creato il</div>
              <div class="flex-1">
                {{ dayjs((booking.order) ? booking.order.created_at : booking.created_at).format('DD/MM/YYYY HH:mm') }}
              </div>
            </div>
          </template>


          <!-- duration -->
          <div class="flex items-center">
            <div class="uppercase text-bb-gray-700 w-36 text-xs">Durata</div>
            <div class="flex-1">
              {{ booking.duration }}
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
                slot: 'price',
                label: 'Prezzo'
              },
              {
                slot: 'extra',
                label: 'Extra'
              },

              // empty column to shrink left other cols
              {
                computed: () => null,
                label: '',
                classes: 'w-full',
              },
            ]"
          >
          <template #price="{ item }">
            <template v-if="! item.service.extra">
              <div class="flex justify-between gap-x-1" v-if="item.service.level === 'primary'">
                <span :class="(subscription && booking.is_father && ! primariesNotIncluded.includes(item.service.title)) ? 'line-through' : ''">{{ helpers.printPrice(item.service.net_price) }} €</span>
                <span v-if="subscription && booking.is_father && ! primariesNotIncluded.includes(item.service.title)">0 €</span>
              </div>
              <div class="flex justify-between gap-x-1" v-if="item.service.level !== 'primary'">
                <span :class="(subscription && booking.is_father && item.service.net_price_discounted) ? 'line-through' : ''">{{ helpers.printPrice(item.service.net_price) }} €</span>
                <span v-if="subscription && booking.is_father && item.service.net_price_discounted">{{ helpers.printPrice(item.service.net_price_discounted) }} €</span>
              </div>
            </template>
            <template v-else>
              <div class="flex justify-between gap-x-1">
                <span>{{ helpers.printPrice(item.service.net_price) }} €</span>
              </div>
            </template>
            
          </template>
          <template #extra="{ item }">
            <div class="flex justify-center" v-if="item.service.extra">
              <CheckCircleIcon class="w-5 h-5 text-green-500" />
            </div>
          </template>
        </bb-table>
        </div>

        <!-- total -->
        <div v-if="booking.is_father" class="flex justify-end space-x-12">
          <!-- amount -->
          <div class="flex items-center space-x-3">
            <div class="uppercase">totale</div>
            <div
              v-if="
                booking.total_net_price_original >
                booking.total_net_price
              "
              class="line-through text-bb-gray-500"
            >
              {{ booking.total_net_price_original }}€
            </div>
            <div class="font-bold text-bb-blue-600">
              {{ booking.total_net_price }} €
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
        <div v-else class="text-right underline">
          <a :href="route('booking.show', booking.parent_id)">
            Vedi prenotazione principale >
          </a>
        </div>

        <!-- payments -->
        <div v-if="booking.is_father" class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Transazioni</h2>

          <bb-table
            :collection="payments"
            :columns="[
              {
                key: 'date',
                label: 'Data',
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
              {
                slot: 'refunded',
                label: '',
              },
            ]"
            :actions="['download']"
            @action="handleTableActions"
          >
            <template #refunded="{ item }">
              <div v-if="item.refunded" class="bb-badge-warning inline-block">Rimborsato</div>
            </template>
          </bb-table>
        </div>

        <!-- refunds -->
        <div v-if="booking.is_father" class="space-y-3 mb-8">
          <h2 class="text-xl font-medium text-bb-primary">Rimborsi</h2>

          <bb-table
            :collection="refunds"
            :columns="[
              {
                key: 'created_at',
                label: 'Data',
                format: 'datetime',
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
          <bb-button @click="addStorePayment">Aggiungi</bb-button>
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

      <!-- history -->
      <div v-if="activeTab === 'history'">
        <div class="p-4 flex justify-start gap-x-4 items-center">
          <p>Ipratico id corrente:</p>
          <p class="font-bold">{{history.current_ipratico_id ?? '-'}}</p>
        </div>
        <template v-for="(h, idx) in history.logs" :key="idx">
          <div class="p-4 mb-4 max-w-md">
            <h3 class="font-bold">{{h.event}} il {{dayjs(h.date).format('D/M/YYYY HH:mm')}}</h3>
            <div v-for="(d, key) in h.diff" :key="key" class="flex justify-between items-center">
              <p>{{key}}</p>
              <p>{{d ?? '-'}}</p>
            </div>
          </div>
        </template>
      </div>

      <!-- form tab -->
      <div v-if="activeTab == 'form'">TODO</div>
    </div>
  </AppLayout>

  <BbDialog ref="deleteDialog" type="plain" size="sm">
    <template #title> Elimina appuntamento </template>

    <p>Una volta eliminato, non potrai più recuperare le informazioni.</p>

    <br>
    <p>Scegli una modalità di cancellazione</p>
    <bb-radio-group
        class="py-2"
        v-model="deleteChoice"
        :vertical="true"
        :options="[
          {value: 'refund', label: 'Rimborso'},
          {value: 'discount', label: 'Genera sconto'},
          {value: 'none', label: 'Nessuna azione'}
        ]"
    ></bb-radio-group>

    <template #buttons>
      <BbButton danger :disabled="deleting" @click="deleteItem">
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
    :amount="(booking.amount_to_pay > 0) ? booking.amount_to_pay : 0"
    @close="closeStorePaymentModal"
  />
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import BookingStatusLight from "./Partials/BookingStatusLight.vue";
import StatusBadge from "@/Pages/Schedules/Partials/ScheduleAppointmentStatusBadge.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import { Inertia } from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import BookingPaymentDialog from "@/Pages/Payments/Partials/BookingPaymentDialog.vue";
import helpers from "../../helpers";
import { CheckCircleIcon } from "@heroicons/vue/solid";

const props = defineProps({
  booking: Object,
  payments: Array,
  storePayments: Array,
  refunds: Array,
  history: Array
});

const isAdmin = computed(() => usePage().props.value.is_admin);
const primariesNotIncluded = usePage().props.value.primaries_not_included;

// Stylist
const stylist = ref(null);
const stylists = ref([]);
onMounted(() => {
  stylist.value = props.booking.stylist_id;
  loadStylists();

  console.log(props.booking);
});

function loadStylists()
{
  axios.get(route("booking.stylists", props.booking.id)).then((res) => {
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
  { key: "info", title: "Info", visible: true },
  { key: "store-payments", title: "Pagamenti in store", visible: props.booking.is_father },
  { key: "history", title: "Storico", visible: props.booking.is_father },
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
function closeStorePaymentModal()
{
  openBookingPaymentDialog.value = false;
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
const deleting = ref(false);
const deleteChoice = ref('refund');

function openDeleteDialog() {
  itemForm.value = useForm(props.booking);
  deleteDialog.value.open();
}
function deleteItem() {
  deleting.value = true;
  Inertia.delete(route("booking.destroy", itemForm.value.id), {
    data: {
      method: deleteChoice.value,
      redirect_to: "schedule.appointment.index" 
    },
    onFinish: () => {
      helpers.flash({
        type: 'success',
        message: 'Appuntamento cancellato'
      });
      deleteDialog.value.close();
      emit('deleted');
      deleting.value = false;
    },
  });
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

// handle table actions
function handleTableActions({ action, item }) {
  switch (action) {
    case "download":
      window.open(route("payment.invoice", [item.user_id, item.id]), "_blank");
      break;
  }
}

// go to
function goTo(routeName) {
  Inertia.visit(route(routeName, props.booking.parent_id ?? props.booking.id));
}
</script>
