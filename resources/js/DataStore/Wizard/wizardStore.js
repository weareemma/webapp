import { defineStore } from "pinia";
import {ref, computed, watch} from "vue";
import dayjs from "dayjs";
import {usePage} from "@inertiajs/inertia-vue3";
import _ from 'lodash';
import {Inertia} from "@inertiajs/inertia";
import helpers from "@/helpers.js";

export const useWizardStore = defineStore('wizard', () => {
    const activeStep = ref({
        value: 0,
        name: 'step_store',
        next: 'step_people'
    })

    const ready = ref(true);
    const totalReady = ref(true);

    const wizardGeneral = ref({
        customers: [],
        original_booking: null,
        editing_id: null,
        disable_edit: false,
        stores: [],
        primaries: [],
        payment_infos: null,
        invalid_discount_code: null,
        discount_errors: null,
        discount_code: null,
        discount: null,
        booking_infos: null,
        stylists: null,
        stylist: null,
        with_refund: false,
        subscribed: false,
        alreadyPaid: 0,
        order: null
    })

    const wizardSelection = ref({
        customer_id: null,
        store_id: null,
        washing_stations: 0,
        multiple: false,
        different_services: false,
        selected_day: null,
        selected_slot: null,
        available_days: null
    })

    const emptyPeople = () => {
        return {
            name: 0,
            primary_service: null,
            addons: {
                updo: [],
                massage: [],
                treatment: [],
            },
            services_selection: [],
            total_time: 0,
            total_price: 0
        }
    }

    const people = ref([emptyPeople()]);

    const isValid = computed(() => {
        return {
            step_store: true,
            step_customer: true,
            step_people: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id,
            step_primary_hair_service: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id,
            step_updo: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id && wizardCheckForPrimaryService(),
            step_addons: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id && wizardCheckForPrimaryService(),
            step_stylist: !!wizardSelection.value.store_id && wizardCheckForPrimaryService(),
            step_calendar: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id && wizardCheckForPrimaryService(),
            step_checkout: !! wizardSelection.value.store_id && !! wizardSelection.value.store_id && wizardCheckForPrimaryService() && !! wizardSelection.value.selected_day && !! wizardSelection.value.selected_slot
        }
    })

    const disableEdit = ref(false)
    const isStepCheckout = computed(() => activeStep.value.name === 'step_checkout')
    const isStylist = computed(() => wizardGeneral.value.stylist)

    const loading =  ref(false);

    const wizardLoadBooking = function (booking) {
        if (booking)
        {
            wizardGeneral.value.original_booking = booking;
            wizardGeneral.value.editing_id = booking.id;
            wizardGeneral.value.disable_edit = booking.customer_must_pay;
            wizardGeneral.value.booking_infos = booking.additional_data;
            wizardGeneral.value.discount = booking.additional_data?.discount;
            wizardGeneral.value.discount_code = booking.additional_data?.discount?.code;
            wizardGeneral.value.stylist = booking.stylist ?? null;
            wizardGeneral.value.alreadyPaid = booking.order?.paid ?? booking.paid_amount ?? 0;
            wizardGeneral.value.order = booking.order;

            wizardSelection.value.customer_id = booking.customer_id;
            wizardSelection.value.store_id = booking.store_id;
            wizardSelection.value.multiple = booking.multiple;
            wizardSelection.value.different_services = booking.different_services;
            wizardSelection.value.selected_day = { date: booking.date };
            wizardSelection.value.selected_slot = {
                time: dayjs(`2000-01-01 ${booking.start}`).format(
                    "HH:mm"
                ),
            }

            // Fill booking owner
            wizardResetPeople(true);
            let owner = emptyPeople();
            owner.name = 0;
            owner.primary_service = booking.slots[0].service ?? null;
            for (let i = 1; i < booking.slots?.length; i++) {
                const slot = booking.slots[i];
                if (slot.service.type)
                {
                    owner.addons[slot.service.type].push(slot.service);
                }
            }

            people.value.push(owner);

            // Fill booking people
            if (booking.multiple)
            {
                booking.get_children.forEach((b, idx) => {
                    let p = emptyPeople();
                    p.name = b.guest;
                    p.primary_service = b.slots[0].service ?? null;
                    for (let i = 1; i < b.slots?.length; i++) {
                        const slot = b.slots[i];
                        p.addons[slot.service.type].push(slot.service);
                    }
                    people.value.push(p);
                })
            }

            // Fetch data
            wizardFetchData('step_store');

            people.value.forEach((p) => {
                wizardFetchData('step_primary_hair_service', {
                    primaryId: p.primary_service.id,
                    name: p.name
                });
            })
        }
    }

    const wizardResetPeople = function (forced = false) {
        people.value = [];
        if (! forced ) people.value.push(emptyPeople());
    }

    const wizardResetAddons = function (name) {
        let p = people.value.find(p => p.name === name);
        if (p)
        {
            p.addons = {
                updo: [],
                massage: [],
                treatment: [],
            }
        }
    }

    const wizardResetPrimary = function (name) {
        let p = people.value.find(p => p.name === name);
        if (p)
        {
            p.primary_service = null;
        }
    }

    const wizardAddPeople = function (n) {
        for (let i=1; i<=n; i++)
        {
            let p = emptyPeople();
            p.name = i;
            people.value.push(p);
        }
    }

    const wizardCheckForPrimaryService = function () {
        let found = people.value.find(p => p.primary_service === null);
        return !found;
    }

    const wizardFetchData = async function (stepName, data = null) {
        ready.value = false;
        switch (stepName)
        {
            case 'step_store':
                await axios.post(route("booking.hair-services.primary")).then((response) => {
                    wizardGeneral.value.primaries = response.data.data;
                })
                break;

            case 'step_customer':
                await axios.post(route("booking.customer.subscribed"), {
                    customer: wizardSelection.value.customer_id
                }).then((response) => {
                    wizardGeneral.value.subscribed = response.data.data;
                })
                break;

            case 'step_primary_hair_service':
                await axios.post(route("booking.hair-services.addon", data.primaryId)).then((response) => {
                    let p = people.value.find(p => p.name === data.name);
                    if (p)
                    {
                        p.services_selection = response.data.data;
                    }
                });
                break;
            case 'step_addons':
                await axios.post(route("booking.hair-services.stylists", data.storeId)).then((response) => {
                    wizardGeneral.value.stylists = response.data.data
                }).catch((err) => {
                    console.error(err)
                });
                break;
            default:
                break;
        }

        ready.value = true;
    }

    return {
        ready,
        totalReady,
        activeStep,
        disableEdit,
        isStepCheckout,
        wizardSelection,
        isValid,
        isStylist,
        loading,
        people,
        wizardGeneral,
        wizardResetPeople,
        wizardResetPrimary,
        wizardLoadBooking,
        wizardAddPeople,
        wizardResetAddons,
        wizardFetchData
    }
})
