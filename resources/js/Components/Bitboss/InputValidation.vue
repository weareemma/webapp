<template>
    <div v-if="hasError" class="text-xs text-bb-danger mt-1">
        {{ errorMessage }}
    </div>
</template>

<script>
import { usePage } from "@inertiajs/inertia-vue3";
import { computed, defineComponent } from "vue";

export default defineComponent({
    props: {
        name: {
            type: String,
            default: null,
        },
        form: {
            type: Object,
            default: null,
        },
    },

    setup(props) {
        const errors = computed(() => {
            if (props.form) return props.form.errors;
            return usePage().props.value.errors;
        });

        const hasError = computed(() => {
            if (!props.name) return Object.keys(errors.value).length;
            return Object.keys(errors.value).includes(props.name);
        });

        const errorMessage = computed(() => {
            if (!props.name) return Object.values(errors.value).join("\n");
            if (props.name in errors.value) return errors.value[props.name];
            return "";
        });

        return { hasError, errorMessage };
    },
});
</script>
