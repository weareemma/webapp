<template>
  <span :class="classes">{{ text }}</span>
</template>

<script setup>
import { computed } from "vue";
import helpers from "@/helpers";

const props = defineProps({
  status: String,
});

const classes = computed(() => ({
  "inline-flex items-center rounded-full px-2.5 py-0.5 text-md font-medium": true,
  "bb-badge-secondary": props.status === helpers.booking_status_todo || props.status === helpers.booking_status_not_executed,
  "bb-badge-primary": props.status === helpers.booking_status_progress,
  "bb-badge-success": props.status === helpers.booking_status_ended,
  "bb-badge-danger": props.status === helpers.booking_status_canceled || props.status === 'cancelled',
  "bg-bb-primary-100 text-bb-primary-600": props.status === helpers.booking_status_not_shown
}));

const text = computed(() => {
  switch (props.status) {
    case "todo":
      return "Da fare";
    case "progress":
      return "In corso";
    case "ended":
      return "Concluso";
    case "cancelled":
      return "Cancellato";
    case "not_executed":
      return "Non eseguito";
    case "not_shown":
      return "Non presentato";  
  }
});
</script>
