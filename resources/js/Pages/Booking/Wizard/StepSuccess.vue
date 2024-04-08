<script setup>


  const props = defineProps({
    paymentLinkSent: {
      type: Boolean,
      default: false
    }
  });
</script>
<template>
  <div class="text-center space-y-6">
    <p v-if="paymentLinkSent">
      Link di pagamento inviato al cliente
    </p>
    <BbLink :href="route('booking.admin-dashboard')">
      Acquista altri servizi
    </BbLink>
    <div>oppure</div>
    <BbLink :href="route('schedule.appointment.index')">
      Torna agli appuntamenti
    </BbLink>
  </div>
</template>
