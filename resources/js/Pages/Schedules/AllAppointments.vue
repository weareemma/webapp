<template>
    <AppLayout title="Appointments all" :show-title="false">
      <div class="pb-16">
        <!-- title -->
        <h2 class="text-2xl font-extrabold text-bb-primary mb-3">
          Tutti gli appuntamenti
        </h2>
  
        <!-- page actions -->
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
          <div>
            <bb-search-input :isSearching="isSearching" v-model="searchQuery" />
          </div>
          <div>
            
          </div>
          <div class="flex justify-start gap-x-2">
            <bb-button type="button" @click="exportCsv">
                <DownloadIcon class="w-6 h-6" />
            </bb-button>
            <button
            class="bb-button-style-filter p-3"
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
          <bb-label class="text-sm mb-1">Tipologia servizio</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                treatment: 'Trattamento',
                updo: 'Raccolto',
                massage: 'Massaggio'
              }"
              v-model="filters.type"
          ></bb-select>
        </div>
        <div class="min-w-[225px]">
          <bb-label class="text-sm mb-1">Stato</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                todo: 'Da fare',
                progress: 'In corso',
                ended: 'Concluso',
                not_executed: 'Non eseguito',
                not_shown: 'Non presentato',
                canceled: 'Cancellato',
              }"
              v-model="filters.status"
          ></bb-select>
        </div>
        <div class="min-w-[225px]">
          <bb-label class="text-sm mb-1">Pagati Online / Store</bb-label>
          <bb-select
              mode="single"
              class="w-full"
              placeholder="Seleziona un'opzione"
              :close-on-select="true"
              :options="{
                store: 'In store',
                online: 'Online',
              }"
              v-model="filters.online"
          ></bb-select>
        </div>
        <div >
          <bb-label class="text-sm mb-1">Da</bb-label>
          <datepicker
              v-model="filters.from"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
        </div>
        <div >
          <bb-label class="text-sm mb-1">A</bb-label>
          <datepicker
              v-model="filters.to"
              format="dd/MM/yyyy"
              previewFormat="dd/MM/yyyy"
              locale="it-IT"
              modelType="dd/MM/yyyy"
              :enableTimePicker="false"
              monthNameFormat="long"
              autoApply
          />
        </div>
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
              }
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
  import { CalendarIcon, ViewListIcon, DownloadIcon } from "@heroicons/vue/solid";
  import { useSearch } from "@/Composables/search.js";
  import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
  import BbTable from "@/Components/Bitboss/Table.vue";
  import BbPopover from "@/Components/Bitboss/Popover.vue";
  import StatusBadge from "@/Pages/Schedules/Partials/ScheduleAppointmentStatusBadge.vue";
  
  const props = defineProps({
    store: Object,
    bookings: Object,
  });
  
  const { searchQuery, isSearching, filters } = useSearch(
        "schedule.appointment.all",
        {
            from: route().params.from,
            to: route().params.to,
            status: route().params.status,
            type: route().params.type,
            online: route().params.online
        }
  );
  const filterOpened = ref(false);
  
  // table actions
  const itemForm = ref(null);
  const deleteDialog = ref(null);
  function handleTableAction(action, item) {
    switch (action) {
      case "view":
        Inertia.visit(route("booking.show", item.id));
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

  function exportCsv()
  {
    window.open(route('booking.export', {
        from: filters.from,
        to: filters.to,
        status: filters.status,
        type: filters.type,
        online: filters.online,
        q: searchQuery.value
    }), '_blank');
  }
</script>
  