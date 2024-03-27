<template>
    <RadioGroup
        :class="{ disabled: disabled }"
        :disabled="disabled"
        v-model="innerValue"
    >
        <RadioGroupLabel class="sr-only">{{ groupLabel }}</RadioGroupLabel>
        <div
            class="flex"
            :class="{
                'space-x-6': (!spaceX && !vertical) || spaceX == 6,
                'space-x-4': spaceX == 4,
                'space-x-2': spaceX == 2,
                'flex-col space-y-2': vertical,
            }"
        >
            <RadioGroupOption
                as="template"
                v-for="option in computedOptions"
                :key="option.value"
                :value="option.value"
                v-slot="{ active, checked }"
            >
                <div v-if="type == 'slot'">
                    <slot
                        :active="active"
                        :checked="checked"
                        :option="option"
                    />
                </div>
                <div v-else class="flex cursor-pointer">
                    <div
                        class="w-6 h-6 rounded-full border border-bb-gray-400 grid place-items-center"
                        :class="{
                            'ring ring-bb-primary-200 ring-opacity-50': active,
                        }"
                    >
                        <div
                            class="w-4 h-4 rounded-full bg-bb-primary opacity-0 transition-opacity"
                            :class="{ 'opacity-100': checked }"
                        ></div>
                    </div>

                    <label class="inline-block ml-2 cursor-pointer">
                        {{ option.label }}
                    </label>
                </div>
            </RadioGroupOption>
        </div>
    </RadioGroup>
</template>

<script>
import {
    computed,
    defineComponent,
    ref,
    toRefs,
    watch,
    watchEffect,
} from "vue";
import {
    RadioGroup,
    RadioGroupLabel,
    RadioGroupDescription,
    RadioGroupOption,
} from "@headlessui/vue";

export default defineComponent({
    components: {
        RadioGroup,
        RadioGroupLabel,
        RadioGroupDescription,
        RadioGroupOption,
    },

    props: [
        "modelValue",
        "disabled",
        "options",
        "group-label",
        "type",
        "space-x",
        "vertical",
    ],

    emits: ["update:modelValue"],

    setup(props, { emit }) {

        const innerValue = ref(null);
        watchEffect(() => {
            innerValue.value = props.modelValue;
        });
        watch(innerValue, (value) => {
            emit("update:modelValue", value);
        });

        const computedOptions = computed(() => {
            if (props.type == "yes-no") {
                return [
                    { label: "Si", value: 1 },
                    { label: "No", value: 0 },
                ];
            }

            return props.options;
        });

        const { disabled } = toRefs(props);
        return { innerValue, disabled, computedOptions };
    },
});
</script>
