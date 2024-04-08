import "./bootstrap";
import "../css/app.css";

import { createApp, h, defineAsyncComponent } from "vue";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import { InertiaProgress } from "@inertiajs/progress";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import { createPinia } from 'pinia';
import resetStore from "./DataStore/Utils";

const appName =
  window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

const pinia = createPinia()
pinia.use(resetStore)

// Global static components
import { Link } from "@inertiajs/inertia-vue3";
import BbDialog from "@/Components/Bitboss/Dialog.vue";
import BbButton from "@/Components/Bitboss/Button.vue";
import BbLabel from "@/Components/Bitboss/Label.vue";
import BbInput from "@/Components/Bitboss/Input.vue";
import BbInputValidation from "@/Components/Bitboss/InputValidation.vue";
import BbSelect from "@/Components/Bitboss/Select.vue";
import BbCheckbox from "@/Components/Bitboss/Checkbox.vue";
import BbBackLink from "@/Components/Bitboss/BackLink.vue";
import BbLink from "@/Components/Bitboss/Link.vue";
import BbModal from "@/Components/Bitboss/Modal.vue";
import BbTextarea from "@/Components/Bitboss/Textarea.vue";
import BbRadioGroup from "@/Components/Bitboss/RadioGroup.vue";
import BbSwitch from "@/Components/Bitboss/Switch.vue";
import BbUploader from "@/Components/Bitboss/Uploader.vue";

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob("./Pages/**/*.vue")
    ),
  setup({ el, app, props, plugin }) {
    return createApp({ render: () => h(app, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .use(pinia)
      .component("Link", Link)
      .component("BbDialog", BbDialog)
      .component("BbButton", BbButton)
      .component("BbLabel", BbLabel)
      .component("BbInput", BbInput)
      .component("BbInputValidation", BbInputValidation)
      .component("BbSelect", BbSelect)
      .component("BbCheckbox", BbCheckbox)
      .component("BbBackLink", BbBackLink)
      .component("BbLink", BbLink)
      .component("BbModal", BbModal)
      .component("BbTextarea", BbTextarea)
      .component("BbRadioGroup", BbRadioGroup)
      .component("BbSwitch", BbSwitch)
      .component("BbUploader", BbUploader)
      .component(
        "Datepicker",
        defineAsyncComponent(() => import("@vuepic/vue-datepicker"))
      )
      .mount(el);
  },
});

InertiaProgress.init({ color: "#4B5563" });
