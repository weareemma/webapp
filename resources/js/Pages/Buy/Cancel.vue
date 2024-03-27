<script setup>
import { Inertia } from "@inertiajs/inertia";
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

function bakcHome()
{
  Inertia.visit(route('home'));
}
</script>

<template>
  <CustomerLayout title="Checkout">
    <div class="bb-card p-5 mx-auto my-5 max-w-lg mt-5">
      <h1 class="text-bb-danger text-center">Si è verificato un errore</h1>
      <bb-button primary @click="bakcHome" class="text-center mx-auto my-5">Torna alla home</bb-button>
    </div>
  </CustomerLayout>
</template>