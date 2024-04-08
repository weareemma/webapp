<template>
  <div :class="classes"></div>
</template>

<script setup>
import { computed } from "vue";
import helpers from "../../../helpers";

const props = defineProps({
  status: String,
});

const classes = computed(() => ({
  "inline-block w-3 h-3 rounded-full": true,
  "bg-bb-gray-100": props.status == helpers.booking_status_todo,
  "bg-[#A2E8EF]": props.status == helpers.booking_status_progress,
  "bg-bb-green-500": props.status == helpers.booking_status_ended,
  "bg-bb-danger": props.status == helpers.booking_status_canceled,
  "bg-bb-primary-100": props.status == helpers.booking_status_not_shown,
}));
</script>
