<script setup>
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { ref, computed, onMounted, onUpdated } from "vue";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import H from "@/helpers";
import TimePicker from "vue3-timepicker";
import BbTable from "@/Components/Bitboss/Table.vue";
import BbLink from "@/Components/Bitboss/Link.vue";
import {
  TrashIcon
} from '@heroicons/vue/solid';

const props = defineProps({
  store: Object,
  openingTimes: Object,
  exceptionalTimes: Object,
  closingDays: Object,
  managers: Object,
  openingTime: Object,
  exceptionalTime: Object,
  closingDay: Object,
  days: Object,
});

/**
 * FORMS
 */
const form = useForm("store", { ...props.store });
const closingDayForm = useForm("closing", props.closingDay);
const exceptionalTimeForm = useForm("exceptional", props.exceptionalTime);
const openingTimeForm = useForm("opening", props.openingTime);

/**
 * MODALS
 */
const closingDaysModal = ref(null);
const exceptionalTimeModal = ref(null);
const openingTimeModal = ref(null);
const openingTimeDeleteModal = ref(null);
const closingDayDeleteModal = ref(null);
const exceptionalTimeDeleteModal = ref(null);

/**
 * ITEM TO DELETE
 */
const exceptionalTimeToDelete = ref(null);
const openingTimeToDelete = ref(null);
const closingDayToDelete = ref(null);

/**
 * STORE METHOD
 */
function storeModel(target) {
  switch (target) {
    case "st":
      form.id
        ? form.put(route("store.update", form.id), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
            },
          })
        : form.post(route("store.store"), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
            },
          });
      break;

    case "et":
      exceptionalTimeForm.id
        ? exceptionalTimeForm.put(
            route("exceptionalTime.update", exceptionalTimeForm.id),
            {
              preserveScroll: true,
              onSuccess: (res) => {
                H.flash(res.props.flash);
                exceptionalTimeModal.value.close();
              },
            }
          )
        : exceptionalTimeForm.post(route("exceptionalTime.store"), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
              exceptionalTimeModal.value.close();
            },
          });
      break;

    case "ot":
      openingTimeForm.id
        ? openingTimeForm.put(route("openingTime.update", openingTimeForm.id), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
              openingTimeModal.value.close();
            },
          })
        : openingTimeForm.post(route("openingTime.store"), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
              openingTimeModal.value.close();
            },
          });
      break;

    case "cd":
      closingDayForm.id
        ? closingDayForm.put(route("closingDay.update", closingDayForm.id), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
              closingDaysModal.value.close();
            },
          })
        : closingDayForm.post(route("closingDay.store"), {
            preserveScroll: true,
            onSuccess: (res) => {
              H.flash(res.props.flash);
              closingDaysModal.value.close();
            },
          });
      break;

    default:
      break;
  }
}

/**
 * MODAL OPEN
 */
function modalOpen(target, data = null) {
  resetForm(target);

  switch (target) {
    // Closing days
    case "cd":
      console.log(closingDayForm, data);
      if (data) {
        Object.assign(closingDayForm, data);
      }
      closingDayForm.store_id = form.id;
      closingDaysModal.value.open();
      break;

    // Exceptional time
    case "et":
      if (data) {
        Object.assign(exceptionalTimeForm, data);
      }
      exceptionalTimeForm.store_id = form.id;
      exceptionalTimeModal.value.open();
      break;

    // Opening time
    case "ot":
      if (data) {
        Object.assign(openingTimeForm, data);
      }
      openingTimeForm.store_id = form.id;
      openingTimeModal.value.open();
      break;

    default:
      break;
  }
}

/**
 * HANDLE TABLE ACTIONS
 */
function handleTableActions(target, action, item) {
  switch (target) {
    case "cd":
      switch (action) {
        case "edit":
          modalOpen("cd", item);
          break;
        case "delete":
          closingDayToDelete.value = item;
          closingDayDeleteModal.value.open();
          break;
        default:
          break;
      }
      break;

    case "et":
      switch (action) {
        case "edit":
          modalOpen("et", item);
          break;
        case "delete":
          exceptionalTimeToDelete.value = item;
          exceptionalTimeDeleteModal.value.open();
          break;
        default:
          break;
      }
      break;

    case "ot":
      switch (action) {
        case "edit":
          modalOpen("ot", item);
          break;
        case "delete":
          openingTimeToDelete.value = item;
          openingTimeDeleteModal.value.open();
          break;
        default:
          break;
      }
      break;

    default:
      break;
  }
}

/**
 * DELETE METHOD
 */
function deleteModel(target) {
  switch (target) {
    case "et":
      Inertia.delete(
        route("exceptionalTime.destroy", exceptionalTimeToDelete.value.id),
        {
          preserveScroll: true,
          onSuccess: (res) => {
            exceptionalTimeDeleteModal.value.close();
            H.flash(res.props.flash);
          },
        }
      );
      break;

    case "cd":
      Inertia.delete(route("closingDay.destroy", closingDayToDelete.value.id), {
        preserveScroll: true,
        onSuccess: (res) => {
          closingDayDeleteModal.value.close();
          H.flash(res.props.flash);
        },
      });
      break;

    case "ot":
      Inertia.delete(
        route("openingTime.destroy", openingTimeToDelete.value.id),
        {
          preserveScroll: true,
          onSuccess: (res) => {
            openingTimeDeleteModal.value.close();
            H.flash(res.props.flash);
          },
        }
      );
      break;

    default:
      break;
  }
}

/**
 * ADD TIME
 */
function addTime(target) {
  let empty = {
    start_time: "00:00",
    end_time: "00:00",
  };
  switch (target) {
    case "ot":
      openingTimeForm.slots
        ? openingTimeForm.slots.push(empty)
        : (openingTimeForm.slots = [empty]);
      break;

    case "et":
      exceptionalTimeForm.slots
        ? exceptionalTimeForm.slots.push(empty)
        : (exceptionalTimeForm.slots = [empty]);
      break;

    default:
      break;
  }
}

/**
 * RESET FORM
 */
function resetForm(target) {
  switch (target) {
    case "cd":
      closingDayForm.reset();
      closingDayForm.clearErrors();
      closingDayForm.id = null;
      break;

    case "et":
      exceptionalTimeForm.reset();
      exceptionalTimeForm.clearErrors();
      exceptionalTimeForm.id = null;
      break;

    case "ot":
      openingTimeForm.reset();
      openingTimeForm.clearErrors();
      openingTimeForm.id = null;
      break;
  }
}

/**
 * REMOVE ITEM
 */
function removeItem(idx, target)
{
  switch (target) {
    case "ot":
      openingTimeForm.slots.splice(idx, 1);
      break;

    default:
      break;
  }
}
</script>

<template>
  <AppLayout title="Store" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <!-- Card store -->
    <div class="bb-card py-5 px-5">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica store</span>
        <span v-else>Aggiungi nuovo store</span>
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Nome</bb-label>
          <bb-input
            type="text"
            placeholder="Nome"
            v-model="form.name"
          ></bb-input>
          <bb-input-validation :form="form" name="name"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Indirizzo</bb-label>
          <bb-input
            type="text"
            placeholder="Indirizzo"
            v-model="form.address"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="address"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Telefono</bb-label>
          <bb-input
            type="text"
            placeholder="Telefono"
            v-model="form.phone"
          ></bb-input>
          <bb-input-validation :form="form" name="phone"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Email</bb-label>
          <bb-input
            type="email"
            placeholder="Email"
            v-model="form.email"
          ></bb-input>
          <bb-input-validation :form="form" name="email"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Postazioni lavaggio</bb-label>
          <bb-input
            type="number"
            placeholder="Postazioni lavaggio"
            v-model="form.washing_stations"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="washing_stations"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Postazioni piega</bb-label>
          <bb-input
            type="number"
            placeholder="Postazioni piega"
            v-model="form.style_stations"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="style_stations"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Store Managers</bb-label>
          <bb-select
            mode="tags"
            placeholder="Seleziona i managers"
            :close-on-select="true"
            :options="managers"
            v-model="form.managers"
          ></bb-select>
          <bb-input-validation
            :form="form"
            name="managers"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Codice Tanda</bb-label>
          <bb-input
            type="text"
            placeholder="Tanda"
            v-model="form.tanda_code"
            readonly
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="tanda_code"
          ></bb-input-validation>
        </div>

        <div class="sm:col-span-2 flex justify-start items-center">
          <bb-switch v-model="form.visible"></bb-switch>
          <bb-label class="mb-0 ml-2">Visibile</bb-label>
        </div>
      </div>

      <div class="flex justify-end items-center mt-4">
        <bb-button
          type="button"
          @click="storeModel('st')"
          :disabled="form.processing"
        >
          <span v-if="form.id">Salva</span>
          <span v-else>Aggiungi</span>
        </bb-button>
      </div>
    </div>

    <!-- Table opening times -->
    <div v-if="form.id" class="bb-card py-5 px-5 mt-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-bb-blue-500 big-header-title mb-0">Orari</h1>
          <p>Gestisci gli orari dello store</p>
        </div>
        <bb-button type="button" @click="modalOpen('ot')"> Aggiungi </bb-button>
      </div>
      <div class="mt-5">
        <bb-table
          :collection="openingTimes.data"
          :columns="[
            {
              computed: (item) => {
                return H.datetime.weekdayName(item.day);
              },
              label: 'Giorno',
            },
            {
              key: 'slots_formatted',
              label: 'Orari',
            },
            {
              key: 'note_preview',
              label: 'Note',
            },
          ]"
          :links="openingTimes.links"
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
                return true;
              },
            },
          ]"
          @action="(data) => handleTableActions('ot', data.action, data.item)"
        >
        </bb-table>
      </div>
    </div>

    <!-- Table closing days -->
    <div v-if="form.id" class="bb-card py-5 px-5 mt-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-bb-blue-500 big-header-title mb-0">
            Giorni di chiusura
          </h1>
          <p>Gestisci i giorni di chiusura dello store</p>
        </div>
        <bb-button type="button" @click="modalOpen('cd')"> Aggiungi </bb-button>
      </div>
      <div class="mt-5">
        <bb-table
          :collection="closingDays.data"
          :columns="[
            {
              key: 'from',
              label: 'Inizio',
            },
            {
              key: 'to',
              label: 'Fine',
            },
            {
              key: 'days',
              label: 'N. giorni',
            },
            {
              key: 'note_preview',
              label: 'Note',
            },
          ]"
          :links="closingDays.links"
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
                return true;
              },
            },
          ]"
          @action="(data) => handleTableActions('cd', data.action, data.item)"
        >
        </bb-table>
      </div>
    </div>

    <!-- Table exceptional times -->
    <div v-if="form.id" class="bb-card py-5 px-5 mt-4 mb-7">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-bb-blue-500 big-header-title mb-0">
            Orari eccezionali
          </h1>
          <p>Imposta orari di apertura straordinari</p>
        </div>
        <bb-button type="button" @click="modalOpen('et')"> Aggiungi </bb-button>
      </div>
      <div class="mt-5">
        <bb-table
          :collection="exceptionalTimes.data"
          :columns="[
            {
              key: 'date',
              label: 'Data',
            },
            {
              key: 'slots_formatted',
              label: 'Orari',
            },
            {
              key: 'note_preview',
              label: 'Note',
            },
          ]"
          :links="exceptionalTimes.links"
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
                return true;
              },
            },
          ]"
          @action="(data) => handleTableActions('et', data.action, data.item)"
        >
        </bb-table>
      </div>
    </div>

    <!-- Modal opening times -->
    <BbDialog ref="openingTimeModal" type="plain" size="md">
      <template #title>
        <h3 class="big-header-title text-bb-blue-500">
          <span v-if="openingTimeForm.id">Modifica orario</span>
          <span v-else>Aggiungi orario</span>
        </h3>
      </template>

      <div class="grid grid-cols-1 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Giorno</bb-label>
          <bb-select
            mode="single"
            placeholder="Seleziona il giorno"
            :close-on-select="true"
            :options="days"
            v-model="openingTimeForm.day"
          ></bb-select>
          <bb-input-validation
            :form="openingTimeForm"
            name="day"
          ></bb-input-validation>
        </div>
        <div>
          <div
            class="my-5"
            v-for="(slot, index) in openingTimeForm.slots"
            :key="index"
          >
            <label>Fascia #{{ index + 1 }} </label>
            <div class="flex justify-between items-center gap-x-2 gap-y-3">
              <label>Da </label>
              <time-picker
                v-model="slot.start_time"
                placeholder="Start Time"
                :minute-interval="15"
                manual-input
                fixed-dropdown-button
                hide-clear-button
              ></time-picker>
              <span> a </span>
              <time-picker
                v-model="slot.end_time"
                placeholder="End Time"
                :minute-interval="15"
                manual-input
                fixed-dropdown-button
                hide-clear-button
              ></time-picker>
              <bb-button outline danger class="px-2.5" @click="removeItem(index, 'ot')">
                <TrashIcon class="w-4 h-4" />
              </bb-button>
            </div>
          </div>
          <Bblink
            link
            @click="addTime('ot')"
            class="cursor-pointer text-bb-blue-500"
            >+ Aggiungi fascia</Bblink
          >
          <bb-input-validation
            :form="openingTimeForm"
            name="slots"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Note</bb-label>
          <bb-textarea
            class="min-h-[180px]"
            type="text"
            v-model="openingTimeForm.note"
          ></bb-textarea>
          <bb-input-validation
            :form="openingTimeForm"
            name="note"
          ></bb-input-validation>
        </div>
      </div>

      <template #buttons>
        <BbButton primary @click="storeModel('ot')"> Aggiungi </BbButton>
      </template>
    </BbDialog>

    <!-- Modal closing days -->
    <BbDialog ref="closingDaysModal" type="plain" size="md">
      <template #title>
        <h3 class="big-header-title text-bb-blue-500">
          <span v-if="closingDayForm.id">Modifica giorno di chiusura</span>
          <span v-else>Aggiungi giorni di chiusura</span>
        </h3>
      </template>

      <div class="grid grid-cols-1 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Data inizio</bb-label>
          <datepicker
            v-model="closingDayForm.from"
            format="dd/MM/yyyy"
            previewFormat="dd/MM/yyyy"
            locale="it-IT"
            modelType="dd/MM/yyyy"
            :enableTimePicker="false"
            monthNameFormat="long"
            autoApply
          />
          <bb-input-validation
            :form="closingDayForm"
            name="from"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Data fine</bb-label>
          <datepicker
            v-model="closingDayForm.to"
            format="dd/MM/yyyy"
            previewFormat="dd/MM/yyyy"
            locale="it-IT"
            modelType="dd/MM/yyyy"
            :enableTimePicker="false"
            monthNameFormat="long"
            autoApply
          />
          <bb-input-validation
            :form="closingDayForm"
            name="to"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Note</bb-label>
          <bb-textarea
            class="min-h-[180px]"
            type="text"
            v-model="closingDayForm.note"
          ></bb-textarea>
          <bb-input-validation
            :form="closingDayForm"
            name="note"
          ></bb-input-validation>
        </div>
      </div>

      <template #buttons>
        <BbButton primary @click="storeModel('cd')"> Aggiungi </BbButton>
      </template>
    </BbDialog>

    <!-- Modal exceptional times -->
    <BbDialog ref="exceptionalTimeModal" type="plain" size="md">
      <template #title>
        <h3 class="big-header-title text-bb-blue-500">
          <span v-if="exceptionalTimeForm.id">Modifica orario eccezionale</span>
          <span v-else>Aggiungi un orario eccezionale</span>
        </h3>
      </template>

      <div class="grid grid-cols-1 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Data</bb-label>
          <datepicker
            v-model="exceptionalTimeForm.date"
            format="dd/MM/yyyy"
            previewFormat="dd/MM/yyyy"
            locale="it-IT"
            modelType="dd/MM/yyyy"
            :enableTimePicker="false"
            monthNameFormat="long"
            autoApply
          />
          <bb-input-validation
            :form="exceptionalTimeForm"
            name="date"
          ></bb-input-validation>
        </div>
        <div>
          <div
            class="my-5"
            v-for="(slot, index) in exceptionalTimeForm.slots"
            :key="index"
          >
            <label>Fascia #{{ index + 1 }} </label>
            <div class="flex justify-between items-center gap-x-2 gap-y-3">
              <label>Da </label>
              <time-picker
                v-model="slot.start_time"
                placeholder="Start Time"
                :minute-interval="15"
                manual-input
                fixed-dropdown-button
                hide-clear-button
              ></time-picker>
              <span> a </span>
              <time-picker
                v-model="slot.end_time"
                placeholder="End Time"
                :minute-interval="15"
                manual-input
                fixed-dropdown-button
                hide-clear-button
              ></time-picker>
            </div>
          </div>
          <Bblink
            link
            @click="addTime('et')"
            class="cursor-pointer text-bb-blue-500"
            >+ Aggiungi fascia</Bblink
          >
          <bb-input-validation
            :form="exceptionalTimeForm"
            name="slots"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Note</bb-label>
          <bb-textarea
            class="min-h-[180px]"
            type="text"
            v-model="exceptionalTimeForm.note"
          ></bb-textarea>
          <bb-input-validation
            :form="exceptionalTimeForm"
            name="note"
          ></bb-input-validation>
        </div>
      </div>

      <template #buttons>
        <BbButton primary @click="storeModel('et')"> Aggiungi </BbButton>
      </template>
    </BbDialog>

    <!-- Delete opening times -->
    <BbDialog ref="openingTimeDeleteModal" type="plain" size="sm">
      <template #title> Elimina orario </template>

      <span>Sei sicuro?</span>

      <template #buttons>
        <BbButton danger @click="deleteModel('ot')"> Elimina </BbButton>
      </template>
    </BbDialog>

    <!-- Delete closing days -->
    <BbDialog ref="closingDayDeleteModal" type="plain" size="sm">
      <template #title> Elimina giorno di chiusura </template>

      <span>Sei sicuro?</span>

      <template #buttons>
        <BbButton danger @click="deleteModel('cd')"> Elimina </BbButton>
      </template>
    </BbDialog>

    <!-- Delete exceptional times -->
    <BbDialog ref="exceptionalTimeDeleteModal" type="plain" size="sm">
      <template #title> Elimina orario eccezionale </template>

      <span>Sei sicuro?</span>

      <template #buttons>
        <BbButton danger @click="deleteModel('et')"> Elimina </BbButton>
      </template>
    </BbDialog>
  </AppLayout>
</template>
