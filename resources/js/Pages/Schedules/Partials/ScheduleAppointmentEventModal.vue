<template>
  <div v-if="form" class="bb-card p-6 space-y-3 text-bb-gray-800">
    <!-- date and time -->
    <div class="text-sm">
      {{ dayjs(form.date).format("dddd DD MMMM YYYY") }}
      /&nbsp;
      <strong>
        {{ dayjs(event.start).format("HH:mm") }} -
        {{ dayjs(event.end).format("HH:mm") }}
      </strong>
    </div>

    <!-- customer -->
    <div class="flex items-center space-x-2">
      <img
        :src="form.customer.profile_photo_url"
        class="block rounded-full w-8 h-8 object-cover object-center"
      />
      <div class="font-bold text-lg underline">
        <Link :href="route('customer.show', form.customer.id)">
          {{ form.customer.full_name }}
        </Link>
      </div>
      <div>
        {{ (form.is_father) ? '' : 'Amica ' + form.guest}}
      </div>
    </div>


    <!-- Created -->
    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Creata
      </div>
      <div class="flex-1">{{ dayjs(event.extendedProps.booking.created_at).format('DD/MM/YYYY HH:mm') }}</div>
    </div>

    <!-- Created by -->
    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Creata da
      </div>
      <div class="flex-1">{{ event.extendedProps.booking.created_by }}</div>
    </div>


    <!-- services -->
    <div class="flex">
      <div class="uppercase text-bb-gray-700 w-20 text-xs pt-[5px]">
        Servizi
      </div>
      <div class="flex-1">{{ event.extendedProps.services }}</div>
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
            v-model="form.stylist_id"
            @change="updateStylist"
        >
        </bb-select>
      </div>
    </div>

    <!-- status -->
    <div class="flex items-center">
      <div class="uppercase text-bb-gray-700 w-20 text-xs">Status</div>
      <div class="flex-1">
        <StatusBadge :status="event.extendedProps.booking.status" />
      </div>
    </div>

    <!-- duration -->
    <div class="flex items-center">
      <div class="uppercase text-bb-gray-700 w-20 text-xs">Durata</div>
      <div class="flex-1">
        {{event.extendedProps.booking.duration}}
      </div>
    </div>

    <!-- to pay -->
    <template v-if="toPay">
      <!-- to pay warning -->
      <div
        class="rounded-lg bg-[#FFDEAD] flex items-center px-3 py-2 space-x-4 cursor-pointer"
        @click="() => (openBookingPaymentDialog = true)"
      >
        <ExclamationIcon class="w-4 h-4 text-bb-danger" />
        <div>
          Il cliente deve pagare in store <strong>{{ toPay }}€</strong>
        </div>
      </div>
    </template>

    <!-- divider -->
    <div class="pt-4 mb-4 border-b border-bb-gray-300"></div>

    <!-- actions -->
    <div class="flex items-center justify-end space-x-3">
      <BbButton danger @click="() => {deleteChoice = 'refund'; deleteDialog.open();}">Elimina</BbButton>
      <BbButton v-if="can()" light @click="goTo('booking.edit', form.parent_id ?? form.id)">Modifica</BbButton>
      <BbButton autofocus @click="goTo((can()) ? 'booking.show' : 'stylist.appointment.details', form.id)">Dettaglio</BbButton>
    </div>
  </div>

  <BookingPaymentDialog
    v-if="form"
    :booking-id="form.id"
    :amount="toPay"
    v-model="openBookingPaymentDialog"
    @finish="
      () => {
        form.amount_to_pay = 0;
        $emit('edited');
      }
    "
  />

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

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import dayjs from "dayjs";
import { Inertia } from "@inertiajs/inertia";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { ExclamationIcon } from "@heroicons/vue/outline";
import StatusBadge from "./ScheduleAppointmentStatusBadge.vue";
import BookingPaymentDialog from "@/Pages/Payments/Partials/BookingPaymentDialog.vue";
import helpers from "@/helpers";

const props = defineProps({
  event: Object,
});

const emit = defineEmits(["edited", "deleted"]);

// Role
const role = ref(usePage().props.value.role);
function can()
{
  return role.value === helpers.role_admin || role.value === helpers.role_manager;
}


// form
const form = ref(null);
onMounted(() => {
  helpers.lg(props.event.extendedProps);
  form.value = useForm(props.event.extendedProps.booking);
  if (can())
  {
    loadStylists();
  }
});

// to pay
const toPay = computed(() => {
  return (form.value.amount_to_pay > 0) ? form.value.amount_to_pay : null;
});
const openBookingPaymentDialog = ref(false);

// go to
function goTo(routeName, id) {
  Inertia.visit(route(routeName, id));
}

// Load available stylists
const stylists = ref([]);
function loadStylists()
{
  axios.get(route("booking.stylists", form.value.id)).then((res) => {
    stylists.value = res.data;
  });
}
function updateStylist(stylist_id)
{
  Inertia.post(route("booking.update.stylist", form.value.id), {
    stylist_id: stylist_id
  }, {
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
    }
  })
}

const deleteDialog = ref(null);
const deleting = ref(false);
const deleteChoice = ref('refund');
// delete
function deleteItem() {
  deleting.value = true;
  Inertia.delete(route("booking.destroy", form.value.id), {
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

</script>
