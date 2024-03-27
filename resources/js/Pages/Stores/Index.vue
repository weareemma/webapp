<script setup>
import { ref, computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import helpers from "../../helpers";

const role = computed(() => usePage().props.value.role);

const props = defineProps({
  stores: Object,
});

// search
const { searchQuery, isSearching } = useSearch("store.index");

// Handle table actions
const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "edit":
      Inertia.visit(route("store.edit", item.id));
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
  Inertia.delete(route("store.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.flash(res.props.flash);
    },
  });
}

function updateStores()
{
  Inertia.post(route('tanda.update.stores'), {}, {
    onSuccess: (res) => {
      helpers.flash(res);
    }
  })
}
</script>

<template>
  <AppLayout title="Stores">
    <div class="flex justify-between items-center mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="flex justify-start gap-2">
        <bb-button @click="updateStores">Refresh Tanda</bb-button>
        <Link :href="route('store.create')">
          <bb-button type="button">Aggiungi</bb-button>
        </Link>
      </div>

    </div>

    <div class="bb-card">
      <bb-table
        :collection="stores.data"
        :columns="[
          {
            key: 'name',
            label: 'Nome',
            classes: 'font-bold',
          },
          {
            key: 'address',
            label: 'Indirizzo',
          },
          {
            slot: 'visible',
            label: 'Visibile'
          },
          {
            key: 'washing_stations',
            label: 'n. post. lavaggio',
          },
          {
            key: 'style_stations',
            label: 'n. post. piega',
          },
        ]"
        :links="stores.links"
        :actions="[
          {
            name: 'edit',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'delete',
            condition: (item) => {
              return true;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
        <template #visible="{item}">
          <span
            :class="{
              'bb-badge-success': item.visible,
              'bb-badge-danger': ! item.visible,
            }"
          >
            {{ (item.visible) ? 'Si' : 'No' }}
          </span>
        </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Elimina Store </template>

      <span
        >Una volta eliminato, non potrai più recuperare le informazioni.</span
      >

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
