<template>
    <div
        class="
            flex flex-col
            text-sm
            lg:flex-row lg:items-center lg:space-x-1 lg:text-base
        "
    >
        <template v-for="(b, i) in breadcrumbs" :key="i">
            <div class="flex items-center space-x-1">
                <template v-if="i < breadcrumbs.length - 1 && !noLinks">
                    <bb-link v-if="b.href" :href="b.href">
                        {{ b.label }}
                    </bb-link>
                    <div v-else class="font-bold text-bb-black-500">
                        {{ b.label }}
                    </div>
                </template>
                <template v-else>
                    <div
                        class="font-bold"
                        :class="{
                            'text-bb-primary': !noLinks,
                        }"
                    >
                        {{ b.label }}
                    </div>
                </template>

                <template v-if="i < breadcrumbs.length - 1">
                    <ChevronRightIcon
                        class="
                            transform-gpu
                            rotate-90
                            lg:rotate-0
                            h-4
                            text-bb-black-500
                        "
                    />
                </template>
            </div>
        </template>
    </div>
</template>

<script>
import { ChevronRightIcon } from "@heroicons/vue/solid";

export default {
    components: {
        ChevronRightIcon,
    },
    props: {
        breadcrumbs: {
            type: Array,
            default: [],
        },
        noLinks: {
            type: Boolean,
            default: false,
        },
    },
};
</script>
