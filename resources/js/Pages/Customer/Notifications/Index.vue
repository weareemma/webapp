<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import BbTable from '@/Components/Bitboss/Table.vue';
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  models: Object,
});

function handleTableAction(action, item) {
  switch (action) {
    case "markAsRead":
      Inertia.post(route('customer.notifications.markAsRead'), {
        notification_id: item.id
      }, {
        onSuccess: (res) => {
          helpers.flash(res);
        }
      });
      break;
    default:
      break;
  }
}

</script>

<template>
  <CustomerLayout title="Notifiche">
    <div class="mx-auto w-full">
      <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5">Notifiche</h3>
      <div v-if="models.data.length > 0">
        <bb-table
            :collection="models.data"
            :columns="[
          {
            key: 'created_at',
            label: 'Data',
            format: 'datetime'
          },
          {
            key: 'data.title',
            label: 'Messaggio',
          },
          {
            key: 'read_at',
            label: 'Letta il',
            format: 'datetime'
          }
        ]"
            :links="models.links"
            :actions="[
          {
            name: 'markAsRead',
            condition: (item) => {
              return item.read_at === null;
            },
          },
        ]"
            @action="(data) => handleTableAction(data.action, data.item)"
        >
        </bb-table>
      </div>
      <div v-else>
        <p class="italic text-bb-blue-500">Non ci sono notifiche</p>
      </div>
    </div>
  </CustomerLayout>
</template>