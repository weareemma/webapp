<template>
  <BbDialog ref="paidDialog" type="plain" size="sm">
    <template #title> Aggiungi pagamento </template>

    <div class="space-y-2">
      <div>
        <bb-label>Data</bb-label>
        <bb-input type="date" v-model="paidData.date" />
        <bb-input-validation name="date" />
      </div>
      <div>
        <bb-label>Importo</bb-label>
        <bb-input type="number" v-model="paidData.amount" />
        <bb-input-validation name="amount" />
      </div>
      <div>
        <bb-label>Mezzo di pagamento</bb-label>
        <bb-select
          mode="single"
          placeholder="Seleziona il mezzo di pagamento"
          :close-on-select="true"
          :options="{
            cash: 'Contanti',
            card: 'Carta',
            satispay: 'Satispay',
          }"
          v-model="paidData.method"
        ></bb-select>
        <bb-input-validation name="method" />
      </div>
    </div>

    <template #buttons>
      <BbButton :disabled="loading" @click="setPaid"> Salva </BbButton>
    </template>
  </BbDialog>
</template>

<script setup>
import { reactive, ref, watchEffect } from "vue";
import { Inertia } from "@inertiajs/inertia";
import dayjs from "dayjs";

const props = defineProps({
  bookingId: Number,
  date: { type: String, default: dayjs().format("YYYY-MM-DD") },
  method: { type: String, default: "cash" },
  amount: { type: Number, default: null },
  modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue", "finish"]);

const paidDialog = ref(null);
const loading = ref(false);
const paidData = reactive({
  date: props.date,
  method: props.method,
  amount: props.amount,
});

watchEffect(() => {
  if (paidDialog.value)
  {
    if (props.modelValue) paidDialog.value.open();
    else paidDialog.value.close();
  }
});

function setPaid() {
  loading.value = true;
  Inertia.post(route("booking.paid", props.bookingId), paidData, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (response) => {
      emit("update:modelValue", false);
      emit("finish");
    },
    onFinish: () => (loading.value = false),
  });
}
</script>
