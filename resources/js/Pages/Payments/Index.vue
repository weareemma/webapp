<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import helpers from "../../helpers";
import { useSearch } from "@/Composables/search.js";
import { ExclamationIcon } from "@heroicons/vue/solid";
import { DownloadIcon } from "@heroicons/vue/solid";

const role = computed(() => usePage().props.value.role);

const props = defineProps({
  models: Object,
  refundables: Number
});

const { searchQuery, isSearching, filters } = useSearch("payment.index", {
  type: route().params.type,
  from: route().params.from,
  to: route().params.to
});

const filterOpened = ref(false);


// Handle table actions
const refundDialog = ref(null);
const paymentToRefund = ref(null);
const isRefunding = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "download":
      window.open(route("payment.invoice", [item.user_id, item.id]), "_blank");
      break;

    case "refund":
      paymentToRefund.value = item;
      refundDialog.value.open();
      break;
    default:
      break;
  }
}


function refund()
{
  isRefunding.value = true;
  Inertia.post(route("payment.refund", paymentToRefund.value.id), {}, {
    onSuccess: (res) => {
      refundDialog.value.close();
      isRefunding.value = false;
      helpers.flash(res.props.flash);
    }
  });
}
</script>

<template>
  <AppLayout title="Transazioni">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input v-model="searchQuery"></bb-search-input>
      </div>

      <div class="grow flex justify-end gap-x-3 mr-3">
        <span v-if="refundables > 0" class="inline-flex items-center rounded-full bg-yellow-100 px-6 py-2.5 text-sm font-medium text-yellow-800">
            <ExclamationIcon class="w-6 h-6 mr-1" />
            {{ refundables }}
          </span>
        <a :href="route('payment.export', {
            type: filters.type,
            from: filters.from,
            to: filters.to,
          })">
          <bb-button type="button">
            <DownloadIcon class="w-6 h-6" />
          </bb-button>
        </a>
      </div>
      <button
        class="bb-button-style-filter"
        type="button"
        @click="filterOpened = !filterOpened"
      >
        <svg
          width="20"
          height="21"
          viewBox="0 0 20 21"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M10 5C14.9706 5 19 4.10457 19 3C19 1.89543 14.9706 1 10 1C5.02944 1 1 1.89543 1 3C1 4.10457 5.02944 5 10 5Z"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M1 3C1 5.23 4.871 9.674 6.856 11.805C7.58416 12.5769 7.99291 13.5959 8 14.657V20L12 18V14.657C12 13.596 12.421 12.582 13.144 11.805C15.13 9.674 19 5.231 19 3"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </button>
    </div>

    <div
      :class="'bb-card mb-3 px-7 py-5 ' + (filterOpened ? 'block' : 'hidden')"
    >
      <h3 class="text-bb-blue-500 font-bold mb-2">Filtri</h3>
      <div class="flex justify-start items-center gap-4 flex-wrap">
        <div class="min-w-[225px]">
          <bb-label class="text-sm mb-1">Tipologia</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                subscription: 'Abbonamento',
                package: 'Pacchetto',
                charge: 'Acquisto Singolo',
              }"
              v-model="filters.type"
          ></bb-select>
        </div>
        <div >
          <bb-label class="text-sm mb-1">Da</bb-label>
          <datepicker
              v-model="filters.from"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
        </div>
        <div >
          <bb-label class="text-sm mb-1">A</bb-label>
          <datepicker
              v-model="filters.to"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
        </div>
      </div>

    </div>

    <div class="bb-card">
      <bb-table
        :collection="models.data"
        :columns="[
          {
            slot: 'customer',
            label: 'Cliente',
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
        :links="models.links"
        :actions="[
          {
            name: 'download',
            condition: (item) => {
              return item.stripe_payment_id !== null;
            },
          },
          {
            name: 'refund',
            condition: (item) => {
              return item.refundable;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
        <template #customer="{item}">
          <Link :href="route('customer.show', item.user.id)" class="underline">
            <strong>{{ item.user.full_name }}</strong>
          </Link>
        </template>  
        <template #refunded="{ item }">
          <div v-if="item.refunded" class="bb-badge-warning inline-block">Rimborsato</div>
        </template>
      </bb-table>

      <BbDialog ref="refundDialog" type="plain" size="sm">
        <template #title> Rimborso </template>

        <span
        >Sei sicuro di voler rimborsare questo pagamento?</span
        >

        <template #buttons>
          <BbButton danger :disabled="isRefunding" @click="refund">
            Rimborsa
          </BbButton>
        </template>
      </BbDialog>
    </div>
  </AppLayout>
</template>
