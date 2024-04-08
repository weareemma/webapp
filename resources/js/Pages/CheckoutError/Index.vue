<template>
    <AppLayout title="Errori checkout">
      <div class="flex justify-between items-center mb-4">
  
      </div>

  
      <div class="bb-card">
        <bb-table
          :collection="errors.data"
          :columns="[
            {
              slot: 'user',
              label: 'Utente',
            },
            {
              key: 'store.name',
              label: 'Store',
            },
            {
              key: 'booking_for',
              label: 'Prenotato per',
              format: 'datetime'
            },
            {
              key: 'paid_at',
              label: 'Pagato il',
              format: 'datetime'
            },
            {
              key: 'total',
              label: 'Totale',
              format: 'currency',
            },
          ]"
          :links="errors.links"
          :actions="[
            {
              name: 'resolve',
              condition: (item) => {
                return true;
              },
            },
          ]"
          @action="(data) => handleTableAction(data.action, data.item)"
        >
        <template #user="{item}">
            <Link :href="route('customer.show', item.user.id)" class="underline">
            <strong>{{ item.user.full_name }}</strong>
          </Link>
        </template>
        </bb-table>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import {ref, computed, onMounted} from "vue";
  import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
  import { Inertia } from "@inertiajs/inertia";
  import AppLayout from "@/Layouts/AppLayout.vue";
  import BbTable from "@/Components/Bitboss/Table.vue";
  import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
  import { useSearch } from "@/Composables/search.js";
  import helpers from "../../helpers";
  import { DownloadIcon, CheckCircleIcon } from "@heroicons/vue/solid";
  
  const props = defineProps({
    errors: Object,
  });
  
  // Handle table actions
  const isResolving = ref(false);
  
  function handleTableAction(action, item) {
    switch (action) {
      case "resolve":
        resolve(item.id);
        break;
      default:
        break;
    }
  }
  
  function resolve(id) {
    isResolving.value = true;
    Inertia.post(route("checkoutError.resolve", id), {
      onSuccess: (res) => {
        isResolving.value = false;
        helpers.flash(res.props.flash);
      },
    });
  }
  </script>
  