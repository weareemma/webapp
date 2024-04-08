<template>
    <teleport to="body">
        <TransitionRoot appear as="template" :show="isOpen">
            <Dialog
                @close="close()"
                class="
                    fixed
                    z-50
                    inset-0
                    grid
                    place-items-center
                    p-4
                    md:p-6
                    overflow-y-auto
                "
            >
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <DialogOverlay
                        class="fixed z-0 inset-0 bg-black opacity-30"
                    />
                </TransitionChild>

                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95"
                >
                    <div
                        class="relative z-10 max-w-full"
                        :class="{
                            'md:w-[896px] md:max-w-4xl': size == 'lg',
                            'md:w-[576px] md:max-w-xl': size == 'md',
                            'md:w-[384px] md:max-w-sm': size == 'sm',
                        }"
                    >
                        <!-- header -->
                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                p-6
                                rounded-t-xl
                                border
                            "
                            :class="{
                                'bg-white border-none pb-0':
                                    type == 'plain',
                                'bg-white border-bb-gray-300':
                                    type == 'default',
                                'bg-bb-primary-100 border-bb-primary-100':
                                    type == 'primary',
                                'bg-bb-success-100 border-bb-success-100':
                                    type == 'success',
                                'bg-bb-warning-100 border-bb-warning-100':
                                    type == 'warning',
                                'bg-bb-danger-100 border-bb-danger-100':
                                    type == 'danger',
                            }"
                        >
                            <div class="flex flex-col justify-center">
                                <DialogTitle
                                    class="font-bold text-2xl"
                                    :class="{
                                        'text-bb-primary':
                                            type == 'default' ||
                                            type == 'primary',
                                        'text-bb-success': type == 'success',
                                        'text-bb-warning': type == 'warning',
                                        'text-bb-danger': type == 'danger',
                                    }"
                                >
                                    <slot name="title"></slot>
                                </DialogTitle>

                                <DialogDescription v-if="$slots.description">
                                    <div class="text-sm">
                                        <slot name="description"></slot>
                                    </div>
                                </DialogDescription>
                            </div>

                            <button class="p-2 focus:outline-none" @click="close()">
                                <XIcon
                                    class="w-6 h-6"
                                    :class="{
                                        'text-bb-primary':
                                            type == 'default' ||
                                            type == 'primary',
                                        'text-bb-success': type == 'success',
                                        'text-bb-warning': type == 'warning',
                                        'text-bb-danger': type == 'danger',
                                    }"
                                />
                            </button>
                        </div>

                        <!-- body -->
                        <div
                            class="
                                p-6
                                rounded-b-xl
                                bg-white
                                border-bb-gray-300
                            "
                        >
                            <slot></slot>

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-end
                                    pt-6
                                    space-x-6
                                "
                            >
                                <button
                                    v-if="!noCancel"
                                    :class="{
                                        'bb-link-primary': type != 'danger',
                                        'bb-link-danger': type == 'danger',
                                    }"
                                    @click="close()"
                                >
                                    Indietro
                                </button>
                                <slot name="buttons"></slot>
                            </div>
                        </div>
                    </div>
                </TransitionChild>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>

<script>
import { ref } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogOverlay,
    DialogTitle,
    DialogDescription,
} from "@headlessui/vue";
import { XIcon } from "@heroicons/vue/solid";

export default {
    components: {
        TransitionRoot,
        TransitionChild,
        Dialog,
        DialogOverlay,
        DialogTitle,
        DialogDescription,
        XIcon,
    },

    props: {
        size: {
            type: String,
            default: "sm",
        },
        type: {
            type: String,
            default: "default",
        },
        noCancel: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['close'],

    setup(_, {emit}) {
        let isOpen = ref(false);

        return {
            isOpen,
            open() {
                isOpen.value = true;
            },
            close() {
                isOpen.value = false;
                emit('close')
            },
        };
    },
};
</script>
