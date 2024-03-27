<script setup>
import { ref, computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import helpers from "../../helpers";
import dayjs from "dayjs";

const role = computed(() => usePage().props.value.role);

const props = defineProps({
  models: Object,
});

// search
const { searchQuery, isSearching } = useSearch("package.index");

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "edit":
      Inertia.visit(route("package.edit", item.id));
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
  Inertia.delete(route("package.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.helpers.flash(res.props.flash);
    },
  });
}

function statusClass(item)
{
  let exp = dayjs(item.expired_at, 'DD-MM-YYYY');
  if (exp.isBefore(dayjs()))
  {
    return 'bb-badge-danger'
  }
  else
  {
    return item.active ? 'bb-badge-success' : 'bb-badge-secondary';
  }
}

function statusLabel(item)
{
  let exp = dayjs(item.expired_at, 'DD-MM-YYYY');
  if (exp.isBefore(dayjs()))
  {
    return 'Scaduto'
  }
  else
  {
    return item.active ? 'Attivo' : 'Non attivo';
  }
}
</script>

<template>
  <AppLayout title="Pacchetti">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="grow flex justify-end mr-3"></div>
      <Link :href="route('package.create')"
        ><bb-button type="button">Aggiungi</bb-button></Link
      >
    </div>

    <div class="bb-card">
      <bb-table
        :collection="models.data"
        :columns="[
          {
            key: 'name',
            label: 'Nome',
            classes: 'font-bold',
          },
          {
            slot: 'services',
            label: 'Servizi',
          },
          {
            key: 'expired_at',
            label: 'Scadenza',
          },
          {
            key: 'price',
            label: 'Prezzo (IVA incl.)',
            format: 'currency',
          },
          {
            slot: 'stores',
            label: 'Store',
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
        <template #active="{ item }">
          <span
            :class="statusClass(item)"
            >{{ statusLabel(item) }}</span
          >
        </template>
        <template #services="{ item }">
          <p v-for="ser in item.services_formatted" :key="ser">{{ ser }}</p>
        </template>
        <template #stores="{ item }">
          <p>{{ item.stores_formatted }}</p>
        </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Elimina Pacchetto </template>

      <span>Sei sicuro di voler eliminare questo pacchetto?</span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
