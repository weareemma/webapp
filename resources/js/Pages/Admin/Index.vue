<template>
    <AppLayout title="Dashboard">
        <div class="bb-card my-4 p-6">
            <p class="text-bb-blue-500 text-right font-semibold text-2xl mb-4">Clienti totali: {{ totalCustomers }}</p>
            <div class="flex justify-start items-center gap-4 flex-wrap">
                <div class="min-w-[225px]">
                    <bb-label class="text-sm mb-1">Store</bb-label>
                    <bb-select
                        mode="tags"
                        class="w-full"
                        placeholder="Tutti"
                        :close-on-select="true"
                        :options="stores"
                        v-model="filters.store"
                    ></bb-select>
                </div>
                <div >
                    <bb-label class="text-sm mb-1">Da</bb-label>
                    <datepicker
                        v-model="filters.from"
                        format="dd/MM/yyyy"
                        previewFormat="dd/MM/yyyy"
                        locale="it-IT"
                        modelType="dd/MM/yyyy"
                        :enableTimePicker="false"
                        monthNameFormat="long"
                        autoApply
                    />
                </div>
                <div >
                    <bb-label class="text-sm mb-1">A</bb-label>
                    <datepicker
                        v-model="filters.to"
                        format="dd/MM/yyyy"
                        previewFormat="dd/MM/yyyy"
                        locale="it-IT"
                        modelType="dd/MM/yyyy"
                        :enableTimePicker="false"
                        monthNameFormat="long"
                        autoApply
                    />
                </div>
                <div class="min-w-[225px]">
                    <bb-label class="text-sm mb-1">Stato appuntamenti</bb-label>
                    <bb-select
                        mode="tags"
                        class="w-full"
                        placeholder="Tutti"
                        :close-on-select="true"
                        :options="[
                            {value: 'todo', label: 'To do'},
                            {value: 'progress', label: 'In corso'},
                            {value: 'ended', label: 'Terminati'},
                            {value: 'cancelled', label: 'Cancellati'},
                            {value: 'not_shown', label: 'Non presentati'},
                            {value: 'not_executed', label: 'Non eseguiti'},
                        ]"
                        v-model="filters.status"
                    ></bb-select>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center gap-4">
            <div class="bb-card my-4 p-6 w-full">
                <p>Nuovi Clienti</p>
                <p class="text-2xl text-bb-primary-500 font-bold">{{newCustomers}}</p>
            </div>
            <div class="bb-card my-4 p-6 w-full">
                <p>Nuovi Appuntamenti</p>
                <p class="text-2xl text-bb-primary-500 font-bold">{{counters.booked}}</p>
            </div>
            <div class="bb-card my-4 p-6 w-full" v-if="false">
                <p>Valore Totale</p>
                <p class="text-2xl text-bb-primary-500 font-bold">{{ (isNumber(counters.amount)) ? new Intl.NumberFormat('it-IT').format(counters.amount) : '--'}} €</p>
            </div>
            <div class="bb-card my-4 p-6 w-full">
                <p>Servizi Primari</p>
                <p class="text-2xl text-bb-primary-500 font-bold">{{(isNumber(counters.primary)) ? new Intl.NumberFormat('it-IT').format(counters.primary) : '--'}} €</p>
            </div>
            <div class="bb-card my-4 p-6 w-full">
                <p>Servizi Secondari</p>
                <p class="text-2xl text-bb-primary-500 font-bold">{{(isNumber(counters.secondary)) ? new Intl.NumberFormat('it-IT').format(counters.secondary) : '--'}} €</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="bb-card my-4 p-6">
                <TotalBookings :stores="filters.store" :from="filters.from" :to="filters.to"/>
            </div>
            <div class="bb-card my-4 p-6">
                <Services :stores="filters.store" :from="filters.from" :to="filters.to" :status="filters.status"/>
            </div>
            <div class="bb-card my-4 p-6">
                <Addon :stores="filters.store" :from="filters.from" :to="filters.to" :status="filters.status"/>
            </div>
        </div>
        

    </AppLayout>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import {usePage} from "@inertiajs/inertia-vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import TotalBookings from '@/Components/Dashboard/TotalBookings.vue';
import Services from '@/Components/Dashboard/Services.vue';
import Addon from '@/Components/Dashboard/Addon.vue';
import axios from 'axios';
import helpers from '@/helpers';
import { isNumber } from 'lodash';

const isAdmin = computed(() => usePage().props.value.is_admin);
const stores = computed(() => usePage().props.value.stores_list);
const filters = ref({
    store: [],
    from: '',
    to: '',
    status: []
})

watch(() => filters.value, (n) => {
    updateData();
}, {deep: true});

onMounted(() => {
    updateData();
})

const updateData = () => {
    updateCounters();
    updateNewCustomers();
}

const counters = ref({
    amount: '--',
    primary: '--',
    secondary: '--',
    booked: '--'
});
const updateCounters = () => {
    axios
        .get(route('admin.dashboard.counters'), {
            params: {
                from: filters.value.from,
                to: filters.value.to,
                store: filters.value.store,
                status: filters.value.status,
            }
        })
        .then((res) => {
            counters.value.amount = res.data.data.amount;
            counters.value.primary = res.data.data.primary;
            counters.value.secondary = res.data.data.secondary;
        })
        .catch(function (error) {
            console.log(error);
        });
    
    axios
        .get(route('admin.dashboard.bookedCounters'), {
            params: {
                from: filters.value.from,
                to: filters.value.to,
                store: filters.value.store,
                status: filters.value.status,
            }
        })
        .then((res) => {
            counters.value.booked = res.data.data.booked;
        })
        .catch(function (error) {
            console.log(error);
        });
}

const totalCustomers = ref('--');
const newCustomers = ref('--');
const updateNewCustomers = () => {
    axios
        .get(route('admin.dashboard.users'), {
            params: {
                from: filters.value.from,
                to: filters.value.to,
            }
        })
        .then((res) => {
            newCustomers.value = res.data.data.new;
            totalCustomers.value = res.data.data.total;
        })
        .catch(function (error) {
            console.log(error);
        });
}

</script>