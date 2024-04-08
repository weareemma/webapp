<template>
  <span :class="classes">{{ label }}</span>
</template>

<script setup>
import { computed, onMounted } from "vue";

const props = defineProps({
  status: String,
  label: String
});

onMounted(() => {
  console.log(props.status, props.label);
})

const classes = computed(() => ({
  "inline-flex items-center rounded-full px-2.5 py-0.5 text-md font-medium": true,
  "bg-bb-gray-100 text-bb-gray-600": props.status === helpers.booking_status_todo,
  "bb-badge-progress": props.status === helpers.booking_status_progress,
  "bb-badge-ended": props.status === helpers.booking_status_ended,
  "bb-badge-danger": props.status === helpers.booking_status_canceled || props.status === 'cancelled',
  "bg-bb-gray-100 text-bb-gray-900": props.status === helpers.booking_status_not_executed,
  "bg-bb-primary-100 text-bb-primary-600": props.status === helpers.booking_status_not_shown
}));
</script>
