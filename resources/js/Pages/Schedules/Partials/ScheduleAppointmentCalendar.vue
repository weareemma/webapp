<template>
  <div class="bg-white rounded-xl overflow-hidden">
    <FullCalendar
      ref="calendar"
      :options="{
        schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',

        plugins: [resourceTimeGridPlugin, interactionPlugin],

        locale: 'it',

        initialView: 'resourceTimeGridDay',

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
          meridiem: 'short',
        },

        allDaySlot: false,
        selectable: true,

        firstDay: 1,
        slotDuration: (slot5) ? '00:05:00' : '00:30:00',
        slotMinTime: '07:00:00',
        slotMaxTime: '23:00:00',

        eventClick: handleEventClick,
        datesSet: handleDatesSet,

        refetchResourcesOnNavigate: true,
        resources: {
          url: route(resources_url),
        },

        events: {
          url: route(events_url),
        },

        slotLabelClassNames: 'bb-fc-slot',
        slotLabelContent,

        eventClassNames: 'bb-fc-event',
        eventContent,
      }"
    />
  </div>

  <!-- event modal -->
  <bb-modal ref="eventModal" size="md" :with-close="true">
    <template #close="{ close }">
      <XIcon
        class="absolute top-14 right-5 w-5 h-5 text-bb-gray-800 cursor-pointer"
        @click="close()"
      />
    </template>
    <EventModal :event="event" @edited="() => calApi.refetchEvents()" @deleted="() => {calApi.refetchEvents(); eventModal.close()}" />
  </bb-modal>
</template>

<script setup>
import { onMounted, reactive, ref, watchEffect } from "vue";
import dayjs from "dayjs";
import { Inertia } from "@inertiajs/inertia";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import EventModal from "@/Pages/Schedules/Partials/ScheduleAppointmentEventModal.vue";
import { XIcon } from "@heroicons/vue/outline";

import "@fullcalendar/core/vdom"; // solves problem with Vite
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import resourceTimeGridPlugin from "@fullcalendar/resource-timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import helpers from "@/helpers";

// Role
const role = ref(usePage().props.value.role);
const resources_url = 'schedule.appointment.resources';
const events_url = 'schedule.appointment.events';

// calendar setup
const calendar = ref(null);
const calApi = ref(null);

// slot size
const slot5 = ref(false);

onMounted(() => {
  calApi.value = calendar.value.getApi();
});

const emit = defineEmits(['changed'])

function slotLabelContent(arg) {
  const date = dayjs(arg.date);
  return `${date.format("H:mm")} - ${date.add((slot5.value) ? 5 : 30, "minutes").format("H:mm")}`;
}

function eventContent(arg) {
  if (arg.event.display == "background") {
    return { domNodes: [document.createElement("div")] };
  }

  const titleContainer = document.createElement("div");
  const title = document.createElement("div");
  const time = document.createElement("div");
  const status = document.createElement("div");
  const services = document.createElement("small");
  const stylist = document.createElement("div");
  const nodes = [titleContainer, time, services, stylist];

  titleContainer.classList.add("bb-fc-event__title-container");
  titleContainer.appendChild(title);
  titleContainer.appendChild(status);

  title.classList.add("bb-fc-event__title");
  title.classList.add('flex', 'justify-between', 'items-center', 'gap-x-0.5');
  let statusClass = '';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_todo) statusClass = 'bg-bb-gray-100';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_progress) statusClass = 'bg-[#A2E8EF]';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_ended) statusClass = 'bg-bb-green-500';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_canceled) statusClass = 'bg-bb-danger';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_not_executed) statusClass = 'bg-bb-gray-100';
  if (arg.event.extendedProps.booking.status === helpers.booking_status_not_shown) statusClass = 'bg-bb-primary-800';
  title.innerHTML = '<h5>'
      + helpers.previewText(arg.event.title, 14)
      + ' '
      + ((arg.event.extendedProps.booking.guest > 0) ? arg.event.extendedProps.booking.guest : '')
      + '</h5>';

  if ( ! arg.event.extendedProps.booking.stylist)
  {
    title.innerHTML +=
          '<div class="grow flex justify-end items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-yellow-300 inline-block">'
        + '<path stroke-linecap="round" stroke-linejoin="round" '
        + 'd="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>'
        + '</svg></div> \n';

  }

  title.innerHTML += '<span class="inline-block w-3 h-3 rounded-full ' + statusClass + '"></span>';


  status.classList.add(
    "bb-fc-event__status",
    `bb-fc-event__status--${arg.event.extendedProps.booking.status}`
  );

  time.innerHTML = '<p>'+ arg.event.extendedProps.booking.hour_formatted +'</p>';

  services.classList.add("bb-fc-event__services");
  services.innerHTML = arg.event.extendedProps.services;

  stylist.classList.add("absolute");
  stylist.classList.add("bottom-0");
  stylist.classList.add("inset-x-0");
  if (arg.event.extendedProps.booking.stylist)
  {
    stylist.innerHTML = '<p class="mb-1 flex justify-start gap-1 align-baseline text-[#2B3B77]"><svg class="self-center" width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
        '<g clip-path="url(#clip0_879_22680)">\n' +
        '<path d="M2.51953 2.32425C2.51953 2.30614 2.53305 2.29079 2.55093 2.2885L7.35082 1.66929C8.55624 1.51345 9.6237 2.45235 9.6237 3.66762C9.6237 4.88289 8.55601 5.82156 7.35082 5.66595L2.55093 5.04675C2.54224 5.04563 2.53426 5.04138 2.52848 5.0348C2.5227 5.02822 2.51952 5.01976 2.51953 5.011V2.32425Z" stroke="#2B3B77"/>\n' +
        '<path d="M2.52018 2.29167L0.916016 1.375V5.95833L2.52018 5.04167" stroke="#2B3B77" stroke-linecap="round" stroke-linejoin="round"/>\n' +
        '<path d="M8.707 5.27148L7.17296 8.91478C7.08423 9.12545 6.93531 9.30528 6.74486 9.43172C6.55442 9.55817 6.3309 9.62563 6.1023 9.62565C5.27157 9.62565 4.70942 8.77888 5.03163 8.01323L6.18617 5.27148" stroke="#2B3B77"/>\n' +
        '<path d="M8.01953 5.72852C9.15862 5.72852 10.082 4.8051 10.082 3.66602C10.082 2.52693 9.15862 1.60352 8.01953 1.60352C6.88044 1.60352 5.95703 2.52693 5.95703 3.66602C5.95703 4.8051 6.88044 5.72852 8.01953 5.72852Z" stroke="#2B3B77"/>\n' +
        '</g>\n' +
        '<defs>\n' +
        '<clipPath id="clip0_879_22680">\n' +
        '<rect width="11" height="11" fill="white"/>\n' +
        '</clipPath>\n' +
        '</defs>\n' +
        '</svg>\n'+ arg.event.extendedProps.booking.stylist.full_name +'</p>';
  }
  else
  {

  }


  if (arg.event.extendedProps.toPay) {
    const toPay = document.createElement("div");
    toPay.classList.add("bb-fc-event__to-pay");
    nodes.push(toPay);
  }

  return { domNodes: nodes };
}

// calendar handlers
function handleEventClick(info) {
  event.value = info.event.toPlainObject();
  eventModal.value.open();
}

function handleDatesSet(e)
{
  emit('changed', e.start);
}

// event modal
const event = ref(null);
const eventModal = ref(null);

// go to day
function goToDay(date) {
  calApi.value.gotoDate(date);
}

defineExpose({ goToDay, slot5 });
</script>
