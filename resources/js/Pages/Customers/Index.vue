<template>
  <AppLayout title="Clienti">
    <div class="flex justify-between items-center mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="flex justify-end gap-x-3">
        <a :href="route('user.export', {
          subStatus: filters.subStatus,
          subName: filters.subName,
          subFrom: filters.subFrom,
          subTo: filters.subTo
        })">
          <bb-button type="button">
            <DownloadIcon class="w-6 h-6" />
          </bb-button>
        </a>
        <Link :href="route('customer.create')">
          <bb-button type="button">Aggiungi</bb-button>
        </Link>
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
    </div>

    <div
      :class="'bb-card mb-3 px-7 py-5 ' + (filterOpened ? 'block' : 'hidden')"
    >
      <h3 class="text-bb-blue-500 font-bold mb-2">Filtri</h3>
      <div class="flex justify-start items-center gap-4 flex-wrap">
        <div class="min-w-[225px]">
          <bb-label class="text-sm mb-1">Abbonato</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                subscribed: 'Abbonato',
                unsubscribed: 'Non abbonato'
              }"
              v-model="filters.subStatus"
          ></bb-select>
        </div>
        <div class="min-w-[225px]">
          <bb-label class="text-sm mb-1">Nome abbonamento</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="plans"
              v-model="filters.subName"
          ></bb-select>
        </div>
        <div >
          <bb-label class="text-sm mb-1">Da</bb-label>
          <datepicker
              v-model="filters.subFrom"
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
              v-model="filters.subTo"
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
        :collection="customers.data"
        :columns="[
          {
            key: 'name',
            label: 'Nome',
          },
          {
            key: 'surname',
            label: 'Cognome',
          },
          {
            key: 'email',
            label: 'Email',
          },
          {
            slot: 'active',
            label: 'Attivo'
          }
        ]"
        :links="customers.links"
        :actions="[
          {
            name: 'wizard',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'impersonate',
            condition: (item) => {
              return isAdmin;
            },
          },
          {
            name: 'edit',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'view',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'delete',
            condition: (item) => {
              return false;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
      <template #active=" { item }">
        <div class="flex justify-start">
          <CheckCircleIcon v-if="item.active" class="w-4 h-4 text-green-400" />
        </div>
      </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Elimina utente </template>

      <span>
        Una volta eliminato, non potrai più recuperare le informazioni.
      </span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>

<script setup>
import {ref, computed, onMounted} from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import helpers from "../../helpers";
import { DownloadIcon, CheckCircleIcon } from "@heroicons/vue/solid";

const props = defineProps({
  customers: Object,
  plans: Object
});
console.log(props.plans)
const isAdmin = computed(() => usePage().props.value.is_admin);

// search
const { searchQuery, isSearching, filters } = useSearch("customer.index", {
  subStatus: route().params.subStatus,
  subName: route().params.subName,
  subFrom: route().params.subFrom,
  subTo: route().params.subTo
});

const filterOpened = ref(false);

// Handle table actions
const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "wizard":
      Inertia.get(route("booking.admin-dashboard", {customer_id: item.id}));
      break;
    case "impersonate":
      Inertia.get(route("impersonate", item.id));
      break;
    case "edit":
      Inertia.visit(route("customer.edit", item.id));
      break;
    case "view":
      Inertia.visit(route("customer.show", item.id));
      break;
    case "delete":
      itemToDelete.value = item;
      deleteDialog.value.open();
      break;
    default:
      break;
  }
}

function deleteItem() {
  isDeleting.value = true;
  Inertia.delete(route("customer.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.flash(res.props.flash);
    },
  });
}
</script>
