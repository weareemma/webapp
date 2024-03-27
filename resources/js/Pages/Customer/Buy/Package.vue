<script setup>
import {ref, computed, onBeforeMount, onMounted} from "vue";
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Packages from "@/Pages/Buy/Packages.vue";

const props = defineProps({
  packages: Object
})
</script>

<template>
  <CustomerLayout title="Acquista un pacchetto">
    <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5 text-center">Acquisto pacchetto</h3>
    <packages
        :packages="packages"
    ></packages>
  </CustomerLayout>
</template>