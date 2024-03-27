4<template>
  <div>
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
      <div>
        <slot name="title"></slot>
      </div>
      <div class="grid grid-cols-2 sm:flex items-center space-x-4 w-full sm:w-auto">
        <bb-button @click="save">Salva</bb-button>
        <bb-button @click="() => {slot5 = !slot5}">
          {{(slot5) ? 'Slot 30' : 'Slot 5'}}
        </bb-button>
      </div>
    </div>

    <div class="bg-white rounded-xl overflow-hidden">
      <FullCalendar
        ref="calendar"
        :options="{
          plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],

          locale: 'it',

          initialView: 'timeGridWeek',

          headerToolbar: {
            start: 'prev,next,title',
            center: '',
            end: '',
          },
          titleFormat: {},

          slotLabelInterval: (slot5) ? '00:05:00' : '00:30:00',
          slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short',
          },

          allDaySlot: false,
          selectable: true,

          firstDay: 1,
          slotDuration: (slot5) ? '00:05:00' : '00:30:00',
          slotMinTime: '07:00:00',
          slotMaxTime: '23:00:00',

          eventClick: handleEventClick,
          eventResize: handleEventResize,
          eventDrop: handleEventDrop,
          select: handleSelect,

          businessHours,
          events,
        }"
      />
    </div>
  </div>

  <bb-dialog ref="createDialog" size="md">
    <template #title>
      Inserisci numero di persone per lo slot
      {{
        `${dayjs(eventForm.start).format("HH:mm")} - ${dayjs(
          eventForm.end
        ).format("HH:mm")}`
      }}
    </template>

    <div>
      <bb-input
        type="number"
        v-model="eventForm.title"
        placeholder="Inserisci il numero di persone"
      />
      <div v-if="validationError" class="text-bb-danger text-sm">
        Questo campo è obbligatorio
      </div>
    </div>

    <template #buttons>
      <bb-button @click="createEvent">OK</bb-button>
    </template>
  </bb-dialog>

  <bb-dialog ref="editDialog" size="md">
    <template #title>
      Inserisci numero di persone per lo slot
      {{
        `${dayjs(eventForm.start).format("HH:mm")} - ${dayjs(
          eventForm.end
        ).format("HH:mm")}`
      }}
    </template>

    <div>
      <bb-input
        type="number"
        v-model="eventForm.title"
        placeholder="Inserisci il numero di persone"
      />
      <div v-if="validationError" class="text-bb-danger text-sm">
        Questo campo è obbligatorio
      </div>
    </div>

    <template #buttons>
      <bb-button danger @click="deleteEvent">Elimina</bb-button>
      <bb-button @click="editEvent">Salva</bb-button>
    </template>
  </bb-dialog>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import dayjs from "dayjs";

import "@fullcalendar/core/vdom"; // solves problem with Vite
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import resourceTimeGridPlugin from "@fullcalendar/resource-timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import { Inertia } from "@inertiajs/inertia";
import helpers from "../../../helpers";

// props
defineProps({
  events: Array,
  businessHours: Array,
});

// calendar setup
const calendar = ref(null);
const calApi = ref(null);

// slot size
const slot5 = ref(false);

onMounted(() => {
  calApi.value = calendar.value.getApi();
});

// event form
const eventForm = reactive({
  id: null,
  title: null,
  start: null,
  end: null,
  editable: true,
});

// calendar handlers
function handleSelect(arg) {
  eventForm.id = "new";
  eventForm.title = "";
  eventForm.start = arg.startStr;
  eventForm.end = arg.endStr;

  resetValidationError();
  createDialog.value.open();
}

function handleEventClick(info) {
  eventObject = info.event;
  eventForm.title = info.event.title;
  eventForm.start = info.event.startStr;
  eventForm.end = info.event.endStr;

  resetValidationError();
  editDialog.value.open();
}

function handleEventResize(info) {
  markEventForSave(info.event);
}

function handleEventDrop(info) {
  markEventForSave(info.event);
}

// validate form
const validationError = ref(false);
function validateForm() {
  if (!eventForm.title) {
    validationError.value = true;
    return false;
  }
  validationError.value = false;
  return true;
}

function resetValidationError() {
  validationError.value = false;
}

// create event
const createDialog = ref(null);
const newEvents = ref([]);

function createEvent() {
  if (!validateForm()) return;
  const e = calApi.value.addEvent(eventForm);
  newEvents.value.push(e);
  createDialog.value.close();

  save();
}

// edit/delete event
const editDialog = ref(null);
const editedEvents = ref([]);
const deletedEvents = ref([]);
let eventObject = null;

function editEvent() {
  if (!validateForm()) return;
  if (eventObject) {
    eventObject.setProp("title", eventForm.title);
    markEventForSave(eventObject);

    save();
  }
  editDialog.value.close();
}

function markEventForSave(event) {
  if (event.id != "new") editedEvents.value.push(event.id);
}

function deleteEvent() {
  if (eventObject) {
    deletedEvents.value.push(eventObject.id);
    eventObject.remove();

    save();
  }
  editDialog.value.close();
}

// save to DB
function save() {
  const events = calApi.value
    .getEvents()
    .filter((ev) => {
      return ev.id == "new" || editedEvents.value.includes(ev.id);
    })
    .map((ev) => {
      return {
        id: ev.id,
        workers: ev.title,
        date: dayjs(ev.startStr).format("YYYY-MM-DD"),
        start: dayjs(ev.startStr).format("HH:mm"),
        end: dayjs(ev.endStr).format("HH:mm"),
      };
    });
  Inertia.post(
    route("schedule.specific.save"),
    {
      events,
      deleted: deletedEvents.value,
    },
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        newEvents.value.forEach((ne) => ne.remove());
        newEvents.value = [];
        helpers.flash({
          type: 'success',
          message: 'Programmazione salvata'
        })
      },
    }
  );
}
</script>
