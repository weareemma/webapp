<template>
    <input
        class="bb-form-control w-full"
        :class="{ readonly: readonly }"
        :readonly="readonly"
        :value="modelValue"
        @input="emitValue"
        @focus="handleFocus"
        @blur="handleBlur"
        ref="input"
    />
</template>

<script>
import { defineComponent, onMounted, ref, toRefs } from "vue";

export default defineComponent({
    props: ["modelValue", "readonly"],

    emits: ["update:modelValue"],

    setup(props, { emit }) {
        const { readonly } = toRefs(props);
        const input = ref(null);
        const type = ref("text");

        onMounted(() => {
            type.value = input.value.type;
            handleBlur();
        });

        function handleFocus() {
            if (type.value === "date") input.value.type = "date";
        }

        function handleBlur() {
            if (type.value === "date" && !input.value.value) input.value.type = "text";
        }

        function focus() {
            input.value.focus();
        }

        function emitValue(event) {
            let val = event.target.value;
            if (event.target.type === "number") val = Number(val);
            emit("update:modelValue", val);
        }

        return { input, handleFocus, handleBlur, focus, readonly, emitValue };
    },
});
</script>
