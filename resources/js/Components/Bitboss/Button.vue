<template>
    <button
        :type="type"
        class="flex items-center justify-center"
        :class="classes"
    >
        <slot></slot>
    </button>
</template>

<script>
import { computed, defineComponent } from "vue";

export default defineComponent({
    props: {
        type: {
            type: String,
            default: "submit",
        },
        secondary: {
            type: Boolean,
            default: false,
        },
        success: {
            type: Boolean,
            default: false,
        },
        warning: {
            type: Boolean,
            default: false,
        },
        danger: {
            type: Boolean,
            default: false,
        },
        light: {
            type: Boolean,
            default: false,
        },
        outline: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const classes = computed(() => ({
            "bb-button-primary":
                !props.secondary &&
                !props.warning &&
                !props.danger &&
                !props.light &&
                !props.outline,
            "bb-button-primary-light":
                !props.secondary &&
                !props.warning &&
                !props.danger &&
                props.light &&
                !props.outline,
            "bb-button-primary-outline":
                !props.secondary &&
                !props.warning &&
                !props.danger &&
                props.outline,
            "bb-button-secondary":
                props.secondary && !props.light && !props.outline,
            "bb-button-secondary-light":
                props.secondary && props.light && !props.outline,
            "bb-button-secondary-outline": props.secondary && props.outline,
            "bb-button-success":
                props.success && !props.light && !props.outline,
            "bb-button-success-light":
                props.success && props.light && !props.outline,
            "bb-button-success-outline": props.success && props.outline,
            "bb-button-warning":
                props.warning && !props.light && !props.outline,
            "bb-button-warning-light":
                props.warning && props.light && !props.outline,
            "bb-button-warning-outline": props.warning && props.outline,
            "bb-button-danger": props.danger && !props.light && !props.outline,
            "bb-button-danger-light":
                props.danger && props.light && !props.outline,
            "bb-button-danger-outline": props.danger && props.outline,
        }));

        return { classes };
    },
});
</script>
