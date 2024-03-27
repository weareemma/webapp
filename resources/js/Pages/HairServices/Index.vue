<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from '@/Layouts/AppLayout.vue';
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import helpers from "../../helpers";

const role = computed(() => usePage().props.value.role);

const props = defineProps({
  models: Object
});

// search
const { searchQuery, isSearching, filters } = useSearch("hairService.index", {
  type: route().params.type,
});

const filterOpened = ref(false);

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action)
  {
    case 'edit':
      Inertia.visit(route("hairService.edit", item.id));
      break;
    case 'delete':
      itemToDelete.value = item;
      deleteDialog.value.open();
      break;
    default:
      break;
  }
}

function deleteItem() {
  isDeleting.value = true;
  Inertia.delete(
      route("hairService.destroy", itemToDelete.value.id),
      {
        onSuccess: (res) => {
          deleteDialog.value.close();
          isDeleting.value = false;
          helpers.flash(res.props.flash)
        },
      }
  );
}

const isSyncing = ref(false);
function syncProducts()
{
  isSyncing.value = true;
  Inertia.post(
      route('hairService.sync'),
      {},
      {
        onSuccess: (res) => {
          helpers.flash(res.props.flash)
        },
        onFinish: () => {
          isSyncing.value = false;
        }
      }
  )
}

</script>

<template>
  <AppLayout title="Servizi">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="grow flex justify-end gap-x-3 mr-3">
        <bb-button :disabled="isSyncing" @click="syncProducts" type="button">Refresh</bb-button>
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
          <bb-label class="text-sm mb-1">Tipologia</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                primary: 'Primario',
                addon: 'Addon',
              }"
              v-model="filters.type"
          ></bb-select>
        </div>
      </div>

    </div>

    <div class="bb-card">
      <bb-table
          :collection="models.data"
          :columns="[
            {
                key: 'title',
                label: 'Nome',
                classes: 'font-bold'
            },
            {
                key: 'level_label',
                label: 'Livello'
            },
            {
                key: 'execution_time',
                label: 'Durata'
            },
            {
                key: 'net_price',
                label: 'Prezzo'
            },
            {
                key: 'order',
                label: 'Ordine'
            },
            {
                slot: 'active',
                label: 'Stato',
            },
          ]"
          :links="models.links"
          :actions="[
            {
                name: 'edit',
                condition: (item) => { return true},
            }
          ]"
          @action="(data) => handleTableAction(data.action, data.item)"
      >
        <template #active="{item}">
          <div class="flex justify-start">
            <span v-if="item.active" class="block h-4 w-4 rounded-full bg-bb-green-300 ring-0 ring-white"></span>
            <span v-if="!item.active" class="block h-4 w-4 rounded-full bg-bb-red-400 ring-0 ring-white"></span>
          </div>
        </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title>
        Elimina Servizio
      </template>

      <span>Sei sicuro di voler eliminare questo servizio?</span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>

  </AppLayout>
</template>