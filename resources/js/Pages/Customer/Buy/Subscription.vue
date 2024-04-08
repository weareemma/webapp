<script setup>
import {ref, computed, onBeforeMount, onMounted} from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Subscriptions from "@/Pages/Buy/Subscriptions.vue";

const props = defineProps({
  plans: Object
})
</script>

<template>
  <CustomerLayout title="Acquista un'abbonamento">
    <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5 text-center">Acquisto abbonamento</h3>
    <subscriptions
        :plans="plans"
    ></subscriptions>
  </CustomerLayout>
</template>
