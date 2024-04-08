<template>
    <textarea
        class="bb-form-control w-full"
        :class="{ readonly: readonly }"
        :readonly="readonly"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="textarea"
    >
    <slot/>
    </textarea>
</template>

<script>
import { defineComponent, ref, toRefs } from "vue";

export default defineComponent({
    props: ["modelValue", "readonly"],

    emits: ["update:modelValue"],

    setup(props) {
        const { readonly } = toRefs(props);
        const textarea = ref(null);

        function focus() {
            textarea.value.focus();
        }

        return { textarea, focus, readonly };
    },
});
</script>
