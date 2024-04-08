<template>
    <teleport to="body">
        <TransitionRoot as="template" :show="isOpen">
            <Dialog
                as="div"
                class="fixed inset-0 overflow-hidden z-50"
                @close="close()"
            >
                <div class="absolute inset-0 overflow-hidden">
                    <DialogOverlay class="absolute inset-0" />

                    <div
                        class="
                            pointer-events-none
                            fixed
                            inset-y-0
                            right-0
                            flex
                            max-w-full
                            pl-10
                        "
                    >
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-500 sm:duration-700"
                            enter-from="translate-x-full"
                            enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-500 sm:duration-700"
                            leave-from="translate-x-0"
                            leave-to="translate-x-full"
                        >
                            <div class="pointer-events-auto w-screen max-w-md">
                                <div
                                    class="
                                        flex
                                        h-full
                                        flex-col
                                        bg-white
                                        py-6
                                        shadow-xl
                                        rounded-l-2xl
                                    "
                                >
                                    <div class="px-4 sm:px-8">
                                        <div
                                            class="
                                                flex
                                                items-start
                                                justify-between
                                                pb-6
                                                border-b border-bb-black-300
                                            "
                                        >
                                            <DialogTitle>
                                                <h1
                                                    class="
                                                        uppercase
                                                        font-bold
                                                        text-2xl text-bb-primary
                                                    "
                                                >
                                                    <slot name="title" />
                                                </h1>
                                            </DialogTitle>
                                            <div
                                                class="
                                                    ml-4
                                                    flex
                                                    h-8
                                                    text-bb-primary
                                                    items-center
                                                "
                                            >
                                                <button @click="close()">
                                                    <span class="sr-only">
                                                        Close panel
                                                    </span>
                                                    <XIcon
                                                        class="h-7 w-7"
                                                        aria-hidden="true"
                                                    />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="
                                            relative
                                            flex-1
                                            pt-6
                                            px-4
                                            sm:px-8
                                            overflow-y-auto
                                        "
                                    >
                                        <slot />
                                    </div>
                                    <div
                                        v-if="'footer' in $slots"
                                        class="pt-6 px-4 sm:px-8"
                                    >
                                        <slot name="footer" />
                                    </div>
                                </div>
                            </div>
                        </TransitionChild>
                    </div>
                </div>
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
} from "@headlessui/vue";
import { XIcon } from "@heroicons/vue/solid";

export default {
    components: {
        TransitionRoot,
        TransitionChild,
        Dialog,
        DialogOverlay,
        DialogTitle,
        XIcon,
    },

    setup() {
        let isOpen = ref(false);

        return {
            isOpen,
            open() {
                isOpen.value = true;
            },
            close() {
                isOpen.value = false;
            },
        };
    },
};
</script>
