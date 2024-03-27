<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import { useSearch } from "@/Composables/search.js";

const props = defineProps({
  logs: Object
})

// search
const { searchQuery, isSearching, filters } = useSearch("logs.index", {
  from: route().params.from,
  to: route().params.to
});
</script>

<template>
  <AppLayout title="Logs">
    <div class="flex justify-between items-center mb-4">
      <div class="bb-card w-full flex flex-col gap-y-2">
        <bb-search-input class="w-1/2" :searching="isSearching" v-model="searchQuery" />
        <div class="flex justify-start items-center gap-4 flex-wrap">
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
    </div>

    <div class="bb-card">
      <bb-table
          :collection="logs.data"
          :columns="[
          {
            key: 'type',
            label: 'Tipo',
            classes: 'font-bold',
          },
          {
            key: 'app',
            label: 'App',
          },
          {
            slot: 'status',
            label: 'Stato',
          },
          {
            key: 'session',
            label: 'Sessione',
          },
          {
            key: 'event',
            label: 'Evento',
          },
          {
            key: 'subject',
            label: 'Soggetto',
          },
          {
            key: 'created_at',
            label: 'Logged',
            format: 'datetime'
          },
        ]"
          :links="logs.links"
          :actions="[
          // {
          //   name: 'impersonate',
          //   condition: (item) => {
          //     return item.id !== user.id && isAdmin;
          //   },
          // },
          // {
          //   name: 'edit',
          //   condition: (item) => {
          //     return true;
          //   },
          // },
          // {
          //   name: 'delete',
          //   condition: (item) => {
          //     return item.id !== user.id;
          //   },
          // },
        ]"
          @action=""
      >
        <template #status="{ item }">
          <span
              :class="{
                'bb-badge-success': item.status === '200',
                'bb-badge-danger': item.status !== '200',
              }"
          >{{ item.status.toUpperCase() }}</span
          >
        </template>
      </bb-table>
    </div>
  </AppLayout>
</template>