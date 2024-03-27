<template>
    <Link :href="href" :class="classes">
        <slot></slot>
    </Link>
</template>

<script>
import { defineComponent } from "vue";
import { Link } from "@inertiajs/inertia-vue3";

export default defineComponent({
    components: { Link },
    props: {
        href: {
            type: String,
            default: "#",
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
    },
    setup(props) {
        const classes = {
            "bb-link-primary": !props.secondary,
            "bb-link-secondary": props.secondary,
            "bb-link-success": props.success,
            "bb-link-warning": props.warning,
            "bb-link-danger": props.danger,
        };

        return { classes };
    },
});
</script>
