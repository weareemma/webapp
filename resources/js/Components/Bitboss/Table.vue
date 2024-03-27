<template>
    <div class="bb-table" :class="{ compact }">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th
                            class="check"
                            v-if="selectable && (selection || selectableSingle)"
                        >
                            <bb-checkbox
                                v-if="!selectableSingle"
                                :checked="
                                    collection.length == checkedItems.length
                                "
                                @input="toggleCheckAll"
                            />
                        </th>
                        <th
                            v-for="(column, cIndex) in columns"
                            :key="cIndex"
                            :class="{ ...getCellAlignment(column.align) }"
                        >
                            {{ column.label }}
                        </th>
                        <th v-if="filteredActions.length"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in collection"
                        :key="item.id ?? `idx-${index}`"
                    >
                        <td
                            class="check"
                            v-if="selectable && (selection || selectableSingle)"
                        >
                            <bb-radio
                                v-if="selectableSingle"
                                :name="`bb-table-radio`"
                                :disabled="
                                    selectableCondition
                                        ? !selectableCondition(item)
                                        : false
                                "
                                :checked="itemIsChecked(item)"
                                @input="(e) => checkItem(e, item)"
                            />
                            <bb-checkbox
                                v-else
                                :disabled="
                                    selectableCondition
                                        ? !selectableCondition(item)
                                        : false
                                "
                                :checked="itemIsChecked(item)"
                                @input="(e) => checkItem(e, item)"
                            />
                        </td>
                        <td
                            v-for="(column, cIndex) in columns"
                            :key="cIndex"
                            :class="{
                                ...getCellAlignment(column.align),
                                ...getClasses(column.classes, item),
                            }"
                        >
                            <template v-if="'key' in column">
                                <span
                                    :title="
                                        getFormattedData(
                                            getData(item, column.key),
                                            column.format
                                        )
                                    "
                                v-html="getFormattedData(
                                            getData(item, column.key),
                                            column.format
                                        )">
                                </span>
                            </template>
                            <template v-else-if="'computed' in column">
                                {{ column.computed(item) }}
                            </template>
                            <slot
                                v-else-if="'slot'"
                                :name="column.slot"
                                :item="item"
                                :index="index"
                                :column="column"
                                :column-index="cIndex"
                            >
                            </slot>
                        </td>
                        <td v-if="filteredActions.length">
                            <div class="actions">
                                <template
                                    v-for="action in filteredActions"
                                    :key="action"
                                >
                                    <button
                                        v-if="
                                            action.condition
                                                ? action.condition(item)
                                                : true
                                        "
                                        :class="action.name"
                                        @click="
                                            $emit('action', {
                                                action: action.name,
                                                item,
                                            })
                                        "
                                    >
                                        <component
                                            v-if="action.icon"
                                            :is="action.icon"
                                        />
                                        <template v-else>
                                            <LoginIcon
                                                v-if="action.name == 'go'"
                                                class="rotate-180"
                                            />
                                            <LoginIcon
                                                v-if="action.name == 'restore'"
                                            />
                                            <EyeIcon
                                                v-if="action.name == 'view'"
                                            />
                                            <PencilIcon
                                                v-if="action.name == 'edit'"
                                            />
                                            <UserIcon
                                                v-if="
                                                    action.name == 'impersonate'
                                                "
                                            />
                                            <TrashIcon
                                                v-if="action.name == 'delete'"
                                            />
                                            <DownloadIcon
                                                v-if="action.name == 'download'"
                                            />
                                            <EyeIcon
                                                v-if="action.name == 'markAsRead'"
                                            />
                                          <PlusIcon
                                              v-if="action.name == 'wizard'"
                                          />
                                          <RefreshIcon
                                              v-if="action.name == 'refund'"
                                          />
                                          <CheckIcon
                                              v-if="action.name == 'resolve'"
                                          />
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!collection.length" class="empty">
                <!-- <img src="/img/sad-illustration.png" /> -->
                <h2 class="title-md text-bb-gray-500">
                  Nessun elemento
                </h2>
            </div>
        </div>

        <div v-if="links && links.length > 3" class="links">
            <Link
                v-for="(link, i) in links"
                :key="i"
                :class="{ active: link.active }"
                :href="link.url"
                preserve-state
                preserve-scroll
            >
                <ChevronDoubleLeftIcon v-if="i == 0" class="h-3 w-3" />
                <ChevronDoubleRightIcon
                    v-else-if="i == links.length - 1"
                    class="h-3 w-3"
                />
                <span v-else>{{ link.label }}</span>
            </Link>
        </div>
    </div>
</template>

<script>
import { computed, defineComponent, ref, watch } from "vue";
import BbCheckbox from "@/Components/Bitboss/Checkbox.vue";
import BbRadio from "@/Components/Bitboss/Radio.vue";
import { Link } from '@inertiajs/inertia-vue3';
import {
    EyeIcon,
    PencilIcon,
    TrashIcon,
    UserIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    DownloadIcon,
    PlusIcon,
    RefreshIcon,
    CheckIcon
} from "@heroicons/vue/solid";
import { LoginIcon } from "@heroicons/vue/outline";
import { useTableUtils } from "@/Composables/tableUtilities.js";

export default defineComponent({
    components: {
        BbCheckbox,
        BbRadio,
        EyeIcon,
        PencilIcon,
        TrashIcon,
        UserIcon,
        LoginIcon,
        ChevronDoubleLeftIcon,
        ChevronDoubleRightIcon,
        DownloadIcon,
        Link,
        PlusIcon,
        RefreshIcon,
        CheckIcon
    },

    props: {
        collection: {
            type: Array,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        links: {
            type: [Array, null],
            default: null,
        },
        actions: {
            type: [Array, null],
            default: null,
        },
        compact: {
            type: Boolean,
            default: false,
        },

        selectable: {
            type: Boolean,
            default: false,
        },
        selectableSingle: {
            type: Boolean,
            default: false,
        },
        selectableCondition: {
            type: [Function, null],
            default: null,
        },
        selection: {
            type: [Array, Object, null],
            default: null,
        },
    },

    emits: ["update:selection", "action"],

    setup(props, { emit }) {
        const filteredActions = computed(() => {
            const fa = [];
            if (!props.actions) return fa;
            for (let i = 0; i < props.actions.length; i++) {
                const action = props.actions[i];
                if (typeof action === "object") {
                    if (!"name" in action || !"condition" in action) continue;
                    fa.push(action);
                } else {
                    fa.push({ name: action, condition: () => true });
                }
            }
            return fa;
        });

        // selection
        const checkedItems = ref(props.selection);

        watch(checkedItems, () => {
            emit("update:selection", checkedItems.value);
        });

        function toggleCheckAll(event) {
            checkedItems.value = [];
            if (event.target.checked) {
                if (props.selectableCondition) {
                    return (checkedItems.value = props.collection.filter(
                        (item) => {
                            return props.selectableCondition(item);
                        }
                    ));
                }
                checkedItems.value = [...props.collection];
            }
        }

        function itemIsChecked(item) {
            if (props.selectableSingle) return checkedItems.value == item;
            return checkedItems.value.includes(item);
        }

        function checkItem(event, item) {
            if (props.selectableSingle) {
                if (event.target.checked) return (checkedItems.value = item);
                return (checkedItems.value = null);
            }
            if (event.target.checked) return checkedItems.value.push(item);
            const index = checkedItems.value.findIndex((i) => i === item);
            if (index > -1) checkedItems.value.splice(index, 1);
        }

        return {
            ...useTableUtils(),
            filteredActions,
            checkedItems,
            toggleCheckAll,
            itemIsChecked,
            checkItem,
        };
    },
});
</script>
