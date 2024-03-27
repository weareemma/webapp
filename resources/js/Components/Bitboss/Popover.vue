<template>
    <Popover class="relative flex" v-slot="{ open, close }">
        <PopoverButton
            ref="popButton"
            class="inline-block"
            @mouseenter="hoverShow"
            @mouseleave="hoverHide"
        >
            <slot name="button" />
        </PopoverButton>

        <teleport to="body">
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <PopoverPanel
                    static
                    ref="popPanel"
                    class="fixed z-50 top-4 left-4"
                    v-show="getOpenStatus(open)"
                >
                    <slot name="panel" :open="open" :close="close" />
                </PopoverPanel>
            </transition>
        </teleport>
    </Popover>
</template>

<script>
import { defineComponent, ref } from "vue";
import { nextTick, watch } from "@vue/runtime-core";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";

export default defineComponent({
    components: { Popover, PopoverButton, PopoverPanel },

    props: {
        mode: { type: String, default: "hover" },
        distance: { type: Number, default: 4 },
        position: {
            type: String,
            default: "bottom-right",
        },
    },

    setup(props) {
        const popButton = ref(null);
        const popPanel = ref(null);
        const isOpen = ref(false);

        watch(isOpen, () => {
            nextTick(() => {
                if (isOpen.value) positionPanel();
            });
        });

        function hoverShow() {
            if (props.mode == "button") return;
            isOpen.value = true;
        }

        function hoverHide() {
            if (props.mode == "button") return;
            isOpen.value = false;
        }

        function getOpenStatus(defaultOpen) {
            if (props.mode == "button") isOpen.value = defaultOpen;
            return isOpen.value;
        }

        function positionPanel() {
            const viewportWidth = window.innerWidth;

            const buttonRect = popButton.value.el.getBoundingClientRect();

            const panel = popPanel.value.el;
            const panelRect = panel.getBoundingClientRect();

            const pos = props.position;

            let yPos = "mid";
            if (pos.includes("top")) yPos = "top";
            if (
                pos.includes("bottom") ||
                (pos.includes("mid") && pos.includes("center"))
            )
                yPos = "bottom";

            let xPos = "right";
            if (pos.includes("left")) xPos = "left";
            if (pos.includes("center")) xPos = "center";

            let estimatedLeft = 0;
            switch (xPos) {
                case "right":
                    estimatedLeft =
                        yPos == "mid"
                            ? buttonRect.x + buttonRect.width + props.distance
                            : buttonRect.x +
                              buttonRect.width / 2 +
                              props.distance;
                    break;
                case "left":
                    estimatedLeft =
                        yPos == "mid"
                            ? buttonRect.x - panelRect.width - props.distance
                            : buttonRect.x +
                              buttonRect.width / 2 -
                              panelRect.width -
                              props.distance;
                    break;
                case "center":
                    estimatedLeft =
                        buttonRect.x +
                        buttonRect.width / 2 -
                        panelRect.width / 2;
                    break;
            }
            if (estimatedLeft < 0) estimatedLeft = 0;
            if (estimatedLeft > viewportWidth)
                estimatedLeft = viewportWidth - panelRect.width;

            let estimatedTop = 0;
            switch (yPos) {
                case "bottom":
                    estimatedTop =
                        buttonRect.y + buttonRect.height + props.distance;
                    break;
                case "top":
                    estimatedTop =
                        buttonRect.y - panelRect.height - props.distance;
                    break;
                case "mid":
                    estimatedTop =
                        buttonRect.y +
                        buttonRect.height / 2 -
                        panelRect.height / 2;
                    break;
            }

            panel.style.left = `${estimatedLeft}px`;
            panel.style.top = `${estimatedTop}px`;
        }

        return {
            popButton,
            popPanel,
            hoverShow,
            hoverHide,
            getOpenStatus,
        };
    },
});
</script>
