<template>
  <AppLayout title="Abbonamenti">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="grow flex justify-end mr-3"></div>
      <Link :href="route('plan.create')">
        <bb-button type="button">Aggiungi</bb-button>
      </Link>
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
            slot: 'pricing_1',
            label: 'Durata 1',
          },
          {
            slot: 'pricing_2',
            label: 'Durata 2',
          },
          {
            slot: 'pricing_3',
            label: 'Durata 3',
          },
          {
            slot: 'pricing_4',
            label: 'Durata 4',
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
              return !item.pricings?.length;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
        <template #pricing_1="{ item }">
          <span
            v-if="item.pricings[0]"
            :class="
              item.pricings[0].active
                ? 'bb-badge-primary'
                : 'bb-badge-secondary'
            "
          >
            {{ getPricingDuration(item.pricings[0]) }}
            ({{ item.pricings[0].price }}€)
          </span>
        </template>
        <template #pricing_2="{ item }">
          <span
            v-if="item.pricings[1]"
            :class="
              item.pricings[1].active
                ? 'bb-badge-primary'
                : 'bb-badge-secondary'
            "
          >
            {{ getPricingDuration(item.pricings[1]) }}
            ({{ item.pricings[1].price }}€)
          </span>
        </template>
        <template #pricing_3="{ item }">
          <span
            v-if="item.pricings[2]"
            :class="
              item.pricings[2].active
                ? 'bb-badge-primary'
                : 'bb-badge-secondary'
            "
          >
            {{ getPricingDuration(item.pricings[2]) }}
            ({{ item.pricings[2].price }}€)
          </span>
        </template>
        <template #pricing_4="{ item }">
          <span
            v-if="item.pricings[3]"
            :class="
              item.pricings[3].active
                ? 'bb-badge-primary'
                : 'bb-badge-secondary'
            "
          >
            {{ getPricingDuration(item.pricings[3]) }}
            ({{ item.pricings[3].price }}€)
          </span>
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
      <template #title> Elimina Abbonamento </template>

      <span>Sei sicuro di voler eliminare questo abbonamento?</span>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>

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
  models: Object,
});

// search
const { searchQuery, isSearching } = useSearch("plan.index");

function getPricingDuration(pricing) {
  let string = `${pricing.duration_qty} `;

  switch (pricing.duration_type) {
    case "month":
      pricing.duration_qty == 1 ? (string += "mese") : (string += "mesi");
      break;
    case "year":
      pricing.duration_qty == 1 ? (string += "anno") : (string += "anni");
      break;
  }

  return string;
}

// delete
const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "edit":
      Inertia.visit(route("plan.edit", item.id));
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
  Inertia.delete(route("plan.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.flash(res.props.flash);
    },
  });
}
</script>
