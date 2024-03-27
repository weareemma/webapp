<template>
    <input
        type="checkbox"
        :checked="proxyChecked"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.checked)"
        class="
            rounded-md
            h-6
            w-6
            border-gray-300
            text-bb-primary
            shadow-sm
            cursor-pointer
            disabled:bg-bb-gray-200
            disabled:hover:bg-bb-gray-200
            disabled:opacity-75
            focus:border-bb-primary-300
            focus:ring
            focus:ring-bb-primary-200
            focus:ring-opacity-50
        "
    />
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
    emits: ["update:modelValue"],

    props: {
        checked: {
            type: [Array, Boolean],
            default: false,
        },
        modelValue: {
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        proxyChecked: {
            get() {
                if (this.modelValue) return this.modelValue;
                return this.checked;
            },

            set(val) {
                this.$emit("update:checked", val);
            },
        },
    },
});
</script>
