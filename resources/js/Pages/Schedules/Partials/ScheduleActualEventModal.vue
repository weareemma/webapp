<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import helpers from "@/helpers";
import dayjs from "dayjs";
import StatusBadge from "./ScheduleAppointmentStatusBadge.vue";

const props = defineProps({
  event: Object,
  booking: Object
});

const emit = defineEmits(["edited", "dateChanged", "deleted"]);

// Load available stylists
const stylists = ref([]);
function loadStylists()
{
  axios.get(route("booking.stylists", props.booking.id)).then((res) => {
    stylists.value = res.data;
  });
}
function updateStylist(stylist_id)
{
  Inertia.post(route("booking.update.stylist", props.booking.id), {
    stylist_id: stylist_id
  }, {
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
    }
  })
}

function updateDate(date)
{
  if (date)
  {
    Inertia.post(route("booking.update.date", props.booking.id), {
      date: dayjs(date).format('YYYY-MM-DD')
    }, {
      onSuccess: (res) => {
        emit('dateChanged', date);
        helpers.flash(res.props.flash);
      }
    })
  }
}

// Role
const role = ref(usePage().props.value.role);
function can()
{
  return role.value === helpers.role_admin || role.value === helpers.role_manager;
}

const calendarDate = ref(dayjs(props.booking.start_date));
const datePick = ref(null);

onMounted(() => {
  if (can())
  {
    loadStylists();
  }

  datePick.value.closeMenu();
});


const deleteDialog = ref(null);
const deleting = ref(false);
const deleteChoice = ref('refund');
// delete
function deleteItem() {
  deleting.value = true;
  Inertia.delete(route("booking.destroy", props.booking.id), {
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

// go to
function goTo(routeName, id) {
  Inertia.visit(route(routeName, id));
}

</script>

<template>
  <div class="bb-card p-6 space-y-3 text-bb-gray-800">
    <div class="flex justify-start items-center gap-x-3">
      <div>
        <datepicker
            ref="datePick"
            class="bb-datepicker-button"
            v-model="calendarDate"
            locale="it-IT"
            :enableTimePicker="false"
            monthNameFormat="long"
            autoApply
            :min-date="new Date()"
            @update:modelValue="updateDate"
        />
      </div>
      <div class="text-sm">
        {{ dayjs(booking.start_date).format("dddd DD MMMM YYYY") }}
        /&nbsp;
        <strong>
          {{ dayjs(event.start).format("HH:mm") }} -
          {{ dayjs(event.end).format("HH:mm") }}
        </strong>
      </div>

    </div>


    <!-- customer -->
    <div class="flex items-center space-x-2">
      <img
          :src="booking.customer.profile_photo_url"
          class="block rounded-full w-8 h-8 object-cover object-center"
      />
      <div class="font-bold text-lg underline">
        <Link :href="route('customer.show', booking.customer.id)">
          {{ booking.customer.full_name }}
        </Link>
      </div>
      <div>
        {{ (booking.is_father) ? '' : 'Amica ' + booking.guest}}
      </div>
    </div>


    <!-- Created -->
    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Creata
      </div>
      <div class="flex-1">{{ dayjs(booking.created_at).format('DD/MM/YYYY HH:mm') }}</div>
    </div>

    <!-- Created by -->
    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Creata da
      </div>
      <div class="flex-1">{{ booking.created_by }}</div>
    </div>

    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Servizi
      </div>
      <div class="flex-1">{{ booking.services_as_string }}</div>
    </div>


    <!-- stylist -->
    <div class="flex items-center" v-if="can()">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Stylist
      </div>
      <div class="flex-1">
        <bb-select
            key="stylist_select"
            mode="single"
            placeholder="Seleziona lo stylist"
            :close-on-select="true"
            :options="stylists"
            v-model="booking.stylist_id"
            @change="updateStylist"
        >
        </bb-select>
      </div>
    </div>

    <!-- status -->
    <div class="flex items-center">
      <div class="uppercase text-bb-gray-700 w-20 text-xs">Status</div>
      <div class="flex-1">
        <StatusBadge :status="booking.status" />
      </div>
    </div>

    <!-- duration -->
    <div class="flex items-center">
      <div class="uppercase text-bb-gray-700 w-20 text-xs">Durata</div>
      <div class="flex-1">
        {{booking.duration ?? '-'}}
      </div>
    </div>

    <!-- divider -->
    <div class="pt-4 mb-4 border-b border-bb-gray-300"></div>

    <!-- actions -->
    <div class="flex items-center justify-end space-x-3">
      <BbButton danger @click="() => deleteDialog.open()">Elimina</BbButton>
      <BbButton v-if="can()" light @click="goTo('booking.edit', booking.parent_id ?? booking.id)">Modifica</BbButton>
      <BbButton autofocus @click="goTo((can()) ? 'booking.show' : 'stylist.appointment.details', booking.id)">Dettaglio</BbButton>
    </div>
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