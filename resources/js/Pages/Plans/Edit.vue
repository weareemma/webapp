<template>
  <CustomerLayout title="Dettaglio abbonamento">
    <div v-if="plan" class="max-w-2xl mx-auto">
      <h1 class="text-3xl font-black text-bb-primary text-center mb-8">
        Dettagli dell'abbonamento
      </h1>
      <div class="bb-card shadow-md p-8 mb-8">
        <div class="bb-info-list">
          <!-- plan name -->
          <div class="bb-info-list__tile">
            <div>Abbonamento</div>
            <div class="flex items-center space-x-4">
              <div class="uppercase font-bold">{{ plan.name }}</div>
              <button v-if="false" @click="changeSubscription()" class="bb-button-rounded-primary-light p-2">
                <PencilIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- price -->
          <div class="bb-info-list__tile">
            <div>Prezzo</div>
            <div v-if="plan.name !== promoName" class="font-bold">
              {{ getPlanIntervalPrice() }} €/ {{
                getPlanIntervalDuration()
              }}
            </div>
            <div v-else class="font-bold">
              {{ getPlanIntervalPrice() }} €
            </div>
          </div>

          <!-- payment method -->
          <div class="bb-info-list__tile">
            <div>Metodo di pagamento</div>
            <div class="flex items-center space-x-4">
              <div v-if="defaultPaymentMethod" class="font-bold">{{ defaultPaymentMethod.card.brand }} *********{{ defaultPaymentMethod.card.last4 }}</div>
              <UpdateMethod></UpdateMethod>
            </div>
          </div>

          <!-- next payment -->
          <template v-if="plan.name !== promoName">
            <div v-if="subEndsAt" class="bb-info-list__tile">
              <div class="text-bb-danger">Scade il </div>
              <div class="font-bold text-bb-danger">
                {{ dayjs(subEndsAt).format("DD/MM/YYYY") }}
              </div>
            </div>
            <div v-else class="bb-info-list__tile">
              <div>Prossimo rinnovo</div>
              <div class="font-bold">
                {{ dayjs(nextPaymentDate).format("DD/MM/YYYY") }}
              </div>
            </div>
          </template>
          <template v-else>
            <div class="bb-info-list__tile">
              <div class="text-bb-danger">Scade il </div>
              <div class="font-bold text-bb-danger">
                {{ dayjs(promoExpires).format("DD/MM/YYYY") }}
              </div>
            </div>
          </template>

          <!-- last payment -->
          <div class="bb-info-list__tile">
            <div>Ultimo pagamento</div>
            <div class="font-bold">
              {{ dayjs(lastPaymentDate).format("DD/MM/YYYY") }}
            </div>
          </div>
        </div>
      </div>

      <bb-button v-if="subEndsAt === null && plan.id !== 1" @click="cancelConfirm" danger light class="w-full">Disdici abbonamento</bb-button>

      <BbDialog ref="deleteDialog" type="plain" size="sm">
        <template #title> Cancella Abbonamento </template>

        <span>Sei sicuro di voler disdire l'abbonamento?</span>

        <template #buttons>
          <BbButton danger @click="cancelSubscription">
            Si
          </BbButton>
        </template>
      </BbDialog>
    </div>

    <!-- if no plan -->
    <div v-else class="flex flex-col items-center space-y-8">
      <h1 class="text-3xl font-black text-bb-primary">
        Non c'è nessun abbonamento attivo
      </h1>
      <bb-button secondary @click="Inertia.visit('/')">Torna alla home</bb-button>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import dayjs from "dayjs";
import CustomerLayout from "@/Layouts/CustomerLayout.vue";
import { PencilIcon } from "@heroicons/vue/outline";
import UpdateMethod from "@/Components/General/UpdatePaymentMethodModal.vue";

const props = defineProps({
  plan: Object,
  price: Object,
  subscription: Object,
  lastPaymentDate: Object,
  nextPaymentDate: String,
});

const promoName = ref(usePage().props.value.promo_name);
const promoExpires = ref(usePage().props.value.promo_expires);

onMounted(() => {
  helpers.lg(usePage().props.value.customer.default_payment_method)
})

const deleteDialog = ref(null);

const subEndsAt = ref(usePage().props.value.customer.subscription_ends_at)
const defaultPaymentMethod = ref(usePage().props.value.customer.default_payment_method)

function cancelConfirm()
{
  deleteDialog.value.open();
}

function getPlanIntervalPrice() {
  return Math.round(props.price.price);
}

function getPlanIntervalDuration() {

  let duration = props.price.duration_qty + ':' + props.price.duration_type;
  switch (duration) {
    case "1:month":
      return "mese";
    case "3:month":
      return "3 mesi";
    case "6:month":
      return "6 mesi";
    case "1:year":
      return "anno";
    default:
      return '-';
  }
}

function changeSubscription()
{
  Inertia.visit(route('buy.subscription.checkout', props.plan.id));
}

function cancelSubscription()
{
  Inertia.post(route('customer.subscription.cancel'), {}, {
    onSuccess: (res) => {
      deleteDialog.value.close();
      helpers.flash(res)
    }
  })
}

</script>
