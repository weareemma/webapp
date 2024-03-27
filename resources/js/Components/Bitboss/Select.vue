<template>
    <multiselect
        class="bb-select w-full"
        :value="modelValue"
        :disabled="disabled"
        @change="(value) => $emit('update:modelValue', value)"
        @search:focus="setFocus"
        v-model="inner"
        ref="select"
    >
        <template #clear="{ clear }">
            <span
                class="multiselect-clear"
                :class="{
                    hidden: noClear,
                }"
                @click="(event) => clearSelection(event, clear)"
            >
            </span>
        </template>

        <template v-if="$slots.option" #option="{ option }">
            <slot name="option" :option="option" />
        </template>
    </multiselect>
</template>

<script>
import { defineComponent, onMounted, ref, toRefs, watch } from "vue";
import Multiselect from "@vueform/multiselect";

export default defineComponent({
    components: { Multiselect },

    props: ["modelValue", "disabled", "noClear"],

    emits: ["update:modelValue"],

    setup(props) {
        const modelValue = toRefs(props).modelValue;
        const inner = ref(modelValue.value);

        watch(modelValue, (v) => {
            inner.value = v;
        });

        const select = ref(null);

        function setFocus() {
            select.value.focus();
        }

        function clearSelection(event, nativeClear) {
            event.preventDefault();
            event.stopPropagation();
            nativeClear();
        }

        onMounted(() => {
            // select.value?.blur();
        });

        return { inner, select, setFocus, clearSelection };
    },
});
</script>
