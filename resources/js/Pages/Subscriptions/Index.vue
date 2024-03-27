<script setup>
import { ref, computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import dayjs from "dayjs";
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
const { searchQuery, isSearching } = useSearch("subscription.index");

const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);
const deleteChoice = ref('now');

function handleTableAction(action, item) {
  switch (action) {
    case "delete":
      itemToDelete.value = item;
      deleteChoice.value = 'now';
      deleteDialog.value.open();
      break;
  }
}

function deleteItem() {
  isDeleting.value = true;
  Inertia.delete(route("subscription.destroy", itemToDelete.value.id), {
    data: {
      method: deleteChoice.value
    },
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.flash({
        type: 'success',
        message: 'Abbonamento annullato'
      });
    },
    onError: () => {
      helpers.flash({
        type: 'error',
        message: "Si è verificato un errore durante la cancellazione dell' abbonamento"
      });
    }
  });
}

function getDuration(pricing) {
  let string = `${pricing.duration_qty} `;
  switch (pricing.duration_type) {
    case "month":
      return pricing.duration_qty == 1
        ? (string += "mese")
        : (string += "mesi");
    case "year":
      return pricing.duration_qty == 1
        ? (string += "anno")
        : (string += "anni");
  }
}

function subscriptionEnded(item) {
  return dayjs(item.ends_at) < dayjs();
}
</script>

<template>
  <AppLayout title="Abbonamenti acquistati">
    <div class="flex justify-between mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <a :href="route('subscription.export')">
        <bb-button type="button">
          <DownloadIcon class="w-6 h-6" />
        </bb-button>
      </a>
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
            key: 'user.email',
            label: 'Email',
          },
          {
            key: 'plan.name',
            label: 'Abbonamento',
          },
          {
            key: 'created_at',
            label: 'Acquistato il',
            format: 'date',
          },
          {
            computed: (item) => {
              if (!item?.pricing) return '-';
              return getDuration(item.pricing);
            },
            label: 'Durata',
          },
          {
            slot: 'stripe_status',
            label: 'Status',
          },
        ]"
        :links="models.links"
        :actions="[
          {
            name: 'delete',
            condition: (item) => {
              return !item.ends_at;
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
        <template #stripe_status="{ item }">
          <span
            :class="{
              'bb-badge-success':
                item.stripe_status == 'active' && !item.ends_at,
              'bb-badge-warning':
                item.stripe_status == 'active' &&
                item.ends_at &&
                !subscriptionEnded(item),
              'bb-badge-danger': item.ends_at && subscriptionEnded(item),
              'bb-badge-secondary': item.stripe_status != 'active',
            }"
          >
            <template v-if="item.stripe_status == 'active'">
              <span v-if="item.ends_at && !subscriptionEnded(item)">
                Attivo fino al
                {{ dayjs(item.ends_at).format("DD/MM/YYYY") }}
              </span>
              <span v-else-if="item.ends_at && subscriptionEnded(item)">
                Annullato
              </span>
              <span v-else>Attivo</span>
            </template>
            <span v-else>Annullato</span>
          </span>
        </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="md">
      <template #title> Annulla abbonamento </template>

      <p>Sei sicuro di voler eliminare questo abbonamento?</p>

      <br>
      <p>Scegli una modalità di cancellazione</p>
      <bb-radio-group
          class="py-2"
          v-model="deleteChoice"
          :vertical="true"
          :options="[
            {value: 'now', label: 'Istantaneamente'},
            {value: 'normal', label: 'Al prossimo rinnovo'}
          ]"
      ></bb-radio-group>

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Annulla abbonamento
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
