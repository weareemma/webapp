<template>
    <Switch
        :value="modelValue"
        @update:modelValue="$emit('update:modelValue', !modelValue)"
        :class="modelValue ? 'bg-bb-primary' : 'bg-bb-gray-300'"
        class="relative inline-flex items-center h-6 rounded-full w-11 focus:outline-none focus:ring focus:ring-offset-1 focus:ring-bb-primary-200 focus:ring-opacity-50"
    >
        <span class="sr-only">{{ srDescription }}</span>
        <span
            :class="modelValue ? 'translate-x-[21px]' : 'translate-x-[1px]'"
            class="
                transition
                duration-200
                ease-in-out
                transform
                inline-block
                w-[22px]
                h-[22px]
                bg-white
                rounded-full
            "
        />
    </Switch>
</template>

<script>
import { defineComponent } from "vue";
import { Switch } from "@headlessui/vue";

export default defineComponent({
    components: { Switch },

    props: ["modelValue", "sr-description"],

    emits: ["update:modelValue"],
});
</script>
