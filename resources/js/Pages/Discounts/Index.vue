<script setup>
import { ref, computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";
import helpers from "../../helpers";
import { DownloadIcon } from "@heroicons/vue/solid";

const role = computed(() => usePage().props.value.role);

const props = defineProps({
  models: Object,
});

// search
const { searchQuery, isSearching } = useSearch("discount.index");

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "edit":
      Inertia.visit(route("discount.edit", item.id));
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
  Inertia.delete(route("discount.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.helpers.flash(res.props.flash);
    },
  });
}
</script>

<template>
  <AppLayout title="Sconti">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="grow flex justify-end mr-3">
        <a :href="route('discount.export')">
          <bb-button type="button">
            <DownloadIcon class="w-6 h-6" />
          </bb-button>
        </a>
      </div>
      <Link :href="route('discount.create')"
        ><bb-button type="button">Aggiungi</bb-button></Link
      >
    </div>

    <div class="bb-card">
      <bb-table
        :collection="models.data"
        :columns="[
          {
            key: 'code',
            label: 'Codice',
            classes: 'font-bold',
          },
          {
            key: 'offer',
            label: 'Offerta',
          },
          {
            slot: 'validity',
            label: 'Valido',
          },
          {
            key: 'stores_formatted',
            label: 'Store',
          },
          {
            slot: 'usages',
            label: 'Utilizzi',
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
              return ! item.sub;
            },
          },
          {
            name: 'delete',
            condition: (item) => {
              return ! item.sub;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
        <template #validity="{ item }">
          <p>{{ item.valid_from }} - {{ item.valid_to }}</p>
        </template>
        <template #usages="{ item }">
          <p>{{ item.usages_count }}/{{ item.maximum_count_per_user }}</p>
        </template>
        <template #active="{ item }">
          <span
            :class="item.active ? 'bb-badge-success' : 'bb-badge-secondary'"
            >{{ item.active ? "Attivo" : "Non attivo" }}</span
          >
        </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Elimina Sconto </template>

      <span>Sei sicuro di voler eliminare questo sconto?</span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
