<script setup>
import { ref, computed, onMounted, onUpdated, watch } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import helpers from "../../helpers";
import dayjs from "dayjs";
import _ from "lodash";
import StatusBadge from "@/Pages/Schedules/Partials/ScheduleAppointmentStatusBadge.vue";

const props = defineProps({
  customer: Object,
  subscription: Object,
  bookings: Object,
  payments: Object,
  packages: Object,
  plan: Object
});

onMounted(() => {
  console.log(props.plan);
})

const bookingsWithNotes = computed(() => {
  return _.filter(props.customer?.bookings, (b) => (b.stylist_notes)) ?? [];
})

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const deleting = ref(false);
const deleteChoice = ref('refund');

function handleTableAction(action, item) {
  switch (action) {
    case "view":
      Inertia.visit(route("booking.show", item.id));
      break;
    case "delete":
      itemToDelete.value = item;
      deleteDialog.value.open();
      break;
    default:
      break;
  }
}
function handleTableActionPayments(action, item)
{
  switch (action) {
    case "download":
      window.open(route("payment.invoice", [item.user_id, item.id]), "_blank");
      break;
    default:
      break;
  }
}

function deleteItem() {
  deleting.value = true;
  Inertia.delete(route("booking.destroy", itemToDelete.value.id), {
    data: {
      method: deleteChoice.value
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

const tabs = [
  { name: 'Info'},
  { name: 'Appuntamenti'},
  { name: 'Transazioni'},
];
const currentTab = ref('Info');
function changeTab(name)
{
  currentTab.value = name;
}

</script>

<template>
  <AppLayout title="Cliente" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8">
      <div class="flex justify-between items-center">
        <div class="flex justify-start items-center">
          <img src="">
          <div>
            <h1 class="text-xl font-extrabold text-bb-blue-500">{{customer.full_name ?? '-'}}</h1>
            <div class="flex justify-start items-center">
              <p class="text-xs text-bb-blue-800 mr-2">TELEFONO:</p>
              <p class="text-sm text-bb-gray-800 mr-2">{{customer.phone ?? '-'}}</p>
              <p class="text-xs text-bb-blue-800 mr-2">EMAIL:</p>
              <p class="text-sm text-bb-gray-800 mr-2">{{customer.email ?? '-'}}</p>
            </div>
          </div>
        </div>
        <p v-if="subscription">
          <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Abbonamento attivo</span>
        </p>
      </div>
      <div>
        <div class="sm:hidden">
          <label for="tabs" class="sr-only"></label>
        </div>
        <div class="hidden sm:block">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <p @click="changeTab(tab.name)" v-for="tab in tabs" :key="tab.name" :class="[currentTab === tab.name ? 'border-bb-blue-500 text-bb-blue-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pt-4 pb-2 px-1 border-b-2 font-medium text-sm font-bold cursor-pointer']" :aria-current="currentTab === tab.name ? 'page' : undefined">{{ tab.name }}</p>
            </nav>
          </div>
        </div>
      </div>

      <div v-show="currentTab === 'Info'" class="py-6">
        <div class="lg:w-1/2 w-full">
          <h1 class="text-md text-bb-blue-500 font-bold mb-3">Informazioni</h1>
          <div class="flex justify-between items-center">
            <p class="text-bb-gray-700 text-sm">INDIRIZZO</p>
            <p class="text-bb-gray-800 text-sm">{{customer.address ?? '-'}}</p>
          </div>

          <h1 class="text-md text-bb-blue-500 font-bold mt-5 mb-3">Abbonamento</h1>
          <div v-if="subscription">
            <div class="flex justify-between items-center my-1">
              <p class="text-bb-gray-700 text-sm">NOME</p>
              <p class="text-bb-gray-800 text-sm">{{plan?.name ?? '--'}}</p>
            </div>
            <div class="flex justify-between items-center my-1">
              <p class="text-bb-gray-700 text-sm">ACQUISTATO IL</p>
              <p class="text-bb-gray-800 text-sm">{{dayjs.unix(subscription.created).format('DD/MM/YYYY')}}</p>
            </div>
            <div v-if="subscription.ended_at" class="flex justify-between items-center my-1">
              <p class="text-bb-gray-700 text-sm">ATTIVO FINO AL</p>
              <p class="text-bb-gray-800 text-sm">{{dayjs.unix(subscription.ended_at).format('DD/MM/YYYY')}}</p>
            </div>
            <div class="flex justify-between items-center my-1">
              <p class="text-bb-gray-700 text-sm">PROSSIMO RINNOVO</p>
              <p class="text-bb-gray-800 text-sm">{{dayjs.unix(subscription.current_period_end).format('DD/MM/YYYY')}}</p>
            </div>
          </div>
          <div v-else>
            <p class="text-bb-gray-800 italic">Nessun abbonamento acquistato</p>
          </div>

          <h1 class="text-md text-bb-blue-500 font-bold mt-5 mb-3">Note cliente</h1>
          <div v-if="customer.last_notes">
            <div>
              <p class="text-sm">{{customer.last_notes}}</p>
              <p class="text-xs text-gray-600">
                Scritte da {{customer.last_notes_by?.name}} {{customer.last_notes_by?.surname}} il {{dayjs(customer.last_notes_updated_at).format('DD/MM/YYYY')}}
              </p>
            </div>
          </div>
          <div v-else>
            <p class="text-bb-gray-800 italic">Nessuna nota</p>
          </div>

          <h1 class="text-md text-bb-blue-500 font-bold mt-5 mb-3">Note appuntamenti</h1>
          <div v-if="bookingsWithNotes.length > 0">
            <div v-for="b in bookingsWithNotes">
              <template v-if="b.stylist_notes">
                <p class="text-xs text-bb-gray-600 mt-3">Appuntamento del {{dayjs(b.start_date).format('DD/MM/YYYY HH:mm')}}</p>
                <p v-if="b.stylist_notes" class="text-bb-gray-800 inline-block">{{b.stylist_notes}}</p>
                <p v-else class="text-bb-gray-800 inline-block italic text-xs">Nessuna nota</p>
              </template>
            </div>
          </div>

          <div v-else>
            <p class="text-bb-gray-800 italic">Nessuna nota</p>
          </div>
          

          <h1 class="text-md text-bb-blue-500 font-bold mt-5 mb-3">Pacchetti</h1>
          <div v-if="packages.length > 0">
            <div class="mb-5" v-for="pack in packages" :key="pack.id">
              <div class="flex justify-between items-center my-1">
                <p class="text-bb-gray-700 text-sm">NOME</p>
                <p class="text-bb-gray-800 text-sm">{{pack.name}}</p>
              </div>
              <div class="flex justify-between items-center my-1">
                <p class="text-bb-gray-700 text-sm">ACQUISTATO IL</p>
                <p class="text-bb-gray-800 text-sm">{{dayjs(pack.created_at).format('DD/MM/YYYY')}}</p>
              </div>
              <div class="flex justify-between items-center my-1">
                <p class="text-bb-gray-700 text-sm">SCADE IL</p>
                <p class="text-bb-gray-800 text-sm">{{pack.expired_at}}</p>
              </div>
            </div>
          </div>
          <div v-else>
            <p class="text-bb-gray-800 italic">Nessun pacchetto acquistato</p>
          </div>
        </div>
      </div>

      <div v-show="currentTab === 'Scheda'" class="py-6">

      </div>

      <div v-show="currentTab === 'Appuntamenti'" class="py-6">
        <bb-table
            :collection="bookings.data"
            :columns="[
          {
            key: 'date_formatted',
            label: 'Data',
          },
          {
            key: 'hour_formatted',
            label: 'Ora',
          },
          {
            key: 'store.name',
            label: 'Store'
          },
          {
            key: 'stylist.full_name',
            label: 'Stylist',
          },
          {
            label: 'Servizi',
            computed: (item) => {
              if (!item.slots) return '-';
              return item.slots.map((s) => s.service.title).join(' - ');
            },
            classes: 'max-w-[240px]'
          },
          {
            slot: 'created',
            label: 'Prenotato il',
          },
          {
            slot: 'status',
            label: 'Stato',
          },
        ]"
            :links="bookings.links"
            :actions="[
          {
            name: 'view',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'delete',
            condition: (item) => {
              return ! item.is_past && item.status !== 'cancelled';
            },
          },
        ]"
            @action="(data) => handleTableAction(data.action, data.item)"
        >
          <template #created="{ item }">
            <p>{{dayjs((item.order) ? item.order.created_at : item.created_at).format('D/MM/YYYY')}}</p>
          </template>
          <template #status="{ item }">
            <StatusBadge :status="item.status" />
          </template>
        </bb-table>

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
      </div>

      <div v-show="currentTab === 'Transazioni'" class="py-6">
        <bb-table
            :collection="payments.data"
            :columns="[
          {
            key: 'customer_name',
            label: 'Cliente',
            classes: 'font-bold',
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
                case 'subscription-create':
                  return 'Acquisto abbonamento';
                case 'subscription-cycle':
                  return 'Rinnovo Abbonamento';
              }
            },
            label: 'Oggetto',
          },
          {
            key: 'date',
            label: 'Data',
          },
          {
            key: 'total',
            label: 'Totale',
            format: 'currency',
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
            slot: 'refunded',
            label: '',
          },
        ]"
            :links="payments.links"
            :actions="[
          {
            name: 'download',
            condition: (item) => {
              return item.stripe_payment_id !== null;
            },
          },
        ]"
            @action="(data) => handleTableActionPayments(data.action, data.item)"
        >
          <template #refunded="{ item }">
            <div v-if="item.refunded" class="bb-badge-warning inline-block">Rimborsato</div>
          </template>
        </bb-table>
      </div>
    </div>



  </AppLayout>
</template>