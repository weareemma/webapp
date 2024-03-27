<template>
  <AppLayout title="Appointments schedule" :show-title="false">
    <div class="pb-16">
      <!-- title -->
      <h2 class="text-2xl font-extrabold text-bb-primary mb-3">
        Calendario {{ store.name }}
      </h2>

      <!-- page actions -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <bb-search-input :isSearching="isSearching" v-model="searchQuery" />
        </div>
      </div>

      <div class="bg-white rounded-xl overflow-hidden truncate">
        <bb-table
          :collection="bookings.data"
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
          :links="bookings.links"
          :actions="[
            {
              name: 'view',
              condition: (item) => {
                return true;
              },
            },
            {
              name: 'delete',
              condition: (item) => {
                return !item.is_past;
              },
            },
          ]"
          @action="(data) => handleTableAction(data.action, data.item)"
        >
          <template #status="{ item }">
            <StatusBadge :status="item.status" :label="item.status_formatted" />
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
    </div>
  </AppLayout>

  <BbDialog ref="deleteDialog" type="plain" size="sm">
    <template #title> Elimina appuntamento </template>

    <span>Una volta eliminato, non potrai più recuperare le informazioni.</span>

    <template #buttons>
      <BbButton danger :disabled="itemForm.processing" @click="deleteItem">
        Elimina
      </BbButton>
    </template>
  </BbDialog>
</template>

<script setup>
import { computed, ref } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import H from "@/helpers";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Inertia } from "@inertiajs/inertia";
import { CalendarIcon, ViewListIcon } from "@heroicons/vue/solid";
import { useSearch } from "@/Composables/search.js";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbPopover from "@/Components/Bitboss/Popover.vue";
import StatusBadge from "@/Pages/Schedules/Partials/ScheduleAppointmentStatusBadge.vue";

const props = defineProps({
  store: Object,
  bookings: Object,
});

const { searchQuery, isSearching } = useSearch("schedule.appointment.past");

// table actions
const itemForm = ref(null);
const deleteDialog = ref(null);
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
  Inertia.delete(route("booking.destroy", itemForm.value.id), {
    onFinish: () => {
      deleteDialog.value.close();
    },
  });
}
</script>
