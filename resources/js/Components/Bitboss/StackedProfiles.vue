<template>
    <div class="flex items-center relative pl-2">
        <div
            v-for="(profile, i) in profiles"
            :key="i"
            class="relative -ml-2 w-7 h-7 hover:z-10"
        >
            <bb-popover mode="hover" class="h-7 w-7">
                <template #button>
                    <div
                        class="
                            h-7
                            w-7
                            rounded-full
                            border border-white
                            overflow-hidden
                            transition-transform
                            transform-gpu
                            hover:scale-125
                        "
                        @click="$emit('profileClick', profile)"
                    >
                        <img
                            :src="profile.image_url"
                            class="h-full w-full object-cover object-center"
                        />
                    </div>
                </template>
                <template #panel>
                    <slot name="popover" :item="profile" />
                </template>
            </bb-popover>
        </div>
    </div>
</template>

<script>
import BbPopover from "@/Components/Bitboss/Popover";

export default {
    components: { BbPopover },
    props: {
        profiles: { type: Array, default: [] },
    },
    emits: ["profileClick"],
};
</script>
