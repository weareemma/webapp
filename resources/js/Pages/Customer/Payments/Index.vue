<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import BbTable from '@/Components/Bitboss/Table.vue';

const props = defineProps({
  models: Object,
});

function handleTableAction(action, item) {
  switch (action) {
    case "download":
      window.open(route("payment.invoice", [item.user_id, item.id]), "_blank");
      break;
    default:
      break;
  }
}
</script>

<template>
  <CustomerLayout title="Pagamenti">
    <div class="mx-auto w-full">
      <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5">Pagamenti</h3>
      <div v-if="models.data.length > 0">
        <bb-table
            :collection="models.data"
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
                case 'subscription-create':
                  return 'Acquisto abbonamento';
                case 'subscription-cycle':
                  return 'Rinnovo Abbonamento';
              }
            },
            label: 'Descrizione',
          },
          {
            key: 'total',
            label: 'Totale',
            format: 'currency',
          },
        ]"
            :links="models.links"
            :actions="[]"
            @action="(data) => handleTableAction(data.action, data.item)"
        >
        </bb-table>
      </div>
      <div v-else>
        <p class="italic text-bb-blue-500">Non ci sono pagamenti</p>
      </div>
    </div>
  </CustomerLayout>
</template>