<script setup>
import {onMounted, ref, computed, watch} from "vue";
import Chart from '@/Components/Dashboard/TotalBookingsChart.vue';

const props = defineProps({
    stores: [],
    from: null,
    to: null
})

onMounted(() => {
    updateData();
})


watch(() => props, () => {
    updateData();
}, {deep: true});

const updateData = () => {
    loading.value = true;
    axios
        .get(route('admin.dashboard.totalBookings'),{
            params: {
                from: props.from,
                to: props.to,
                store: props.stores
            }
        })
        .then((res) => {
            data.value = res.data.data;
            prepareData();
            loading.value = false;
        })
        .catch(function (error) {
            console.log(error);
            loading.value = false;
        });
}

const data = ref({});
const chartData = ref([]);
const loading = ref(true);
const listView = ref(false);
const legend = ref([
    {value: 0, percent: 0, key: 'todo', label: 'Todo', bg: '#519ACA'},
    {value: 0, percent: 0, key: 'progress', label: 'In progress', bg: '#F8AB51'},
    {value: 0, percent: 0, key: 'ended', label: 'Terminati', bg: '#6BC198'},
    {value: 0, percent: 0, key: 'cancelled', label: 'Cancellati', bg: '#E5414B'},
    {value: 0, percent: 0, key: 'not_shown', label: 'Non presentati', bg: '#444291'},
    {value: 0, percent: 0, key: 'not_executed', label: 'Non eseguiti', bg: '#656e87'},
]);

function prepareData()
{
    chartData.value = [];
    Object.entries(data.value.chart).forEach(([key, value]) => {
        chartData.value.push(value);
        let found = legend.value.find((l) => l.key === key);
        if (found)
        {
            found.value = value;
        }
    });
}

</script>

<template>
    <div class="stat-card">
        <div v-if="!loading" class="flex flex-col h-full justify-between items-start gap-4">
            <div class="flex justify-between items-center flex-wrap gap-2 w-full flex-grow-0">
                <p class="stat-title">Appuntamenti totali</p>
                <div class="p-2 bg-blue-50 rounded-md cursor-pointer" @click="listView = ! listView">
                    <svg v-if="!listView" width="13" height="11" viewBox="0 0 13 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.23102 0.5C0.918184 0.5 0.666504 0.757212 0.666504 1.07692V2.23077C0.666504 2.55048 0.918184 2.80769 1.23102 2.80769H2.36005C2.67289 2.80769 2.92457 2.55048 2.92457 2.23077V1.07692C2.92457 0.757212 2.67289 0.5 2.36005 0.5H1.23102ZM4.80629 0.884615C4.38996 0.884615 4.0536 1.22837 4.0536 1.65385C4.0536 2.07933 4.38996 2.42308 4.80629 2.42308H11.5805C11.9968 2.42308 12.3332 2.07933 12.3332 1.65385C12.3332 1.22837 11.9968 0.884615 11.5805 0.884615H4.80629ZM4.80629 4.73077C4.38996 4.73077 4.0536 5.07452 4.0536 5.5C4.0536 5.92548 4.38996 6.26923 4.80629 6.26923H11.5805C11.9968 6.26923 12.3332 5.92548 12.3332 5.5C12.3332 5.07452 11.9968 4.73077 11.5805 4.73077H4.80629ZM4.80629 8.57692C4.38996 8.57692 4.0536 8.92067 4.0536 9.34615C4.0536 9.77163 4.38996 10.1154 4.80629 10.1154H11.5805C11.9968 10.1154 12.3332 9.77163 12.3332 9.34615C12.3332 8.92067 11.9968 8.57692 11.5805 8.57692H4.80629ZM0.666504 4.92308V6.07692C0.666504 6.39663 0.918184 6.65385 1.23102 6.65385H2.36005C2.67289 6.65385 2.92457 6.39663 2.92457 6.07692V4.92308C2.92457 4.60336 2.67289 4.34615 2.36005 4.34615H1.23102C0.918184 4.34615 0.666504 4.60336 0.666504 4.92308ZM1.23102 8.19231C0.918184 8.19231 0.666504 8.44952 0.666504 8.76923V9.92308C0.666504 10.2428 0.918184 10.5 1.23102 10.5H2.36005C2.67289 10.5 2.92457 10.2428 2.92457 9.92308V8.76923C2.92457 8.44952 2.67289 8.19231 2.36005 8.19231H1.23102Z" fill="#859BF8"/>
                    </svg>
                    <svg v-if="listView" width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.52237 5.83335V1.02399C6.52237 0.830239 6.67307 0.666626 6.86682 0.666626C9.52983 0.666626 11.6891 2.82589 11.6891 5.4889C11.6891 5.68265 11.5255 5.83335 11.3317 5.83335H6.52237ZM0.666748 6.52225C0.666748 3.9109 2.60642 1.74949 5.12305 1.40504C5.32111 1.37705 5.48902 1.53636 5.48902 1.73657V6.8667L8.85816 10.2358C9.0024 10.3801 8.99163 10.6169 8.82587 10.7331C7.98197 11.3359 6.94862 11.689 5.83347 11.689C2.98101 11.689 0.666748 9.37686 0.666748 6.52225ZM11.9991 6.8667C12.1993 6.8667 12.3565 7.03461 12.3306 7.23267C12.1649 8.43609 11.5858 9.50603 10.7397 10.2961C10.6105 10.4167 10.4082 10.4081 10.2833 10.281L6.86682 6.8667H11.9991Z" fill="#859BF8"/>
                    </svg>
                </div>
            </div>
            <template v-if="true">
                <div v-if="!listView" class="flex justify-center items-center flex-wrap flex-grow gap-10 w-full">
                    <div class="relative">
                        <Chart class="py-0 px-1 w-56" :data="(data.total > 0) ? chartData : [0,0,0,0,0,0,100]"/>
                        <p class="absolute top-[76px] left-[70px] text-center w-[84px] text-lg font-bold">{{ data.total }}</p>
                    </div>
                    <div class="text-sm">
                        <div v-for="l in legend" class="flex justify-between gap-2 items-center">
                            <div class="flex justify-start gap-2 items-center">
                                <div class="w-3 h-3 rounded-full" :style="{backgroundColor: l.bg}"></div>
                                <p class="font-bold">{{l.value}}</p>
                            </div>
                            <p>{{l.label}}</p>
                        </div>
                    </div>
                </div>
                <div v-if="listView" class="flex-grow flex flex-col justify-center divide-y w-full">
                    <div v-for="l in legend" class="flex justify-between items-baseline gap-2 py-1.5">
                        <div class="w-3 h-3 rounded-full" :style="{backgroundColor: l.bg}"></div>
                        <p class="grow text-sm">{{l.label}}</p>
                        <p class="font-bold">{{l.value}}</p>
                    </div>
                </div>
            </template>
            <div v-else class="text-gray-700 text-center flex-1 flex flex-col justify-center">
                <p class="text-sm">Nessun paziente in anagrafica</p>
            </div>
        </div>
        <div v-else>
            Loading...
        </div>
    </div>
</template>
