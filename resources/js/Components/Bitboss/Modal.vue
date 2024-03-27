<template>
  <teleport to="body">
    <TransitionRoot appear as="template" :show="isOpen">
      <Dialog
        @close="close()"
        class="fixed z-50 inset-0 grid place-items-center p-2 sm:p-0 overflow-y-auto"
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
          <DialogOverlay class="fixed z-0 inset-0 bg-black opacity-30" />
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
            class="relative z-10 max-w-full py-8"
            :class="{
              'xl:w-[1280px] xl:max-w-7xl': size == 'xl',
              'lg:w-[1024px] lg:max-w-5xl': size == 'lg' || size == 'xl',
              'sm:w-[576px] sm:max-w-xl':
                size == 'md' || size == 'lg' || size == 'xl',
              'sm:w-[384px] sm:max-w-sm': size == 'sm',
            }"
          >
            <template v-if="$slots.close">
              <slot name="close" :close="close"></slot>
            </template>
            <template v-else>
              <XIcon
                v-if="withClose"
                class="absolute top-16 right-8 w-8 h-8 text-white cursor-pointer"
                @click="close()"
              />
            </template>
            <slot></slot>
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
} from "@headlessui/vue";
import { XIcon } from "@heroicons/vue/solid";

export default {
  components: {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogOverlay,
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
    withClose: {
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
