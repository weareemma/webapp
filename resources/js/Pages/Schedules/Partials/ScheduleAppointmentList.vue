<template>
  <div class="bg-white rounded-xl overflow-hidden truncate">
    <bb-table
      :collection="collection"
      :columns="[
        {
          label: 'Data',
          computed: (item) => {
            if (!item.date) return '-';
            return dayjs(item.date).format('DD/MM/YYYY');
          },
        },
        {
          label: 'Ora',
          computed: (item) => {
            if (!item.start) return '-';
            const start = dayjs(`2000-01-01 ${item.start}`);
            const end = start.add(item.total_execution_time, 'minutes');
            return `${start.format('HH:mm')} - ${end.format('HH:mm')}`;
          },
        },
        {
          slot: 'customer',
          label: 'Cliente',
        },
        {
          label: 'Stylist',
          key: 'stylist.full_name', // TODO
        },
        {
          label: 'Servizi',
          computed: (item) => {
            if (!item.slots) return '-';
            return item.slots.map((s) => s.service.title).join(' - ');
          },
          classes: 'max-w-[240px]',
        },
        {
          label: 'Status',
          slot: 'status',
        },
        {
          label: '',
          slot: 'to_pay',
        },
      ]"
      :links="links"
      :actions="[
        {
          name: 'view',
          condition: (item) => {
            return !item.deleted_at;
          },
        },
        {
          name: 'delete',
          condition: (item) => {
            return !item.deleted_at;
          },
        },
      ]"
      @action="(data) => handleTableAction(data.action, data.item)"
    >
      <template #status="{ item }">
        <StatusBadge :status="item.status" />
      </template>

      <template #customer="{item}">
        <Link :href="route('customer.show', item.customer.id)" class="underline">
          <strong>{{ item.customer.full_name }}</strong>
        </Link>
      </template>

      <template #to_pay="{ item }">
        <bb-popover mode="hover" class="h-7 w-7">
          <template #button>
            <div
              v-if="item.amount_to_pay > 0"
              class="bg-[#FFDEAD] rounded-full h-7 w-7 grid place-items-center"
            >
              <img src="/img/alert-icon.svg" class="w-4 h-4" />
            </div>
          </template>
          <template #panel>
            <div
              class="bg-bb-primary text-white rounded text-xs p-4 max-w-[124px]"
            >
              Il cliente deve pagare in store {{ item.amount_to_pay }}€
            </div>
          </template>
        </bb-popover>
      </template>
    </bb-table>
  </div>

  <BbDialog ref="deleteDialog" type="plain" size="sm">
    <template #title> Elimina appuntamento </template>

    <p>Una volta eliminato, non potrai più recuperare le informazioni.</p>

    <br>
    <p>Scegli una modalità di cancellazione</p>
    <bb-radio-group
        class="py-2"
        v-model="deleteChoice"
        :vertical="true"
        :options="[
          {value: 'refund', label: 'Rimborso'},
          {value: 'discount', label: 'Genera sconto'},
          {value: 'none', label: 'Nessuna azione'}
        ]"
    ></bb-radio-group>

    <template #buttons>
      <BbButton danger :disabled="deleting" @click="deleteItem">
        Elimina
      </BbButton>
    </template>
  </BbDialog>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useForm } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import dayjs from "dayjs";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbPopover from "@/Components/Bitboss/Popover.vue";
import StatusBadge from "./ScheduleAppointmentStatusBadge.vue";

defineProps({
  collection: Array,
  links: Object,
});

// table actions
const itemForm = ref(null);
const deleteDialog = ref(null);
const deleting = ref(false);
const deleteChoice = ref('refund');
function handleTableAction(action, item) {
  switch (action) {
    case "view":
      Inertia.visit(route("booking.show", item.id));
      break;
    case "delete":
      itemForm.value = useForm(item);
      deleteDialog.value.open();
      break;
  }
}

// delete
function deleteItem() {
  deleting.value = true;
  Inertia.delete(route("booking.destroy", itemForm.value.id), {
    data: {
      method: deleteChoice.value
    },
    onFinish: () => {
      helpers.flash({
        type: 'success',
        message: 'Appuntamento cancellato'
      });
      deleteDialog.value.close();
      emit('deleted');
      deleting.value = false;
    },
  });
}
</script>
