<template>
    <bb-link @click="back">
        <ChevronLeftIcon class="h-4" />
        <template v-if="text">
            {{ text }}
        </template>
        <template v-else>
            Torna indietro
        </template>
    </bb-link>
</template>

<script>
import { defineComponent } from "vue";
import { ChevronLeftIcon } from "@heroicons/vue/solid";
import { Inertia } from "@inertiajs/inertia";

export default defineComponent({
    components: { ChevronLeftIcon },

    props: {
        url: {
            type: String,
            default: null,
        },
        text: {
            type: String,
            default: null,
        },
    },

    setup(props) {
        function back() {
            if (props.url) {
                Inertia.visit(props.url);
                return;
            }
            window.history.back();
        }
        return { back };
    },
});
</script>
