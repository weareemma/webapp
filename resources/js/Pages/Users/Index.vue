<script setup>
import { ref, computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbSearchInput from "@/Components/Bitboss/SearchInput.vue";
import helpers from "../../helpers";
import { useSearch } from "@/Composables/search.js";
import { CheckCircleIcon } from "@heroicons/vue/solid";

const role = computed(() => usePage().props.value.role);
const user = computed(() => usePage().props.value.user);

const props = defineProps({
  users: Object,
});

const isAdmin = computed(() => usePage().props.value.is_admin);

// search
const { searchQuery, isSearching } = useSearch("user.index");

// Handle table actions
const deleteDialog = ref(null);
const itemToDelete = ref(null);
const isDeleting = ref(false);

function handleTableAction(action, item) {
  switch (action) {
    case "impersonate":
      Inertia.get(route("impersonate", item.id));
      break;
    case "edit":
      Inertia.visit(route("user.edit", item.id));
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
  Inertia.delete(route("user.destroy", itemToDelete.value.id), {
    onSuccess: (res) => {
      deleteDialog.value.close();
      isDeleting.value = false;
      helpers.flash(res.props.flash);
    },
  });
}
function updateUsers()
{
  Inertia.post(route('tanda.update.users'), {}, {
    onSuccess: (res) => {
      helpers.flash(res);
    }
  })
}
</script>

<template>
  <AppLayout title="Utenti">
    <div class="flex justify-between items-center mb-4">
      <div class="w-1/2">
        <bb-search-input :searching="isSearching" v-model="searchQuery" />
      </div>

      <div class="flex justify-start gap-2">
        <bb-button @click="updateUsers">Refresh Tanda</bb-button>
        <Link :href="route('user.create')">
          <bb-button type="button">Aggiungi</bb-button>
        </Link>
      </div>

    </div>

    <div class="bb-card">
      <bb-table
        :collection="users.data"
        :columns="[
          {
            key: 'full_name',
            label: 'Nome',
            classes: 'font-bold',
          },
          {
            key: 'email',
            label: 'Email',
          },
          {
            key: 'role',
            label: 'Ruolo',
          },
          {
            key: 'tanda_code',
            label: 'Tanda'
          },
          {
            slot: 'active',
            label: 'Attivo'
          }
        ]"
        :links="users.links"
        :actions="[
          {
            name: 'impersonate',
            condition: (item) => {
              return item.id !== user.id && isAdmin;
            },
          },
          {
            name: 'edit',
            condition: (item) => {
              return true;
            },
          },
          {
            name: 'delete',
            condition: (item) => {
              return item.id !== user.id;
            },
          },
        ]"
        @action="(data) => handleTableAction(data.action, data.item)"
      >
      <template #active=" { item }">
        <div class="flex justify-start">
          <CheckCircleIcon v-if="item.active" class="w-4 h-4 text-green-400" />
        </div>
      </template>
      </bb-table>
    </div>

    <BbDialog ref="deleteDialog" type="plain" size="sm">
      <template #title> Elimina utente </template>

      <span
        >Una volta eliminato, non potrai più recuperare le informazioni.</span
      >

      <template #buttons>
        <BbButton danger :disabled="isDeleting" @click="deleteItem">
          Elimina
        </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
