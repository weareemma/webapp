import { usePage } from "@inertiajs/inertia-vue3";
import { useColorSwitch } from "@/Composables/colorSwitch";
import { useI18n } from "vue-i18n";
import { useAlerts } from "@/Composables/alerts";
import { computed, watch } from "vue";

export function initPage() {
    // set color swatch
    const { setSwatch } = useColorSwitch();
    setSwatch(usePage().props.value.color);

    // set locale
    const { locale } = useI18n();
    locale.value = usePage().props.value.locale;

    // read errors
    const { showError } = useAlerts();
    const errors = computed(() => usePage().props.value.errors);
    watch(errors, () => {
        if (errors.value && typeof errors.value == "object") {
            Object.keys(errors.value).forEach((errCode) => {
                switch (String(errCode)) {
                    case "400":
                        showError("errors.title", "errors.400");
                        break;
                    case "403":
                        showError("errors.title", "errors.403");
                        break;
                    case "404":
                        switch (errors.value[errCode]) {
                            case "company_not_selected":
                                showError(
                                    "errors.title",
                                    "errors.404.company_not_selected"
                                );
                                break;

                            default:
                                showError("errors.title", "errors.404.default");
                                break;
                        }
                        break;
                    case "422":
                        const error = errors.value[errCode];
                        showError("errors.title", error);
                        break;
                }
            });
        }
    });
}
