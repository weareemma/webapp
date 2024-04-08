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
import helpers from "@/helpers.js";
import { XIcon } from "@heroicons/vue/outline";
import EventModal from "./ScheduleActualEventModal.vue";
import { createPopper } from '@popperjs/core';

const calendar = ref(null);
const calApi = ref(null);

// slot size
const slot5 = ref(false);

const event = ref(null);
const eventModal = ref(null);

onMounted(() => {
  calApi.value = calendar.value.getApi();
});

const updateShiftsDisabled = ref(false);
function updateShifts()
{
  updateShiftsDisabled.value = true;
  axios.post(route("tanda.update.shifts")).then((response) => {
    helpers.flash({
      type: 'success',
      message: 'Aggiornamento turni in corso'
    });
  });

  setTimeout(() => {
    updateShiftsDisabled.value = false;
  }, 5000);
}

// go to day
function goToDay(date) {
  calApi.value.gotoDate(date);
}

function handleClick(info, e)
{
  console.log(info,e)
  event.value = info.event.toPlainObject();
  if (event.value.extendedProps)
  {
    eventModal.value.open();
  }
}

function handleEventDrop(info) {
  
  event.value = info.event.toPlainObject();
  if (event.value.extendedProps)
  {
    let message = (event.value.extendedProps.linked)
        ? "\nAPPUNTAMENTO MULTIPLO \n\nSei sicuro di voler modificare questo l'appuntamento?"
        : "\nSei sicuro di voler modificare questo l'appuntamento?";

    if ( ! window.confirm(message))
    {
      info.revert();
    }
    else
    {
      updateStylist(
          event.value.extendedProps.booking.id,
          info.newResource?.id,
          dayjs(info.event.startStr).format('HH:mm:ss')
      );
    }
  }
}

function eventAllowed(dropInfo, event)
{
  // Insert here any allow rules
  // return (dropInfo.startStr === event.startStr);
  return true;
}

// function eventContent(arg) {
//
//   if (arg.event.display === "background") {
//     return { domNodes: [document.createElement("div")] };
//   }
//
//   let ev = arg.event.toPlainObject();
//
//   console.log(ev)
//
//   const titleContainer = document.createElement("div");
//   const title = document.createElement("div");
//   const time = document.createElement("div");
//   const nodes = [titleContainer, time];
//
//   titleContainer.appendChild(title);
//
//   title.classList.add('flex', 'justify-between', 'items-center', 'gap-x-0.5', 'font-[10px]');
//
//   time.innerHTML = '<p>'+ ev.extendedProps?.booking?.hour_formatted +'</p>';
//
//   return { domNodes: nodes };
// }

function updateStylist(booking_id, stylist_id, start)
{
  Inertia.post(route("booking.update.calendar", booking_id), {
    stylist_id: stylist_id,
    start: start
  }, {
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
    }
  })
}

defineExpose({ goToDay });
</script>

<template>
  
  <div class="grid grid-cols-2 sm:flex items-center justify-between mb-4 flex-wrap gap-2">
    <slot name="title"></slot>
    
    <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto col-span-2">

      <bb-button class="px-1 sm:px-6" :disabled="updateShiftsDisabled" @click="updateShifts">Refresh Tanda</bb-button>
      <bb-button @click="() => {slot5 = !slot5}">
        {{(slot5) ? 'Slot 30' : 'Slot 5'}}
      </bb-button>
    </div>
  </div>

  <div class="bg-white rounded-xl overflow-hidden">
    <FullCalendar
        ref="calendar"
        :options="{
          schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',

          plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, resourceTimeGridPlugin],

          locale: 'it',

          initialView: 'resourceTimeGridDay',

          stickyFooterScrollbar: true,

          headerToolbar: {
            start: 'prev,next,title',
            center: '',
            end: 'today',
          },
          titleFormat: {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long',
          },

          slotLabelInterval: (slot5) ? '00:05:00' : '00:30:00',
          slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridian: 'short',
          },

          allDaySlot: false,
          selectable: false,
          editable: true,
          droppable: true,

          firstDay: 1,
          slotDuration: (slot5) ? '00:05:00' : '00:30:00',
          slotMinTime: '07:00:00',
          slotMaxTime: '23:00:00',

          eventAllow: eventAllowed,

          resources: {
            url: route('schedule.actual.resources'),
          },

          eventClick: handleClick,
          eventDrop: handleEventDrop,
          eventDurationEditable: false,

          eventSources: [
              {
                url: route('schedule.actual.events'),
              },
              {
                url: route('schedule.actual.bookings'),
                borderColor: 'red'
              }
          ],
        }"
    />

    <bb-modal ref="eventModal" size="md" :with-close="true">
      <template #close="{ close }">
        <XIcon
            class="absolute top-14 right-5 w-5 h-5 text-bb-gray-800 cursor-pointer"
            @click="close()"
        />
      </template>
      <EventModal
          :event="event"
          :booking="event.extendedProps.booking"
          @dateChanged="(d) => {goToDay(d); eventModal.close()}"
          @deleted="() => {eventModal.close();}"
      />
    </bb-modal>
  </div>
</template>