<script>
import { defineComponent, h } from 'vue'

import { Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

export default defineComponent({
    name: 'DoughnutChart',
    components: {
        Doughnut
    },
    props: {
        chartId: {
            type: String,
            default: 'doughnut-chart'
        },
        width: {
            type: Number,
        },
        height: {
            type: Number,
            default: 180
        },
        cssClasses: {
            default: '',
            type: String
        },
        styles: {
            type: Object,
            default: () => { }
        },
        data: {
            type: Array,
            default: [0,0,0,0,0,0,100]
        },
    },
    setup(props) {
        const chartData = {
            labels: [
                'Todo',
                'In progress',
                'Terminati',
                'Cancellati',
                'Non presentati',
                'Non eseguiti'
            ],
            datasets: [
                {
                    backgroundColor: [
                        '#519ACA',
                        '#F8AB51',
                        '#6BC198',
                        '#E5414B',
                        '#444291',
                        '#656e87',
                        '#dddddd',
                    ],
                    data: props.data
                }
            ],
        }

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            borderWidth: 0,
            cutout: 55,
            plugins: {
                tooltip: {
                    enabled: true
                },
                legend: {
                    display: false,
                    position: 'right',
                    pointStyle: 'circle',
                    textAlign: 'center',
                    labels: {
                        color: '#1A202A',
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }

        return () =>
            h(Doughnut, {
                chartData,
                chartOptions,
                chartId: props.chartId,
                width: props.width,
                height: props.height,
                cssClasses: props.cssClasses,
                styles: props.styles,
                plugins: props.plugins
            })
    }
})
</script>
